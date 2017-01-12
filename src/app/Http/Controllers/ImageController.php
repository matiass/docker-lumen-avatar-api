<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ImageController extends Controller
{
    /**
     * A instance of Image Manager.
     *
     * @var \Intervention\Image\ImageManager
     */
    protected $imgMngr;

    /**
     * initialize image manager and disk
     */
    public function __construct()
    {
        $this->imgMngr = new ImageManager(config('image'));
    }

    /**
     * Get the avatar image
     * @param  string $filename
     * @return \Illuminate\Http\Response
     */
    public function getAvatar($filename)
    {
        return $this->imgMngr->make(Storage::disk('avatar')->get($filename))->response();
    }

    /**
     * Get the test image
     * @param  string $filename
     * @return \Illuminate\Http\Response
     */
    public function getTests($filename)
    {
        return $this->imgMngr->make(Storage::disk('mock')->get($filename))->response();
    }
}