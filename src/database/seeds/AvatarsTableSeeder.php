<?php

use Illuminate\Database\Seeder;
use App\Avatar;

class AvatarsTableSeeder extends Seeder
{
    /**
     * Run the avatars table seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Avatar::class, 10);
    }
}