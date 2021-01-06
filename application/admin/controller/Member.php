<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 17:09
 */
namespace app\admin\controller;
use think\Db;

class Member extends Common{
    public function index(){
        if(request()->isGet()){
            //查询会员信息
            $member = \app\admin\model\Member::MemberSelect();
            return view('',['member'=>$member]);
        }
        if(request()->isPost()){
        }
    }

    public function add(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()){
            $data = input('param.');
            $data['pwds']=$data['pwd'];
            $data['pwd']=md5($data['pwd']);
            $data['time'] = time();
            $member = \app\admin\model\Member::MemberAdd($data);
            if($member){
                echo json_encode(['status'=>1,'msg'=>'会员注册成功']);
            }else{
                echo json_encode(['status'=>2,'msg'=>'请稍后再试']);
            }
        }
    }

    //会员删除
    public function delete(){
        $member['id'] = input('member_id');
        $member_id = \app\admin\model\Member::MemberDelete($member);
        if($member_id){
            echo json_encode(['status'=>1,'msg'=>'会员删除成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'会员删除失败']);
        }
    }

    //会员修改功能
    public function update(){
        if(request()->isGet()){
            //查询会员资料
            $id['id']=input("member_id");
            $member = Db::name('member')->where($id)->find();
            return view('',['member'=>$member]);
        }

        if(request()->isPost()){
            $data=input("param.");
            $id=$data['id'];unset($data['id']);
            if(Db::name('member')->where(['id'=>$id])->update($data)){
                echo json_encode(['status'=>1,'msg'=>'会员信息修改完成！']);
            }else{
                echo json_encode(['status'=>2,'msg'=>'修改出错，请稍后再试！']);
            }
        }
    }

    //会员状态修改
    public function status(){
        $id = input("id");
        $status = Db::name('member')->where(['id'=>$id])->field('status')->find();
        if($status['status'] == 1){
            if(Db::name('member')->where(['id'=>$id])->update(['status'=>2])){
                echo json_encode(['status'=>1,'msg'=>'该会员账号已被冻结！']);
            }
        }else{
            if(Db::name('member')->where(['id'=>$id])->update(['status'=>1])){
                echo json_encode(['status'=>1,'msg'=>'该会员账号已被解冻！']);
            }
        }
    }
}