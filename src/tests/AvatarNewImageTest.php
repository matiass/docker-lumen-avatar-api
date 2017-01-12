<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Avatar;
use Illuminate\Support\Facades\Storage;

class AvatarNewImageTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function testEmail()
    {
    }

    public function test201()
    {
        $avatar = factory(Avatar::class)->create();
        $mimeType = Avatar::$allowedMimeTypes[array_rand(Avatar::$allowedMimeTypes)];
        $file = Storage::disk('mock')->get('1.jpg');
        $data = [
            'email' => $avatar->email,
            'image' => base64_decode($file),
            'mime-type' => $mimeType
        ];
        $this->call('POST', "avatars/$avatar->email_hash", $data, [], [], ['CONTENT_TYPE' => $mimeType]);
        $this->assertResponseStatus('201');
        // check existence of file
        // check existentce of avatar ops
    }

    public function test400()
    {
        $avatar = factory(Avatar::class)->create();
        $emailHash = $avatar->email_hash;
        $avatar->forceDelete();
        $mimeType = Avatar::$allowedMimeTypes[array_rand(Avatar::$allowedMimeTypes)];
        $response = $this->call('POST', "avatars/$emailHash", [], [], [], ['CONTENT_TYPE' => $mimeType]);
        $this->assertResponseStatus('400');
        $this->assertJson(json_encode(config('validator.rules')['email.required']), $response->getContent());
    }
}
