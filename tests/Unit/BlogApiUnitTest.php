<?php

namespace Tests\Unit;

use stdClass;
use Tests\TestCase;

class BlogApiUnitTest extends TestCase
{
    protected $user;
    protected $post;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->user = new stdClass();
        $this->user->name = 'Joe90';
        $this->user->email = 'joe@fakeemail.com';
        $this->user->password = 'aBcD1two3f0ur';

        $this->post = new stdClass();
        $this->post->title = 'This ia a title';
        $this->post->body = 'Some text that will form the body of the posted article';
    }

    public function testCreateUser()
    {
        $data = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => $this->user->password
        ];

        $this->post(route('user.create'), $data)
            ->assertStatus(200);
    }

    public function testAuthenticateUser()
    {
        $data = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => $this->user->password
        ];

        $this->post(route('user.create'), $data)
            ->assertStatus(200);

        $data = [
            'email' => $this->user->email,
            'password' => $this->user->password
        ];

        $response = $this->post(route('user.authenticate'), $data)
            ->assertStatus(200);

        $json = $response->json();
        $this->user->token = $json['token'];
        self::assertNotEmpty($this->user->token);
    }

    public function testCreatePost()
    {
        $data = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => $this->user->password
        ];

        $this->post(route('user.create'), $data)
            ->assertStatus(200);

        $data = [
            'email' => $this->user->email,
            'password' => $this->user->password
        ];

        $response = $this->post(route('user.authenticate'), $data)
            ->assertStatus(200);

        $json = $response->json();
        $this->user->token = $json['token'];

        self::assertNotEmpty($this->user->token);

        $data = [
            'title' => $this->post->title,
            'body' => $this->post->body,
        ];

        $header = [
            'Authorization' => 'Bearer '.$this->user->token,
            'Accept' => 'application/json',
        ];

        $response = $this->post(route('blog.store'), $data, $header)
            ->assertStatus(201);

        $json = $response->json();

        self::assertArrayHasKey('message', $json);
        self::assertContains('post created', $json['message']);

        self::assertArrayHasKey('id', $json);
        self::assertEquals(1, $json['id']);

        self::assertArrayHasKey('title', $json);
        self::assertContains($this->post->title, $json['title']);

        self::assertArrayHasKey('creator', $json);
        self::assertContains($this->user->name, $json['creator']);

        self::assertArrayHasKey('links', $json);
        $links = ['href' => 'http://localhost/api/v1/blog/1'];
        self::assertEquals($links, $json['links']);
    }
}