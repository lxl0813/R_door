<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 14:13
 */
namespace app\admin\model;
use think\Model;

class Node extends Model{
    //所有
    public static function RoleAll(){
        return self::all()->toArray();
    }

    //条件查询
    public static function RoleSelect($data){
        return self::all($data)->toArray();
    }


    public static function RoleName($where,$data){
        return self::where($where)->column($data);
    }

	public static function RoleNames($where,$data){
		return self::where($where)->value($data);
	}

    //角色添加
    public static function RoleAdd($data){
        return self::insert($data);
    }
}