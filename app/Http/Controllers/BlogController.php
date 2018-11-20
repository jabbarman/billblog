<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use App\Blog;
use App\Upload;
use App\Label;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    protected $request;
    protected $fullUrl;
    protected $url;
    protected $blog;
    protected $upload;
    protected $label;

    /**
     * BlogController constructor.
     *
     * @param Request      $request
     * @param UrlGenerator $url
     * @param Blog         $blog
     * @param Upload       $upload
     * @param Label        $label
     */
    public function __construct(
        Request $request,
        UrlGenerator $url,
        Blog $blog,
        Upload $upload,
        Label $label
    ) {
        $this->middleware('jwt.auth')->only('store', 'update', 'destroy', 'upload', 'remove', 'addLabel', 'delLabel');

        $this->request = $request;
        $this->fullUrl = $this->request->fullUrl();
        $this->url = $url->to('/');
        $this->blog = $blog;
        $this->upload = $upload;
        $this->label = $label;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $posts = null;
        foreach ($this->blog->all() as $post) {
            $postLinks['href'] = $this->fullUrl . '/' . $post->id;
            $posts[] = [
                "id" => $post->id,
                "title" => $post->title,
                "creator" => $post->user->name,
                "links"=>$postLinks
            ];
        }

        $message = "no posts found!";
        if (count($posts) > 0) {
            $message = "posts found";
        }

        $links = ['self' => $this->fullUrl];
        return response()->json([
            "message" => $message,
            "posts" => $posts,
            "links" => $links
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $user = auth()->user();

        if ($user->id) {
            $post = new $this->blog;
            $post->user_id = $user->id;
            $post->title = $this->request->title;
            $post->body = $this->request->body;

            if ($post->save()) {
                $links['href'] = $this->fullUrl . '/' . $post->id;
                return response()->json([
                    "message" => "post created",
                    "id" => $post->id,
                    "title" => $post->title,
                    "creator" => $post->user->name,
                    "links" => $links
                ], 201);
            };
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $post = $this->blog->findOrFail($id);
        $uploads = $this->upload->all()->where('post_id', $id);
        $labels = $this->label->all()->where('post_id', $id);

        $images = null;
        foreach ($uploads as $upload) {
            if ($path_parts = pathinfo($upload->path)) {
                $images[] = [
                    "id" => $upload->id,
                    "href" => $this->url . "/storage/" . $path_parts['basename'],
                ];
            }
        }

        $labels_list = null;
        foreach ($labels as $label) {
            $labels_list[] = [
                "id" => $label->id,
                "name" => $label->name,
            ];
        }

        $post = [
            "id" => $post->id,
            "title" => $post->title,
            "body" => $post->body,
            "images" => $images,
            "labels" => $labels_list,
            "user_id" => $post->user_id,
            "creator" => $post->user->name,
            "created_at" => $post->created_at,
            "updated_at" => $post->updated_at,
        ];

        $links['self'] = $this->fullUrl;
        return response()->json([
            "message" => "post found",
            "post" => $post,
            "links" => $links,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update($id)
    {
        $post = $this->blog->findOrFail($id);
        $user = auth()->user();

        if ($user->id == $post->user_id) {
            (!empty(trim($this->request->title)) ? $post->title = (trim($this->request->title)) : null);
            (!empty(trim($this->request->body)) ? $post->body = (trim($this->request->body)) : null);

            if ($post->save()) {
                $links['self'] = $this->fullUrl;
                return response()->json([
                    "message" => "post edited",
                    "id" => $post->id,
                    "title" => $post->title,
                    "user_id" => $post->id,
                    "creator" => $post->user->name,
                    "links" => $links
                ], 200);
            };
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $post = $this->blog->findOrFail($id);
        $user = auth()->user();

        if ($user->id == $post->user_id) {
            if ($post->delete()) {
                return response()->json([
                    "message" => "post deleted",
                    "id" => $post->id,
                    "title" => $post->title
                ], 200);
            };
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

    /**
     * Upload the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function upload($id)
    {
        $post = $this->blog->findOrFail($id);
        $user = auth()->user();

        if ($user->id == $post->user_id) {
            $upload = new $this->upload;

            if ($upload->path = $this->request->file('image')->store('public')) {
                $upload->post_id = $id;
            } else {
                return response()->json([
                    "message" => "file not accepted",
                    "blog_id" => $id
                ], 400);
            };

            if ($upload->save()) {
                $path_parts = pathinfo($upload->path);
                $links['id'] = $upload->id;
                $links['href'] = $this->url . "/storage/" . $path_parts['basename'];
                return response()->json([
                    "message" => "file accepted",
                    "post" => ["id" => $post->id, "title" => $post->title, "image" => $links]
                ], 202);
            };
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param      $upload_id
     *
     * @return Response
     */
    public function remove($id, $upload_id)
    {
        $post = $this->blog->findOrFail($id);
        $user = auth()->user();

        if ($user->id == $post->user_id) {
            if ($upload = $this->upload->findorFail($upload_id)) {
                // TODO remove from storage
                $upload->delete();
                Storage::delete($upload->path);
                return response()->json([
                    "message" => "file removed",
                    "post" => ["id" => $post->id, "title" => $post->title]
                ], 400);
            }
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function addLabel($id)
    {
        $post = $this->blog->findOrFail($id);
        $user = auth()->user();

        if ($user->id == $post->user_id) {
            if ($label = $this->label->firstOrCreate([
                "name" => $this->request->name,
                "post_id" => $id,
            ])) {
                return response()->json([
                    "message" => "label created",
                    "id" => $label->id,
                    "name" => $label->name
                ], 201);
            }
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

    /**
     * Remove the specified label from storage.
     *
     * @param  int $id
     * @param  int $label_id
     *
     * @return Response
     */
    public function delLabel($id, $label_id)
    {
        $post = $this->blog->findOrFail($id);
        $user = auth()->user();

        if ($user->id == $post->user_id) {
            if ($label = $this->label->find($label_id)) {
                if ($label->delete()) {
                    return response()->json([
                        "message" => "label deleted",
                        "id" => $label->id,
                        "name" => $label->name
                    ], 200);
                }
            } else {
                return response()->json([
                    "message" => "label not found",
                    "id" => $label_id
                ], 200);
            };
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

}
