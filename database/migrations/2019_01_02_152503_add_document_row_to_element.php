<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentRowToElement extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->integer('document_row')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->dropColumn('document_row');
        });
    }
}
