<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/27
 * Time: 11:08
 */
namespace app\index\model;

use think\Model;

class FormTypeModel extends Model{
    public static function FormTypeSelect(){
        return self::all()->toArray();
    }

    //条件查找
    public static function FormTypeWhereSelect($field,$data){
        return self::field($field)->get($data)->toArray();
    }
}