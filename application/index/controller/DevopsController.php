<?php


namespace app\index\controller;


use think\Controller;
use think\Db;
use think\facade\Cache;
use think\facade\Request;
use think\Validate;

class DevopsController extends Controller
{

    private $today_data;        //今日访问量（左上图）
    private $history_data;      //历史数据量（左下图）
    private $difference;        //较昨日差值（右上图）
    private $near_future_data;  //近30天   （右下图）


    public function index()
    {
        return view();
    }


    public function devops_centers(Request $request)
    {
        $devops_type                            =   $this->request->param('devops_type');
        $data_analysis_result                   =   $this->data_analysis($devops_type);             //数据采集分析（表格）
        $this->today_data['title']              =   $data_analysis_result['title'];                 //今日访问量标题（左上图）
        $this->today_data['today_data']         =   $data_analysis_result['today_data'];            //今日访问量（左上图）
        $this->history_data['title']            =   $data_analysis_result['title'];                 //历史数据量标题（左下图）
        $this->history_data['history_data']     =   $data_analysis_result['history_data'];          //历史数据量和（左下图）
        $this->difference['title']              =   $data_analysis_result['title'];                 //较昨日标题（右上图）
        $this->difference['difference']         =   $data_analysis_result['difference'];            //较昨日差值（右上图）
        //近30日的单点访问量
        $this->near_future_data                 =   $this->near_future_data($devops_type);          //近30天数据（右下图）
        //每日访问量总计
        $data_analysis_result['today_data_sum'] =   array_sum($data_analysis_result['today_data']);
        //累计访问量总计
        $data_analysis_result['data_sum']       =   array_sum($data_analysis_result['history_data'])+$data_analysis_result['today_data_sum'];
        return view();
    }



    //数据采集分析（表格）
    public function data_analysis($data)
    {
        $today_devops_data   =   Db::name('devops')
                                ->alias('d')
                                ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                                ->where('d.state','1')
                                ->where('dv.platform_name',$data)
                                ->where('dv.create_time',date('Y-m-d',time()))
                                ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                                ->select();
        $today_devops_data   =   $today_devops_data  ?   $today_devops_data  :   Db::name('devops')
                                                                                ->alias('d')
                                                                                ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                                                                                ->where('d.state','1')
                                                                                ->where('dv.platform_name',$data)
                                                                                ->where('dv.create_time',date("Y-m-d",strtotime("-1 day",time())))
                                                                                ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                                                                                ->select();
        $table_data =   [];
        foreach ($today_devops_data as $key=>$item)
        {
            //关键词
            $table_data['title'][]          =   $item['data_name'];
            //今日数据量
            $table_data['today_data'][]     =   $item['data_value'];
            //历史数据量
            $table_data['history_data'][]   =   Db::name('devops_value')
                                                ->where('data_name_id',$item['id'])
                                                ->where('create_time','<',date('Y-m-d',time()))
                                                ->sum('data_value');
            $table_data['difference'][]     =   $item['data_value']-(Db::name('devops_value')
                                                ->where('data_name_id',$item['id'])
                                                ->where('create_time',date("Y-m-d",strtotime("-1 day",strtotime($item['create_time']))))
                                                ->value('data_value'));
        }
        unset($today_devops_data);
        return $table_data;
    }



    //页面数据ajax调取
    public function ajax_data()
    {
        var_dump($this->near_future_data);exit;
        echo json_encode(['today_data'=>$this->today_data,'history_data'=>$this->history_data,'difference'=>$this->difference,'near_future_data'=>$this->near_future_data]);
    }



    //近30日访问量表
    public function near_future_data($data)
    {
        $near_future_data   =   Db::name('devops')
                                ->alias('d')
                                ->leftJoin('devops_value dv' ,'d.id=dv.data_name_id')
                                ->where('d.state','1')
                                ->where('dv.platform_name',$data)
                                ->where('dv.create_time','between',[date("Y-m-d",strtotime("-30 day",time())),date("Y-m-d",time())])
                                ->order('dv.create_time')
                                ->order('dv.data_name_id')
                                ->select();
        foreach ($near_future_data as $k => $v) {
            $near[$v['create_time']][] = $v;
        }
        foreach ($near_future_data as $k => $v) {
            $a[$v['data_name']][] = $v;
        }
        foreach ($near as $key=>$item)
        {
            $near_today_data['title'][]    =    array_column($item,'data_name');break;

        }
        foreach ($near as $key=>$item)
        {
            $near_today_data['date'][]     =    $key;
        }
        $near_today_data['title']=array_unique($near_today_data['title']);
        $near_today_data['date']=array_unique($near_today_data['date']);

        foreach ($a as $k=>$v)
        {
            foreach ($v as $k1=>$v1)
            {
                $near_today_data['data'][$k][]     =    $v1['data_value'];
            }
        }
        unset($near_future_data,$a);
        return $near_today_data;
    }



}