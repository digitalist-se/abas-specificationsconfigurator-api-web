<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChoiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('choice_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->boolean('multiple');
            $table->string('type')->unique();
            $table->boolean('tiles')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('choice_types');
    }
}
