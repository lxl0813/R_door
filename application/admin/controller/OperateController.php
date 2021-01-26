<?php


namespace app\admin\controller;


use app\admin\model\AnalysisFormModel;
use app\admin\model\AnalysisNameModel;
use think\Db;

class OperateController extends CommonController
{
    //子屏
    public function operate_list()
    {
        //查询自有表单字段的已填写列表

        return view();
    }


    //表单数据点分配，
    public function operate_form_field()
    {
        //查询已分配的

        return view('');
    }

    //添加分析模块页面
    public function analysis_model_add()
    {
        //取平台
        $platform_name  =   Db::name('platform_name')->select();
        //取管理员
        $admin          =   Db::name('admin')->select();
        return view('',['platform_name'=>$platform_name,'admin'=>$admin]);
    }

    //添加分析模块以及对于字段和字段记录人
    public function analysis_model_add_do()
    {
        $data       =   input();
        $sql        =   new AnalysisNameModel();
        $where      =   ['platform_id'=>$data['platform_id'],'paltform_name'=>$data['paltform_name'],'analysis_name'=>$data['analysis_name']];
        $analysis   =   $sql->where($where)->find();
        if($analysis)
        {
            //如果该平台下，该模块已存在，则直接添加对应表单字段
            $analysisFormSql   =   new AnalysisFormModel();
            $insert =   [
                            'analysis_name_id'  =>  $analysis['analysis_name_id'],
                            'analysis_name'     =>  $data['analysis_name'],
                            'form_field'        =>  $data['form_field'],
                            'create_time'       =>  date("Y-m-d H:i:s",time()),
                            'create_by'         =>  $this->cookie_admin()['name'],
                            'note_taker_id'     =>  $data['people_id'],
                            'note_taker_id'     =>  $data['people_name'],
                        ];
            try{
                $result =   $analysisFormSql->insert($insert);
                if($result)
                {
                    echo json_encode(['code'=>200,'msg'=>'添加成功！']);
                }
            }catch(\Exception $e){
                echo json_encode(['code'=>500,'msg'=>$e->getMessage()]);
            }
        }else{
            //如果改平台下，不存在，则添加模块，然后添加模块对应字段
            // 启动事务
            Db::startTrans();
            try {
                $insert1 =   [
                    'analysis_name'     =>  $data['analysis_name'],
                    'create_time'       =>  date("Y-m-d H:i:s",time()),
                    'create_by'         =>  $this->cookie_admin()['name'],
                    'platfoem_name_id'  =>  $data['platform_id'],
                    'platform_name'     =>  $data['platform_name'],
                ];
                $analysis_name_id   =   Db::table('analysis_name')->insertGetId($insert1);
                $insert2 =   [
                    'analysis_name_id'  =>  $analysis_name_id,
                    'analysis_name'     =>  $data['analysis_name'],
                    'form_field'        =>  $data['form_field'],
                    'create_time'       =>  date("Y-m-d H:i:s",time()),
                    'create_by'         =>  $this->cookie_admin()['name'],
                    'note_taker_id'     =>  $data['people_id'],
                    'note_taker_id'     =>  $data['people_name'],
                ];
                Db::table('analysis_form')->insert($insert2);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }

    }



    //分析模块下添加表字段，并指定记录人
    public function analysis_form_field()
    {

    }


    //子屏表单数据点添加页面
    public function operate_form_field_add()
    {

    }


    //子屏表单数据点添加
    public function operate_form_field_add_do()
    {

    }


    //子屏表单数据添加页面
    public function operate_form_add()
    {

    }


    //子屏表单数据添加
    public function operate_form_add_do()
    {

    }
}