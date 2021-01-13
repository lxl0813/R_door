<?php
namespace app\admin\controller;
use think\Db;

class BookController extends CommonController{
    public function index(){
        $no="1";
        $data=input("param.");
        if(!empty($data)){
            if($data['status']==1){
                $book=Db::name('book')->where(['status'=>1])->paginate(30);
            }else{
                $book=Db::name('book')->where(['status'=>0])->paginate(30);
            }
        }else{
            $book=Db::name('book')->paginate(30);
        }
        return view('',['no'=>$no,'book'=>$book]);
    }

    //查看
    public function see(){
        $id =input("id");
        $book = Db::name('book')->where(['id'=>$id])->find();
        Db::name('book')->where(['id'=>$id])->update(['status'=>1]);
        $books="<b>昵称：</b>".$book['name']."<br>"."<b>联系方式：</b>".$book['phone']."<br>"."<b>留言：</b>".$book['content'];
        echo json_encode(['status'=>1,'data'=>$books]);
    }
    public function delete(){
        $id = input("id");
        if(Db::name('book')->where(['id'=>$id])->delete()){
            echo json_encode(['status'=>1,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'删除失败！']);
        }
    }
}