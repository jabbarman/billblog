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
        //
        $blogs = Blog::all();

        $response = [];

        foreach($blogs as $blog) {
            $response[$blog->id] = $blog->title;
        }

        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $blog = new Blog;

        $blog->user_id = 1;  // hard coded until we get authentication on stream
        $blog->title = $request->title;
        $blog->body = $request->body;

        $blog->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $blog = Blog::find($id);

        return response()->json([
            "id" => $blog->id,
            "title" => $blog->title,
            "body" => $blog->body,
            "user_id" => $blog->user_id,
            "created_at" => $blog->created_at,
            "updated_at" => $blog->updated_at
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //

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
        //
        $blog = Blog::find($id);

        $blog->user_id = 1;  // hard coded until we get authentication on stream
        (!empty(trim($request->title))?$blog->title = (trim($request->title)):null);
        (!empty(trim($request->body))?$blog->body = (trim($request->body)):null);

        $blog->save();
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
        return "result of DELETE/{$id}";
    }
}
