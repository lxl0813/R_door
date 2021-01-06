<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/27
 * Time: 14:01
 */
namespace app\index\model;
use think\Model;

class Member extends Model{
     public static function MemberGet($data){
        return self::get($data);
    }

    //查询所有会员
    public static function MemberAll($field){
        return self::field($field)->all()->toArray();
    }
}