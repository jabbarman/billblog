<?php

namespace App\Http\Controllers;

use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use App\Blog;

class BlogController extends Controller
{
    public function __construct()
    {
        // NOT WORKING Needs investigating or using OAuth2 or JWT (JJ - 2018/01/10)
        //$this->middleware('auth.basic.once')->only('store', 'update');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $blogs = Blog::all();

        $response = [];

        foreach($blogs as $blog) {
            $response[] = [
                "id" => $blog->id,
                "title" => $blog->title,
                "href" => "/api/v1/blog/".$blog->id,
                "method" => "GET"
            ];
        }

        return response()->json([
            "message" => "posts found",
            "posts" => $response
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $blog = new Blog;

        $blog->user_id = 1;  // hard coded until we get authentication on stream
        $blog->title = $request->title;
        $blog->body = $request->body;

        if ($blog->save()) {
            return response()->json([
                "message" => "post created",
                "id" => $blog->id,
                "title" => $blog->title,
                "href" => "/api/v1/blog/".$blog->id,
                "method" => "GET"
            ],201);
        };

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blog = Blog::findOrFail($id);

        $post = [
            "id" => $blog->id,
            "title" => $blog->title,
            "body" => $blog->body,
            "user_id" => $blog->user_id,
            "created_at" => $blog->created_at,
            "updated_at" => $blog->updated_at,
            "href" => "/api/v1/blog/" . $blog->id,
            "method" => 'GET'
        ];

        return response()->json([
            "message" => "post found",
            "post" => $post
        ],200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $blog->user_id = 1;  // hard coded until we get authentication on stream
        (!empty(trim($request->title))?$blog->title = (trim($request->title)):null);
        (!empty(trim($request->body))?$blog->body = (trim($request->body)):null);

        if ($blog->save()) {
            return response()->json([
                "message" => "post edited",
                "id" => $blog->id,
                "title" => $blog->title,
                "href" => "/api/v1/blog/".$blog->id,
                "method" => "GET"
            ],201);
        };

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $blog = Blog::findOrFail($id);

        if ($blog->delete()) {
            return response()->json([
                "message" => "post deleted",
                "id" => $blog->id,
                "title" => $blog->title
            ],200);
        };
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addImage($id, Request $request)
    {
        //

        return response()->json($request);
        //return "result of addImage/{$id}";
    }
}
