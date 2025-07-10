<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class About extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('about',function (Blueprint $table) {
            $table->integer('inst');
            $table->primary('inst');
            $table->Text('header')->nullable();   
            $table->Text('footer')->nullable();   
            $table->Text('about_txt')->nullable();   
            $table->Text('open_hours')->nullable();   
            $table->String('country')->nullable();   
            $table->String('state')->nullable();   
            $table->String('city')->nullable();   
            $table->Text('address')->nullable();   
            $table->String('zip')->nullable();   
            $table->String('phone')->nullable();   
            $table->String('fax')->nullable();   
            $table->String('email')->nullable();
            $table->Text('contact_info')->nullable();

            $table->foreign('inst')
                ->references('no')
                ->on('vwmldbm_inst');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('about');
    }
}
