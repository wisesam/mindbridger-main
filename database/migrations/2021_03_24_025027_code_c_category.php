<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CodeCCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_c_category',function (Blueprint $table){
            $table->integer('inst');
            $table->integer('code'); 
            $table->integer('c_genre'); 
            $table->integer('c_lang'); 
            $table->primary(['inst','code','c_genre','c_lang']); 
            $table->string('name')->nullable();
            $table->char('use_yn',1)->nullable()->default('Y');

            $table->foreign(['inst','c_genre', 'c_lang'])
                ->references(['inst','code', 'c_lang'])
                ->on('code_c_genre');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('code_c_category');
    }
}
