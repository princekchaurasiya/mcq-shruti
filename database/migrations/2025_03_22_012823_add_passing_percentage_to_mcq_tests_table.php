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
    Schema::table('mcq_tests', function (Blueprint $table) {
        $table->integer('passing_percentage')->after('duration_minutes')->default(0);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('mcq_tests', function (Blueprint $table) {
        $table->dropColumn('passing_percentage');
    });
}

};
