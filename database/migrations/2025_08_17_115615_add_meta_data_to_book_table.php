<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book', function (Blueprint $table) {
            $table->longText('meta_data')->nullable()->after('auto_toc');
        });
    }

    public function down(): void
    {
        Schema::table('book', function (Blueprint $table) {
            $table->dropColumn('meta_data'); // JSON
        });
    }
};
