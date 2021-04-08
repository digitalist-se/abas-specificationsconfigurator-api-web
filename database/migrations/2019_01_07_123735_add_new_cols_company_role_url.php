<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColsCompanyRoleUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_company', 100)->nullable();
            $table->string('user_role', 100)->nullable();
            $table->string('user_url', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'user_company')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_company');
            });
        }

        if (Schema::hasColumn('users', 'user_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_role');
            });
        }

        if (Schema::hasColumn('users', 'user_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_url');
            });
        }
    }
}
