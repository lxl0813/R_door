<?php


namespace app\admin\controller;


use think\Db;
use think\facade\Cache;
use function Composer\Autoload\includeFile;

class Devops extends Common
{

    public function index()
    {
        return view();
    }


    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 化纤中文平台页面
     */
    public function fiber_ch_list()
    {
        $data   =   Db::name('devops')
                    ->alias('d')
                    ->join('devops_value dv','d.id=dv.data_name_id')
                    ->where('d.state','1')
                    ->where('dv.platform_name','化纤中文平台')
                    ->group('create_time')
                    ->field('dv.create_time,dv.create_by,dv.platform_name')
                    ->order('dv.create_time')
                    ->select();
        return view('',['fiber_ch_devops'=>$data]);
    }

    //化纤中文添加
    public function fiber_ch_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['fiber_ch_title'=>$devops]);
        }

        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '化纤中文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }


    //化纤中文修改
    public function fiber_ch_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }

    //化纤中文删除
    public function fiber_ch_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }

    //化纤英文列表
    public function fiber_en_list()
    {
        $data   =   Db::name('devops')
            ->alias('d')
            ->join('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.platform_name','化纤英文平台')
            ->group('create_time')
            ->field('dv.create_time,dv.create_by,dv.platform_name')
            ->order('dv.create_time')
            ->select();
        return view('',['fiber_en_devops'=>$data]);
    }

    //化纤英文添加
    public function fiber_en_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['fiber_en_title'=>$devops]);
        }

        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '化纤英文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }

    //化纤英文修改
    public function fiber_en_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }

    //化纤英文删除
    public function fiber_en_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }

    //无纺中文列表
    public function non_ch_list()
    {
        $data   =   Db::name('devops')
            ->alias('d')
            ->join('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.platform_name','无纺中文平台')
            ->group('create_time')
            ->field('dv.create_time,dv.create_by,dv.platform_name')
            ->order('dv.create_time')
            ->select();
        return view('',['non_ch_devops'=>$data]);
    }

    //无纺中文添加
    public function non_ch_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['non_ch_title'=>$devops]);
        }

        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '无纺中文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }

    //无纺中文修改
    public function non_ch_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }

    //无纺中文删除
    public function non_ch_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }

    //无纺英文列表
    public function non_en_list()
    {
        $data   =   Db::name('devops')
            ->alias('d')
            ->join('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.platform_name','无纺英文平台')
            ->group('create_time')
            ->field('dv.create_time,dv.create_by,dv.platform_name')
            ->order('dv.create_time')
            ->select();
        return view('',['non_en_devops'=>$data]);
    }

    //无纺英文添加
    public function non_en_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['non_en_title'=>$devops]);
        }

        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '无纺英文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }

    //无纺英文修改
    public function non_en_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }

    //无纺英文删除
    public function non_en_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }

    //制品中文列表
    public function zhi_ch_list()
    {
        $data   =   Db::name('devops')
            ->alias('d')
            ->join('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.platform_name','制品中文平台')
            ->group('create_time')
            ->field('dv.create_time,dv.create_by,dv.platform_name')
            ->order('dv.create_time')
            ->select();
        return view('',['zhi_ch_devops'=>$data]);
    }

    //制品中文添加
    public function zhi_ch_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['zhi_ch_title'=>$devops]);
        }

        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '制品中文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }

    //制品中文修改
    public function zhi_ch_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }

    //制品中文删除
    public function zhi_ch_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }

    //制品英文列表
    public function zhi_en_list()
    {
        $data   =   Db::name('devops')
            ->alias('d')
            ->join('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.platform_name','制品英文平台')
            ->group('create_time')
            ->field('dv.create_time,dv.create_by,dv.platform_name')
            ->order('dv.create_time')
            ->select();
        return view('',['zhi_en_devops'=>$data]);
    }

    //制品英文添加
    public function zhi_en_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['zhi_en_title'=>$devops]);
        }

        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '制品英文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }

    //制品英文修改
    public function zhi_en_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }

    //制品英文删除
    public function zhi_en_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }

    //纱线中文列表
    public function sha_ch_list()
    {
        $data   =   Db::name('devops')
            ->alias('d')
            ->join('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.platform_name','纱线中文平台')
            ->group('create_time')
            ->field('dv.create_time,dv.create_by,dv.platform_name')
            ->order('dv.create_time')
            ->select();
        return view('',['sha_ch_devops'=>$data]);
    }

    //纱线中文添加
    public function sha_ch_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['sha_ch_title'=>$devops]);
        }

        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '纱线中文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }

    //纱线中文修改
    public function sha_ch_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }


    //纱线中文删除
    public function sha_ch_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
    }

    //纱线英文列表
    public function sha_en_list()
    {
        $data   =   Db::name('devops')
            ->alias('d')
            ->join('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.platform_name','纱线英文平台')
            ->group('create_time')
            ->field('dv.create_time,dv.create_by,dv.platform_name')
            ->order('dv.create_time')
            ->select();
        return view('',['sha_en_devops'=>$data]);
    }

    //纱线英文添加
    public function sha_en_add()
    {
        if(request()->isGet()){
            $devops =   Db::name('devops')->where('state','1')->select();
            return view('',['sha_en_title'=>$devops]);
        }
        if(request()->isPost())
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
                $datas[$k]['platform_name'] =   '纱线英文平台';
            }
            try {
                Db::name('devops_value')->insertAll($datas);
            }catch (\Exception $e){
                echo json_encode(['code'=>200,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功']);
        }
    }


    //纱线英文修改
    public function sha_en_update()
    {
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost())
        {

        }
    }


    //纱线英文删除
    public function sha_en_delete()
    {
        $where=input();
        if(Db::name('devops_value')->where($where)->delete())
        {
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败！']);
        }
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
        $create_time    =   input('create_time');
        $platform_name  =   input('platform_name');
        $data=Db::name('devops_value')
            ->alias('dv')
            ->leftJoin('devops d','dv.data_name_id=d.id')
            ->where('dv.create_time',$create_time)
            ->where('platform_name',$platform_name)
            ->field('d.data_name,dv.id,dv.data_value,dv.create_time,dv.platform_name')
            ->select();
        return view('',['devops_data'=>$data]);
    }

}