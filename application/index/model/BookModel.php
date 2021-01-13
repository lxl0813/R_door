<?php
namespace app\index\model;
use think\Model;

class BookModel extends Model{
    public static function Add($data){
        return self::insert($data);
    }
}