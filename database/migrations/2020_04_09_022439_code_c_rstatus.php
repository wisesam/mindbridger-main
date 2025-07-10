<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CodeCRstatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_c_rstatus', function (Blueprint $table){
            $table->integer('inst'); 
            $table->integer('code'); 
            $table->integer('c_lang'); 
            $table->primary(['inst','code','c_lang']); 
            $table->string('name')->nullable();
            $table->char('use_yn',1)->nullable()->default('Y');
            $table->char('available_yn',1)->nullable()->default('N');
            $table->char('direct_change_yn',1)->nullable()->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('code_c_rstatus');
    }
}
