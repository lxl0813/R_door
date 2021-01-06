<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/27
 * Time: 14:00
 */
namespace app\index\controller;
use app\index\model\Member;
use think\Controller;

class Login extends Controller{
    public function index(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()){
            if(request()->isPost()){
                //接值判断
                $member_name = request()->post("member_name");
                $member_pwd = request()->post("member_pwd");
                if ($member_name == ""){
                    echo json_encode(["status" => 2, "msg" => "账号不能为空"]);
                }
                if($member_name == ""){
                    echo json_encode(["status" => 2, "msg" => "密码验不能为空"]);
                }
                $data=Member::MemberGet(['account'=>$member_name]);
                if(!$data){
                    echo json_encode(["status" => 2, "msg" => "账号不存在"]);
                }else{
                    //密码加密进行查询
                    if(md5($member_pwd) != $data["pwd"]){
                        echo json_encode(["status" => 2, "msg" => "密码错误，请重新输入"]);
                    }else{
                        $arr = [
                            "member_id" => $data["id"],
							"member_account" => $data['account'],
                        ];
                        cookie("ranglei_members", $arr);
                        echo json_encode(["status" => 1, "msg" => "登录成功"]);
                    }
                }
            }
        }
    }
}