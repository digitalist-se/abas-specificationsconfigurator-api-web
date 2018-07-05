<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Element;
use App\Models\Role;
use Tests\PassportTestCase;

class AnswerControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    public function testGetList()
    {
        factory(Answer::class, 10)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/answers');
        $this->assertStatus($response, 200);
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

    public function testGetAnswer()
    {
        $answer   = factory(Answer::class)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/answers/'.$answer->element_id);
        $this->assertStatus($response, 200);

        $response->assertJsonStructure(
            [
                'value',
            ]
        );
    }

    public function testCreateAnswer()
    {
        $element  = factory(Element::class)->create();
        $response = $this->putJson('/api/answers/'.$element->id, [
            'value' => ['text' => 'Das ist ein Test'],
        ]);
        $this->assertStatus($response, 204);
        $response = $this->getJson('/api/answers/'.$element->id);
        $this->assertStatus($response, 200);
        $response->assertJson([
            'value' => ['text' => 'Das ist ein Test'],
        ]);
    }

    public function testStartFresh()
    {
        factory(Answer::class, 10)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/answers');
        $this->assertStatus($response, 200);
        $response->assertJsonCount(10);
        $response = $this->postJson('/api/answers/reset');
        $this->assertStatus($response, 204);
        $response = $this->getJson('/api/answers');
        $this->assertStatus($response, 200);
        $response->assertJsonCount(0);
    }
}
