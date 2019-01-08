<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;

class AddUniqueToAnswers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::transaction(function () {
            if (!App::environment('testing')) {
                DB::table('answers')->selectRaw('GROUP_CONCAT(id ORDER BY updated_at DESC SEPARATOR \',\') as ids')
                    ->groupBy('element_id', 'user_id')
                    ->havingRaw('count(1) > ?', [1])->get()->each(function ($result) {
                        $ids = explode(',', $result->ids);
                        if (count($ids) > 1) {
                            unset($ids[0]); // not delete first;
                            DB::table('answers')->whereIn('id', $ids)->delete();
                        }
                    });
            }
            Schema::table('answers', function (Blueprint $table) {
                $table->unique(['user_id', 'element_id']);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'element_id']);
        });
    }
}
