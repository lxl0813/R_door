<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/17
 * Time: 10:38
 */
namespace app\index\controller;
use think\Controller;
use think\facade\Cookie;

class NewsController extends Controller{
    public function index(){
        //获取所有新闻
        //获取新闻
        if(Cookie::get('think_var')=='en_us'){
            //所要获取的字段
            $field='id,title_en,content_en,img,news_time';
            $new=\app\index\model\NewsModel::AllNewsFen($field);
            foreach($new as $k=>$v){
                $new[$k]['title']=$v['title_en'];unset($new[$k]['title_en']);
                $new[$k]['content']=$v['content_en'];unset($new[$k]['conent_en']);
                $new[$k]['time_year']=date('Y',strtotime($new[$k]['news_time']));
                $new[$k]['time_m']= date('m',strtotime($new[$k]['news_time']));
                $new[$k]['time_d']= date('d',strtotime($new[$k]['news_time']));
            }
        }else{
            //所要获取的字段
            $field='id,title_ch,content_ch,img,news_time';
            $new=\app\index\model\NewsModel::AllNewsFen($field);
            foreach($new as $k=>$v){
                $new[$k]['title']=$v['title_ch'];unset($new[$k]['title_ch']);
                $new[$k]['content']=$v['content_ch'];unset($new[$k]['conent_ch']);
                $new[$k]['time_year']=date('Y',strtotime($new[$k]['news_time']));
                $new[$k]['time_m']= date('m',strtotime($new[$k]['news_time']));
                $new[$k]['time_d']= date('d',strtotime($new[$k]['news_time']));
            }
        }
        //var_dump($new);exit;
        return view('',['new'=>$new]);
    }

    //新闻详情
    public function news_show(){
        $news_id['id'] = input('news_id');
        //获取该新闻详情
        if(Cookie::get('think_var')=='en_us'){
            //所要获取的字段
            $field='id ,title_en,content_en,img,news_time';
            $new=\app\index\model\NewsModel::GetNews($field,$news_id);
            $new['title']=$new['title_en'];
            $new['content']=htmlspecialchars_decode($new['content_en']);
            $new['time_year']=date('Y',strtotime($new['news_time']));
            $new['time_m']= date('m',strtotime($new['news_time']));
            $new['time_d']= date('d',strtotime($new['news_time']));
            //htmlspecialchars_decode($new['content']);
        }else{
            //所要获取的字段
            $field='id ,title_ch,content_ch,img,news_time';
            $new=\app\index\model\NewsModel::GetNews($field,$news_id);
            $new['title']=$new['title_ch'];
            $new['content']=htmlspecialchars_decode($new['content_ch']);
            $new['time_year']=date('Y',strtotime($new['news_time']));
            $new['time_m']= date('m',strtotime($new['news_time']));
            $new['time_d']= date('d',strtotime($new['news_time']));
            //htmlspecialchars_decode($new['content']);
        }
         return view('',['new'=>$new]);
    }
}