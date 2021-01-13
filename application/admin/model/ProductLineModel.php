<?php
namespace app\admin\model;
use think\Model;

class ProductLineModel extends Model{
    public static function ProductLineInsert($data){
        return self::insert($data);
    }
    //生产线删除
    public static function ProductLineDelete($where){
        return self::destroy($where);
    }
}