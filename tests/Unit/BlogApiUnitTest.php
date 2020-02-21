<?php

namespace Tests\Unit;

use Tests\TestCase;

class BlogApiUnitTest extends TestCase
{
    public function testCreateUser()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->password
        ];

        $this->post(route('user.create'), $data)
            ->assertStatus(200);
    }
}