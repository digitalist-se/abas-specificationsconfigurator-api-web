<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('role');
            $table->rememberToken();
            $table->timestamps();

            $table->string('sex')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('street')->nullable();
            $table->string('additional_street_info')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('city')->nullable();
            $table->string('contact')->nullable();
            $table->string('contact_function')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
