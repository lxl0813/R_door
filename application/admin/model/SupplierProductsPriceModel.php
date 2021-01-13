<?php
namespace app\admin\model;
use think\Db;
use think\Model;

class SupplierProductsPriceModel extends Model{
    /**
     * @return array|\think\Paginator
     * @throws \think\exception\DbException
     */
    //供应商报价查询
    public function suppler_offer_price($where=null){
        $suppler = Db::name('supplier_products_price')
            ->alias('spp')
            ->join('suppliers s','spp.supplier_id=s.id')
            ->where($where)
            ->order('spp.create_time')
            ->paginate(10,false,['var_page'=>'pageC']);
        if(count($suppler)>0){
            return $suppler;
        }else{
            return [];
        }
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    //供应商过期报价修改状态
    public function suppler_offer_price_overdue(){
        $time=date('Y-m-d',time());
        self::where('end_time' ,"<" ,$time)->update(['price_status'=>2]);
    }

    /**
     * @param $where
     * @return array|\think\Paginator
     * @throws \think\exception\DbException
     */
    //供应商报价状态查询
    public function offer_price_search_status($where=null){
        $suppler = Db::name('supplier_products_price')
            ->alias('spp')
            ->join('suppliers s','spp.supplier_id=s.id')
            ->where($where)
            ->order('spp.create_time')
            ->paginate(10,false,['var_page'=>'pageC']);
        if(count($suppler)>0){
            return $suppler;
        }else{
            return [];
        }
    }

    /**
     * @param $data
     */
    //供应商报价添加
    public function supplier_offer_price_add_do($data){
        //查询供应商id
        $data['supplier_id']=Db::name('suppliers')->where('suppliers_name',$data['supplier_name'])->value('id');
        unset($data['supplier_name']);
        //供应商报价时间
        $data['offer_time']=$data['date']." ".$data['h'].":".$data['m'].":"."00";
        unset($data['date']);unset($data['h']);unset($data['m']);
        try {
            self::insert($data);
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return 1;
        //var_dump($data);exit;
    }

    /**
     * 供应商所有产品报价查询
     */
    public function supplier_product_price($where){
        $res = Db::name('supplier_products_price')
            ->where($where)
            ->field('supplier_products_name,price,unit,start_time,end_time,price_status')
            ->select();
        return $res;
    }
}