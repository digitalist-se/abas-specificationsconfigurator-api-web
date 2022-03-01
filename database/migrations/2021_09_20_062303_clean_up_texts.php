<?php

use App\Models\Text;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['print_description', 'print_name']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['print_description']);
        });

        Schema::table('elements', function (Blueprint $table) {
            $table->dropColumn(['print']);
        });

        Text::truncate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->string('print_name')
                ->after('name')
                ->nullable();
            $table->string('print_description')
                ->after('description')
                ->nullable();
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->string('print_description')
                ->after('description')
                ->nullable();
        });

        Schema::table('elements', function (Blueprint $table) {
            $table->string('print')
                ->after('content')
                ->nullable();
        });

        Text::truncate();
    }
};
