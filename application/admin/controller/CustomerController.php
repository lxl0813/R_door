<?php
namespace app\admin\controller;
use app\admin\model\CustomersMarketModel;
use app\admin\model\EnquiryOfferModel;
use app\admin\model\EnquiryTypeModel;
use app\admin\model\MarketModel;
use app\admin\model\MarketTypeModel;
use app\admin\model\ProductLineModel;
use app\admin\model\PurchaserModel;
use app\admin\model\PurchaserEnquiry;
use app\admin\model\SupplierLineModel;
use app\admin\model\SupplierProductsModel;
use app\admin\model\SupplierProductsPriceModel;
use app\admin\model\SuppliersModel;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Cookie;
use think\Request;
use think\validate\ValidateRule;

class CustomerController extends CommonController{


    /**
     * @return \think\response\View
     * @throws \think\exception\DbException
     */

    //客户管理系统首页
    public function customer_index(){
        if(in_array(Cookie::get('ranglei_admin')['admin_name'],config('app.check_admin'))){
            $this->redirect('Customer/check_customer_market');
        }
        //序号定义
        $enquiry_no="1";
        $market_no="1";
        $supplier_no="1";
        $is_fob   ="";
        $is_over  ="";
        $admin_id = (new CommonController())->admin_id();
        //采购商询单待报价列表
        $enquiry  =(new PurchaserEnquiry())->stay_enquiry_search();
        //客户管理
        $customer = (new CustomersMarketModel())->customerToMarket(['create_admin_id'=>$admin_id]);
        //管理类型查询
        $market_type=MarketTypeModel::MarketSelect();
        //最新未过期报价表
        $suppler_offer_price=(new SupplierProductsPriceModel())->suppler_offer_price(['spp.create_admin_id'=>$admin_id]);
        return view('',['is_over'=>$is_over,'supplier_no'=>$supplier_no,'supplier_offer_price'=>$suppler_offer_price,'is_fob'=>$is_fob,'market_no'=>$market_no,'enquiry_no'=>$enquiry_no,'market_type'=>$market_type,'enquiry'=>$enquiry,'customer'=>$customer]);
    }



    /**
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    //客户平台类型查询
    public function customer_type_search(){
        $type = input('param.');
        //序号定义
        $enquiry_no="1";
        $market_no="1";
        $supplier_no="1";
        $is_over="";
        $admin_id = (new CommonController())->admin_id();
        $where=['create_admin_id'=>$admin_id];
        $where=$type+$where;
        $customer = (new CustomersMarketModel())->customerToMarket($where);
        //采购商询单待报价列表(老样子)
        $enquiry=(new PurchaserEnquiry())->stay_enquiry_search();
        $market_type=MarketTypeModel::MarketSelect();
        //最新未过期报价表
        $suppler_offer_price=(new SupplierProductsPriceModel())->suppler_offer_price();
        if(isset($type['is_fob'])){
            $is_fob=$type['is_fob'];
        }else{
            $is_fob="";
        }
        return view('customer/customer_index',['is_over'=>$is_over,'supplier_no'=>$supplier_no,'supplier_offer_price'=>$suppler_offer_price,'is_fob'=>$is_fob,'market_no'=>$market_no,'enquiry_no'=>$enquiry_no,'market_type'=>$market_type,'enquiry'=>$enquiry,'customer'=>$customer]);
    }




    /**
     * 首页--询单按钮查询
     */
    public function purchaser_enquiry_search_button(){
        $data=input('');
        //序号定义
        $enquiry_no="1";
        $market_no="1";
        $supplier_no="1";
        $is_over="";
        $is_fob="";
        $admin_id = (new CommonController())->admin_id();
        //客户管理
        $customer = (new CustomersMarketModel())->customerToMarket(['create_admin_id'=>$admin_id]);
        //管理类型查询
        $market_type=MarketTypeModel::MarketSelect();
        //最新未过期报价表
        $suppler_offer_price=(new SupplierProductsPriceModel())->suppler_offer_price();
        //询单按钮查询
        $enquiry = (new PurchaserEnquiry())->purchaser_enquiry_search_content($data['search_content']);
        return view('Customer/customer_index',['is_over'=>$is_over,'supplier_no'=>$supplier_no,'supplier_offer_price'=>$suppler_offer_price,'is_fob'=>$is_fob,'market_no'=>$market_no,'enquiry_no'=>$enquiry_no,'market_type'=>$market_type,'enquiry'=>$enquiry,'customer'=>$customer]);
    }




