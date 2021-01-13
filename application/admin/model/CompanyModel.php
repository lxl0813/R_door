<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/27
 * Time: 14:55
 */
namespace app\admin\model;
use think\Model;

class CompanyModel extends Model{
    public static function CompanyAll(){
        return self::all()->toArray();
    }

    //公司删除
    public static function CompanyDelete($data){
        return self::where($data)->delete();
    }

    //条件查询
    public static function CompanyWhereGet($where,$data){
        return self::where($where)->value($data);
    }

    //公司添加
    public static function CompanyAdd($data){
        return self::insert($data);
    }
}