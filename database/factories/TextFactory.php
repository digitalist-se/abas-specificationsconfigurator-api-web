<?php

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Text;
use Illuminate\Database\Eloquent\Factories\Factory;

class TextFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Text::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'key'         => $this->faker->uuid,
            'locale'      => Locale::DE,
            'value'       => $this->faker->text,
            'description' => '',
            'public'      => 1,
        ];
    }
}
