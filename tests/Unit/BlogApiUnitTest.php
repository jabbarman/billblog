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
        $this->post->title = 'This is a title';
        $this->post->body = 'Some text that will form the body of the posted article';
    }

    public function testCreateUser()
    {
        $this->createUser()->assertStatus(200);
    }

    public function testAuthenticateUser()
    {
        $this->createUser();

        $this->authenticateUser()->assertStatus(200);
        self::assertNotEmpty($this->user->token);
    }

    public function testCreatePost()
    {
        $this->createUser();
        $this->authenticateUser();

        $this->createPost()
            ->assertStatus(201)
            ->assertExactJson([
                'message' => 'post created',
                'id' => 1,
                'title' => $this->post->title,
                'creator' => $this->user->name,
                'links' => ['href' => 'http://localhost/api/v1/blog/1']
            ]);
    }

    public function testListPosts()
    {
        $this->createUser();
        $this->authenticateUser();
        $this->createPost();

        $this->listPosts()
            ->assertStatus(200)
            ->assertExactJson([
                'message' => 'posts found',
                'posts' => [
                    [
                        'id' => 1,
                        'title' => $this->post->title,
                        'creator' => $this->user->name,
                        'links' => ['href' => 'http://localhost/api/v1/blog/1']
                    ]
                ],
                'links' => ['self' => 'http://localhost/api/v1/blog']
            ]);
    }

    public function testListPost()
    {
        $this->createUser();
        $this->authenticateUser();
        $this->createPost();

        $this->listPost()
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'post' => [
                    'id',
                    'title',
                    'body',
                    'images',
                    'labels',
                    'user_id',
                    'creator',
                    'created_at' => [
                        'date',
                        'timezone_type',
                        'timezone',
                    ],
                    'updated_at' => [
                        'date',
                        'timezone_type',
                        'timezone',
                    ]
                ],
                'links',
            ]);
    }

    private function createUser()
    {
        $data = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => $this->user->password
        ];

        return $this->post(route('user.create'), $data);
    }

    private function authenticateUser()
    {
        $data = [
            'email' => $this->user->email,
            'password' => $this->user->password
        ];

        $response = $this->post(route('user.authenticate'), $data);
        $json = $response->json();
        $this->user->token = $json['token'];

        return $response;
    }

    private function createPost()
    {
        $data = [
            'title' => $this->post->title,
            'body' => $this->post->body,
        ];

        $header = [
            'Authorization' => 'Bearer '.$this->user->token,
            'Accept' => 'application/json',
        ];

        return $this->post(route('blog.store'), $data, $header);
    }

    private function listPosts()
    {
        return $this->get(route('blog.index'));
    }

    private function listPost()
    {
        $data = [1];
        return $this->get(route('blog.show', $data));
    }
}