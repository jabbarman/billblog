<?php

namespace Tests\Unit;

use App\Blog;
use App\Http\Controllers\BlogController;
use Illuminate\Foundation\Testing\TestResponse;
use stdClass;
use Tests\TestCase;

class BlogApiUnitTest extends TestCase
{
    protected $user;
    protected $post;
    protected $limit;
    protected $date;

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

        $this->limit = BlogController::LIMIT_DEFAULT;

        $this->date = new \DateTime('today');
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
                'limit' => $this->limit,
                'posts' => [
                    [
                        'id' => 1,
                        'title' => $this->post->title,
                        'body' => $this->post->body,
                        'images' => null,
                        'labels' => null,
                        'user_id' => 1,
                        'creator' => $this->user->name,
                        'created_at' => $this->date->format('Y-m-d'),
                        'updated_at' => $this->date->format('Y-m-d'),
                        'links' => ['href' => 'http://localhost/api/v1/blog/1'],
                    ]
                ],
                'links' => ['self' => 'http://localhost/api/v1/blog'],
                'size' => 1,
                'start' => 0
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
                    'created_at',
                    'updated_at',
                ],
                'links',
            ]);
    }

    public function testPaginationReturnsPostsUptoLimit()
    {
        $this->createUser();
        $this->authenticateUser();
        $this->createManyPosts(100);

        $response = $this->listPosts()
            ->assertStatus(200);
        $json = $response->json();

        self::assertCount($this->limit, $json['posts']);

        $response->assertJson([
            'limit' => $this->limit,
            'size' => $this->limit,
            'start' => 0,
        ]);
    }

    public function testPaginationHandlesStartParameter()
    {
        $this->createUser();
        $this->authenticateUser();
        $this->createManyPosts();

        $response = $this->listPostsWithSetStart(5)
            ->assertStatus(200);
        $json = $response->json();

        self::assertCount(5, $json['posts']);

        $response->assertJson([
            'limit' => $this->limit,
            'size' => 5,
            'start' => 5,
        ]);
    }

    public function testPaginationHandlesOutOfRangeStartParameter()
    {
        $this->createUser();
        $this->authenticateUser();
        $this->createManyPosts();

        $response = $this->listPostsWithSetStart(50)
            ->assertStatus(400);
        $json = $response->json();

        self::assertEquals(null, $json['posts']);

        $response->assertJson([
            'limit' => $this->limit,
            'size' => 0,
            'start' => 50,
        ]);
    }

    public function testPaginationHandlesLimitParameter()
    {
        $this->createUser();
        $this->authenticateUser();
        $this->createManyPosts();

        $response = $this->listPostsWithSetlimit(7)
            ->assertStatus(200);
        $json = $response->json();

        self::assertCount(7, $json['posts']);

        $response->assertJson([
            'limit' => 7,
            'size' => 7,
            'start' => 0,
        ]);
    }

    public function testUpdatePost()
    {
        $this->createUser();
        $this->authenticateUser();

        $this->createPost();

        $this->updatePost()
            ->assertStatus(200)
            ->assertExactJson([
                'message' => 'post edited',
                'id' => 1,
                'title' => $this->post->title . ' Updated',
                'creator' => $this->user->name,
                'user_id' => 1,
                'links' => ['self' => 'http://localhost/api/v1/blog/1']
            ]);
    }

    public function testSearchPosts()
    {
        $this->createUser();
        $this->authenticateUser();

        $this->createManyPosts();

        $this->updatePost()
            ->assertStatus(200)
            ->assertExactJson([
                'message' => 'post edited',
                'id' => 1,
                'title' => $this->post->title . ' Updated',
                'creator' => $this->user->name,
                'user_id' => 1,
                'links' => ['self' => 'http://localhost/api/v1/blog/1']
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

    /**
     * @param int $start
     *
     * @return TestResponse
     */
    private function listPostsWithSetStart(int $start)
    {
        return $this->get("http://localhost/api/v1/blog/?start={$start}");
    }

    /**
     * @param int $limit
     *
     * @return TestResponse
     */
    private function listPostsWithSetlimit(int $limit)
    {
        return $this->get("http://localhost/api/v1/blog/?limit={$limit}");
    }

    private function listPost()
    {
        $data = [1];
        return $this->get(route('blog.show', $data));
    }

    /**
     * @param int $numOfPosts
     */
    private function createManyPosts(int $numOfPosts = 10):void
    {
        for ($i=0; $i<$numOfPosts; $i++) {
            $post = new Blog();
            $post->user_id = 1;
            $post->title = $this->faker->sentence($nbWords = 3, $variableNbWords = true);
            $post->body = $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true);
            $post->save();
        }
    }

    /**
     * @param int $id
     *
     * @return TestResponse
     */
    private function updatePost(int $id = 1)
    {
        $data = [
            'title' => $this->post->title . ' Updated',
            'body' => $this->post->body . ' Updated too!',
        ];

        $header = [
            'Authorization' => 'Bearer '.$this->user->token,
            'Accept' => 'application/json',
        ];

        return $this->patch("http://localhost/api/v1/blog/$id/", $data, $header);
    }
}