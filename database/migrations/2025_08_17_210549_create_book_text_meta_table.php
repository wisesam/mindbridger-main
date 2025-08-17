<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_text_meta', function (Blueprint $table) {
            $table->id();

            $table->integer('inst');
            $table->integer('book_id');

            $table->longText('meta')->nullable();
            $table->longText('text')->nullable();

            // Composite foreign key: inst + book_id → book(inst, id)
            $table->foreign(['inst', 'book_id'])
                  ->references(['inst', 'id'])
                  ->on('book')
                  ->onDelete('cascade');

            // Standard Laravel timestamps (snake_case)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_text_meta');
    }
};
