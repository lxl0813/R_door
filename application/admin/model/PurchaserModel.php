<?php
namespace app\admin\model;
use app\admin\controller\CommonController;
use think\Db;
use think\facade\Cookie;
use think\Model;

class PurchaserModel extends Model{

    //再添加询单时，没有采购商和询单的，先添加采购商，再添加询单
    public function purchaser_enquiry_add($data){
        Db::startTrans();
        try {
            $purchaser_id =self::insertGetId(
                [
                    'purchaser_name'=>$data['purchaser_name'],
                    'contact_name'  =>$data['contact_name'],
                    'contact_phone' =>$data['contact_phone'],
                    'create_time'   =>date("Y-m-d H:i:s",time()),
                    'purchaser_address' =>$data['purchaser_address'],
                    'contact_wx'   =>$data['contact_wx'],
                    'is_fob'        =>$data['purchaser_type'],
                    'email'         =>$data['email']
                ]
            );
            $enquiry_id = Db::name('purchaser_enquiry')
                ->insertGetId(
                    [
                        'purchaser_id'=>$purchaser_id,
                        'purchaser_name'=>$data['purchaser_name'],
                        'enquiry_sn'=>$data['enquiry_sn'],
                        'enquiry_time'=>$data['enquiry_time'],
                        'product_name'=>$data['product_name'],
                        'create_admin_id'=>Cookie::get('ranglei_admin')['admin_id'],
                        'create_time'    =>date("Y-m-d H:i:s",time()),
                        'status'=>1,
                        'enquiry_type'=>$data['enquiry_type'],
                        'contents'=>$data['contents']
                    ]
                );
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
     * 采购商信息查询
     */
    public function purchaser_information($where=null,$paginate){
        $purchaser = self::where($where)->paginate($paginate);
        if(count($purchaser)>0){
            return $purchaser;
        }else{
            return [];
        }
    }


    /**
     * 采购商信息修改
     */
    public function purchaser_update_do($data){
        try {
            self::update($data);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return 1;
    }


    /**
     * 采购商询单列表页面
     */
    public function purchaser_enquiry_see($data){
        $purchaser = Db::name('purchaser')->where(['id'=>$data])->find();
        $purchaser['enquiry'] = Db::name('purchaser_enquiry')->where(['purchaser_id'=>$data])->paginate(30);
        return $purchaser;
    }

    /**
     * 采购商添加
     */
    public function purchaser_add_do($data){
        try {
            self::insert($data);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return true;
    }

}