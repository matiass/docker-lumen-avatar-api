<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAvatarsOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatars_operations', function (Blueprint $table) {
            $table->string('code', 32)->unique()->primary();
            $table->string('email_hash', 32);
            $table->foreign('email_hash')->references('email_hash')
                ->on('avatars')
                ->onDelete('cascade');
            $table->tinyInteger('method');
            $table->string('image_file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatars_operations');
    }
}
