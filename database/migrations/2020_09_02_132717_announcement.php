<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Announcement extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcement',function (Blueprint $table) {
            $table->integer('inst');
            $table->integer('id');
            $table->primary(['inst','id']);
            $table->Text('title')->nullable();   
            $table->Text('body')->nullable();   
            $table->char('top_yn',1)->nullable();
            $table->char('create_id',20)->nullable();
            $table->char('mod_id',20)->nullable();
            $table->datetime('ctime')->nullable();
            $table->datetime('mtime')->nullable();

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
        Schema::dropIfExists('announcement');
    }
}
