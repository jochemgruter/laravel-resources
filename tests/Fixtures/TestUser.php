<?php


namespace Gruter\ResourceViewer\Tests\Fixtures;


class TestUser extends \Illuminate\Foundation\Auth\User
{

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

}