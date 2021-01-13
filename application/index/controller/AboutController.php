<?php
/**
 * Created by PhpStorm.
 * User: 李小龙
 * Date: 2020/4/17
 * Time: 9:24
 */
namespace app\index\controller;
use app\index\model\BookModel;
use think\Controller;

class AboutController extends Controller{
    public function index(){
        return view();
    }

    public function book(){
        $data = input("param.");
        $data['time'] = date("Y-m-d H:i:s",time());

        $book = BookModel::Add($data);
        if($book){
            echo json_encode(['status'=>1,'msg'=>"您已经成功留言！"]);
        }else{
            echo json_encode(['status'=>2,'msg'=>"留言系统维护中！"]);
        }
    }
}