<?php


namespace app\admin\controller;


use app\admin\model\AnalysisFormFieldValueModel;
use app\admin\model\AnalysisFormModel;
use app\admin\model\AnalysisNameModel;
use think\Db;

class OperateController extends CommonController
{
    //子屏
    public function operate_list()
    {
        //查询自有表单字段的已填写列表
        $platform_name  =   Db::name('analysis_form')
                            ->where(['note_taker_id'=>$this->cookie_admin()['id']])
                            ->field('platform_name_id,platform_name,note_taker_name')
                            ->group('platform_name')
                            ->select();

        foreach ($platform_name as $key=>$item)
        {
            $where  =   [
                "platform_name"     =>  $item['platform_name'],
                "platform_name_id"  =>  $item['platform_name_id'],
                "note_taker_name"   =>  $item['note_taker_name'],
            ];
            $platform_name[$key]['model']   =   $this->assoc_unique(Db::name('analysis_form')->where($where)->select(),'analysis_name');
        }
        return view('',['platform'=>$platform_name]);
    }

    //二维数组去重
    function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
    }

    //添加分析模块页面
    public function analysis_model_add()
    {
        //取平台
        $platform_name  =   Db::name('platform_name')->select();
        //取管理员
        $admin          =   Db::name('admin')->select();
        //分析模块名
        $analysis_model =   Db::name('analysis_name')->select();
        return view('',['platform_name'=>$platform_name,'admin'=>$admin,'analysis_model'=>$analysis_model]);
    }

    //表单字段指定提交
    public function analysis_model_add_do()
    {
        $data   =   input();
        if(isset($data['analysis_name_id']))
        {
            //如果有分析模块的id，表示已存在，则，直接添加
            $sql    =   new AnalysisFormModel();
            $insert =   [
                "analysis_name_id"  =>  $data['analysis_name_id'],
                "analysis_name"     =>  $data['analysis_name'],
                "form_field"        =>  $data['form_field'],
                "create_time"       =>  date("Y-m-d H:i:s",time()),
                "create_by"         =>  $this->cookie_admin()['name'],
                "note_taker_name"   =>  $data['note_taker_name'],
                "note_taker_id"     =>  $data['note_taker_id'],
                "platform_name_id"  =>  $data['platform_name_id'],
                "platform_name"     =>  $data['platform_name'],
            ];
            try {
                $result =   $sql->insert($insert);
                if($result){
                    echo json_encode(['code'=>200,'msg'=>'成功！']);return;
                }
            }catch (\Exception $e){
                echo json_encode(['code'=>500,'msg'=>$e->getMessage()]);return;
            }
        }else{
            //如果没有分析模块的id，表示不存在。则。先加模块在家表单
            $analysisNameSql        =   new AnalysisNameModel();
            $analysisFormSql        =   new AnalysisFormModel();
            $nameParam              =   [
                "analysis_name"     =>  $data['analysis_name'],
                "create_time"       =>  date("Y-m-d H:i:s",time()),
                "create_by"         =>  $this->cookie_admin()['name'],
                "platform_name"     =>  $data['platform_name'],
                "platform_name_id"  =>  $data['platform_name_id'],
            ];
            // 启动事务
            Db::startTrans();
            try {
                $id =   $analysisNameSql->insertGetId($nameParam);
                $formParam              =   [
                    "analysis_name_id"  =>  $id,
                    "analysis_name"     =>  $data['analysis_name'],
                    "form_field"        =>  $data['form_field'],
                    "create_time"       =>  date("Y-m-d H:i:s",time()),
                    "create_by"         =>  $this->cookie_admin()['name'],
                    "note_taker_name"   =>  $data['note_taker_name'],
                    "note_taker_id"     =>  $data['note_taker_id'],
                    "platform_name_id"  =>  $data['platform_name_id'],
                    "platform_name"     =>  $data['platform_name'],
                ];
                $analysisFormSql->insert($formParam);
                // 提交事务
                Db::commit();
                echo json_encode(['code'=>200,'msg'=>'成功！']);return;
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                echo json_encode(['code'=>500,'msg'=>$e->getMessage()]);return;
            }
        }
    }

    //分配页面，根据所选平台查询分析模块
    public function platformFindAnalysis()
    {
        $sql        =   new AnalysisNameModel();
        $analysis   =   $sql->where(input())->select();
        echo json_encode(['code'=>200,'msg'=>'成功!','result'=>$analysis]);
    }

    //运营平台的模块管理
    public function operate_administration()
    {
        $data       =   input();
        $admin_id   =   $this->cookie_admin()['id'];
        //查询已记录的数据展示
        $sql        =   new AnalysisFormModel();
        $where      =   [
            'note_taker_id'=>$admin_id,
            'platform_name_id'=>input('platform_name_id'),

        ];
        $analysis_form_data =   $sql->where($where)->select();

    }

    //运营平台的模块管理
    public function analysis_model_admin()
    {
        $data   =   input();
        //var_dump($data);exit;
        $data['note_taker_id']  =   $this->cookie_admin()['id'];
        $sql    =   new AnalysisFormFieldValueModel();
        $result =   $sql->where($data)->order('create_time desc')->group('create_time')->select()->toArray();
        return view('',['result'=>$result,'params'=>$data]);
    }

    //运营平台的模块字段数据添加页面
    public function analysis_model_data_add()
    {
        //查询该管理员在改平台以及该模块下，所需要填写的字段。
        $data   =   input();
        $sql    =   new AnalysisFormModel();
        $where  =   [
            'note_taker_id'     =>  $this->cookie_admin()['id'],
            'note_taker_name'   =>  $this->cookie_admin()['name'],
            'analysis_name_id'     =>  $data['analysis_name_id'],
            'platform_name_id'  =>  $data['platform_name_id'],
        ];
        $result =   $sql->where($where)->select()->toArray();
        foreach ($result as $key=>$item)
        {
            $attr['platform_name_id']   =   $item['platform_name_id'];
            $attr['platform_name']      =   $item['platform_name'];
            $attr['analysis_name_id']   =   $item['analysis_name_id'];
            $attr['analysis_name']      =   $item['analysis_name'];
        }
        return view('',['result'=>$result,'attr'=>$attr]);
    }

    //运营平台的模块字段数据添加提交
    public function analysis_model_data_add_do()
    {
        $public_param         =   input();
        unset($public_param['array']);
        $param          =   input('array');

        foreach ($param as$key=>$item)
        {
            $chai                             =    explode('|',$item);
            $findParams[]                     =    $chai[0];
            $findParam['create_time']         =    $public_param['create_time'];
            $findParam['platform_name_id']    =    $public_param['platform_name_id'];
            $findParam['analysis_name_id']    =    $public_param['analysis_name_id'];
            $findParam['platform_name']       =    $public_param['platform_name'];
            $findParam['analysis_name']       =    $public_param['analysis_name'];
            $findParam['note_taker_id']       =    $this->cookie_admin()['id'];
            $findParam['note_taker_name']     =    $this->cookie_admin()['name'];
        }
        $sql    =   new AnalysisFormFieldValueModel();
        $result =   $sql->where($findParam)->whereIn('form_field_id',$findParams)->find();
        if($result)
        {
            echo json_encode(['code'=>500,'msg'=>'请不要重复添加！']);return;
        }else{
            foreach ($param as$key=>$item)
            {
                $chai                                =    explode('|',$item);
                $params[$key]['form_field_id']       =    $chai[0];
                $params[$key]['form_field_value']    =    $chai[1];
                $params[$key]['form_field']          =    $chai[2];
                $params[$key]['create_time']         =    $public_param['create_time'];
                $params[$key]['platform_name_id']    =    $public_param['platform_name_id'];
                $params[$key]['analysis_name_id']    =    $public_param['analysis_name_id'];
                $params[$key]['platform_name']       =    $public_param['platform_name'];
                $params[$key]['analysis_name']       =    $public_param['analysis_name'];
                $params[$key]['note_taker_id']       =    $this->cookie_admin()['id'];
                $params[$key]['note_taker_name']     =    $this->cookie_admin()['name'];
            }
            unset($public_param,$param);
            $sql    =   new AnalysisFormFieldValueModel();
            try {
                $sql->insertAll($params);
            }catch (\Exception $e){
                echo json_encode(['code'=>500,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['code'=>200,'msg'=>'添加成功！']);return;
        }
    }

    //运营平台某人某平台下，某模块下，某天的数据删除
    public function platform_analysis_data_delete()
    {
        $param                      =   explode('|',input('param'));
        $params['platform_name']    =   $param[0];
        $params['analysis_name']    =   $param[1];
        $params['create_time']      =   $param[2];
        $params['note_taker_id']    =   $this->cookie_admin()['id'];
        $params['note_taker_name']  =   $this->cookie_admin()['name'];
        $sql    =   new AnalysisFormFieldValueModel();
        try {
            $result =   $sql->where($params)->delete();
            if($result){
                echo json_encode(['code'=>'200','msg'=>'删除成功！']);
            }
        }catch (\Exception $e){
            echo json_encode(['code'=>500,'msg'=>$e->getMessage()]);
        }
    }

    //




}