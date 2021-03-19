<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Element;
use App\Models\Role;
use Tests\PassportTestCase;

class AnswerControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    public function test_get_list()
    {
        Answer::factory()->count(10)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/answers');
        static::assertStatus($response, 200);
        $response->assertJsonCount(10);
        $response->assertJsonStructure(
            [
                '*' => [
                    'elementId',
                    'value',
                ],
            ]
        );
    }

    public function test_get_answer()
    {
        $answer   = Answer::factory()->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/answers/'.$answer->element_id);
        static::assertStatus($response, 200);

        $response->assertJsonStructure(
            [
                'value',
            ]
        );
    }

    public function test_create_answer()
    {
        $element  = Element::factory()->create();
        $response = $this->putJson('/api/answers/'.$element->id, [
            'value' => ['text' => 'Das ist ein Test'],
        ]);
        static::assertStatus($response, 204);
        $response = $this->getJson('/api/answers/'.$element->id);
        static::assertStatus($response, 200);
        $response->assertJson([
            'value' => ['text' => 'Das ist ein Test'],
        ]);
    }

    public function test_start_fresh()
    {
        Answer::factory()->count(10)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/answers');
        static::assertStatus($response, 200);
        $response->assertJsonCount(10);
        $response = $this->postJson('/api/answers/reset');
        static::assertStatus($response, 204);
        $response = $this->getJson('/api/answers');
        static::assertStatus($response, 200);
        $response->assertJsonCount(0);
    }
}
