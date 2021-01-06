<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2020/4/26
 * Time: 17:50
 */
namespace app\admin\controller;
use app\admin\model\Company;
use app\admin\model\Form;
use app\admin\model\FormType;
use think\Db;
use think\facade\Cache;
use think\facade\Cookie;

class Finance extends Common{
    public function choose(){
        return view();
    }

    //报表中心
    public function form_index(){
        if(request()->isGet()){
            $form = Form::FormSelect();
            //var_dump($form);exit;
            foreach($form as $k=>$v){
                $form[$k]['type'] = FormType::FormTypeWhereFind("form_type",['id'=>$v['form_type_id']]);
                $form[$k]['company'] = Company::CompanyWhereGet(["id"=>$v['form_company_id']],"company");
                $form[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
            }
            //查询所有公司
            $company = $this->company();
            //查询所有类型
            $type = $this->type();
            return view('',['form'=>$form,'company'=>$company,'type'=>$type]);
        }
    }

    //报表添加
    public function form_add(){
        if(request()->isGet()){
            //查询所有报表类型
            $form_type = $this->type();
            //查询所有公司
            $form_company = $this->company();
            return view('',['form_type'=>$form_type,'form_company'=>$form_company]);
        }
        if(request()->isPost()){
            $form = input('param.');
            $y_m_d = explode("-",$form['form_time']);
            $form['year']=$y_m_d[0];
            $form['month'] = $y_m_d[1];
            $form['data']= $y_m_d[2];
            //图片接收
            $content = request()->file('form_url');
            if($content == ''){
                $this->error('请上传图片选项');
            }
            $ext=$_FILES['form_url']['name'];//图片名称
            $end = strrchr($ext,'.');//截取后缀
            $filetype = ['.pdf'];   //报表文件类型范围
            //判断上传文件格式
            if(!in_array($end,$filetype)){
                $this->error('请上传pdf格式的文件');
            }
            $dir='../public/static/file/'.date('Y/m/d').'/';                   //上传路径
            if (!file_exists($dir)){                                          //创建文件夹
                mkdir($dir,0777,true);
            }
            $tmp_name=$_FILES['form_url']['tmp_name'];
            $new_photo=uniqid().$end;
            if (move_uploaded_file($tmp_name,$dir.$new_photo)){
                $form['form_url']=substr($dir.$new_photo,9);
                $form['time']=time();
                $form['admin_id']=Cookie::get('ranglei_admin')['admin_id'];
                $form['admin_name']=Cookie::get('ranglei_admin')['name'];
                //提交入库
                $subject=Form::FormAdd($form);
                if($subject){
                    echo json_encode(['code'=>200,'msg'=>'报表上传成功']);
                }else{
                    echo json_encode(['code'=>500,'msg'=>'报表上传失败！']);
                }
            }
        }
    }

    //报表状态修改
    public function form_status(){
        $id=input("form_id");
        $status = Db::name('form')->where(['id'=>$id])->field('status')->find();
        if($status['status']==1){
            if(Db::name('form')->where(['id'=>$id])->update(['status'=>2])){
                echo json_encode(['status'=>1,'msg'=>'该报表已设置为禁止对外展示！']);
            }
        }else{
            if(Db::name('form')->where(['id'=>$id])->update(['status'=>1])){
                echo json_encode(['status'=>1,'msg'=>'该报表已设置为对外展示！']);
            }
        }
    }

    //报表删除
    public function form_delete(){
        $form_id['id'] = input('form_id');
        $form = Form::FormDelete($form_id);
        if($form){
            echo json_encode(['status'=>1,'msg'=>'报表删除成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'报表删除失败']);
        }
    }

    //报表个人中心
    public function form_personal(){
        $company = $this->company();
        $type = $this->type();
        $admin_id = Cookie::get("ranglei_admin")['admin_id'];
        $form_list = Db::name('form')->where(['admin_id'=>$admin_id])->select();
        foreach($form_list as $k=>$v){
            $form_list[$k]['type'] = FormType::FormTypeWhereFind("form_type",['id'=>$v['form_type_id']]);
            $form_list[$k]['company'] = Company::CompanyWhereGet(["id"=>$v['form_company_id']],"company");
            $form_list[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
        }
        return view('',['company'=>$company,'type'=>$type,'form_list'=>$form_list]);
    }


    //报表类型列表
    public function form_type_index(){
        if(request()->isGet()){
            //查询类型
            $form_type = $this->type();
            return view('',['form_type'=>$form_type]);
        }
    }

    //报表类型添加
    public function form_type_add(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()){
            $form_type = input('param.');
            $form_type['time']=time();
            $form_type_id = FormType::FromTypeAdd($form_type);
            if($form_type_id){
                echo json_encode(['status'=>1,'msg'=>'添加成功']);
            }else{
                echo json_encode(['status'=>2,'msg'=>'添加失败']);
            }
        }
    }

    //报表类型删除
    public function form_type_delete(){
        $type['id'] = input("type_id");
        $type_id = FormType::FormTypeDelete($type);
        if($type_id){
            echo json_encode(['status'=>1,'msg'=>'报表类型删除成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'报表类型删除失败']);
        }
    }

    //报表类型修改
    public function form_type_update(){
    }

    //公司页面
    public function company_index(){
        //公司查询
        $form_company = $this->company();
        return view('',['form_company'=>$form_company]);
    }

    //报表公司添加
    public function company_add(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()){
            $company['company'] = input("form_company");
            $company['time']    = time();
            if(Company::CompanyAdd($company)){
                echo json_encode(['status'=>1,'msg'=>'公司添加成功']);
            }else{
                echo json_encode(['status'=>2,'msg'=>'公司添加失败']);
            }
        }
    }

    //报表公司删除
    public function company_delete(){
        $company_id['id'] = input("company_id");
        $company = Company::CompanyDelete($company_id);
        if($company){
            echo json_encode(['status'=>1,'msg'=>'公司删除成功']);
        }else{
            echo json_encode(['status'=>2,'msg'=>'公司删除失败']);
        }
    }

    //报表公司修改
    public function company_update(){
    }

    //报表修改
    public function form_update(){
        if(request()->isGet()){
            $id=input("form_id");
            //查询所有报表类型
            $form_type = $this->type();
            //查询所有公司
            $form_company = $this->company();
            $form_list = Db::name('form')->where(['id'=>$id])->find();
            return view('',['company'=>$form_company,'type'=>$form_type,'form'=>$form_list]);
        }
        if(request()->isPost()){
            $data=input("param.");//接收普通信息
            $y_m_d = explode("-",$data['form_time']);
            $data['year']=$y_m_d[0];
            $data['month'] = $y_m_d[1];
            $data['data']= $y_m_d[2];
            $id = $data['id'];
            unset($data['id']);
            //图片接收
            $content = request()->file();//接收文件信息
            if(empty($content)){
                //没有文件上传，直接修该报表普通信息
                if(Db::name('form')->where(['id'=>$id])->update($data)){
                    $this->success('报表信息修改成功！','Finance/form_personal');
                }
            }else{
                //有文件上传
                $ext=$_FILES['form_url']['name'];//图片名称
                $end = strrchr($ext,'.');//截取后缀
                $filetype = ['.pdf'];   //报表文件类型范围
                //判断上传文件格式
                if(!in_array($end,$filetype)){
                    $this->error('请上传pdf格式的文件');
                }
                $dir='../public/static/file/'.date('Y/m/d').'/';                   //上传路径
                if (!file_exists($dir)){                                          //创建文件夹
                    mkdir($dir,0777,true);
                }
                $tmp_name=$_FILES['form_url']['tmp_name'];
                $new_photo=uniqid().$end;
                if (move_uploaded_file($tmp_name,$dir.$new_photo)){
                    $data['form_url']=substr($dir.$new_photo,9);
                    //删除原有报表
                    $form_url = Db::name('form')->where(['id'=>$id])->value('form_url');
                    unlink("../public".$form_url);
                    //提交入库
                    $subject=Db::name('form')->where(['id'=>$id])->update($data);
                    if($subject){
                        $this->success('报表信息修改成功',"Finance/form_index");
                    }else{
                        $this->error("报表信息修改失败");
                    }
                }
            }
        }
    }


    //根据公司搜索
    public function company_search(){
        $company_id['form_company_id'] = input("company_id");
        //根据公司id查询相关报表
        $form = Form::FormWhereFind($company_id);
        foreach($form as $k=>$v){
            $form[$k]['type'] = FormType::FormTypeWhereFind("form_type",['id'=>$v['form_type_id']]);
            $form[$k]['company'] = Company::CompanyWhereGet(["id"=>$v['form_company_id']],"company");
            $form[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
        }
        if($form){
            echo json_encode(['status'=>1,'finance'=>$form]);
        }else{
            echo json_encode(['status'=>2,'msg'=>'该公司下没有相关报表']);
        }
    }

    //根据类型搜索
    public function type_search(){
        $type_id['form_type_id'] = input("type_id");
        //根据公司id查询相关报表
        $form = Form::FormWhereFind($type_id);
        foreach($form as $k=>$v){
            $form[$k]['type'] = FormType::FormTypeWhereFind("form_type",['id'=>$v['form_type_id']]);
            $form[$k]['company'] = Company::CompanyWhereGet(["id"=>$v['form_company_id']],"company");
            $form[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
        }
        if($form){
            echo json_encode(['status'=>1,'finance'=>$form]);
        }else{
            echo json_encode(['status'=>2,'msg'=>'该公司下没有相关报表']);
        }
    }

    //pdf
    public function form_pdf(){
        return view();
    }


    //流水中心
    public function water_index()
    {
        //查询所有流水账单（显示当月的流水账单）
        $company=$this->company();
        $no="1";
        //条件查询
        $data=input('param.');
        if($data){
            //根据条件进行查取
            $water_list = Db::name('water_list')
                ->where(['company_id'=>$data['company_id']])
                ->where("water_time","between",[$data['start_time'],$data['end_time']])
                ->order('time')
                ->select();
        }else{
            $time = date('m',time());
            $water_list = Db::name('water_list')
                ->where(['month'=>$time])
                ->order('time')
                ->select();
        }
        return view('',['no'=>$no,'company'=>$company,'water_list'=>$water_list]);
    }


    //流水账单添加页面
    public function water_add()
    {
        if(request()->isGet()){
            $company=$this->company();
            $bank = $this->bank();
            return view('',['company'=>$company,'bank'=>$bank]);
        }

        if(request()->isPost()){
            $data=input("param.");
            //var_dump($data);exit;
            $data['time']=time();
            //查取用户
            $data['admin_name'] = Cookie::get("ranglei_admin")['name'];
            $data['admin_id']   = Cookie::get("ranglei_admin")['admin_id'];
            $y_m_d = explode('-',$data['water_time']);
            $data['year']  = $y_m_d[0];
            $data['month'] = $y_m_d[1];
            $data['data']  = $y_m_d[2];

            $content = request()->file('water_url');
            if($content == ''){
                echo json_encode(['status'=>2,'msg'=>'请上传图片！']);
            }
            $ext=$_FILES['water_url']['name'];//图片名称
            $end = strrchr($ext,'.');//截取后缀
            $filetype = ['.pdf'];   //报表文件类型范围
            //判断上传文件格式
            if(!in_array($end,$filetype)){
                echo json_encode(['status'=>2,'msg'=>'请上传pdf格式的文件！']);
            }
            $dir='../public/static/waterFile/'.date('Y/m/d').'/';                   //上传路径
            if (!file_exists($dir)){                                                                //创建文件夹
                mkdir($dir,0777,true);
            }
            $tmp_name=$_FILES['water_url']['tmp_name'];
            $new_photo=uniqid().$end;
            if (move_uploaded_file($tmp_name,$dir.$new_photo)){
                $data['water_url']=substr($dir.$new_photo,9);
                if(Db::name('water_list')->insert($data)){
                    echo json_encode(['status'=>1,'msg'=>'流水账单上传成功！']);
                }else {
                    echo json_encode(['status' => 2, 'msg' => '流水账单上传失败！']);
                }
            }
        }
    }

    //公司余额获取
    public function company_water_balance()
    {
        $company_id = input('company_id');
        $bank_id    = input('bank_id');
        $company_water_balance = Cache::get($company_id.$bank_id);
        if ($company_water_balance == false) {
            $company_water_balance = "0.00";
        }
        echo json_encode(['data'=>$company_water_balance]);
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

    //流水文件中心
    public function water_excel(){
        if(request()->isGet()){
            $company=$this->company();
            $no="1";
            //c查询流水excel文件
            $excel = Db::name('water_excel')->paginate(30);
            if(count($excel)<=0){
                $excel=[];
            }
            return view('',['no'=>$no,'excel'=>$excel,'company'=>$company]);
        }
        if(request()->isPost()){
            $data = input("param.");
            $titles= $data['company_name']."银行流水表";
            $excel = Db::name("water_list")
                ->where(['company_id'=>$data['company_id']])
                ->where("water_time","between",[$data['start_time'],$data['end_time']])
                ->order('water_time')
                ->order('time')
                ->field('water_time,bank_name,water_subject,water_abstract,water_income,water_expenditure,water_balance,water_content')
                ->select();
            if(empty($excel)){
                echo json_encode(['status'=>2,'msg'=>'有没有查询到可用数据！']);
            }else{
                //往这个数组中塞入序号
                $no="1";
                foreach ($excel as $k=>$v){
                    array_unshift($excel[$k],$no);
                    $no++;
                }
                $title = $data['company_name'].$data['start_time']."——".$data['end_time']."银行流水表";
                $datas['excel_url'] = $this->water_excel_create($excel,$titles,$title);
                $datas['admin_id']=Cookie::get("ranglei_admin")['admin_id'];
                $datas['admin_name'] = Cookie::get('ranglei_admin')['name'];
                $datas['time']=date('Y-m-d H:i:s',time());
                $datas['title']=$title;
                if(Db::name('water_excel')->insert($datas)){
                    echo json_encode(['status'=>1,'msg'=>'excel表生成成功！']);
                }else{
                    echo json_encode(['status'=>2,'msg'=>'excel表生成失败，稍后再试！']);
                }
            }
        }
    }

    //个人流水中心
    public function water_personal(){
        $company=$this->company();
        $no="1";
        $admin_id = Cookie::get("ranglei_admin")['admin_id'];
        $data= input("param.");
        if($data){
            //根据条件进行查取
            $water_list = Db::name('water_list')
                ->where(['company_id'=>$data['company_id'],'admin_id'=>$admin_id])
                ->where("water_time","between",[$data['start_time'],$data['end_time']])
                ->order('water_time')
                ->paginate(30);
            if(count($water_list)<=0){
                $water_list=[];
            }
        }else{
            //查询当前管理员的流水账单
            $water_list = Db::name('water_list')
                ->where(['admin_id'=>$admin_id])
                ->order('water_time')
                ->paginate(30);
            if(count($water_list)<=0){
                $water_list=[];
            }
        }
        return view('',['no'=>$no,'water_list'=>$water_list,'company'=>$company]);
    }

   //流水账单修改
    public function water_update(){
        if(request()->isGet()){
            $id = input('id');
            $water_list = Db::name('water_list')->where(['id'=>$id])->find();
            //查询银行
            $bank = Db::name('bank')->select();
            //查询公司
            $company=$this->company();
            return view('',['id'=>$id,'water_list'=>$water_list,'bank'=>$bank,'company'=>$company]);
        }

        if(request()->isPost()){
            $data=input("param.");
            $id=$data['id'];unset($data['id']);
            //查取用户
            $data['admin_name'] = Cookie::get("ranglei_admin")['name'];
            $data['admin_id']   = Cookie::get("ranglei_admin")['admin_id'];
            $y_m_d = explode('-',$data['water_time']);
            $data['year']  = $y_m_d[0];
            $data['month'] = $y_m_d[1];
            $data['data']  = $y_m_d[2];
            $content = request()->file('water_url');
            if($content == NULL){
                if(Db::name('water_list')->where(['id'=>$id])->update($data)){
                    echo json_encode(['status'=>1,'msg'=>'流水账单修改成功！']);
                }else {
                    echo json_encode(['status' => 2, 'msg' => '流水账单修改失败！']);
                }
            }else {
                $ext=$_FILES['water_url']['name'];//图片名称
                $end = strrchr($ext,'.');//截取后缀
                $filetype = ['.pdf'];   //报表文件类型范围
                //判断上传文件格式
                if(!in_array($end,$filetype)){
                    $this->error('请上传pdf格式的文件');
                }
                $dir='../public/static/waterFile/'.date('Y/m/d').'/';                   //上传路径
                if (!file_exists($dir)){                                          //创建文件夹
                    mkdir($dir,0777,true);
                }
                $tmp_name=$_FILES['water_url']['tmp_name'];
                $new_photo=uniqid().$end;
                if (move_uploaded_file($tmp_name,$dir.$new_photo)){
                    $data['water_url']=substr($dir.$new_photo,9);
                    if(Db::name('water_list')->where(['id'=>$id])->update($data)){
                        echo json_encode(['status'=>1,'msg'=>'流水账单修改成功！']);
                    }else {
                        echo json_encode(['status' => 2, 'msg' => '流水账单修改失败！']);
                    }
                }
            }
        }
    }

   /**
     *运营报告
     */
    public function operation_report(){
        $role = $this->role();
        $reports = Db::name('reports')->paginate(30);
        if(count($reports)<=0){
            $reports=[];
        }
        return view('',['role'=>$role,'reports'=>$reports]);
    }


    /**
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function reports_personal(){
        $role = $this->role();
        $reports = Db::name('reports')->where(['create_admin_id'=>Cookie::get('ranglei_admin')['admin_id']])->paginate(30);
        if(count($reports)<=0){
            $reports=[];
        }
        return view('',['role'=>$role,'reports'=>$reports]);
    }

    /**
     * 运营报告添加
     */
    public function report_add(){
        if(request()->isGet()){
            return view();
        }

        if(request()->isPost()){
            $data = input();
            //$file = $_FILES['file'];
            $dir='../public/reports/'.date('Y/m/d').'/';                //上传路径
            if (!file_exists($dir)){                                          //创建文件夹
                mkdir($dir,0777,true);
            }
            $tmp_name=$_FILES['file']['tmp_name'];
            $new_photo=uniqid().'.'.'pdf';
            if (move_uploaded_file($tmp_name,$dir.$new_photo)){
                $url=substr($dir.$new_photo,9);
                if(Db::name('reports')
                    ->insert(
                        [
                            'title'=>$data['report_title'],
                            'time'=>$data['report_time'],
                            'url'=>$url,
                            'create_admin_id'=>Cookie::get('ranglei_admin')['admin_id'],
                            'create_admin_name'=>Cookie::get('ranglei_admin')['admin_name'],
                            'create_time'=>date('Y-m-d H:i:s',time()),
                        ]
                    )
                ){
                    echo json_encode(['code'=>200,'msg'=>'运营报告上传成功！']);
                }else{
                    echo json_encode(['code'=>500,'msg'=>'上传失败！稍后再试！']);
                }
            }else{
                echo json_encode(['code'=>500,'msg'=>'上传失败！稍后再试！']);
            }
        }
    }

    public function reports_del(){
        $id= input('id');
        $url=Db::name('reports')->where(['id'=>$id])->value('url');
        unlink("../public".$url);
        if(Db::name('reports')->delete(['id'=>$id])){
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败，稍后再试！']);
        }
    }



    /**
     * 商业计划书
     */
    public function business_plan(){
        $plan = Db::name('plans')->paginate(30);
        if(count($plan)<=0){
            $plan=[];
        }
        return view('',['plan'=>$plan]);
    }

    /**
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function plans_personal(){
        $plans = Db::name('plans')->where(['create_admin_id'=>Cookie::get('ranglei_admin')['admin_id']])->paginate(30);
        if(count($plans)<=0){
            $plans=[];
        }
        return view('',['plans'=>$plans]);
    }

    /**
     * 商业计划书添加
     */
    public function plans_add(){
        if(request()->isGet()){
            return view();
        }
        if(request()->isPost()){
            $data = input();
            //$file = $_FILES['file'];
            $dir='../public/plans/'.date('Y/m/d').'/';                //上传路径
            if (!file_exists($dir)){                                        //创建文件夹
                mkdir($dir,0777,true);
            }
            $tmp_name=$_FILES['file']['tmp_name'];
            $new_photo=uniqid().'.'.'pdf';
            if (move_uploaded_file($tmp_name,$dir.$new_photo)){
                $url=substr($dir.$new_photo,9);
                if(Db::name('plans')
                    ->insert(
                        [
                            'title'=>$data['plan_title'],
                            'time'=>$data['plan_time'],
                            'url'=>$url,
                            'create_admin_id'=>Cookie::get('ranglei_admin')['admin_id'],
                            'create_admin_name'=>Cookie::get('ranglei_admin')['admin_name'],
                            'create_time'=>date('Y-m-d H:i:s',time()),
                        ]
                    )
                ){
                    echo json_encode(['code'=>200,'msg'=>'商业计划书上传成功！']);
                }else{
                    echo json_encode(['code'=>500,'msg'=>'上传失败！稍后再试！']);
                }
            }else{
                echo json_encode(['code'=>500,'msg'=>'上传失败！稍后再试！']);
            }
        }
    }


    public function plans_del(){
        $id= input('id');
        $url=Db::name('plans')->where(['id'=>$id])->value('url');
        unlink("../public".$url);
        if(Db::name('plans')->delete(['id'=>$id])){
            echo json_encode(['code'=>200,'msg'=>'删除成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'删除失败，稍后再试！']);
        }
    }

}