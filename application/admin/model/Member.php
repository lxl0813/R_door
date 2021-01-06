<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 14:13
 */
namespace app\admin\model;
use think\Model;

class Member extends Model{
    public static function MemberSelect(){
        return self::all()->toArray();
    }

    //会员添加
    public static function MemberAdd($data){
        return self::insert($data);
    }

    //会员删除
    public static function MemberDelete($data){
        return self::where($data)->delete();
    }
}