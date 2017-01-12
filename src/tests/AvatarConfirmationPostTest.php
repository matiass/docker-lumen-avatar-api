<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Avatar;
use App\AvatarOperation;

class AvatarConfirmationPostTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function test200()
    {
        factory(Avatar::class)->create();
        $avtrOp = factory(AvatarOperation::class)->create(['method' => AvatarOperation::METHOD_POST]);
        $response = $this->call('GET', "confirmation/$avtrOp->code", [], [], [], []);
        $this->assertResponseOk();
        $this->assertJson($response->getContent(), json_encode(['email' => $avtrOp->avatar->email]));
        $this->assertEquals($avtrOp->image_file, $avtrOp->avatar->image_file);
    }

    public function test404()
    {
        factory(Avatar::class)->create();
        $avtrOp = factory(AvatarOperation::class)->create(['method' => AvatarOperation::METHOD_POST]);
        $code = $avtrOp->code;
        $avtrOp->avatar->forceDelete();
        $response = $this->call('GET', "confirmation/$code", [], [], [], []);
        $this->assertResponseStatus('404');
        $this->assertJson($response->getContent(), json_encode(config('validator.rules')['email.required']));
    }
}
