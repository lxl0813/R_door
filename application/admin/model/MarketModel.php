<?php
namespace app\admin\model;
use think\Model;

class MarketModel extends Model{

    //根据客户id查询对应的管理详情
    public function customerMarket($where){
        return self::where($where)->order('time desc')->order('market_type_id')->limit(11)->select()->toArray();
    }

    /**
     * 营销管理入库
     */
    public function market_add($data){
        try {
            self::insertAll($data);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return true;
    }


    /**
     * 营销管理修改
     */
    public function market_update($data){
        try {
            self::saveAll($data);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return true;
    }

}