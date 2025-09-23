<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_review', function (Blueprint $table) {
            $table->id();

            $table->integer('inst');
            $table->string('user_id');
            $table->integer('book_id');

            $table->longText('review')->nullable(); // public or personal (use CKeditor)
            $table->longText('comment')->nullable(); // personal (use CKeditor)

            $table->Char('review_share_yn',1)->nullable()->nullable()->default('N');
            $table->Char('review_public_share_yn',1)->nullable()->nullable()->default('N'); // the book should be public otherwise no effect

            $table->foreign(['inst', 'user_id'])
                  ->references(['inst', 'id'])
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign(['inst', 'book_id'])
                  ->references(['inst', 'id'])
                  ->on('book')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_review');
    }
};
