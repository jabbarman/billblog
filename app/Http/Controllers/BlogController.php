<?php

namespace App\Http\Controllers;

use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use App\Blog;
use App\Upload;
use App\Label;


class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth')->only('store', 'update', 'destroy', 'upload');
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

        if (count($response) > 0) {
            $message = "posts found";
        }
        else {
            $message = "no posts found!";
        }

        return response()->json([
            "message" => $message,
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
        $uploads = Upload::all()->where('post_id',$id);
        $labels = Label::all()->where('post_id',$id);

        $images = [];
        foreach($uploads as $upload) {
            if ($path_parts = pathinfo($upload->path)) {
                $images[] = [
                    "id" => $upload->id,
                    "href" => "/storage/" . $path_parts['basename'],
                    "method" => 'GET'
                ];
            }
        }

        $labels_list = [];

        foreach($labels as $label) {
            if ($path_parts = pathinfo($upload->path)) {
                $labels_list[] = [
                    "id" => $label->id,
                    "name" => $label->name,
                ];
            }
        }

        $post = [
            "id" => $blog->id,
            "title" => $blog->title,
            "body" => $blog->body,
            "images" => $images,
            "labels" => $labels_list,
            "user_id" => $blog->user_id,
            "created_at" => $blog->created_at,
            "updated_at" => $blog->updated_at,
            "href" => "/api/v1/blog/" . $blog->id,
            "method" => 'GET'
        ];

        return response()->json([
            "message" => "post found",
            "post" => $post,
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
            ],200);
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
     * Upload the specified resource from storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload($id, Request $request)
    {
        $upload = new Upload;

        if ($upload->path = $request->file('image')->store('public')) {
            $upload->post_id = $id;
        }
        else {
            return response()->json([
                "message" => "file not accepted",
                "blog_id" => $id
            ],400  );
        };

        if ($upload->save()) {
            return response()->json([
                "message" => "file accepted",
                "blog_id" => $id,
                "upload_id" => $upload->id
            ],202 );
        };
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addLabel($id, Request $request)
    {

        if ( $label = Label::firstOrCreate([
                "name" => $request->name,
                "post_id" => $id,
            ]))
            {
                return response()->json([
                    "message" => "label created",
                    "id" => $label->id,
                    "name" => $label->name,
                    "href" => "/api/v1/blog/".$id,
                    "method" => "GET"
                ],201);
            } else
        {}

    }

    /**
     * Remove the specified label from storage.
     *
     * @param  int  $id
     * @param  int  $label_id
     * @return \Illuminate\Http\Response
     */
    public function delLabel($id,$label_id)
    {
        $blog = Blog::findOrFail($id);

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
        }
        else {
            return response()->json([
                "message" => "label not found",
                "id" => $label_id,
                "href" => "/api/v1/blog/".$blog->id,
                "method" => "GET"
            ],200);
        };
    }

}
