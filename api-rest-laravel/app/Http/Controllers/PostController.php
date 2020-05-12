<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {

        //index function to get all psts in database with each category
        $posts = Post::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'posts' => $posts
        ]);
    }

    public function show($id) {

        $posts = Post::find($id)->load('category');

        if (is_object($posts)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'posts' => $posts
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Post not existing'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {

        //Get data with post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //Get user identified
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

            //Validate data
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);

            //Save category
            if ($validate->fails()) {
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Post unsaved, it needs more info'
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Error. Send info correctly please'
            ];
        }
        return response()->json($data, $data['code']);
    }

}
