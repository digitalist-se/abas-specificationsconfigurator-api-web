<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('print_name');
            $table->string('slug_name')->unique();
            $table->integer('sort');
            $table->text('description')->nullable();
            $table->string('print_description')->nullable();
            $table->boolean('visible');
            $table->text('illustration_states')->nullable();
            $table->timestamps();
        });
        Schema::create('sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chapter_id');
            $table->string('headline');
            $table->string('slug_name')->unique();
            $table->boolean('has_headline')->nullable();
            $table->string('description');
            $table->string('print_description')->nullable();
            $table->integer('sort');
            $table->text('illustration_states')->nullable();
            $table->timestamps();
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('sections');
        Schema::dropIfExists('chapters');
    }
};
