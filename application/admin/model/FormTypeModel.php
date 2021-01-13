<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 18:45
 */
namespace app\admin\model;
use think\Model;

class FormTypeModel extends Model{
    public static function FormTypeSelect(){
        return self::all()->toArray();
    }

    //添加
    public static function FromTypeAdd($data){
        return self::insert($data);
    }

    //类型删除
    public static function FormTypeDelete($data){
        return self::where($data)->delete();
    }

    //条件查询
    public static function FormTypeWhereFind($field,$data){
        return self::where($data)->value($field);
    }
}