<?php
/**
 * Created by PhpStorm.
 * User: 李小龙
 * Date: 2020/4/26
 * Time: 10:17
 */
namespace app\admin\controller;
use app\admin\model\NodeModel;
use app\admin\service\DataToTreeService;
use think\Db;

class RoleController extends CommonController{
    public function index()
    {
        //查询所有角色（父子结构的数组）
        $roles  =   (new \app\admin\model\RoleModel())::select();
        return view('',['roles'=>$roles]);
    }

    //角色添加
    public function add()
    {
        if(request()->isGet()){
            //权限查询（父子结构）
            $node   =   new NodeModel();
            $nodes  =   (new DataToTreeService())->getToTree($node::where('status','1')->select()->toArray());
            //var_dump($nodes);exit;
            return view('',['nodes'=>$nodes]);
        }
        if(request()->isPost()){
            //接值
            $role_name=request()->post("role_name");
            $node_id=request()->post("node_id");
            if($role_name==""){
                $this->error("角色名称不能为空");
            }
            if($node_id==""){
                $this->error("请选择权限");
            }
            //开启事务，添加角色名称，然后添加角色权限表
            Db::startTrans();
            try {
                //添加角色
                $role_id=Db::name('role')->insertGetId(['role_name'=>$role_name,'create_time'=>date('Y-m-d H:i:s',time())]);
                \app\admin\model\RoleModel::addNodeRole($role_id,$node_id);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error($e->getMessage());return;
            }
            $this->success('','Role/index');
        }
    }

    //角色删除
    public function delete()
    {
    }

    //角色修改
    public function update()
    {
    }
}