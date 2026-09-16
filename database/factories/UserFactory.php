<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        /*
         | La factory livree par Laravel posait un champ `name`, que la table
         | `users` n'a pas : un compte Ultra-book porte un login (qui est
         | aussi son sous-domaine), un nom et un prenom separes.
         */
        return [
            'login' => Str::slug(fake()->unique()->userName()),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'brand' => 'ub',
            'locale' => 'fr',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
