<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/27
 * Time: 17:53
 */
namespace app\index\model;

use think\Model;

class Company extends  Model{
    //所有公司查询
    public static function CompanyAll(){
        return self::all()->toArray();
    }

    //公司条件查询
    public static function CompanyWhereFind($field,$data){
        return self::field($field)->get($data)->toArray();
    }

}