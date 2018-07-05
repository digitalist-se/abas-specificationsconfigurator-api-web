<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElementsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->string('type'); // type of element
            $table->integer('sort');

            // text, headline or question
            $table->text('content');
            $table->string('print')->nullable();

            // choice type that define choice values
            $table->uuid('choice_type_id')->nullable();

            // slider values:
            $table->integer('steps')->nullable();
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();

            // parent node
            $table->uuid('section_id');

            // layout params
            $table->boolean('layout_two_columns')->nullable();
            $table->text('illustration_states')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('elements');
    }
}
