<?php

namespace Database\Factories;

use App\Models\FrontendUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<FrontendUser>
 */
class FrontendUserFactory extends Factory
{
    /**
     * The model that this factory corresponds to.
     *
     * @var class-string<FrontendUser>
     */
    protected $model = FrontendUser::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'United States',
            'customer_type' => 'simple',
            'orders_count' => 0,
            'total_spent' => 0.00,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a special VIP customer.
     */
    public function special(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_type' => 'special',
            'orders_count' => 5,
            'total_spent' => 2500.00,
        ]);
    }
}
