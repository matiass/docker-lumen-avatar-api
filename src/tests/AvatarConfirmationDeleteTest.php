<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Avatar;
use App\AvatarOperation;

class AvatarConfirmationDeleteTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function testDelete200()
    {
        factory(Avatar::class)->create();
        $avtrOp = factory(AvatarOperation::class)->create(['method' => AvatarOperation::METHOD_DELETE]);
        $email = $avtrOp->avatar->email;
        $emailHash = $avtrOp->avatar->email_hash;
        $code = $avtrOp->code;
        $response = $this->call('GET', "confirmation/$avtrOp->code", [], [], [], []);
        $this->assertResponseOk();
        $this->assertJson($response->getContent(), json_encode(['email' => $email]));
        $avtrOp = AvatarOperation::find($code);
        $avtr = Avatar::find($emailHash);
        $this->assertNull($avtrOp);
        $this->assertNull($avtr);
    }

    public function test400()
    {
        factory(Avatar::class)->create();
        $avtrOp = factory(AvatarOperation::class)->create(['method' => AvatarOperation::METHOD_DELETE]);
        $code = $avtrOp->code;
        $avtrOp->avatar->forceDelete();
        $response = $this->call('GET', "confirmation/$code", [], [], [], []);
        $this->assertResponseStatus('404');
        $this->assertJson($response->getContent(), json_encode(config('validator.rules')['email.required']));
    }
}
