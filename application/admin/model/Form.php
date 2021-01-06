<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 18:11
 */
namespace app\admin\model;
use think\Model;

class Form extends Model{
    public static function FormSelect(){
        return self::order('form_time')->all()->toArray();
    }

    public static function FormAdd($data){
        return self::insert($data);
    }

    //报表删除
    public static function FormDelete($data){
        return self::where($data)->delete();
    }

    //条件查找
    public static function FormWhereFind($data){
        return self::all($data)->toArray();
    }
}