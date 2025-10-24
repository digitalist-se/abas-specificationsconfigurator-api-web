<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('salesforces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('user_id')->unique();
            $table->string('lead_id')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('task_id')->nullable();
            $table->string('content_version_id')->nullable();
            $table->string('content_document_id')->nullable();
            $table->string('content_document_link_id')->nullable();
            $table->timestamps();
        });

        Schema::table('salesforces', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salesforces');
    }
};
