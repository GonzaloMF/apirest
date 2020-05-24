<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show', 
            'getImage','getPostsByCategory','getPostsByUser']]);
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
            $user = $this->getIdentity($request);

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

    public function update($id, request $request) {

        //Collect data with POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'message' => 'Error updating post',
        );

        if (!empty($params_array)) {

            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required'
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            //Data that we dont want to update    
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            //Get user identified
            $user = $this->getIdentity($request);

            //Search post that we want to update data
            $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if (!empty($post) && is_object($post)) {

                //Update post in database
                $post->update($params_array);

                //Return something
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'post' => $post,
                    'changes' => $params_array
                );
            }
            /*
             * $where = [
              'id' => $id,
              'user_id' => $user->sub
              ];
              $post = Post::updateOrCreate($where, $params_array);

              $post = Post::where('id', $id)
              ->where('user_id', $user->sub)
              ->updateOrCreate($params_array);
             */
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        //Get user identified
        $user = $this->getIdentity($request);

        //Get post data
        $post = Post::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if (!empty($post)) {
            //Delete post
            $post->delete();

            //Return something
            $data = array(
                'status' => 'success',
                'code' => 200,
                'post' => $post
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Post doesnt exist'
            );
        }
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request) {
        //Get user identified
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request) {
        //Get image
        $image = $request->file('file0');

        //Verify image
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        /* $validate = $this->validate($request, [
          'file0' => 'required|image|mimes:jpg, jpeg, png, gif'
          ]); */

        //Save image
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error uploading image'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName(); //Unique name for each image
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }

        //Return data
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        //Verify if exist image
        $isset = \Storage::disk('images')->exists($filename);

        if ($isset) {
            //Get image
            $file = \Storage::disk('images')->get($filename);

            //Return image
            return new Response($file, 200);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Image doesnt exist'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory($id) {

        $posts = Post::where('category_id', $id)->get();

        return response()->json([
                'status' => 'success',
                'posts' => $posts
            ], 200);
    }
    
    public function getPostsByUser($id) {

        $posts = Post::where('user_id', $id)->get();

        return response()->json([
                'status' => 'success',
                'posts' => $posts
            ], 200);
    }
}
