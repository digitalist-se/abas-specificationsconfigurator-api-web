<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Element;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $element = Element::factory()->create();

        return [
            'element_id' => $element,
            'value'      => $this->faker->text(),
        ];
    }
}
