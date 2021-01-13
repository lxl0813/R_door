<?php
namespace app\admin\model;
use think\Model;

class SupplierProductsModel extends Model{
    /**
     * 供应商产品条件查询
     * @param int $where 供应商id
     */
    public function supplier_product_search($where){
        $supplier_product=self::where($where)->select()->toArray();
        return $supplier_product;
    }

    /**
     * 供应商产品添加
     */
    public function supplier_product_add($data){
        try {
            self::insertAll($data);
        }catch (\Exception $e){
            return $e->getMessage();return;
        }
        return 1;
    }

    /**
     * 顾上产品批量修改
     */
    public function supplier_product_update_batch($data){
        try {
            self::saveAll($data);
        }catch (\Exception $e){
            return $e->getMessage();return;
        }
        return 1;
    }

}