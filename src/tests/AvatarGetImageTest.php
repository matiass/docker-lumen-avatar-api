<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

use App\Avatar;

class AvatarGetImageTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    /**
     * Get the Avatar file by emailhash
     *
     * @return void
     */
    public function testEmailHash200()
    {
        $avatar = factory(Avatar::class)->create();
        $mimeType = Avatar::$allowedMimeTypes[array_rand(Avatar::$allowedMimeTypes)];
        $response = $this->call('GET', "avatars/$avatar->email_hash", [], [], [], ['HTTP_ACCEPT' => $mimeType]);
        $this->assertResponseOk();
        //compare image
    }

    /**
     * Return status 404
     *
     * @return void
     */
    public function testDefaultImage404()
    {
        $avatar = factory(Avatar::class)->create();
        $emailHash = $avatar->email_hash;
        $avatar->forceDelete();
        $mimeType = Avatar::$allowedMimeTypes[array_rand(Avatar::$allowedMimeTypes)];
        $response = $this->call('GET', "avatars/$emailHash?d=404", [], [], [], ['HTTP_ACCEPT' => $mimeType]);
        $this->assertResponseStatus('404');
        $this->assertJson(json_encode(config('validator.rules.404')), $response->getContent());
    }

    /**
     * Return image in specific hex color
     *
     * @return void
     */
    public function testDefaultHexColor()
    {
        $avatar = factory(Avatar::class)->create();
        $emailHash = $avatar->email_hash;
        $avatar->forceDelete();
        $mimeType = Avatar::$allowedMimeTypes[array_rand(Avatar::$allowedMimeTypes)];
        $response = $this->call('GET', "avatars/$emailHash?d=%23f6546a", [], [], [], ['HTTP_ACCEPT' => $mimeType]);
        $this->assertResponseStatus('200');
        //compare image
    }

    /**
     * Return an image loaded from an external url
     *
     * @return void
     */
    public function testDefaultUrl()
    {
        $avatar = factory(Avatar::class)->create();
        $emailHash = $avatar->email_hash;
        $avatar->forceDelete();
        $mimeType = Avatar::$allowedMimeTypes[array_rand(Avatar::$allowedMimeTypes)];
        $url = urlencode("http://www.w3schools.com/css/trolltunga.jpg");
        $response = $this->call('GET', "avatars/$emailHash?d=$url", [], [], [], ['HTTP_ACCEPT' => $mimeType]);
        $this->assertResponseStatus('200');
        //compare image
    }

    /**
     * Return a transparent gif
     *
     * @return void
     */
    public function testDefaultBlank()
    {
        $avatar = factory(Avatar::class)->create();
        $emailHash = $avatar->email_hash;
        $avatar->forceDelete();
        $mimeType = Avatar::$allowedMimeTypes[array_rand(Avatar::$allowedMimeTypes)];
        $response = $this->call('GET', "avatars/$emailHash?d=blank", [], [], [], ['HTTP_ACCEPT' => $mimeType]);
        $this->assertResponseStatus('200');
        //check ext
        //compare image
    }
}
