<?php
namespace App\Http\Controllers;


use App\Avatar;
use App\AvatarOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Mockery\CountValidator\Exception;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\ImageManager;

class AvatarController extends Controller
{

    /**
     * A instance of Image Manager.
     *
     * @var \Intervention\Image\ImageManager
     */
    protected $imgMngr;

    /**
     * A filesystem instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    /**
     * Create a new AvatarController instance.
     * initialize image manager and disk.
     * @param  ImageManager $imgMngr
     * @param  Storage $storage
     */
    public function __construct(ImageManager $imgMngr, Storage $storage)
    {
        $this->imgMngr = $imgMngr->configure(config('image'));
        $this->disk = $storage::disk('avatar');
    }

    /**
     * Get the Avatar file
     * @param  string $emailHash email hash parameter is an MD5 hash
     * of the avatar's owner email address.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getImage($emailHash, Request $request)
    {
        $avtr = Avatar::find($emailHash);
        $size = $request->get('s', 80);
        $ext = $request->header('ACCEPT');
        $defImg = $request->get('d');
        $attr = [
            'd' => $defImg,
            's' => $size,
            'headers_accept' => $ext
        ];
        $allowedMT = implode(',', Avatar::$allowedMimeTypes);
        $rules = [
            's' => 'int',
            'headers_accept' => 'in:' . $allowedMT,
        ];
        if (!$avtr) {
            $rules['d'] = 'string|defaults';
            $validator = $this->makeValidator($attr, $rules);
            if ($validator->fails()) {
                return response()->json($validator->failed());
            }
            $rules['d'] = 'v404|url_encoded|blank|hex_color';
            $validator = $this->makeValidator($attr, $rules);
            $errorRules = $validator->errorsArray();
            if (!isset($errorRules['v404'])) {
                return response()->json($validator->getCustomMessages()['v404'], 404);
            } else {
                if (!isset($errorRules['url_encoded'])) {
                    $url = $defImg;
                    $img = $this->imgMngr->cache(function ($image) use ($url, $ext) {
                        $image->make($url)->encode($ext);
                    }, config('imagecache.lifetime_callback'), true);
                } else {
                    if (!isset($errorRules['blank'])) {
                        $img = $this->imgMngr->cache(function ($image) use ($size) {
                            $image->canvas($size, $size)->encode('gif');
                        }, config('imagecache.lifetime_callback'), true);
                    } else {
                        if (!isset($errorRules['hex_color'])) {
                            $hexColor = $defImg;
                            $img = $this->imgMngr->cache(function ($image) use ($size, $hexColor, $ext) {
                                $image->canvas($size, $size, $hexColor)->encode($ext);
                            }, config('imagecache.lifetime_callback'), true);
                        }
                    }
                }
            }
        } else {
            $validator = $this->makeValidator($attr, $rules);
            if ($validator->fails()) {
                return response()->json($validator->failed());
            }
            $img = $this->imgMngr->cache(function ($image) use ($avtr, $ext) {
                $image->make($this->disk->get($avtr->image_file))->encode($ext);
            }, config('imagecache.lifetime_callback'), true);
        }
        return response($img->response(), 200);
    }

    /**
     * Register a new avatar. Send the image in base64 encoding + the metadata fields.
     * Send an email with a validation token
     * @param  string $emailHash email hash parameter is an MD5 hash
     * of the avatar's owner email address.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function newImage($emailHash, Request $request)
    {
        $cT = $request->header('CONTENT-TYPE');
        $email = $request->request->get('email') ?? '';
        $mimeType = $request->request->get('mime-type') ?? '';
        $image = $request->request->get('image') ?? '';
        $allowedMT = implode(',', Avatar::$allowedMimeTypes);
        $attr = [
            'content_type' => $cT,
            'email' => $email,
            'mime_type' => $mimeType,
            'image' => $image,
        ];
        $rules = [
            'content_type' => 'required|in:' . $allowedMT,
            'email' => 'required|email',
            'mime_type' => 'required|in:' . $allowedMT,
        ];
        $validator = $this->makeValidator($attr, $rules);
        if ($validator->fails()) {
            $failedRules = $validator->failedVerbose();
            if (isset($failedRules['email']['Required'])) {
                return response()->json($failedRules['email']['Required'], 400);
            }
            return response()->json($failedRules);
        }
        $ext = getExtension($mimeType);
        $imgPath = $this->getImagePath($emailHash, $ext);
        $this->disk->put($imgPath, $image);
        $avatar = Avatar::find($emailHash);
        if (!$avatar) {
            $avatar = Avatar::create([
                'email_hash' => $emailHash,
                'email' => $email,
                'image_file' => $imgPath
            ]);
            $avatar->delete();
        }
        $code = AvatarOperation::generateCode();
        $avatarOperation = new AvatarOperation([
            'method' => AvatarOperation::METHOD_POST,
            'image_file' => $imgPath,
            'code' => $code
        ]);
        $avatar->operations()->save($avatarOperation);
        if (app()->environment('production')) {
            $url = route('confirmation', ['code' => $code]);
            $from = config('mail.from.address');
            $name = config('mail.from.name');
            $to = $email;
            Mail::send('email.confirmation.register', ['url' => $url], function ($message) use ($from, $name, $to) {
                $message->from($from, $name);
                $message->to($to, $to)
                    ->subject('Confirmation code');
            });
        }
        return response('', 201);
    }

    /**
     * Deletes the uploaded file, it will also send an email
     * with a link que te confirm the operation
     * @param  string $emailHash email hash parameter is an MD5 hash
     * of the avatar's owner email address.
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function deleteImage($emailHash)
    {
        $avtr = Avatar::find($emailHash);
        $attr = [
            'email' => $avtr->email ?? ''
        ];
        $rules = [
            'email' => 'required'
        ];
        $validator = $this->makeValidator($attr, $rules);
        if ($validator->fails()) {
            return response()->json($validator->failed(), 404);
        }
        $code = AvatarOperation::generateCode();
        $avatarOperation = new AvatarOperation([
            'method' => AvatarOperation::METHOD_DELETE,
            'image_file' => $avtr->image_file,
            'code' => $code
        ]);
        $avtr->operations()->save($avatarOperation);
        if (app()->environment('production')) {
            $url = route('confirmation', ['code' => $code]);
            $from = config('mail.from.address');
            $name = config('mail.from.name');
            $to = $avtr->email;
            Mail::send('email.confirmation.register', ['url' => $url], function ($message) use ($from, $name, $to) {
                $message->from($from, $name);
                $message->to($to, $to)
                    ->subject('Confirmation code');
            });
        }
        return response()->json([
            'email' => $avtr->email
        ]);
    }

    /**
     * Endpoint for the user to validate de received code after uploading
     * an image or requesting deletion of one
     * @param  string $code
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function confirmation($code)
    {
        $avtrOp = AvatarOperation::find($code);
        $attr = [
            'email' => $avtrOp->avatar->email ?? ''
        ];
        $rules = [
            'email' => 'required'
        ];
        $validator = $this->makeValidator($attr, $rules);
        if ($validator->fails()) {
            return response()->json($validator->failed(), 404);
        }
        switch ($avtrOp->method) {
            case AvatarOperation::METHOD_DELETE:
                if ($this->disk->has($avtrOp->image_file)) {
                    $this->disk->delete($avtrOp->image_file);
                }
                $avtrOp->avatar()->forceDelete();
                $avtrOp->delete();
                break;
            case AvatarOperation::METHOD_POST:
                $this->disk->setVisibility($avtrOp->image_file, 'public');
                $avtrOp->avatar()->update([
                    'image_file' => $avtrOp->image_file
                ]);
                $avtrOp->avatar()->restore();
                $avtrOp->delete();
                break;
        }
        return response()->json([
            'email' => $avtrOp->avatar->email
        ]);
    }

    /**
     * Get image path
     * @param  string $emailHash
     * @param  string $ext extension
     * @return string
     */
    private function getImagePath($emailHash, $ext)
    {
        return $emailHash . DIRECTORY_SEPARATOR . uniqid('img_') . $ext;
    }
}