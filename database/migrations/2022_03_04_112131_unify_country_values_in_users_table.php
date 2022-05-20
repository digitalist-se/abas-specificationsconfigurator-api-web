<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')
            ->select('country')
            ->distinct()
            ->get()
        ->pluck('country')
        ->each(function ($countryValue) {
            $newCountryValue = \App\Models\Country::findMatch($countryValue)->value;
            if ($countryValue != $newCountryValue) {
                DB::table('users')
                    ->where('country', '=', $countryValue)
                    ->update([
                        'country' => $newCountryValue,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
