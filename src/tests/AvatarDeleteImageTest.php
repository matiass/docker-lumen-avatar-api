<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Avatar;

class AvatarDeleteImageTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function testEmail()
    {

    }

    public function test200()
    {
        $avatar = factory(Avatar::class)->create();
        $response = $this->call('DELETE', "avatars/$avatar->email_hash", [], [], [], []);
        $this->assertResponseOk();
        $this->assertJson($response->getContent(), json_encode(['email' => $avatar->email]));
    }

    public function test404()
    {
        $avatar = factory(Avatar::class)->create();
        $emailHash = $avatar->email_hash;
        $avatar->forceDelete();
        $response = $this->call('DELETE', "avatars/$emailHash", [], [], [], []);
        $this->assertJson($response->getContent(), json_encode(config('validator.rules')['email.required']));
        $this->assertResponseStatus('404');
    }
}
