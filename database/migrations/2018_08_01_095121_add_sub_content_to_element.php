<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubContentToElement extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->string('sub_content')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->dropColumn('sub_content');
        });
    }
}
