
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('code_c_utype', function (Blueprint $table) {
            $table->char('create_yn', 1)
                  ->default('N')
                  ->after('default_utype_yn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('code_c_utype', function (Blueprint $table) {
            $table->dropColumn(['create_yn']);
        });
    }
};
