<?php


namespace app\admin\model;


use think\Db;
use think\Model;

class Role  extends Model
{
    public static function addNodeRole($data,$node_id){
        $arr=[];
        foreach($node_id as $k=>$v){
            $arr[]=["role_id"=>$data,"node_id"=>$v,'create_time'=>time()];
        }
        return Db::name("role_node")->insertAll($arr);
    }
}