<?php
/**
 * Created by PhpStorm.
 * User: 李小龙
 * Date: 2020/4/8
 * Time: 13:41
 */
namespace app\admin\controller;
use think\Db;

class Pub extends Common{
    public function admin_update(){
        $admin=input('nextxt');
        if(Db::name('admin')->where(['id'=>1])->update(['admin'=>$admin])){
            echo json_encode(['status'=>1,'msg'=>'管理员账号修改成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'请稍后再试']);
        }
    }

}