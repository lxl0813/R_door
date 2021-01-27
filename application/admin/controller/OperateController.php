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
        $data['note_taker_id']  =   $this->cookie_admin()['id'];
        $sql    =   new AnalysisFormFieldValueModel();
        $result =   $sql->where($data)->order('create_time desc')->group('create_time')->select()->toArray();
        return view('',['result'=>$result]);
    }


    //运营平台的模块字段数据添加页面
    public function analysis_model_data_add()
    {
        //查询该管理员在改平台以及该模块下，所需要填写的字段。

        return view();
    }
    //运营平台的模块字段数据添加提交
    public function analysis_model_data_add_do()
    {

    }



}