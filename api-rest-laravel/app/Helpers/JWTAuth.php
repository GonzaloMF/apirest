<?php

namespace App\Helpers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{
    
    public $key;
    
    public function __construct() {
        $this->key = 'secretkey2020';
    }
    
    public function signup($email, $password, $getToken = null) {
        
        //Search for existing user with his credentials
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();
              
        //Check if they are correct
        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        
        //Generate token with identify user data       
        if($signup){
          
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (30*24*60*60)
            );
            
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            
            //Return token data
            if(is_null($getToken)){
                $data = $jwt;   
            }else{
                $data = $decoded;
            }
            
        }else{
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrect'
            );
        }
        return $data;
    }
    
    public function checktoken($jwt, $getIdentity = false){
        $auth = false;
        
        try{
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            
        }catch(\UnexpectedValueException $e){
            $auth = false;
            
        }catch(\DomainException $e){
            $auth = false;
        }
        
        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
            
        }else{
            $auth = false;
        }
        
        if($getIdentity){
            return $decoded;
        }   
        return $auth;
    }
}