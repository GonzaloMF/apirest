<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    
    
    protected $fillable = [
        'title', 'content', 'category_id'
    ];
    
    //An user can create multiple posts
    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
     //Multiple post can be in one category
     public function category(){
        return $this->belongsTo('App\Category','category_id');
    }
}
