<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SplitNameInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('contact_first_name')->nullable();
            $table->string('contact_last_name')->nullable();
        });
        $this->splitNameToFirstAndLastName();
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'contact']);
        });
    }

    protected function splitNameToFirstAndLastName() {
        User::query()
            ->orderBy('id')
            ->chunk(20,
                fn ($users) => $users->each(
                    fn ($user) => $this->migrateNames($user)
                )
            );
    }

    protected function migrateNames(User $user) {
        list($firstName, $lastName) = $this->parseNames($user->name);
        list($contactFirstName, $contactLastName) = $this->parseNames($user->contact);
        $user->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'contact_first_name' => $contactFirstName,
            'contact_last_name'  => $contactLastName,
        ]);
    }

    /**
     * @return string[]
     */
    public function parseNames(?string $name): array
    {
        if ($name == null) {
            return [
                null,
                null,
            ];
        }
        $nameParts = explode(' ', $name);

        if (count($nameParts) < 2) {
            return [
                $name,
                null,
            ];
        }
        $lastName = array_pop($nameParts);
        return [
            implode(' ', $nameParts),
            $lastName,
        ];
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
            $table->renameColumn('contact_first_name', 'contact');
            $table->dropColumn(['last_name', 'contact_last_name']);
        });
    }
}
