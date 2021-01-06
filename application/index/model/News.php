<?php

namespace app\index\model;

use think\Model;

class News extends Model

{
    //获取多个新闻
    public static function AllNews($field)
    {
        return self::where(['type'=>1,'status'=>1])->order('news_time desc')->field($field)->all()->toArray();
    }
    //获取f分页多个新闻
    public static function AllNewsFen($field)
    {
        return self::where(['type'=>1,'status'=>1])->order('news_time desc')->field($field)->paginate(10);
    }

    //查询单个新闻
    public static function  GetNews($field,$data)
    {
        return self::field($field)->get($data)->toArray();
    }

    //分页查询所有文化活动
    public static function AllActivityFen($field)
    {
        return self::where(['type'=>2,'status'=>1])->field($field)->paginate(5);
    }
    //查询单个
    public static function GetActivity($field , $data)
    {
        return self::field($field)->get($data)->toArray();
    }

}
