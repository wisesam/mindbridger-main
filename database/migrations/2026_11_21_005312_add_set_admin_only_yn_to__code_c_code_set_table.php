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
        Schema::table('code_c_code_set', function (Blueprint $table) {
            $table->char('set_admin_only_yn', 1)
                  ->default('N')
                  ->after('use_yn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('code_c_code_set', function (Blueprint $table) {
            $table->dropColumn(['set_admin_only_yn', 'disk_quota']);
        });
    }
};
