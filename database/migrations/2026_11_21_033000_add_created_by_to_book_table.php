<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToBookTable extends Migration
{
    public function up()
    {
        Schema::table('book', function (Blueprint $table) {
            // The authenticated users.id is stored as a string; legacy rows remain NULL.
            $table->string('created_by', 191)->nullable()->after('creator');
            $table->index(['inst', 'created_by'], 'book_inst_created_by_idx');
        });
    }

    public function down()
    {
        Schema::table('book', function (Blueprint $table) {
            $table->dropIndex('book_inst_created_by_idx');
            $table->dropColumn('created_by');
        });
    }
}
