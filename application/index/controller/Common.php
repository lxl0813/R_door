<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/20
 * Time: 14:24
 */
namespace app\index\controller;
use app\index\model\Member;
use think\Controller;
use think\Exception;
use think\facade\Cookie;
use think\exception\ErrorException;
use think\exception\HttpException;


class Common extends Controller
{
     public function __construct()
    {
        parent::__construct();
        if(!cookie("ranglei_members")){
            $this->redirect("Login/index");
        }else{
            $member = Member::MemberAll('account');
            $member_account = Cookie::get("ranglei_members");
            if(is_array($member_account)){
                if(array_key_exists('member_account',$member_account)){
                    foreach($member as $k=>$v){
                        if(!in_array($member_account['member_account'],$v)){
                            $this->redirect("Login/index");
                        }
                    }
                }else{
                    $this->redirect("Login/index");
                }
            }else{
                $this->redirect("Login/index");
            }
        }
	}
}