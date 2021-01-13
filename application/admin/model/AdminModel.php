<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/1
 * Time: 15:11
 */
namespace app\admin\model;

use think\Model;

class AdminModel extends Model
{
    //管理员登录查询
    public static function Login($data){
        return self::get($data);
    }

    //管理员查询
    public static function AdminSelect(){
        return self::all()->toArray();
    }

    //管理员添加
    public static function AdminAdd($data){
        return self::insertGetId($data);
    }

    //管理员删除
    public static function AdminDelete($data){
        return self::where($data)->delete();
    }


}
