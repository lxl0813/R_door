<?php
/**
 * Created by PhpStorm.
 * User: 李小龙
 * Date: 2020/4/26
 * Time: 11:13
 */
namespace app\admin\controller;
use think\Db;

class News extends Common{
    public function index(){
        //查取所有新闻资料
        $news=Db::name('news')->order('news_time desc')->paginate(10);
        $this->assign('news', $news);
        return $this->fetch();
    }

    //添加
    public function add(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()) {
            $data = input('param.');
            $data['time'] = time();
            try {
                $news = Db::name('news')->insert($data);
                if ($news) {
                } else {
                    throw new Exception('提交失败');
                }
            } catch (\Exception $e) {
                echo json_encode(['status'=>2,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['status'=>1,'msg'=>'新闻上传成功！']);
        }
    }    //修改
    public function update(){
        if(request()->isGet()){
            //接值
            $id=input('id');
            //查询
            $news=Db::name('news')->where(['id'=>$id])->find();
            return view('',['news'=>$news]);
        }
        if(request()->isPost()) {
            $data = input('param.');
            $id['id']=$data['id'];unset($data['id']);
            $data['time'] = time();
            try {
                Db::name('news')->where($id)->update($data);
            }catch (\Exception $e){
                echo json_encode(['status'=>2,'msg'=>$e->getMessage()]);return;
            }
            echo json_encode(['status'=>1,'msg'=>'信息修改成功']);
        }
    }

    //新闻图片修改
    public function img_update(){
        $id = input("id");
        $img= input("img");
        try {
            Db::name('news')->where(['id'=>$id])->update(['img'=>$img]);
        }catch (\Exception $e){
            echo json_encode(['status'=>2,'msg'=>$e->getMessage()]);return;
        }
        echo json_encode(['status'=>1,'msg'=>'图片修改成功']);
    }

    //删除
    public function delete(){
        //接值
        $id=input('id');
        if(Db::name('news')->where(['id'=>$id])->delete()){
            echo json_encode(['status'=>1,'msg'=>'删除成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'请稍后再试']);
        }
    }

    //状态修改
    public function status_update(){
        $id = input('id');
        $news=Db::name('news')->where(['id'=>$id])->find();
        if($news['status']==1){
            if(Db::name('news')->where(['id'=>$id])->update(['status'=>0])){
                echo json_encode(['status'=>1,'msg'=>'更改成功']);
            }else{
                echo json_encode(['status'=>2,'msg'=>'稍后再试']);
            }
        }else{
            if(Db::name('news')->where(['id'=>$id])->update(['status'=>1])){
                echo json_encode(['status'=>1,'msg'=>'更改成功']);
            }else{
                echo json_encode(['status'=>2,'msg'=>'稍后再试']);
            }
        }
    }

    //标题修改(中)
    public function title_update_zhong(){
        $id=input('id');
        $title_ch=input('nextxt');
        if(Db::name('news')->where(['id'=>$id])->update(['title_ch'=>$title_ch])){
            echo json_encode(['status'=>1,'msg'=>'修改成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'请输入新的标题']);
        }
    }

    //修改标题（英）
    public function title_update_ying(){
        $id=input('id');
        $title_en=input('nextxt');
        if(Db::name('news')->where(['id'=>$id])->update(['title_en'=>$title_en])){
            echo json_encode(['status'=>1,'msg'=>'修改成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'请输入新的标题']);
        }
    }

    //大图显示
    public function old_img(){
        $img = input('img');
        return view('',['img'=>$img]);
    }

}