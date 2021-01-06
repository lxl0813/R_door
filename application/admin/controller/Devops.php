<?php


namespace app\admin\controller;


use think\Db;
use think\facade\Cache;
use function Composer\Autoload\includeFile;

class Devops extends Common
{

    public function index()
    {
        $data   =   Db::name('devops')
                    ->alias('d')
                    ->join('devops_value dv','d.id=dv.data_name_id')
                    ->where('d.state','1')
                    ->group('create_time')
                    ->field('dv.create_time,dv.create_by,dv.platform_name')
                    ->order('dv.create_time')
                    ->select();

        return view('',['devops'=>$data]);
    }

    //今日数据添加页面
    public function today_data_add()
    {
        $devops =   Db::name('devops')->where('state','1')->select();
        return view('',['devops'=>$devops]);
    }

    //今日数据添加
    public function devops_add()
    {
        $data=input('array','');
        $time=input('time','');
        foreach ($data as $k=>$v)
        {
            $data[$k]                   =   explode('|',$v);
            $datas[$k]['data_name_id']  =   $data[$k][0];
            $datas[$k]['data_value']    =   $data[$k][1];
            $datas[$k]['create_time']   =   $time;
            $datas[$k]['create_by']     =   $this->cookie_admin()['name'];
        }
        try {
            Db::name('devops_value')->insertAll($datas);
        }catch (\Exception $e){
            echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
        }
        Cache::rm('devops_data_biao');Cache::rm('devops_data_title');
        echo json_encode(['code'=>200,'msg'=>'添加成功']);
    }

    

    public function devops_delete()
    {
        $create_time=input('create_time');
        if(Db::name('devops_value')->where('create_time',$create_time)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }


    public function devops_update()
    {

    }


    //看板所展示信息点管理
    public function data_name_index()
    {
        $devops    =   Db::name('devops')->select();
        return view('',['devops'=>$devops]);
    }

    //看板信息点删除
    public function data_delete()
    {
        $id   =   input('id');
        if (Db::name('devops')->where('id',$id)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'该条看板信息点已删除成功']);return;
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败，稍后再试！']);return;
        }
    }

    //看板信息点状态修改
    public function data_update()
    {
        $id    =   input('id');
        if(Db::name('devops')->where('id',$id)->update(['state'=>$state=Db::name('devops')->where('id',$id)->value('state')==1?2:1]))
        {
            echo json_encode(['code'=>200,'msg'=>'状态修改成功！']);return;
        }else{
            echo json_encode(['code'=>500,'msg'=>'状态修改失败稍后再试！']);return;
        }
    }

    //详情
    public function see_detail()
    {
        $create_time=input('create_time');
        $data=Db::name('devops_value')
            ->alias('dv')
            ->leftJoin('devops d','dv.data_name_id=d.id')
            ->where('dv.create_time',$create_time)
            ->field('d.data_name,dv.id,dv.data_value,dv.create_time')
            ->select();
        return view('',['devops_data'=>$data]);
    }

}