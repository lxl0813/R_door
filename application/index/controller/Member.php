<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/27
 * Time: 10:57
 */
namespace app\index\controller;


use app\index\model\Company;
use app\index\model\Form;
use app\index\model\FormType;
use think\Controller;
use think\Db;

class Member extends Common{
    public function index(){
          return view('');
    }

    public function more(){
        $form_id['form_type_id'] = input("form_type_id");  //表单类型id
        $form_id['form_company_id'] = input("company_id"); //公司id
        $form = Form::FormAll($form_id);                   //查询该公司下的该报表类型下的所有报表
        $form_type = FormType::where(['id'=>$form_id['form_type_id']])->value('form_type'); //查询报表类型
        $company   = Company::CompanyWhereFind("company",['id'=>$form_id['form_company_id']]);//查询该公司
        return view('',['finance'=>$form,'form_type'=>$form_type,'company'=>$company]);
    }

    public function pdf(){
        return view();
    }

    //报表中心
    public function form_index(){
        //查询所有公司
        $company = Company::CompanyAll();
        //查询所有类型
        $type = Db::name('form_type')->select();
        $datas=input("param.");
        $data['start_time']=input("start_time");
        $data['end_time']  =input("end_time");
        $data['company_id']=input("company_id")?input("company_id"):null;
        $data['form_type'] =input("form_type")?input("form_type"):null;

        if($datas){
            if($data['company_id'] && $data['form_type']){
                $form = Db::name('form')
                    ->where(['form_company_id'=>$data['company_id'],'form_type_id'=>$data['form_type']])
                    ->where('form_time','between',[$data['start_time'],$data['end_time']])
                    ->order('form_time')
                    ->select();
            }elseif($data['company_id'] && !$data['form_type']){
                $form = Db::name('form')
                    ->where(['form_company_id'=>$data['company_id']])
                    ->where('form_time','between',[$data['start_time'],$data['end_time']])
                    ->order('form_time')
                    ->select();

            }elseif($data['form_type'] && !$data['company_id'] ){
                $form = Db::name('form')
                    ->where(['form_type_id'=>$data['form_type']])
                    ->where('form_time','between',[$data['start_time'],$data['end_time']])
                    ->order('form_time')
                    ->select();
            }elseif(!$data['company_id'] && !$data['form_type']){
                $form = Db::name('form')
                    ->where('form_time','between',[$data['start_time'],$data['end_time']])
                    ->order('form_time')
                    ->select();
            }

            if(empty($form)){
                $form=[];
            }else{
                foreach($form as $k=>$v){
                    $form[$k]['type'] = \app\admin\model\FormType::FormTypeWhereFind("form_type",['id'=>$v['form_type_id']]);
                    $form[$k]['company'] = \app\admin\model\Company::CompanyWhereGet(["id"=>$v['form_company_id']],"company");
                    $form[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
                }
            }
        }else{
            //查询所有流水账单（显示当月的流水账单）
            $form = \app\admin\model\Form::FormSelect();
            foreach($form as $k=>$v){
                $form[$k]['type'] = \app\admin\model\FormType::FormTypeWhereFind("form_type",['id'=>$v['form_type_id']]);
                $form[$k]['company'] = \app\admin\model\Company::CompanyWhereGet(["id"=>$v['form_company_id']],"company");
                $form[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
            }
        }



        return view('',['form'=>$form,'company'=>$company,'type'=>$type]);

    }

    //流水中心
    public function water_index(){
        $company= \app\admin\model\Company::CompanyAll();
        $no="1";
        //条件查询
        $data=input('param.');
        if($data){
            //根据条件进行查取
            $water_list = Db::name('water_list')
                ->where(['company_id'=>$data['company_id']])
                ->where("water_time","between",[$data['start_time'],$data['end_time']])
                ->order('water_time desc')
                ->paginate(30);
            if(count($water_list)<=0){
                $water_list=[];
            }
        }else{
            $time = date('m',time());
            $water_list = Db::name('water_list')
                ->where(['month'=>$time])
                ->order('water_time desc')
                ->paginate(30);
            if(count($water_list)<=0){
                $water_list=[];
            }
        }
        return view('',['no'=>$no,'company'=>$company,'water_list'=>$water_list]);
    }


    //银行流水在线预览
    public function water_see(){
        $data = input('param.');
        if(count($data)>1){
            $lists =Db::name('water_list')
                ->where(['company_id'=>$data['company_id']])
                ->where("water_time","between",[$data['start_time'],$data['end_time']])
                ->order('water_time')
                ->order('time')
                ->select();
            if(empty($lists)){
                $this->error('没有流水数据');
            }
        }else{
            $list =Db::name('water_list')
                ->where($data)
                ->find();
            if(empty($list)){
                $this->error('没有流水数据');
            }else{
                if(count($list) == count($list,1)){
                    $lists[]=$list;
                }
            }
        }
        $no="1";
        return view('',['no'=>$no,'lists'=>$lists]);
    }

	 /**
     * @return \think\response\View
     * @throws \think\exception\DbException
     * 运营报告查看
     */
    public function operation_report(){
        $reports = Db::name('reports')->paginate(30);
        if(!$reports){
            $reports=[];
        }
        return view('',['reports'=>$reports]);
    }


    /**
     * @return \think\response\View
     * @throws \think\exception\DbException
     * 商业计划书查看
     */
    public function business_plan(){
        $plan = Db::name('plans')->paginate(30);
        $plan?$plan:[];
        return view('',['plan'=>$plan]);
    }
}