<?php

namespace Spatie\Comments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Comments\Tests\TestSupport\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'first_name' => $this->faker->firstName,
            'email' => $this->faker->email,
            'password' => bcrypt($this->faker->sentence),
        ];
    }
}
