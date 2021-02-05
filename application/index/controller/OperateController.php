<?php


namespace app\index\controller;
use app\index\model\AnalysisFormFieldValueModel;
use app\index\model\AnalysisFormModel;
use app\index\model\AnalysisNameModel;
use think\Controller;
use think\Db;
use think\Request;
class OperateController extends Controller
{


    public function platformFindAnalysis($platform_name)
    {
        $analysis_model =   AnalysisNameModel::where($platform_name)->select();
        return $analysis_model;
    }

    /**
     * 运营大屏页面
     * @return \think\response\View
     */
    public function operateBigScreen(Request $request)
    {
        $platform_name      =   $request->param();
        //取该平台下的分析模块信息
        $analysis_model     =   $this->platformFindAnalysis($platform_name);
        //查询该平台下的运营数据分析模块ID
        $platform_name['analysis_name_id']   =   AnalysisNameModel::where($platform_name)->where(['analysis_name'=>'运营数据分析'])->value('id');
        $where              =   $platform_name;
        //查询该平台、该模块下的字段ID、初始值
        $form_field         =   AnalysisFormModel::where($where)->whereIn('form_field',['网站浏览量','APP浏览量','产品单品数','发布产品数'])->select()->toArray();
        //根据平台、分析模块，查找计算：总网站浏览量、总APP浏览量、总产品单品数、总发布产品数
        foreach ($form_field as $key=>$item)
        {
            $form_field[$key]['nums']   =   $item['initial_value']+(int)AnalysisFormFieldValueModel::where($platform_name)->where(['form_field_id'=>$item['id']])->sum('form_field_value');
        }
        return view('',['model'=>$analysis_model,'platform_name'=>$platform_name['platform_name'],'form_field'=>$form_field]);
    }
    /**
     *运营中心大屏 ajax获取数据
     * @param  Request $request   platform_name(平台名称)  analysis_name_id(模块id)
     * @return array
     */
    public function operateBigScreenAjax(Request $request)    {
        $param              =   $request->request();
        //运营中心右上角相关数据
        $right_top  =   $this->operate_right_top($param);
        //运营中心左上角相关数据
        $left_top   =   $this->operate_left_top($param);
        //运营中心右下角相关数据
        $right_lower=   $this->operate_right_lower($param);
        echo json_encode(['right_top'=>$right_top,'left_top'=>$left_top,'right_lower'=>$right_lower]);return;
    }

