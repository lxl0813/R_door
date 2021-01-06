<?php
namespace app\admin\model;
use think\Db;
use think\Model;

class EnquiryOffer extends Model{
    //询单报价添加，修改该询单状态
    public function enquiryOfferAdd($data){
        Db::startTrans();
        try {
            //修改该询单状态

            Db::name('purchaser_enquiry')->where(['id'=>$data['enquiry_id']])->update(['status'=>2]);
            //添加报价
            self::insert($data);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
        return 1;
    }
}