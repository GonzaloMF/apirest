<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {

        //index function to get all categories in database
        $categories = Category::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'categories' => $categories
        ]);
    }

    public function show($id) {

        //Show eazh category
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Category not existing'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {

        //Get data with post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //Validate data
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            //Save category
            if ($validate->fails()) {
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Category unsaved'
                ];
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'You dont send any category'
            ];
        }
        return response()->json($data, $data['code']);
    }
    public function update($id, Request $request){
        //Get data with POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
        //Validate data
        $validator = \Validator::make($params_array, [
            'name' => 'required'
        ]);
                
        //Remove data that we dont want to update
        unset($params_array['id']);
        unset($params_array['created_at']);
       
        //Update category
        
        $category = Category::where('id', $id)->update($params_array);
        $data = [
                'code' => 200,
                'status' => 'success',
                'message' => $params_array
            ];
        
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'You dont send any category'
            ];
        }
        return response()->json($data, $data['code']);
    }
}
