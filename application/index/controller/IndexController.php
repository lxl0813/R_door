<?php
namespace app\index\controller;

use app\index\model\NewsModel;
use think\Controller;
use think\facade\Cookie;

class IndexController extends Controller
{
    public function index()
    {
        //获取新闻
        if(Cookie::get('think_var')=='en_us'){
            //所要获取的字段
            $field='id,title_en,content_en,img,time';
            $new=NewsModel::AllNews($field);
            foreach($new as $k=>$v){
                $new[$k]['title']=$v['title_en'];unset($new[$k]['title_en']);
                $new[$k]['content']=$v['content_en'];unset($new[$k]['conent_en']);
            }
        }else{
            //所要获取的字段
            $field='id,title_ch,content_ch,img,time';
            $new=NewsModel::AllNews($field);
            foreach($new as $k=>$v){
                $new[$k]['title']=$v['title_ch'];unset($new[$k]['title_ch']);
                $new[$k]['content']=$v['content_ch'];unset($new[$k]['conent_ch']);
            }
        }
        //只获取四个
        $news=array_slice($new,0,4);
        return view('',['news'=>$news]);
    }

}
