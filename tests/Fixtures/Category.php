<?php


namespace Gruter\ResourceViewer\Tests\Fixtures;


use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    public function users(){
        return $this->belongsToMany(TestUser::class, 'testing_category_user');
    }
}