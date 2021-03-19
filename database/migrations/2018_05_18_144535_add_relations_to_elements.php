<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationsToElements extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->foreign('choice_type_id')
                    ->references('id')->on('choice_types');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
        });
        Schema::table('options', function (Blueprint $table) {
            $table->foreign('choice_type_id')
                    ->references('id')->on('choice_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->dropForeign(['choice_type_id']);
            $table->dropForeign(['section_id']);
        });
        Schema::table('options', function (Blueprint $table) {
            $table->dropForeign(['choice_type_id']);
        });
    }
}
