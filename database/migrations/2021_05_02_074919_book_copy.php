<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookCopy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_copy',function (Blueprint $table){
            $table->integer('inst');
            $table->integer('id');
            $table->primary(['inst','id']);
            $table->integer('bid');
            $table->string('barcode',128)->nullable();
            $table->string('call_no',128)->nullable();
            $table->text('location')->nullable();
            $table->integer('c_rstatus')->nullable();
            $table->text('comment')->nullable();

            $table->foreign(['inst','c_rstatus'])
                    ->references(['inst','code'])
                    ->on('code_c_rstatus');

            $table->foreign(['inst','bid'])
                    ->references(['inst','id'])
                    ->on('book');

            $table->unique(['inst','call_no']);
            $table->unique(['inst','barcode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_copy');
    }
}
