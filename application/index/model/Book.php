<?php
namespace app\index\model;
use think\Model;

class Book extends Model{
    public static function Add($data){
        return self::insert($data);
    }
}