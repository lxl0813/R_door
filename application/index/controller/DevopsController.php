<?php


namespace app\index\controller;
use think\Controller;
use think\Db;
use think\facade\Cache;
use think\facade\Request;
class DevopsController extends Controller
{
    public function index()
    {
        return view();
    }
    public function devops_centers(Request $request)
    {
        $devops_type                                =   $this->request->param('devops_type');
        $data_analysis_result                       =   $this->data_analysis($devops_type);             //数据采集分析（表格）
        $title  =   $data_analysis_result['title'];
        $today  =   $data_analysis_result['today_data'];
        $arr = array();//创建一个空数组
        foreach ($title as $key => $val) {
            foreach ($today as $k1=>$v1)
            {
                if($key==$k1){
                    $arr[$key]['type']  =   'bar';
                    $arr[$key]['data']  =   array($v1);
                    $arr[$key]['coordinateSystem']  =   'polar';
                    $arr[$key]['name']  =   $val;//循环变量id并赋值进数组内
                }
            }
        }
        $today_data['title']                        =   $data_analysis_result['title'];                 //今日访问量标题（左上图）
        $today_data['today_data']                   =   $arr;  unset($arr);                             //今日访问量（左上图）
        $history_data['title']                      =   $data_analysis_result['title'];                 //历史数据量标题（左下图）
        $history_data['history_data']               =   $data_analysis_result['history_data'];          //历史数据量和（左下图）
        $difference['title']                        =   $data_analysis_result['title'];                 //较昨日标题（右上图）
        $arrs = array();//创建一个空数组
        foreach ($data_analysis_result['title'] as $key=>$item)
        {
            foreach ($data_analysis_result['difference'] as $k1=>$v1)
            {
                if($key==$k1){
                    $arrs[$key]['name']   =   $item;//循环变量id并赋值进数组内
                    $arrs[$key]['value']  =   $v1;
                }
            }
        }
        $difference['difference']                   =   $arrs;           //较昨日差值（右上图）
        //近30日的单点访问量
        $near_future_data                           =   $this->near_future_data($devops_type);          //近30天数据（右下图）
        Cache::set('today_data',$today_data,5);
        Cache::set('history_data',$history_data,5);
        Cache::set('difference',$difference,5);
        Cache::set('near_future_data',$near_future_data,5);
        $data_analysis_result['today_data_sum']     =   array_sum($data_analysis_result['today_data']);
        //累计访问量总计
        $data_analysis_result['data_sum']           =   array_sum($data_analysis_result['history_data'])+$data_analysis_result['today_data_sum'];
        //var_dump($data_analysis_result);exit;
        return view('',['data_analysis_result'=>$data_analysis_result]);
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

        if($today_devops_data){

        }else{
            $today_devops_data  =   Db::name('devops')
                                    ->alias('d')
                                    ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                                    ->where('d.state','1')
                                    ->where('dv.platform_name',$data)
                                    ->where('dv.create_time',date("Y-m-d",strtotime("-1 day",time())))
                                    ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                                    ->select();
            if(!$today_devops_data){
                $today_devops_data  =   Db::name('devops')
                                        ->alias('d')
                                        ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                                        ->where('d.state','1')
                                        ->where('dv.platform_name',$data)
                                        ->where('dv.create_time',date("Y-m-d",strtotime("-2 day",time())))
                                        ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                                        ->select();
                if(!$today_devops_data){
                    $today_devops_data  =   Db::name('devops')
                                            ->alias('d')
                                            ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                                            ->where('d.state','1')
                                            ->where('dv.platform_name',$data)
                                            ->where('dv.create_time',date("Y-m-d",strtotime("-3 day",time())))
                                            ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                                            ->select();
                }
            }
        }
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
        $near_today_data['title']=array_unique($near_today_data['title'][0]);
        $near_today_data['date']=array_unique($near_today_data['date']);
        foreach ($a as $k=>$v)
        {
            foreach ($v as $k1=>$v1)
            {
                $near_today_data['data'][$k]['name']        =    $v1['data_name'];
                $near_today_data['data'][$k]['type']        =    'line';
                $near_today_data['data'][$k]['data'][]      =    $v1['data_value'];
                $near_today_data['data'][$k]['smooth']      =    true;
                if($near_today_data['data'][$k]['name']=='软文'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#2085CA'];
                }elseif($near_today_data['data'][$k]['name']=='百度关键字'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#DF1F20'];
                }elseif($near_today_data['data'][$k]['name']=='今日头条'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#DF8B1F'];
                }elseif($near_today_data['data'][$k]['name']=='公众号'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#9E8A6A'];
                }elseif($near_today_data['data'][$k]['name']=='展会信息'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#61B7B4'];
                }elseif($near_today_data['data'][$k]['name']=='手机报'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#6561B7'];
                }elseif($near_today_data['data'][$k]['name']=='行业资讯'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#61B76F'];
                }elseif($near_today_data['data'][$k]['name']=='分析中心'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#77C8FF'];
                }elseif($near_today_data['data'][$k]['name']=='数据中心'){
                    $near_today_data['data'][$k]['lineStyle']   =    ['color'=>'#E672FF'];
                }
            }
        }
        foreach ($near_today_data['data'] as $k=>$v)
        {
            $as[]=$v;
        }
        $near_today_data['data']=$as;
        unset($near_future_data,$a);
        return $near_today_data;
    }
    //页面数据ajax调取
    public function ajax_data()
    {
        $today_data         =   Cache::get('today_data');
        $history_data       =   Cache::get('history_data');
        $difference         =   Cache::get('difference');
        $near_future_data   =   Cache::get('near_future_data');
        echo json_encode(['today_data'=>$today_data,'history_data'=>$history_data,'difference'=>$difference,'near_future_data'=>$near_future_data]);
    }
    //getkitToken获取
    public function getkitToken(){
        $post_data['system']['ver']="1.0";
        $post_data['system']['appId']="lc22490f64d5c142d2";                 //使用的应用信息里的APP KEY
        $post_data['system']['sign']="a29f288dfb3e494e8617542e79578c";      //根据
        $post_data['system']['time']=time();
        $post_data['system']['nonce']="fbf19fc6-17a1-4f73-a967-75easbc805a3";
        $post_data['id']="98a7a257-c4e4-4db3-a2d3-s97a3836b87d";
        $post_data['params']=(object)[];
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/json',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents("https://openapi.lechange.cn/openapi/accessToken", false, $context);
        return $result;
    }
}