    //运营中心大屏右上角统计图
    public function operate_right_top($param)
    {
        $analysisFormModel  =   AnalysisFormModel::where($param)->whereIn('form_field',['免费供应商数量','收费供应商数量','采购商注册数量'])->select()->toArray();
        foreach ($analysisFormModel as $key=>$item)
        {
            $params['form_field_id']            =   $item['id'];
            $params['analysis_name_id']         =   $item['analysis_name_id'];
            $params['platform_name']            =   $item['platform_name'];
            $data['date']                       =   array_reverse(Db::name('analysis_form_field_value')->where($params)->order('create_time desc')->limit(13)->column('create_time'));
            $data['title'][]       =   $item['form_field'];
            $data['data'][] =   [
                'name'      =>  $item['form_field'],
                'type'      =>  'bar',
                'barWidth'  =>  '22',
                'data'      =>  array_reverse(Db::name('analysis_form_field_value')->where($params)->order('create_time desc')->limit(13)->column('form_field_value'))
            ];

        }
        return $data;
    }
    //运营中心左上角统计图
    public function operate_left_top($param)
    {
        $analysisFormModel  =   AnalysisFormModel::where($param)->whereIn('form_field',['免费供应商数量','收费供应商数量','采购商注册数量','供应商总数量'])->select()->toArray();
        foreach ($analysisFormModel as $key=>$item)
        {
            $params['form_field_id']            =   $item['id'];
            $params['analysis_name_id']         =   $item['analysis_name_id'];
            $params['platform_name']            =   $item['platform_name'];
            $nums    =   $item['initial_value']+(int)Db::name('analysis_form_field_value')->where($params)->sum('form_field_value');
            if($item['form_field']=="采购商注册数量")
            {
                $new['in'][]    =   [
                    'value' =>  $nums,
                    'name'  =>  '采购商',
                    'itemStyle'=>[
                        'normal'=>[
                            'color'=>   '#30A3F1'
                        ]
                    ]
                ];
                $new['wai'][] =   [
                    'value' =>  $nums,
                    'name'  =>  $item['form_field'],
                    'itemStyle'=>[
                        'normal'=>[
                            'color'=>   '#30A3F1'
                        ]
                    ]
                ];
            }
            if($item['form_field']=='供应商总数量')
            {
                $new['in'][]    =   [
                    'value' =>  $nums,
                    'name'  =>  '供应商',
                    'itemStyle'=>[
                        'normal'=>[
                            'color'=>   '#0C66E0'
                        ]
                    ]
                ];
                $new['wai'][] =   [
                    'value' =>  $nums,
                    'name'  =>  $item['form_field'],
                    'itemStyle'=>[
                        'normal'=>[
                            'color'=>   '#58DDD8'
                        ]
                    ]
                ];
            }
            if($item['form_field']=='收费供应商数量'){
                $new['wai'][] =   [
                    'value' =>  $nums,
                    'name'  =>  $item['form_field'],
                    'itemStyle'=>[
                        'normal'=>[
                            'color'=>   '#E49631'
                        ]
                    ]
                ];
            }
            if($item['form_field']=='免费供应商数量'){
                $new['wai'][] =   [
                    'value' =>  $nums,
                    'name'  =>  $item['form_field'],
                    'itemStyle'=>[
                        'normal'=>[
                            'color'=>   '#EA5051'
                        ]
                    ]
                ];
            }
        }
        return $new;

    }
    //运营中心右下角统计图
    public function operate_right_lower($param)
    {
        $analysisFormModel         =   AnalysisFormModel::where($param)->whereIn('form_field',['网站浏览量','APP浏览量','产品单品数','发布产品数'])->select()->toArray();
        //根据平台、分析模块，查找计算：总网站浏览量、总APP浏览量、总产品单品数、总发布产品数
        foreach ($analysisFormModel as $key=>$item)
        {
            $params['form_field_id']            =   $item['id'];
            $params['analysis_name_id']         =   $item['analysis_name_id'];
            $params['platform_name']            =   $item['platform_name'];
            $data['title']  =   ['网站浏览量','APP浏览量','产品单品数','发布产品数'];
            $data['date']   =   array_reverse(Db::name('analysis_form_field_value')->where($params)->order('create_time desc')->limit(12)->column('create_time'));
            if($item['form_field']=='APP浏览量')
            {
                $data['data'][]   =   [
                    'name'      =>  $item['form_field'],
                    'type'      =>  'bar',
                    'barWidth'  =>  '16',
                    'data'      =>  array_reverse(Db::name('analysis_form_field_value')->where($params)->order('create_time desc')->limit(13)->column('form_field_value'))
                ];
            }
            if($item['form_field']=='网站浏览量')
            {
                $data['data'][]   =   [
                    'name'      =>  $item['form_field'],
                    'type'      =>  'bar',
                    'barWidth'  =>  '16',
                    'data'      =>  array_reverse(Db::name('analysis_form_field_value')->where($params)->order('create_time desc')->limit(13)->column('form_field_value'))
                ];
            }
            if($item['form_field']=='发布产品数')
            {
                $data['data'][]   =   [
                    'name'      =>  $item['form_field'],
                    'type'      =>  'line',
                    'data'      =>  array_reverse(Db::name('analysis_form_field_value')->where($params)->order('create_time desc')->limit(13)->column('form_field_value'))
                ];
            }
            if($item['form_field']=='产品单品数')
            {
                $data['data'][]   =   [
                    'name'      =>  $item['form_field'],
                    'type'      =>  'line',
                    'data'      =>  array_reverse(Db::name('analysis_form_field_value')->where($params)->order('create_time desc')->limit(13)->column('form_field_value'))
                ];
            }
        }
        return $data;
    }
















