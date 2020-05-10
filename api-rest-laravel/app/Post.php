<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    
    //Muchos post pueden ser creados por un usuario
    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
     //Muchos post pueden esta perteneciendo a una categoría
     public function category(){
        return $this->belongsTo('App\Category','category_id');
    }
}
