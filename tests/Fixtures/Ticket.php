<?php


namespace Gruter\ResourceViewer\Tests\Fixtures;


use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{



    public function user(){
        return $this->belongsTo(TestUser::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}