    /**
     * 营销大屏页面
     * @return \think\response\View
     */
    public function marketingBigScreen(Request $request)
    {
        echo 123;exit;
        $platform_name  =   $request->input();
        //取分析模块信息
        $analysis_model =   $this->platformFindAnalysis($platform_name);
        return view('',['model'=>$analysis_model,'platform'=>$platform_name]);
    }
    /**
     *营销大屏 ajax数据获取
     * @param Request $request
     * @return array
     */
    public function marketingBigScreenAjax(Request $request)
    {
        $param  =   $request->input();
        $result =   AnalysisFormModel::analysisFormWithValue($param);
        var_dump($result);
    }


    /**
     * 订单大屏页面
     * @return \think\response\View
     */
    public function orderBigScreen(Request $request)
    {
        $platform_name  =   $request->input();
        //取分析模块信息
        $analysis_model =   $this->platformFindAnalysis($platform_name);
        return view('',['model'=>$analysis_model,'platform'=>$platform_name]);
    }
    /**
     * 订单大屏 ajax 数据获取
     * @param Request $request
     * @return array
     */
    public function orderBigScreenAjax(Request $request)
    {
        $param  =   $request->input();
        $result =   AnalysisFormModel::analysisFormWithValue($param);
        var_dump($result);
    }


    /**
     * 外媒大屏页面
     * @return \think\response\View
     */
    public function foreignBigScreen(Request $request)
    {
        $platform_name  =   $request->input();
        //取分析模块信息
        $analysis_model =   $this->platformFindAnalysis($platform_name);
        return view('',['model'=>$analysis_model,'platform'=>$platform_name]);
    }
    /**
     * 外媒大屏 ajax数据获取
     * @param Request $request
     * @return array
     */
    public function foreignBigScreenAjax(Request $request)
    {
        $param  =   $request->input();
        $result =   AnalysisFormModel::analysisFormWithValue($param);
        var_dump($result);
    }


    /**
     * 测试大屏页面
     */
    public function testBigScreen(Request $request)
    {
        $platform_name      =   $request->param();
        //取该平台下的分析模块信息
        $analysis_model     =   $this->platformFindAnalysis($platform_name);//$analysis_model     =   $this->status($analysis_model,'运营数据分析');
        //查询该平台下的运营数据分析模块ID
        $platform_name['analysis_name_id']   =   AnalysisNameModel::where($platform_name)->where(['analysis_name'=>'测试订单数据分析'])->value('id');
        $where              =   $platform_name;
        //查询该平台、该模块下的字段ID、初始值
        $form_field         =   AnalysisFormModel::where($where)->whereIn('form_field',['网站浏览量','APP浏览量','产品单品数','发布产品数'])->select()->toArray();
        //根据平台、分析模块，查找计算：总网站浏览量、总APP浏览量、总产品单品数、总发布产品数
        foreach ($form_field as $key=>$item)
        {
            $form_field[$key]['nums']   =   $item['initial_value']+(int)AnalysisFormFieldValueModel::where($platform_name)->where(['form_field_id'=>$item['id']])->sum('form_field_value');
        }
        return view('',['model'=>$analysis_model,'platform_name'=>$platform_name['platform_name'],'form_field'=>$form_field]);
    }
    /**
     * 测试大屏 ajax数据获取
     * @param Request $request
     * @return array
     */
    public function testBigScreenAjax(Request $request)
    {
        $param  =   $request->input();
        $result =   AnalysisFormModel::analysisFormWithValue($param);
        var_dump($result);
    }


}