<?php
namespace app\admin\model;
use think\Model;

class SupplierLine extends Model{
    /**
     * 供应商生产线添加
     */
    public function supplier_line_add($data){
        try{
            self::insertAll($data);
        }catch (\Exception $e){
            return $e->getMessage();die;
        }
        return 1;

    }

    /**
     * 供应商生产线查询
     */
    public function supplier_line_suspension_search($where){
        return self::where($where)->select()->toArray();
    }


    /**
     * 顾上生产线删除
     */
    public function supplier_line_delete($data){
        try{
            self::destroy($data);
        }catch (\Exception $e){
            return $e->getMessage();return;
        }
        return 1;
    }

    /**
     * 供应商生产线批量修改
     */
    public function supplier_line_update_batch($data){
        try{
            self::saveAll($data);
        }catch (\Exception $e){
            return $e->getMessage();return;
        }
        return 1;


    }

}