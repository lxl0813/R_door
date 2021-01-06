<?php
/**
 * Created by PhpStorm.
 * User: 李小龙
 * Date: 2020/4/26
 * Time: 13:51
 */
namespace app\admin\controller;
use app\admin\model\Admin;
use think\Controller;
use think\Cookie;
use think\Db;
use think\validate\ValidateRule;

class Login extends Controller{
    public function index(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()){
            if(request()->isPost()){
                //接值判断
                $admin_name = request()->post("admin_name");
                $admin_pwd = request()->post("admin_pwd");
                if ($admin_name == ""){
                    echo json_encode(["status" => 2, "msg" => "账号不能为空"]);
                }
                if($admin_pwd == ""){
                    echo json_encode(["status" => 2, "msg" => "密码验不能为空"]);
                }
                $data=Admin::Login(['admin'=>$admin_name]);
                if(!$data){
                    echo json_encode(["status" => 2, "msg" => "账号不存在"]);
                }else{
                    //密码加密进行查询
                    if(md5($admin_pwd) != $data["pwd"]){
                        echo json_encode(["status" => 2, "msg" => "密码错误，请重新输入"]);
                    }else{
                        $arr = [
                            "admin_id" => $data["id"],
                            "admin_name" =>$data["admin"],
                            "name"=>$data['name'],
                        ];
                        cookie("ranglei_admin", $arr);
                        echo json_encode(["status" => 1, "msg" => "登录成功"]);
                    }
                }
            }
        }
    }


    //退出
    public function out(){
        cookie('ranglei_admin',null);
        $this->redirect('Index/index');
    }

}