<?php
namespace app\admin\model;
use think\Db;
use think\Model;

class SuppliersModel extends Model{
    /**
     * 供应商名称模糊查询
     *
     */
    public function supplier_name_like_search($where){
        $supplier_name=Db::name('suppliers')->where('suppliers_name','like','%'.$where.'%')->select();
        return $supplier_name;

    }

    /**
     *供应商id查询
     */
    public function supplier_name_search($where){
        return self::where($where)->value('id');
    }

    /**
     * 供应商添加
     */
    public function supplier_add_do($data){
       $supplier_id = self::insertGetId($data);
        var_dump($supplier_id);exit;
    }

    /**
     * 供应商查询
     */
    public function supplier_select($where=null,$paginate=null){
        $supplier = self::where($where)->paginate($paginate);
        if(count($supplier)>0){
            return $supplier;
        }else{
            return [];
        }
    }

    /**
     * 供应商基本信息查询
     */
    public function supplier_essential_information($where){
        $supplier=Db::name('suppliers')->where($where)->field('id,suppliers_name,suppliers_short_name,contact_name,contact_phone,contact_wx,suppliers_address,status,email')->find();
        $supplier['line']=Db::name('supplier_line')->where(['supplier_id'=>$where['id']])->field('id,line_name')->select();
        $supplier['product']=Db::name('supplier_products')->where(['supplier_id'=>$where['id']])->field('id,product_name')->select();
        return $supplier;
    }

    /**
     * 供应商基本信息修改
     */
    public function supplier_update($where,$data){
        try{
            self::where($where)->update($data);
        }catch (\Exception $e){
            return $e->getMessage();
        }
         return 1;
    }



}