<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {

    public function pruebas(Request $request) {
        return "prueba de user controller";
    }

    public function register(Request $request) {

        /* Get user data with POST */

        $json = $request->input('json', null); //Get String data to convert it an object in PHP
        $params = json_decode($json); //Get String data to convert it an object in PHP
        $params_array = json_decode($json, true); //Get data in array

        /* Validate obtained data */

        $validate = \Validator::make($params_array, [
                    'name' => 'required|alpha',
                    'surname' => 'required|alpha',
                    'email' => 'required|email|unique:users',
                    'password' => 'required'
        ]);
        if (!empty($params) && !empty($params_array)) {

            $params_array = array_map('trim', $params_array); //trim makes no spaces so we can keep oru data clean.

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'User does not create',
                    'errors' => $validate->errors()
                );
            } else {

                /* Validation correctly */

                $pwd = hash('sha256', $params->password);

                $user = new User(); //Create new user
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                $user->save(); //Save the new user in database

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'User created',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Incorrectly sent data'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {

        $jwtauth = new \JwtAuth();

        //Collect data with POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //Validate date (collect it previosly)
        $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'User can not login',
                'errors' => $validate->errors()
            );
        } else {
            //Encrypt password
            $pwd = hash('sha256', $params->password);

            //Return token or data
            $signup = $jwtauth->signup($params->email, $pwd);

            if (!empty($params->getToken)) {
                $signup = $jwtauth->signup($params->email, $pwd, true);
            }
        }

        //$pwd = password_hash($password, PASSWORD_BCRYPT, ['cost'=>4]); Genera diferente token codificado
        //$pwd = hash('SHA256',$password); //Genera el mismo token codificado

        return response()->json($signup, 200);
    }

    public function update(request $request) {

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Collect data with POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {

            //Get user identify
            $user = $jwtAuth->checkToken($token, true);

            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users,' . $user->sub
            ]);

            //Data that we dont want to update    
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Update user in database
            $user_update = User::where('id', $user->sub)->update($params_array);

            //Recieve array with data
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user_update,
                'changes' => $params_array
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'User not identifyed',
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {

        //Get data
        $image = $request->file('file0');

        //Validate image
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg, png, jpeg, gif',
        ]);

        //Save image
        if (!$image || $validate->fails()) {

            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Error to upload image',
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'status' => 'success',
                'code' => 200,
                'image' => $image_name
            );
        }
        //return response($data, $data['code'])->header('Content-Type', 'text/plain');
        //return response($data, $data['code']);
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {

        $isset = \Storage::disk('users')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Image not existing'
            );

            return response()->json($data, $data['code']);
        }
    }

    public function detail($id) {

        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
            /* var_dump($user);
              die(); */
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'User doesnt exist'
            );

            return response()->json($data, $data['code']);
        }
    }

}
