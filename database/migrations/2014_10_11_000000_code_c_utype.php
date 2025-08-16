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
        Schema::create('code_c_utype', function (Blueprint $table) {
            $table->integer('inst');
            $table->integer('code');
            $table->integer('c_lang');
            $table->primary(['inst', 'code', 'c_lang']);

            $table->string('name', 255)->nullable();
            $table->integer('max_book')->nullable();
            $table->integer('max_book_rent_days')->nullable();
            $table->integer('max_extend_times')->nullable();
            $table->char('use_yn', 1)->nullable()->default('Y');
            $table->char('w2_utype', 1)->nullable();
            $table->char('default_utype_yn', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('code_c_utype');
    }
};
