<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\PassportTestCase;

class ChapterControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    public function test_get_list()
    {
        $response = $this->getJson('/api/chapters');
        static::assertStatus($response, 200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
            ],
        ]);
        $chapters = $response->json();
        static::assertNotEmpty($chapters);
        foreach ($chapters as $chapter) {
            $this->assertTextWithKeyIsGiven($chapter, 'name');
        }
    }
}
