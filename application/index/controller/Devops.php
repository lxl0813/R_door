<?php


namespace app\index\controller;


use think\Db;
use think\facade\Cache;
use think\Validate;

class Devops
{
    private $table_data  =   [];
    public function index()
    {
        return view();
    }

    public function devops_center()
    {
        //今日访问总量   昨日新增  历史访问总量
        $data=Db::name('devops')
            ->alias('d')
            ->leftJoin('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.create_time',date('Y-m-d',time()))
            ->field('d.id,d.data_name,dv.data_value,dv.create_time')
            ->select();
        if(empty($data)){
            $data=Db::name('devops')
                ->alias('d')
                ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                ->where('d.state','1')
                ->order('dv.create_time desc')
                ->order('dv.data_name_id')
                ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                ->limit(9)
                ->select();
        }

        $todayVisitNums=0;  //定义今日访问总数值
        $yesNewAdd=0;       //定义比昨日新增数值
        $historyNum=0;      //定义历史访问总量
        foreach ($data as $key=>$item)
        {
            //该数据点的历史数据和
            $data[$key]['history_num']=Db::name('devops_value')
                ->where('data_name_id',$item['id'])
                ->where('create_time','<',date('Y-m-d',time()))
                ->sum('data_value');

            //该数据点和昨日数据点差值
            $data[$key]['difference']=$item['data_value']-(Db::name('devops_value')
                    ->where('data_name_id',$item['id'])
                    ->where('create_time',date("Y-m-d",strtotime("-1 day",strtotime($item['create_time']))))
                    ->value('data_value'));
            //今日访问总量
            $todayVisitNums+=$item['data_value'];
            //比昨日新增总量
            $yesNewAdd+=$data[$key]['difference'];
            //历史访问总量和
            $historyNum+=$data[$key]['history_num'];
        }
        $datas['todayVisitNums']=$todayVisitNums;
        $datas['yesNewAdd']=$yesNewAdd;
        $datas['historyNum']=(int)$historyNum;
        $this->table_data=$data;
        return view('',['devops_data'=>$data,'num'=>$datas]);
    }

    //页面数据ajax调取
    public function ajax_data()
    {
        $history_visit_num  =   $this->history_visit_num();     //历史图表数据
        $data_detail        =   $this->data_detail();           //数据图表
        $near_future_data   =   $this->near_future_data();      //近30天数据表
        echo json_encode(['history_visit_num'=>$history_visit_num,'data_detail'=>$data_detail,'near_future_data'=>$near_future_data]);
    }

    //左上角每日访问量图表
//    public function everyday_visit_num()
//    {
//        Db::name('devops')
//            ->alias('d')
//            ->
//
//    }





    //表格数据
    public function table_data()
    {
        $data=Db::name('devops')
            ->alias('d')
            ->leftJoin('devops_value dv','d.id=dv.data_name_id')
            ->where('d.state','1')
            ->where('dv.create_time',date('Y-m-d',time()))
            ->field('d.id,d.data_name,dv.data_value,dv.create_time')
            ->select();
        if(empty($data)){
            $data=Db::name('devops')
                ->alias('d')
                ->leftJoin('devops_value dv','d.id=dv.data_name_id')
                ->where('d.state','1')
                ->order('dv.create_time desc')
                ->order('dv.data_name_id')
                ->field('d.id,d.data_name,dv.data_value,dv.create_time')
                ->limit(9)
                ->select();
        }

        $todayVisitNums=0;  //定义今日访问总数值
        $yesNewAdd=0;       //定义比昨日新增数值
        $historyNum=0;      //定义历史访问总量
        foreach ($data as $key=>$item)
        {
            //该数据点的历史数据和
            $data[$key]['history_num']=Db::name('devops_value')
                ->where('data_name_id',$item['id'])
                ->where('create_time','<',date('Y-m-d',time()))
                ->sum('data_value');

            //该数据点和昨日数据点差值
            $data[$key]['difference']=$item['data_value']-(Db::name('devops_value')
                    ->where('data_name_id',$item['id'])
                    ->where('create_time',date("Y-m-d",strtotime("-1 day",strtotime($item['create_time']))))
                    ->value('data_value'));
            //今日访问总量
            $todayVisitNums+=$item['data_value'];
            //比昨日新增总量
            $yesNewAdd+=$data[$key]['difference'];
            //历史访问总量和
            $historyNum+=$data[$key]['history_num'];
        }
        $datas['todayVisitNums']=$todayVisitNums;
        $datas['yesNewAdd']=$yesNewAdd;
        $datas['historyNum']=(int)$historyNum;
        $data['data']=$datas;
        var_dump($data);exit;
        return $data;
    }


    //历史累计访问量图表
    public function history_visit_num()
    {
        $devops_name                            =   Db::name('devops')->where('state','1')->order('id')->select();
        foreach ($devops_name as $key=>$item)
        {
            $history_visit_num['title'][]       =   $item['data_name'];
            $history_visit_num['num'][] =   Db::name('devops_value')->where('data_name_id',$item['id'])->where('create_time','<',date('Y-m-d',time()))->sum('data_value');
        }
        unset($devops_name);
        return $history_visit_num;
    }

    //数据图表
    public function data_detail()
    {
        $devops_data                    =   Db::name('devops')->where('state','1')->order('id')->select();
        foreach ($devops_data as $key=>$item)
        {
            $data_detail['title'][]     =   $item['data_name'];
            $num                        =   Db::name('devops_value')->where('data_name_id',$item['id'])->where('create_time',date('Y-m-d',time()))->value('data_value');
            if(empty($num)){
                $num                        =   Db::name('devops_value')->where('data_name_id',$item['id'])->where('create_time',date('Y-m-d',strtotime("-1 day")))->value('data_value');
                $data_detail['num'][]       =   $num;
                $data_detail['c'][]         =   $num-Db::name('devops_value')->where('data_name_id',$item['id'])->where('create_time',date("Y-m-d",strtotime("-2 day")))->value('data_value');
            }else{
                $data_detail['num'][]       =   $num;
                $data_detail['c'][]         =   $num-Db::name('devops_value')->where('data_name_id',$item['id'])->where('create_time',date("Y-m-d",strtotime("-1 day")))->value('data_value');
            }

        }
        unset($devops_data);
        return $data_detail;
    }

    //近30日访问量表
    public function near_future_data()
    {
        $devops_near                        =   Db::name('devops')->where('state','1')->order('id')->field('id,data_name')->select();
        foreach ($devops_near as $key=>$item)
        {
            $near_future_data[$key]         =   Db::name('devops_value')->where('data_name_id',$item['id'])->where('create_time','<',date('Y-m-d',time()))->order('create_time')->limit(30)->field('data_value,create_time')->select();
        }
        foreach ($near_future_data as $key=>$item)
        {
            foreach ($item as $k=>$v)
            {
                $future_data[$key][]=$v['data_value'];
            }
        }
        $i = 0;
        while($i < count(current($near_future_data))){
            $data[] = array_column($future_data,$i);
            $i++;
        }
        $sum=[];
        foreach ($data as $key=>$item)
        {
            $sum['num'][$key]  =   array_sum($item);
        }
        foreach ($near_future_data[0] as $k=>$v)
        {
            $sum['date'][]=$v['create_time'];
        }
        foreach ($devops_near as $k=>$v)
        {
            $sum['title'][]=$v['data_name'];
        }
        return $sum;
    }



}