<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/27
 * Time: 11:10
 */
namespace app\index\model;
use think\Model;

class FormModel extends Model{
    //报表查询5条
    public static function FormSelect($data){
        return self::limit(5)->order('time desc')->all($data)->toArray();
    }

    //某类型下报表查询
    public static function FormAll($data){
        return self::all($data)->toArray();
    }

    //条数排序查询
    public static function FormLimitAll($limit,$order,$data){
        return self::limit($limit)->order($order)->all($data)->toArray();
    }
}