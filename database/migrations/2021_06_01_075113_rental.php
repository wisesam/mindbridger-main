<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Rental extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental',function (Blueprint $table) {
            $table->integer('inst');
            $table->integer('id');
            $table->primary(['inst','id']);
            $table->integer('bcid');
            $table->char('uid',20); 
            $table->datetime('rent_date')->nullable();
            $table->datetime('due_date')->nullable();
            $table->datetime('return_date')->nullable();
            $table->Integer('c_rent_status')->nullable();
            $table->Text('rcomment')->nullable();                    
         
            $table->foreign(['inst','bcid'])
                ->references(['inst','id'])
                ->on('book_copy');
            
            $table->foreign(['inst','uid'])
                ->references(['inst','id'])
                ->on('users');
            
            $table->foreign(['inst','c_rent_status'])
                ->references(['inst','code'])
                ->on('code_c_rent_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rental');
    }
}