    /**
     * 报价状态查询
     */
    public function offer_price_search_status(){
        $data['spp.price_status']=input('is_over');
        //序号定义
        $enquiry_no="1";
        $market_no="1";
        $supplier_no="1";
        $is_fob="";
        $admin_id = (new CommonController())->admin_id();
        //询单查询
        $enquiry=(new PurchaserEnquiry())->stay_enquiry_search();
        //客户管理
        $customer = (new CustomersMarketModel())->customerToMarket(['create_admin_id'=>$admin_id]);
        //管理类型查询
        $market_type=MarketTypeModel::MarketSelect();
        //报价查询
        $suppler_offer_price=(new SupplierProductsPriceModel())->offer_price_search_status($data);
        return view('Customer/customer_index',['is_over'=>$data['spp.price_status'],'supplier_no'=>$supplier_no,'supplier_offer_price'=>$suppler_offer_price,'is_fob'=>$is_fob,'market_no'=>$market_no,'enquiry_no'=>$enquiry_no,'market_type'=>$market_type,'enquiry'=>$enquiry,'customer'=>$customer]);
    }






    /**
     * @param array $data 接收值
     * @param array $price_unit 价格单位
     */
    //询单添加报价
    public function enquiry_offer(){
        $data = input('param.');
        if(is_numeric($data['price'])){
            echo json_encode(['code'=>500,'msg'=>'请输入正确的报价，带上单位！']);return;
        }
        $price_unit = explode("|",$data['price']);
        $data['own_quotation']=$price_unit[0];
        $data['unit']=$price_unit[1];
        $data['offer_time']=date("Y-m-d H:i:s",time());
        $data['offer_admin_id']=$this->admin_id();
        unset($data['price']);
        $enquiry_offer_add = (new EnquiryOfferModel())->enquiryOfferAdd($data);
        if($enquiry_offer_add==1){
            echo json_encode(['code'=>200,'msg'=>'您已经报价成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>$enquiry_offer_add]);
        }
    }




    /**
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    //采购商询单新增
    public function purchaser_enquiry_add(){
        $admin = $this->cookie_admin();
        //生成时分
        $h=[];$m=[];
        for($i=1;$i <=24;$i ++){
            if(strlen($i) < 2){
                $h[]="0".$i;
            }else{
                $h[]=$i;
            }
        }
        for($i=1;$i<=60;$i ++){
            if(strlen($i) < 2){
                $m[]="0".$i;
            }else{
                $m[]=$i;
            }
        }
        //化纤无纺规格查询
        $enquiry_type['hx']=EnquiryTypeModel::where(['type'=>1])->select();
        $enquiry_type['wf']=EnquiryTypeModel::where(['type'=>2])->select();
        return view('',['enquiry_type'=>$enquiry_type,'h'=>$h,'m'=>$m,'admin'=>$admin]);
    }




    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    //询单提交
    public function purchaser_enquiry_add_do(){
        $data=input('param.');
        if(empty($data['enquiry_sn'])){
            $time=time();
            $data['enquiry_sn']=date("Y",$time).date("m",$time).date('d',$time).date('H',$time).date('i',$time);
        }
        //询单时间处理
        $data['enquiry_time']=$data['date']." ".$data['h'].":".$data['m'].":"."00";unset($data['date']);unset($data['h']);unset($data['m']);
        //拿里面的内容进行查询，如果查询到了，就代表相同询单已存在，进行追加报价
        $where=[
            'purchaser_name'=>$data['purchaser_name'],
            'contact_name'=>$data['contact_name'],
            'contact_phone'=>$data['contact_phone'],
            'product_name'=>$data['product_name'],
            'enquiry_type'=>$data['enquiry_type'],
            'purchaser_type'=>$data['purchaser_type'],
        ];
        $resout = (new PurchaserEnquiry())->enquiry_is_existence_search($where);

        if($resout['return']==1){
            echo json_encode(['code'=>500,'msg'=>'相同询单已存在!可前往询单中心进行追加报价！询单编号:'.$resout['enquiry_sn']]);return;
        }elseif($resout['return']==3){
            //没有相同询单，采购商也不存在
            foreach ($data['type'] as $k=>$v){
                $data['enquiry_value'][]=explode("|",$v);
            }
            unset($data['type']);
            foreach ($data['enquiry_value'] as $k=>$v){
                $a[$k]['enquiry_type_id']=$v['0'];
                $a[$k]['enquiry_type_value']=$v['1'];
            }
            unset($data['enquiry_value']);
            $data['type']=$a;
            $return  = (new PurchaserModel())->purchaser_enquiry_add($data);
            if($return==1){
                echo json_encode(['code'=>200,'msg'=>'已添加了采购商，并为该采购商添加了询单记录！']);
            }else{
                echo json_encode(['code'=>500,'msg'=>$return]);
            }
        }else{
            //采购商存在，相同询单不存在
            $data['purchaser_id']=$resout['id'];
            foreach ($data['type'] as $k=>$v){
                $data['enquiry_value'][]=explode("|",$v);
            }
            unset($data['type']);
            foreach ($data['enquiry_value'] as $k=>$v){
                $a[$k]['enquiry_type_id']=$v['0'];
                $a[$k]['enquiry_type_value']=$v['1'];
            }
            unset($data['enquiry_value']);
            $data['type']=$a;
            $return = (new PurchaserEnquiry())->purchaser_enquiry_add($data);
            if($return==1){
                echo json_encode(['code'=>200,'msg'=>'已为该采购商添加询单记录！']);
            }else{
                echo json_encode(['code'=>500,'msg'=>$return]);
            }
        }
    }



    /**
     * 询单编号生成
     * @return $enquiry_sn 生成的询单编号
     */
    //询单编号点击生成()
    public function generate(){
        $time=time();
        $enquiry_sn = date("YmdHi",$time);
        if($enquiry_sn){
            echo json_encode(['code'=>200,'msg'=>'询单编号已生成！','enquiry_sn'=>$enquiry_sn]);
        }else{
            echo json_encode(['code'=>500,'msg'=>'系统繁忙！']);
        }
    }




    /**
     * 采购商询单管理中心--首页
     * @return \think\response\View
     * @return $role 一级角色
     */
    public function purchaser_enquiry_index(){
        if(request()->isGet()){
            return view();
        }
    }





    /**
     * @return \think\response\View
     */
    //采购商询单管理中心--询单状态查询
    public function purchaser_enquiry_search_status(){
        $status = input('');
        $no="1";
        $enquiry_list = (new PurchaserEnquiry())->purchaser_enquiry_search_status($status);
        return view('customer/purchaser_enquiry_index',['no'=>$no,'enquiry'=>$enquiry_list]);
    }





    /** 采购商询单管理中心--根据询单编号或者单位名称查询
     * @return \think\response\View
     * @throws \think\exception\DbException
     * @param array $data *查询条件
     * @return $no 返回的序号
     * @return $role 一级角色
     * @return $enquiry_list 查询结果
     */
    public function purchaser_enquiry_search_content(){
        $data=input('');
        $no="1";
        $enquiry_list = (new PurchaserEnquiry())->purchaser_enquiry_search_content($data['search_content']);
        return view('customer/purchaser_enquiry_index',['no'=>$no,'enquiry'=>$enquiry_list]);
    }





    /**
     * 采购商询单查看页面
     * @return \think\response\View
     * @return $role 一级角色
     */
    public function purchaser_enquiry_see(){
        $enquiry_id = input("enquiry_id");
        $enquiry_details = (new PurchaserEnquiry())->pruchaser_enquiry_details_see($enquiry_id);
        return view('',['enquiry_details'=>$enquiry_details]);
    }




    /**
     * 供应商名称查询
     */
    public function supplier_name_like_search(){
        $data=input("supplier_name");
        $supplier_name=(new SuppliersModel())->supplier_name_like_search($data);
        echo json_encode(['code'=>200,'supplier_name'=>$supplier_name]);
    }




    /**
     * 供应商产品条件查询
     */
    public function supplier_product_search(){
        $data['suppliers_name']=input("supplier_name");
        $supplier_id['supplier_id'] = (new SuppliersModel())->supplier_name_search($data);
        if(empty($supplier_id['supplier_id'])){
            echo json_encode(['code'=>500,'msg'=>'对不起！没有查询到该供应商！，请先前往添加供应商！']);return;
        }
        $supplier_product_list=(new SupplierProductsModel())->supplier_product_search($supplier_id);
        if(empty($supplier_product_list)){
            echo json_encode(['code'=>500,'msg'=>'该供应商还未添加过产品信息！请前往添加产品信息后，再进行报价添加！']);
        }else{
            echo json_encode(['code'=>200,'supplier_product_list'=>$supplier_product_list]);
        }
    }




    /**
     * 供应商添加报价页面
     */
    public function supplier_offer_price_add(){
        //生成时分
        $h=[];$m=[];
        for($i=1;$i <=24;$i ++){
            if(strlen($i) < 2){
                $h[]="0".$i;
            }else{
                $h[]=$i;
            }
        }
        for($i=1;$i<=60;$i ++){
            if(strlen($i) < 2){
                $m[]="0".$i;
            }else{
                $m[]=$i;
            }
        }
        $admin = $this->cookie_admin();
        return view('',['h'=>$h,'m'=>$m,'admin'=>$admin]);
    }


    /**
     * @return \think\response\View
     * 供应商添加报价提交
     *
     */
    public function supplier_offer_price_add_do(){
        $data=input("param.");
        $resout = (new SupplierProductsPriceModel())->supplier_offer_price_add_do($data);
        if($resout==1){
            echo json_encode(['code'=>200,'msg'=>'供应商报价添加成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>$resout]);
        }
    }



    /**
     * @return \think\response\View
     * 供应商商品报价首页
     */
    public function supplier_offer_price_index(){
        $no="1";
        $is_over="";
        //查询供应商报价数据
        $where['spp.create_admin_id']=$this->admin_id();
        $offer_price = (new SupplierProductsPriceModel())->suppler_offer_price($where);
        return view('',['is_over'=>$is_over,'no'=>$no,'offer_price'=>$offer_price]);
    }


    /**
     * @return \think\response\View
     * 供应商报价中心--状态查询
     *
     */
    public function supplier_offer_price_search_status(){
        $data['spp.price_status']=input('is_over');
        $data['spp.create_admin_id']=$this->admin_id();
        $offer_price = (new SupplierProductsPriceModel())->offer_price_search_status($data);
        $no="1";
        return view('Customer/supplier_offer_price_index',['is_over'=>$data['spp.price_status'],'no'=>$no,'offer_price'=>$offer_price]);
    }





    /**
     * @return \think\response\View
     *添加供应商页面
     */
    public function supplier_add(){
        $role = $this->role();
        return view('',['role'=>$role]);
    }




    /**
     * @return \think\response\View
     * 添加供应商提交
     */
    public function supplier_add_do(Request $request){
        $data=$request->post();
        //先查询是否存在相同供应商
        $is_supplier = Db::name('suppliers')->where(['suppliers_name'=>$data['suppliers_name'],'contact_name'=>$data['contact_name'],'status'=>$data['status']])->find();
        if($is_supplier){
            echo json_encode(['code'=>500,'msg'=>'该供应商已存在！不需要反复添加！']);return;
        }
        $data['create_time']=date("Y-m-d H:i:s",time());
        $data['create_admin_id']=$this->admin_id();
        if(!empty($data['line_name'])){
            $line = explode("-",$data['line_name']);
            foreach ($line as $k=>$v){
                $lines[$k]['line_name']=$v;
            }
            unset($line);unset($data['line_name']);
        }
        if(!empty($data['product_name'])) {
            $product = explode("-", $data['product_name']);
            foreach ($product as $k => $v) {
                $products[$k]['product_name'] = $v;
            }
            unset($product);unset($data['product_name']);
        }
        unset($data['line_name']);unset($data['product_name']);
        Db::startTrans();
        try {
            $supplier_id=Db::name('suppliers')->insertGetId($data);
            if(isset($lines)){
                foreach ($lines as $k=>$v){
                    $lines[$k]['supplier_id']=$supplier_id;
                }
                Db::name('supplier_line')->insertAll($lines);
            }
            if(isset($products)){
                foreach ($products as $k=>$v){
                    $products[$k]['supplier_id']=$supplier_id;
                }
                Db::name('supplier_product')->insertAll($products);
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            echo json_encode(['code'=>500,'msg'=>$e->getMessage()]);return;
        }
        echo json_encode(['code'=>200,'msg'=>'供应商添加成功！']);
    }




    /**
     * @return \think\response\View
     * 供应商管理页面
     */
    public function supplier_index(){
        //查询
        $where['create_admin_id']=$this->admin_id();
        $paginate="20";
        $no="1";
        $suplier=(new SuppliersModel())->supplier_select($where,$paginate);
        return view('',['no'=>$no,'supplier'=>$suplier]);
    }




    /**
     * 供应商生产线鼠标悬浮事件查询
     */
    public function supplier_line_suspension_search(Request $request){
        $data=$request->post();
        $supplier_line = (new SupplierLineModel())->supplier_line_suspension_search($data);
        $content="";
        if(!empty($supplier_line)){
            foreach ($supplier_line as $k=>$v){
                $content.=$v['line_name']."<br>";
            }
        }else{
            $content="该供应商未添加生产线！";
        }
        echo json_encode(['code'=>200,'line'=>$content]);
    }




    /**
     * 供应商产品鼠标悬浮查询事件
     */
    public function supplier_product_suspension_search(Request $request){
        $data=$request->post();
        $supplier_product = (new SupplierProductsModel())->supplier_product_search($data);
        $content="";
        if(!empty($supplier_product)){
            foreach ($supplier_product as $k=>$v){
                $content.=$v['product_name']."<br>";
            }
        }else{
            $content="该供应商未添加产品！";
        }
        echo json_encode(['code'=>200,'line'=>$content]);
    }



    /**
     * 供应商信息修改页面
     */
    public function supplier_update_index(){
        //查询所有供应商基本信息
        $data['id']=input('supplier_id');
        $supplier=(new SuppliersModel())->supplier_essential_information($data);
        return view('',['supplier'=>$supplier]);
    }


    /**
     *供应商基本信息修改
     */
    public function supplier_update_j_do(Request $request){
        $data=$request->post();
        $where['id']=$data['id'];unset($data['id']);
        $res = (new SuppliersModel())->supplier_update($where,$data);
        if($res==1){
            echo json_decode(['code'=>200,'msg'=>'供应商基本信息修改成功！']);
        }else{
            echo json_decode(['code'=>500,'msg'=>$res]);
        }
    }

    /**
     *供应商生产线信息修改
     */
    public function supplier_update_line_do(Request $request){
        $data=$request->post();
        $where['id']=$data['id'];unset($data['id']);
        if($data['line_type']==1){
            //修改
            foreach ($data['line'] as $k=>$v){
                $line[$k] = explode("-",$v);
            }
            foreach($line as $k=>$v){
                $lines[$k]['id']=$v[0];
                $lines[$k]['line_name']=$v[1];
            }
            unset($line);
            $res = (new SupplierLineModel())->supplier_line_update_batch($lines);
            if($res==1){
                echo json_encode(['code'=>200,'msg'=>'生产线修改成功！']);
            }else{
                echo json_encode(['code'=>500,'msg'=>$res]);
            }
        }else{
            //新增
            $line=explode("-",$data['line_name']);
            foreach($line as $k=>$v){
                $lines[$k]['supplier_id']=$where['id'];
                $lines[$k]['line_name']=$v;
            }
            $res=(new SupplierLineModel())->supplier_line_add($lines);
            if($res==1){
                echo json_encode(['code'=>200,'msg'=>'已成功新增生产线！']);
            }else{
                echo json_encode(['code'=>500,'msg'=>$res]);
            }
        }
    }


    /**
     * 供应商产品修改
     */
    public function supplier_update_product_do(Request $request){
        $data=$request->post();
        $where['id']=$data['id'];unset($data['id']);
        if($data['product_type']==1){
            foreach ($data['product'] as $k=>$v){
                $product[$k] = explode("-",$v);
            }
            foreach($product as $k=>$v){
                $products[$k]['id']=$v[0];
                $products[$k]['product_name']=$v[1];
            }
            unset($line);
            $res = (new SupplierProductsModel())->supplier_product_update_batch($products);
            if($res==1){
                echo json_encode(['code'=>200,'msg'=>'产品信息修改成功！']);
            }else{
                echo json_encode(['code'=>500,'msg'=>$res]);
            }
        }else{
            $product=explode("-",$data['product_name']);
            foreach($product as $k=>$v){
                $products[$k]['supplier_id']=$where['id'];
                $products[$k]['product_name']=$v;
            }
            $res=(new SupplierProductsModel())->supplier_product_add($products);
            if($res==1){
                echo json_encode(['code'=>200,'msg'=>'已成功新增产品！']);
            }else{
                echo json_encode(['code'=>500,'msg'=>$res]);
            }
        }
    }



    /**
     * 供应商生产线删除
     */
    public function supplier_delete_line_do(Request $request){
        $data = $request->post();
        $res = (new SupplierLineModel())->supplier_line_delete($data);
        if($res==1){
            echo json_encode(['code'=>200,'msg'=>'该生产线已删除！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>$res]);
        }
    }



    /**
     * 供应商报价查看
     */
    public function supplier_offer_see(){
        $data=input('');
        $res = (new SupplierProductsPriceModel())->supplier_product_price($data);
        echo json_encode(['code'=>200,'data'=>$res]);
    }



    /**
     * @return \think\response\View
     * 采购商管理页面
     */
    public function purchaser_index(){
        //查询
        $paginate="20";
        $no="1";
        $purchaser = (new PurchaserModel())->purchaser_information(['create_admin_id'=>$this->admin_id()],$paginate);
        return view('',['no'=>$no,'purchaser'=>$purchaser]);
    }



    /**
     * 采购商查看
     */

    public function purchaser_update_index(){
        $data['id']=input('purchaser_id');
        $purchaser = (new PurchaserModel())->purchaser_information($data,0);
        $purchaser=json_decode(json_encode($purchaser),true)['data'][0];
        return view('',['purchaser'=>$purchaser]);
    }


    /**
     *采购商基本信息修改
     */

    public function purchaser_update_do(){
        $data=input('param.');
        $purchaser = (new PurchaserModel())->purchaser_update_do($data);
        if($purchaser==1){
            echo json_encode(['code'=>200,'msg'=>'信息修改成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>$purchaser]);
        }
    }

    /**
     * 采购商询单查看
     */
    public function purchaser_enquiry_sees(){
        $data=input('purchaser_id');
        $purchaser=(new PurchaserModel())->purchaser_enquiry_see($data);
        return view('',['purchaser'=>$purchaser]);
    }



    /**
     * 客户营销管理
     */
    public function customer_market_add(){
        $customer_id=input('customer_id');
        $customer=(new CustomersMarketModel())->customer_information(['id'=>$customer_id],'customer_name',null,1);
        //取管理标签
        $market_type=MarketTypeModel::MarketSelect();
        return view('',['market_type'=>$market_type,'customer'=>$customer,'customer_id'=>$customer_id]);
    }


    /**
     * 营销管理提交
     */
    public function customer_market_add_do(){
        $data=input('param.');
        $time=time();
        $y_m_d=date("Y-m-d",$time);
        foreach ($data['change'] as $k=>$v){
            $exp=explode("-",$v);
            $res[$k]['market_type_id']=$exp[0];
            $res[$k]['status']=$exp[1];
            if($exp[0]==6){
                if($exp[1]==1){
                    $res[$k]['status_value']="未交易";
                }elseif($exp[1]==2){
                    $res[$k]['status']="线上交易";
                }elseif($exp[1]==3){
                    $res[$k]['status_value']="线下交易";
                }else{
                    $res[$k]['status_value']="双线交易";
                }
            }else{
                if($exp[1]==1){
                    $res[$k]['status_value']="否";
                }else{
                    $res[$k]['status_value']="是";
                }
            }
            $res[$k]['customer_id']=$data['customer_id'];
            $res[$k]['y_m_d']=$y_m_d;
            $res[$k]['time']=$time;
            $res[$k]['create_admin_id']=$this->admin_id();
        }
        $resout = (new MarketModel())->market_add($res);
        if($resout==true){
            echo json_encode(['code'=>200,'msg'=>'今日营销管理成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>$resout]);
        }
    }


    /**
     * 客户营销管理页面
     */
    public function customer_market_index(){
        $no="1";
        $where['create_admin_id']=$this->admin_id();
        $is_fob="";
        //查询该管理员所管理的所有客户
        $customer=(new CustomersMarketModel())->customerToMarket($where);
        $market_type=MarketTypeModel::MarketSelect();
        return view('',['is_fob'=>$is_fob,'no'=>$no,'customer'=>$customer,'market_type'=>$market_type]);
    }


    /**
     * 客户详细管理
     */

    public function customer_market_detail_index(){
        $customer_id=input("customer_id");
        $no="1";
        $customer=(new CustomersMarketModel())->customer_market_all($customer_id);
        $market_type=MarketTypeModel::MarketSelect();
        return view('',['customer'=>$customer,'no'=>$no,'market_type'=>$market_type,'customer_id'=>$customer_id]);
    }

    /**
     * 客户营销平台类型查询
     */
    public function customer_market_type_search(){
        $data=input('param.');
        $no="1";
        $where=['create_admin_id'=>$this->admin_id()];
        $where = $data+$where;
        $customer = (new CustomersMarketModel())->customerToMarket($where);
        $market_type=MarketTypeModel::MarketSelect();
        if(isset($data['is_fob'])){
            $is_fob=$data['is_fob'];
        }else{
            $is_fob="";
        }
        return view('Customer/customer_market_index',['no'=>$no,'market_type'=>$market_type,'is_fob'=>$is_fob,'customer'=>$customer]);
    }


    /**
     *  管理修改
     */
    public function customer_market_update(){
        $customer_id=input('param.');
        $market=(new MarketModel())->customerMarket($customer_id);
        foreach ($market as $k=>$v){
            $market[$k]['market_type']=Db::name('market_type')->where(['id'=>$v['market_type_id']])->value('type_name');
        }
        return view('',['customer_id'=>$customer_id['customer_id'],'market'=>$market]);

    }

    /**
     * 执行修改
     */
    public function customer_market_update_do(){
        $data=input('param.');
        $time=time();
        $y_m_d=date("Y-m-d",$time);
        foreach ($data['change'] as $k=>$v){
            $exp=explode("-",$v);
            $res[$k]['id']=$exp[0];
            $res[$k]['status']=$exp[1];
            if($exp[0]==6){
                if($exp[1]==1){
                    $res[$k]['status_value']="未交易";
                }elseif($exp[1]==2){
                    $res[$k]['status']="线上交易";
                }elseif($exp[1]==3){
                    $res[$k]['status_value']="线下交易";
                }else{
                    $res[$k]['status_value']="双线交易";
                }
            }else{
                if($exp[1]==1){
                    $res[$k]['status_value']="否";
                }else{
                    $res[$k]['status_value']="是";
                }
            }
        }
        $resout = (new MarketModel())->market_update($res);
        if($resout==true){
            echo json_encode(['code'=>200,'msg'=>'修改成功！']);
        }else{
            echo json_encode(['code'=>500,'msg'=>$resout]);
        }
    }


    /**
     * @return \think\response\View
     */
    public function customer_add(){
        return view();
    }


    /**
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function customer_add_do(Request $request){
        $data = $request->post();
        //查询客户信息是否已经存在；
        if(Db::name('customers_market')->where($data)->find()){
            echo json_encode(['code'=>500,'msg'=>'已存在相同客户！']);return;
        }
        $data['create_admin_id']=$this->admin_id();
        $data['create_time']=date('Y-m-d H:i:s',time());
        $res = (new CustomersMarketModel())->customer_add_do($data);
        if($res==true){
            echo json_encode(['code'=>200,'msg'=>"新增客户成功！"]);
        }else{
            echo json_encode(['code'=>500,'msg'=>$res]);
        }
    }


    /**
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 客户添加前的匹配搜索
     */
    public function customer_match_search(Request $request){
        $data=$request->post();
        //对客户表进行查询，是否已经有这个用户了
        $customer=Db::name('customers_market')->where($data)->find();
        if($customer){
            echo json_encode(['code'=>500,'msg'=>'已存在相同名称、客户类型、联系人的客户!']);return;
        }

        //前往供应商和采购商用户表进行匹配查询
        $res=Db::name('purchaser')
            ->where(['purchaser_name'=>$data['customer_name'],'is_fob'=>$data['is_fob'],'contact_name'=>$data['contact_name']])
            ->field('purchaser_short_name,contact_phone,contact_wx,email,purchaser_address')
            ->find();
        if(!$res){
            $res=Db::name('suppliers')
                ->where(['suppliers_name'=>$data['customer_name'],'is_fob'=>$data['is_fob'],'contact_name'=>$data['contact_name']])
                ->field('suppliers_short_name,contact_phone,contact_wx,email,suppliers_address')
                ->find();
            if(!$res){
                echo json_encode(['code'=>500,'msg'=>"对不起！没有为您匹配到对应信息，后续信息需要您自己进行填写！"]);
            }else{
                echo json_encode(['code'=>300,'msg'=>"已经在供应商用户中为您匹配到相同信息，是否需要对信息进行填充？",'data'=>$data]);
            }
        }else{
            echo json_encode(['code'=>200,'msg'=>'已经在采购商用户中为您匹配到相同信息，是否需要对信息进行填充？','data'=>$res]);
        }
    }

    /**
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 供应商填写匹配搜索
     */
    public function supplier_match_search(Request $request){
        $data = $request->post();
        //查询是否存在相同供应商
        if(Db::name('suppliers')->where($data)->find()){
            echo json_encode(['code'=>500,'msg'=>'已存在相同供应商！']);return;
        }
        $res=Db::name('purchaser')->where(['purchaser_name'=>$data['suppliers_name'],'is_fob'=>$data['is_fob'],'contact_name'=>$data['contact_name']])->find();
        if(!$res){
            echo json_encode(['code'=>500,'msg'=>'未匹配到相应信息，后续信息需要您亲自填写！']);
        }else{
            echo json_encode(['code'=>200,'msg'=>'已为您在采购商用户中匹配到相应信息，是否自动填充信息？','data'=>$res]);
        }
    }

    /**
     * @return \think\response\View
     * 采购商添加
     */
    public function purchaser_add(){
        return view();
    }

    /**
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function purchaser_add_do(Request $request){
        $data=$request->post();
        if(Db::name('purchaser')->where($data)->find()){
            echo json_encode(['code'=>200,'msg'=>'已经存在相同采购商！']);return;
        }
        $data['create_admin_id']=$this->admin_id();
        $data['create_time']=date('Y-m-d H:i:s',time());
        $res=Db::name('purchaser')->insert($data);
        if($res==true){
            echo json_encode(['code'=>200,'msg'=>'采购商添加成功！']);
        }
    }


    /**
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 采购商添加前的信息匹配搜索
     */
    public function purchaser_match_search(Request $request){
        $data = $request->post();
        if(Db::name('purchaser')->where($data)->find()){
            echo json_encode(['code'=>500,'msg'=>'已存在相同采购商！']);return;
        }
        $res=Db::name('suppliers')->where(['suppliers_name'=>$data['purchaser_name'],'is_fob'=>$data['is_fob'],'contact_name'=>$data['contact_name']])->find();
        if($res){

            echo json_encode(['code'=>200,'msg'=>'已为您在供应商中匹配到相关信息，是否填充信息？','data'=>$res]);
        }else{
            echo json_encode(['code'=>500,'msg'=>'未匹配到相应信息，后续信息需要您亲自填写！']);
        }
    }


    /**
     * 营销管理情况查看
     */
    public function check_customer_market(){
        $is_fob="";
        $no="1";
        //查询各个管理员下的客户管理情况
        $admin =Db::name('admin')
            ->alias('a')
            ->rightJoin('admin_role ar',"a.id=ar.admin_id")
            ->where(['ar.role_id'=>58])
            ->field('a.id,a.name')
            ->select();
        foreach ($admin as $k=>$v){
            $admin[$k]['nums']=Db::name('customers_market')->where(['create_admin_id'=>$v['id']])->count();
        }
        return view('',['no'=>$no,'is_fob'=>$is_fob,'admin'=>$admin]);
    }

    /**
     * 查看
     */
    public function check_customer_see(){
        $no ="1";
        $admin_id = input('admin_id');
        $customer = Db::name('customers_market')
            ->alias('cm')
            ->join('admin a','a.id=cm.create_admin_id')
            ->where(['cm.create_admin_id'=>$admin_id])
            ->field('a.name,cm.id,cm.customer_name,cm.customer_short_name,cm.contact_name,cm.contact_phone,cm.is_fob')
            ->paginate(20);
        if(count($customer)<=0){
            $customer=[];
        }
        return view('',['no'=>$no,'customer'=>$customer]);
    }

    /**
     *
     */
    public function check_customer_market_see(){
        $no ="1";
        $data= input('customer_id');
        $customer=Db::name('customers_market')->where(['id'=>$data])->find();
        $market = Db::name('market')->where(['customer_id'=>$data])->order('time desc')->select();
        $market = array_chunk($market,11);
        //管理类型查询
        $market_type=MarketTypeModel::MarketSelect();
        return view('',['no'=>$no,'customer'=>$customer,'market'=>$market,'market_type'=>$market_type]);
    }


}