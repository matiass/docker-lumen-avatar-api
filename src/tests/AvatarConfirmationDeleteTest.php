<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Avatar;
use App\AvatarOperation;
use Illuminate\Support\Facades\Storage;

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
        $orgFile = $avtrOp->image_file;
        $bkpFile = $avtrOp->image_file.'.bkp';
        Storage::disk('mock')->copy($orgFile, $bkpFile);
        $response = $this->call('GET', "confirmation/$avtrOp->code", [], [], [], []);
        $this->assertResponseOk();
        $this->assertJson($response->getContent(), json_encode(['email' => $email]));
        $avtrOp = AvatarOperation::find($code);
        $avtr = Avatar::find($emailHash);
        $this->assertNull($avtrOp);
        $this->assertNull($avtr);
        Storage::disk('mock')->move($bkpFile, $orgFile);
        Storage::disk('mock')->setVisibility($orgFile, 'public');
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
