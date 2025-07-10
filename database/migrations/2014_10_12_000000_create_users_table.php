<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('inst'); // Institution ID
            $table->char('id', 20);  // Custom user ID

            $table->string('name')->nullable();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();

            $table->char('utype', 2)->nullable();         // User type
            $table->integer('code_c_utype')->nullable();  // User type code
            $table->char('ustatus', 2)->nullable();       // User status
            $table->time('last_login')->nullable();       // Last login time

            $table->timestamps();

            $table->primary(['inst', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
