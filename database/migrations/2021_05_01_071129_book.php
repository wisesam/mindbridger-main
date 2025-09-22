<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Book extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book',function (Blueprint $table) {
            $table->integer('inst');
            $table->integer('id');
            $table->primary(['inst','id']);
            $table->integer('rid');
            $table->Text('title')->nullable();
            $table->String('author')->nullable();
            $table->String('publisher')->nullable();
            $table->Date('pub_date')->nullable();
            $table->Integer('c_lang')->nullable();
            $table->String('isbn',128)->nullable();
            $table->String('eisbn',128)->nullable();
            $table->Date('reg_date')->nullable();
            $table->String('price',128)->nullable();
            $table->String('cover_image')->nullable();
            $table->String('cover_image_url')->nullable();
            $table->Text('keywords')->nullable();
            $table->Integer('c_rtype')->nullable();
            $table->Integer('c_genre')->nullable();
            $table->Integer('c_grade')->nullable();
            $table->Integer('c_category')->nullable();
            $table->Integer('c_category2')->nullable();
            $table->Char('e_resource_yn',1)->nullable();
            $table->Text('abstract')->nullable();

            $table->Text('files')->nullable();
            $table->Text('rfiles')->nullable();
            $table->Char('rdonly_pdf_yn',1)->nullable()->default('Y');
            $table->Char('rdonly_video_yn',1)->nullable()->default('Y');
            $table->Char('hide_yn',1)->nullable()->nullable()->default('N');;
            $table->Char('hide_from_guest_yn',1)->nullable()->nullable()->default('N');;
            $table->Char('e_res_af_login_yn',1)->nullable()->default('Y');
            $table->longText('desc')->nullable();
            $table->Text('url')->nullable();

            $table->foreign(['inst','c_rtype'])
                    ->references(['inst','code'])
                    ->on('code_c_rtype');
            
            $table->foreign(['inst','c_genre'])
                    ->references(['inst','code'])
                    ->on('code_c_genre');

            $table->foreign(['inst','c_grade'])
                    ->references(['inst','code'])
                    ->on('code_c_grade');
            
            $table->foreign(['inst','c_category'])
                    ->references(['inst','code'])
                    ->on('code_c_category');

            $table->foreign(['inst','c_category2'])
                    ->references(['inst','code'])
                    ->on('code_c_category2');

            $table->unique(['inst','isbn']); // SJH, composite Key
            $table->unique(['inst','eisbn']); // SJH, composite Key
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book');
    }
}