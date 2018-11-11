<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;
use App\Upload;
use App\Label;
use Illuminate\Http\Response;
use Tymon\JWTAuth\JWTAuth;

class BlogController extends Controller
{
    /**
     * BlogController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth')->only('store', 'update', 'destroy', 'upload', 'addLabel', 'delLabel');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $blogs = Blog::all();

        $response = [];

        $links = ['rel'=>"self","href"=> Request::capture()->fullUrl(), "method"=>"GET"];

        foreach ($blogs as $blog) {
            $response[] = [
                "id" => $blog->id,
                "title" => $blog->title,
                "creator" => $blog->user->name,
                "_links"=>$links,
            ];
        }

        if (count($response) > 0) {
            $message = "posts found";
        } else {
            $message = "no posts found!";
        }

        return response()->json([
            "message" => $message,
            "posts" => $response
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->toUser();

        if ($user->id) {
            $blog = new Blog;
            $blog->user_id = $user->id;
            $blog->title = $request->title;
            $blog->body = $request->body;

            if ($blog->save()) {
                return response()->json([
                    "message" => "post created",
                    "id" => $blog->id,
                    "title" => $blog->title,
                    "creator" => $blog->user->name,
                    "href" => "/api/v1/blog/" . $blog->id,
                    "method" => "GET"
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
        $blog = Blog::findOrFail($id);
        $uploads = Upload::all()->where('post_id', $id);
        $labels = Label::all()->where('post_id', $id);

        $images = [];
        foreach ($uploads as $upload) {
            if ($path_parts = pathinfo($upload->path)) {
                $images[] = [
                    "id" => $upload->id,
                    "href" => "/storage/" . $path_parts['basename'],
                    "method" => 'GET'
                ];
            }
        }

        $labels_list = [];

        foreach ($labels as $label) {
            $labels_list[] = [
                "id" => $label->id,
                "name" => $label->name,
            ];
        }

        $post = [
            "id" => $blog->id,
            "title" => $blog->title,
            "body" => $blog->body,
            "images" => $images,
            "labels" => $labels_list,
            "user_id" => $blog->user_id,
            "creator" => $blog->user->name,
            "created_at" => $blog->created_at,
            "updated_at" => $blog->updated_at,
            "href" => "/api/v1/blog/" . $blog->id,
            "method" => 'GET'
        ];

        return response()->json([
            "message" => "post found",
            "post" => $post,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return Response
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $user = JWTAuth::parseToken()->toUser();

        if ($user->id == $blog->user_id) {
            (!empty(trim($request->title)) ? $blog->title = (trim($request->title)) : null);
            (!empty(trim($request->body)) ? $blog->body = (trim($request->body)) : null);

            if ($blog->save()) {
                return response()->json([
                    "message" => "post edited",
                    "id" => $blog->id,
                    "title" => $blog->title,
                    "user_id" => $blog->id,
                    "creator" => $blog->user->name,
                    "href" => "/api/v1/blog/" . $blog->id,
                    "method" => "GET"
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
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function destroy($id)
    {
        //
        $blog = Blog::findOrFail($id);

        $user = JWTAuth::parseToken()->toUser();

        if ($user->id == $blog->user_id) {
            if ($blog->delete()) {
                return response()->json([
                    "message" => "post deleted",
                    "id" => $blog->id,
                    "title" => $blog->title
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
     * @param  int                      $id
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function upload($id, Request $request)
    {
        $blog = Blog::findOrFail($id);

        $user = JWTAuth::parseToken()->toUser();

        if ($user->id == $blog->user_id) {
            $upload = new Upload;

            if ($upload->path = $request->file('image')->store('public')) {
                $upload->post_id = $id;
            } else {
                return response()->json([
                    "message" => "file not accepted",
                    "blog_id" => $id
                ], 400);
            };

            if ($upload->save()) {
                return response()->json([
                    "message" => "file accepted",
                    "blog_id" => $id,
                    "upload_id" => $upload->id
                ], 202);
            };
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int                      $id
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function addLabel($id, Request $request)
    {
        $blog = Blog::findOrFail($id);

        $user = JWTAuth::parseToken()->toUser();

        if ($user->id == $blog->user_id) {
            if ($label = Label::firstOrCreate([
                "name" => $request->name,
                "post_id" => $id,
            ])) {
                return response()->json([
                    "message" => "label created",
                    "id" => $label->id,
                    "name" => $label->name,
                    "href" => "/api/v1/blog/" . $id,
                    "method" => "GET"
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
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function delLabel($id, $label_id)
    {
        $blog = Blog::findOrFail($id);

        $user = JWTAuth::parseToken()->toUser();

        if ($user->id == $blog->user_id) {
            if ($label = Label::find($label_id)) {
                if ($label->delete()) {
                    return response()->json([
                        "message" => "label deleted",
                        "id" => $label->id,
                        "name" => $label->name,
                        "href" => "/api/v1/blog/" . $blog->id,
                        "method" => "GET"
                    ], 200);
                }
            } else {
                return response()->json([
                    "message" => "label not found",
                    "id" => $label_id,
                    "href" => "/api/v1/blog/" . $blog->id,
                    "method" => "GET"
                ], 200);
            };
        } else {
            return response()->json([
                "message" => "the action is forbidden for this user",
            ], 403);
        }
    }

}
