<?php
namespace app\admin\model;
use app\admin\controller\Common;
use think\Db;
use think\facade\Cookie;
use think\Model;

class PurchaserEnquiry extends Model{

    /**
     * @return array|\think\Paginator
     * @throws \think\exception\DbException
     */
    //待报价询单查询
    public function stay_enquiry_search(){
        $admin_id = (new Common())->admin_id();
        $list = Db::name('purchaser_enquiry')
            ->alias('pe')
            ->join('purchaser p','pe.purchaser_id=p.id')
            ->where(['pe.status'=>1,'pe.create_admin_id'=>$admin_id])
            ->order('enquiry_time')
            ->field('pe.id,pe.purchaser_id,pe.purchaser_name,pe.enquiry_sn,pe.enquiry_time,pe.product_name,pe.create_admin_id,pe.create_time,pe.status,pe.contents,p.contact_name,p.contact_phone')
            ->paginate(10,false,['var_page'=>'pageB']);
        if(count($list)>0){
            return $list;
        }else{
            return [];
        }
    }

    /**
     * @param $where
     * @return int  1存在相同询单  $purchaser存在采购商，询单不存在,返回采购商id，3全都不存在；
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    //根据条件查询该询单是否存在，如已存在，提示追加报价
    public function enquiry_is_existence_search($where){
        $purchaser = Db::name('purchaser')
                    ->where(['purchaser_name'=>$where['purchaser_name'],'is_fob'=>$where['purchaser_type'],'contact_name'=>$where['contact_name'],'contact_phone'=>$where['contact_phone']])
                    ->field('id')
                    ->find();
        if($purchaser){
            $enquiry=Db::name('purchaser_enquiry')
                    ->where(['purchaser_id'=>$purchaser['id'],'enquiry_type'=>$where['enquiry_type'],'product_name'=>$where['product_name']])
                    ->field('enquiry_sn')
                    ->find();
            if($enquiry){
                $enquiry['return']="1";
                return $enquiry;
            }else{
                $purchaser['return']="2";
                return $purchaser;
            }
        }else{
            $array['return']="3";
            return $array;
        }
    }

    //有采购商id，添加询单
    public function purchaser_enquiry_add($data){
        //var_dump($data);exit;
        Db::startTrans();
        try {
            //询单入库
            $enquiry_id = self::insertGetId(
                [
                    'purchaser_id'   =>$data['purchaser_id'],
                    'purchaser_name' =>$data['purchaser_name'],
                    'enquiry_sn'     =>$data['enquiry_sn'],
                    'enquiry_time'   =>$data['enquiry_time'],
                    'product_name'   =>$data['product_name'],
                    'create_admin_id'=>Cookie::get('ranglei_admin')['admin_id'],
                    'create_time'    =>date("Y-m-d H:i:s",time()),
                    'enquiry_type'=>$data['enquiry_type'],
                    'status'=>1,
                    'contents'=>$data['contents']
                ]
            );
            //询单规格入库
            foreach ($data['type'] as $k=>$v){
                $data['type'][$k]['enquiry_id']=$enquiry_id;
            }
            Db::name('enquiry_value')->insertAll($data['type']);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();return;
        }
        return 1;
    }

    /**
     * @param $status 询单状态
     * @return array
     */
    //根据询单状态查询询单信息
    public function purchaser_enquiry_search_status($status){
        $list = Db::name('purchaser_enquiry')
            ->alias('pe')
            ->join('purchaser p','pe.purchaser_id=p.id')
            ->where(['pe.status'=>$status['status']])
            ->order('enquiry_time')
            ->field('pe.id,pe.purchaser_id,pe.purchaser_name,pe.enquiry_sn,pe.enquiry_time,pe.product_name,pe.create_admin_id,pe.create_time,pe.status,pe.contents,p.contact_name,p.contact_phone')
            ->paginate(30);
        if(count($list)>0){
            return $list;
        }else{
            return [];
        }
    }

    /**
     *  根据询单编号或者单位名称查询
     *  @param $where //查询条件
     * @return array
     *
     */
    public function purchaser_enquiry_search_content($where){
        if(is_numeric($where)){
            //询单编号查询
            $list = Db::name('purchaser_enquiry')
                ->alias('pe')
                ->join('purchaser p','pe.purchaser_id=p.id')
                ->where(['pe.enquiry_sn'=>$where])
                ->order('enquiry_time')
                ->field('pe.id,pe.purchaser_id,pe.purchaser_name,pe.enquiry_sn,pe.enquiry_time,pe.product_name,pe.create_admin_id,pe.create_time,pe.status,pe.contents,p.contact_name,p.contact_phone')
                ->paginate(30);
            if(count($list)>0){
                return $list;
            }else{
                return [];
            }
        }else{
            //单位名称查询
            $list = Db::name('purchaser_enquiry')
                ->alias('pe')
                ->join('purchaser p','pe.purchaser_id=p.id')
                ->where('pe.purchaser_name','like',"%".$where."%")
                ->order('enquiry_time')
                ->field('pe.id,pe.purchaser_id,pe.purchaser_name,pe.enquiry_sn,pe.enquiry_time,pe.product_name,pe.create_admin_id,pe.create_time,pe.status,pe.contents,p.contact_name,p.contact_phone')
                ->paginate(30);
            if(count($list)>0){
                return $list;
            }else{
                return [];
            }
        }
    }

    /**
     * 采购商询单详情页
     * @param $where *查询条件
     * @return  询单信息
     */
    public function pruchaser_enquiry_details_see($where){
        $information = Db::name('purchaser_enquiry')
            ->alias('pe')
            ->join('purchaser p','pe.purchaser_id=p.id')
            ->where(['pe.id'=>$where])
            ->order('enquiry_time')
            ->field('pe.id,pe.enquiry_type,pe.purchaser_id,pe.purchaser_name,pe.enquiry_sn,pe.enquiry_time,pe.product_name,pe.create_admin_id,pe.status,pe.contents,p.contact_name,p.contact_phone,p.contact_wx,p.purchaser_address,p.is_fob,p.email,pe.contents')
            ->find();
        $information['create_admin_id']=Db::name('admin')->where(['id'=>$information['create_admin_id']])->value('name');
        //类型值
        $information['enquiry_type_value'] = Db::name('enquiry_value')
            ->alias('ev')
            ->join('enquiry_type et','ev.enquiry_type_id=et.id')
            ->where(['enquiry_id'=>$where])
            ->select();
        //该询单所产生的所有报价信息
        $enquiry_offer = Db::name('enquiry_offer')->where(['enquiry_id'=>$where])->select();

        if(!empty($enquiry_offer)){
            foreach ($enquiry_offer as $k=>$v){
                $enquiry_offer[$k]['offer_Suppliers_name']=Db::name('suppliers')->where(['id'=>$v['offer_Suppliers_id']])->value('suppliers_name');
            }
            $information['enquiry_offer_price']=$enquiry_offer;
        }else{

            $information['enquiry_offer_price']=[];
        }
        return $information;
    }
}