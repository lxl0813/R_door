<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 14:56
 */
namespace app\admin\controller;
use app\admin\model\AdminRoleModel;
use app\admin\model\NodeModel;
use think\Db;

class AdminController extends CommonController{

    //管理员列表
    public function index(){
        //获取管理员角色
        $admin  =   Db::name('admin')->select();
        foreach ($admin as $key=>$item)
        {
            $admin[$key]['role']  =   Db::name('admin_roles')->where('admin_id',$item['id'])->select();
        }
        return view('',['admin'=>$admin]);
    }

    //管理员添加以及分配权限
    public function add(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()){
            $data = input('param.');
            unset($data['checkID']);
		    $data['pwds']=$data['pwd'];
            $data['pwd']=md5($data['pwd']);
            $admin = \app\admin\model\AdminModel::AdminAdd($data);
            if($admin){
                echo json_encode(['status'=>1,'msg'=>'管理员添加成功']);
            }else{
                echo json_encode(['status'=>2,'msg'=>'管理员添加失败']);
            }
        }
    }

    //管理员删除
    public function delete(){
        $admin['id'] = input("admin_id");
		//var_dump($admin['id']);exit;
		if($admin['id']=="4"){
	
			echo json_encode(['status'=>2,'msg'=>'您还没有删除超级管理员的权限']);exit;
		}
        //删除管理员
        $admin_id = \app\admin\model\AdminModel::AdminDelete($admin);
        //删除改管理员权限
        $admin_role_id = AdminRoleModel::AdminRoleDelete(['admin_id'=>$admin['id']]);
        if($admin_id && $admin_role_id){
            echo json_encode(['status'=>1,'msg'=>'管理员删除成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'管理员删除失败']);
        }
    }

    //管理员权限等信息修改
    public function update(){
        if(request()->isGet()){
        }
        if(request()->isPost()){
        }
    }

    public function role()
    {
        $admin=input('admin_id');
        $role   =   (new \app\admin\model\RoleModel())->all();
        return view('',['role'=>$role,'admin'=>$admin]);
    }

    public function role_add()
    {
        $role       =   input('role');
        $admin_id   =   input('admin');
        foreach ($role as $k=>$v)
        {
            $roles[$k]['role_id']       =   $v;
            $roles[$k]['role_name']     =   Db::name('role')->where('id',$v)->value('role_name');
            $roles[$k]['create_time']   =   time();
            $roles[$k]['admin_id']      =   $admin_id;
            $roles[$k]['admin_name']    =   Db::name('admin')->where('id',$admin_id)->value('name');
        }
        if(Db::name('admin_roles')->insertAll($roles)){
            $this->success('','Admin/role');
        }
    }
}