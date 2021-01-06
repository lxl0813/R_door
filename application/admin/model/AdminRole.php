<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 14:13
 */
namespace app\admin\model;
use think\Model;

class AdminRole extends Model{
    public static function AdminRole($where,$data){
        return self::where($where)->column($data);
    }

    //管理员权限添加
    public static function AdminRoleAdd($data){
        return self::insert($data);
    }

    //管理员权限删除
    public static function AdminRoleDelete($data){
        return self::where($data)->delete();
    }
}