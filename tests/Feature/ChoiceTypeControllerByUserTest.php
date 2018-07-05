<?php

namespace Tests\Feature;

use Tests\PassportTestCase;

class ChoiceTypeControllerByUserTest extends PassportTestCase
{
    public function testGetListOfChoiceTypes()
    {
        $response = $this->getJson('/api/choice-types');
        $this->assertStatus($response, 200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'type',
                'multiple',
                'tiles',
                'options' => [
                    '*' => [
                        'type',
                        'text',
                        'value',
                    ],
                ],
            ],
        ]);
    }
}
