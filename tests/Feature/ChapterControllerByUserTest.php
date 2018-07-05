<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\PassportTestCase;

class ChapterControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    public function testGetList()
    {
        $response = $this->getJson('/api/chapters');
        $this->assertStatus($response, 200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
            ],
        ]);
        $chapters = $response->json();
        $this->assertNotEmpty($chapters);
        foreach ($chapters as $chapter) {
            $this->assertTextWithKeyIsGiven($chapter, 'name');
        }
    }
}
