<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/17
 * Time: 9:24
 */
namespace app\index\controller;
use app\index\model\NewsModel;
use think\Controller;
use think\facade\Cookie;

class CultureController extends Controller{

    public function index()
    {
        return view();
    }

    //员工行为规范
    public function regulation()
    {
        return view();
    }

    //企业活动
    public function cultural_activity()
    {
        //获取文化活动
        if(Cookie::get('think_var')=='en_us'){
            //所要获取的字段
            $field='id,title_en,content_en,img,time';
            $activity=NewsModel::AllActivityFen($field);
            foreach($activity as $k=>$v){
                $activity[$k]['title']       =   $v['title_en'];unset($activity[$k]['title_en']);
                $activity[$k]['content']     =   $v['content_en'];unset($activity[$k]['conent_en']);
                $activity[$k]['time_year']   =   date('Y',$activity[$k]['time']);
                $activity[$k]['time_m']      =   date('m',$activity[$k]['time']);
                $activity[$k]['time_d']      =   date('d',$activity[$k]['time']);
            }
        }else{
            //所要获取的字段
            $field='id,title_ch,content_ch,img,time';
            $activity=NewsModel::AllActivityFen($field);
            foreach($activity as $k=>$v){
                $activity[$k]['title']       =   $v['title_ch'];unset($activity[$k]['title_ch']);
                $activity[$k]['content']     =   $v['content_ch'];unset($activity[$k]['conent_ch']);
                $activity[$k]['time_year']   =   date('Y',$activity[$k]['time']);
                $activity[$k]['time_m']      =   date('m',$activity[$k]['time']);
                $activity[$k]['time_d']      =   date('d',$activity[$k]['time']);
            }
        }
        return view('',['activity'=>$activity]);
    }

    public function activity_show(){
        $id['id'] = input('id');
        if(Cookie::get('think_var')=='en_us'){
            //所要获取的字段
            $field='id ,title_en,content_en,img,time';
            $new=NewsModel::GetActivity($field,$id);
            $new['title']=$new['title_en'];
            $new['content']=htmlspecialchars_decode($new['content_en']);
            $new['time_year']=date('Y',$new['time']);
            $new['time_m']= date('m',$new['time']);
            $new['time_d']= date('d',$new['time']);
        }else{
            //所要获取的字段
            $field='id ,title_ch,content_ch,img,time';
            $new=NewsModel::GetActivity($field,$id);
            $new['title']=$new['title_ch'];
            $new['content']=htmlspecialchars_decode($new['content_ch']);
            $new['time_year']=date('Y',$new['time']);
            $new['time_m']= date('m',$new['time']);
            $new['time_d']= date('d',$new['time']);
        }
        return view('',['new'=>$new]);
    }
}