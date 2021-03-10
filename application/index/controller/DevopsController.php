<?php


namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Request;
class DevopsController extends Controller
{

    public function index()
    {
        // $data   =   Db::name('devops_value')->select();
        // $pt =   ['化纤英文平台','无纺中文平台','无纺英文平台','制品中文平台','制品英文平台','纱线中文平台','纱线英文平台'];
        // //var_dump($data);exit;
        // $copy   =   $data;
        // for ($i=0; $i<=6; $i++) {
        //     foreach ($copy as $key => $item) {
        //         unset($copy[$key]['id']);
        //         $copy[$key]['platform_name']=$pt[$i];
        //         $a[]=$copy[$key];
        //     }
        // }
        // Db::name('devops_value')->insertAll($a);
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

        //初始获取办公室的监控accessToken;
        $bgAccessToken    =   $this->getBgAccessToken();
        $url    =   "https://open.ys7.com/ezopen/h5/iframe_se?url=ezopen://open.ys7.com/E66358911/1.live&autoplay=0&audio=1&accessToken=".$bgAccessToken;

        return view('',['data_analysis_result'=>$data_analysis_result,'url'=>$url,'devops_type'=>$devops_type]);
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
                    if(!$today_devops_data){
                        $today_devops_data  =   Db::name('devops')
                            ->alias('d')
                            ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                            ->where('d.state','1')
                            ->where('dv.platform_name',$data)
                            ->order('dv.create_time desc')
                            ->limit(9)
                            //->where('dv.create_time',date("Y-m-d",strtotime("-3 day",time())))
                            ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                            ->select();
                    }
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


    //济南工厂监控接口请求
    public function jiNanJK()
    {
        $accessToken    =   $this->getAccessToken();
        if($accessToken!=false){
            $kitToken   =   $this->getkitToken($accessToken);
            echo json_encode(['code'=>200,'kitToken'=>$kitToken]);
        }else{
            echo json_encode(['code'=>500,'msg'=>'accessToken获取失败!']);
        }
    }


    //工厂监控的accessToken获取
    public function getAccessToken(){
        $time                           =   time();                                             //时间戳           sign加密所需
        $appSecret                      =   "a29f288dfb3e494e8617542e79578c";                   //账户appSecret    sign加密所需
        $nonce                          =   md5(uniqid());                                      //32位随机数        sign加密所需
        $initsign                       =   "time:$time".","."nonce:$nonce".","."appSecret:$appSecret";
        $sign                           =   md5($initsign);
        //接口请求参数
        $post_data['system']['ver']     =   "1.0";
        $post_data['system']['appId']   =   "lc22490f64d5c142d2";                               //使用的应用信息里的APP KEY
        $post_data['system']['sign']    =   $sign;       //加密生成
        $post_data['system']['time']    =   (string)$time;
        $post_data['system']['nonce']   =   (string)$nonce;
        $post_data['id']                =   (string)time();
        $post_data['params']            =   (object)[];
        $apiParams                      =   json_encode($post_data);
        $accessTokenUrl                 =   "https://openapi.lechange.cn:443/openapi/accessToken";
        $accessTokenResult              =   json_decode($this->postCurl($apiParams,$accessTokenUrl),true);
        $accessToken                    =   $accessTokenResult['result']['data']['accessToken'];
        if($accessTokenResult['result']['code']==0){
            return $accessToken;
        }else{
            return false;
        }
    }

    //kitToken获取
    public function getKitToken($accessToken)
    {
        if(Cache::get('kitToken'))
        {
            return  $kitToken   =   Cache::get('kitToken');
        }else{
            $param=[
                '0'=>[
                    'token'     =>  $accessToken,
                    'deviceId'  =>  '5K03C42PAZ34B44',
                    'channelId' =>  '4',
                    'type'      =>  '0',
                ],
                '1'=>[
                    'token'     =>  $accessToken,
                    'deviceId'  =>  '5K03C42PAZ34B44',
                    'channelId' =>  '3',
                    'type'      =>  '0',
                ],
                '2'=>[
                    'token'     =>  $accessToken,
                    'deviceId'  =>  '5K03C42PAZ34B44',
                    'channelId' =>  '0',
                    'type'      =>  '0',
                ]
            ];
            $time                           =   time();                                             //时间戳           sign加密所需
            $appSecret                      =   "a29f288dfb3e494e8617542e79578c";                   //账户appSecret    sign加密所需
            //接口请求参数
            $kitTokenUrl                    =   "https://openapi.lechange.cn:443/openapi/getKitToken";
            foreach ($param as $k=>$v)
            {
                $nonce                       =   md5($time++);
                $initsign                    =   "time:$time".","."nonce:$nonce".","."appSecret:$appSecret";
                $sign                        =   md5($initsign);
                $post_data['system']['ver']     =   "1.0";
                $post_data['system']['appId']   =   "lc22490f64d5c142d2";                               //使用的应用信息里的APP KEY
                $post_data['system']['sign']    =   $sign;       //加密生成
                $post_data['system']['time']    =   (string)$time;
                $post_data['system']['nonce']   =   (string)$nonce;
                $post_data['id']                =   (string)time();
                $post_data['params']=$v;
                $kitTokenResult             =   json_decode($this->postCurl(json_encode($post_data),$kitTokenUrl),true);
                if($kitTokenResult['result']['code']=='0'){
                    $kitToken[]=$kitTokenResult['result']['data']['kitToken'];
                }else{
                    $kitToken[]=$kitTokenResult;
                }
            }
            Cache::set('kitToken',$kitToken,60*60);
            return $kitToken;
        }
    }

    //
    public function postCurl($params,$url)
    {
        $curl   =   curl_init();
        $header =   ['Content-Type:'.'application/json'];
        curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HEADER,0);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$params);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        $return_str =   curl_exec($curl);
        $errorMsg   =   curl_error($curl);
        if($errorMsg){
            throw new Exception($errorMsg);return;
        }
        curl_close($curl);
        return $return_str;
    }


    //办公室监控的accessToken获取
    public function getBgAccessToken()
    {
        $appKey     =   "780b4b1393894eeaad29b0037e08d9e2";
        $appSecret  =   "a16d09c52fafd069d15a14589011e073";
        $url        =   "https://open.ys7.com/api/lapp/token/get";
        $param['appKey']    =   $appKey;
        $param['appSecret'] =   $appSecret;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = json_decode(curl_exec($ch),true);//运行curl
        $errorMsg   =   curl_error($ch);
        if($errorMsg){
            throw new Exception($errorMsg);return;
        }
        curl_close($ch);
        if($data['code']==200){
            $A=Cache::get('bgAccessToken');
            if(!$A){
                Cache::set('bgAccessToken',$data['data']['accessToken'],60*60*24*6);
            }
        }
        return  $data['data']['accessToken'];
    }

    //办公室播放地址ajax获取
    public function ajaxGetAccessToken(){
        $ajax_data      =   input();
        if(empty($ajax_data)){
            echo json_encode(['code'=>500,'msg'=>'参数错误！']);
        }
        $accessToken    =   $this->getBgAccessToken();
        $url            =   "https://open.ys7.com/ezopen/h5/iframe_se?url=ezopen://open.ys7.com/".$ajax_data['eqnumber']."/".$ajax_data['passageway'].".live&autoplay=0&audio=1&accessToken=".$accessToken;
        echo json_encode(['code'=>200,'url'=>$url]);
    }


}