<?php
namespace app\admin\model;
use app\admin\controller\CommonController;
use think\Db;
use think\Model;

class CustomersMarketModel extends Model{
    //查询当前管理员的客户，并查取对应的管理记录
    public function customerToMarket($where){
        $customer = self::where($where)->paginate(10,false,['var_page'=>'pageA']);
        if(count($customer)>0){
            foreach ($customer as $k=>$v){
                $market = Db::name('market')
                    ->where(['customer_id'=>$v['id'],'y_m_d'=>date("Y-m-d",time())])                    
		    ->order('market_type_id') 
                    ->limit(11)
                    ->select();
                if($market){
                    $customer[$k]['market'] =$market;
                    $customer[$k]['market_time'] = $market[0]['y_m_d'];
                }else{
                    $customer[$k]['market'] = [];
                    $customer[$k]['market_time'] = "";
                }
            }
        }else{
            $customer=[];
        }
	    return $customer;
    }


    /**
     * 客户信息查询
     * @param null $field 查询字段
     * @param null $order 排序规则
     * @param $type 查询数量
     * @param null $where 查询条件
     */

    public function customer_information($where=null , $field=null , $order=null , $type){
        if($type==1){
            return self::where($where)->field($field)->order($order)->find()->toArray();
        }
        if($type==2){
            return self::where($where)->field($field)->order($order)->select()->toArray();
        }
    }


    /**
     * 客户查询
     */
    public function customer_select($where,$paginate){
        $customer = self::where($where)->paginate($paginate);
        if(count($customer)>0){
            return $customer;
        }else{
            return [];
        }
    }


    /**
     * 查询该客户所有管理记录
     */
    public function customer_market_all($where){
        $customer = self::where(['id'=>$where])->find()->toArray();
        $market = Db::name('market')->where(['customer_id'=>$where])->order('time desc')->order('market_type_id')->select();
        if(!empty($market)){
            $market = array_chunk($market,11);
            foreach ($market as $k=>$v){
                $res[$k]['customer']=$customer;
                $res[$k]['market']=$v;
                $res[$k]['customer']['market_time']=$v[0]['y_m_d'];
            }
            return $res;
        }else{
            return [];
        }
    }

    /**
     * 客户添加
     * @param $data
     * @return bool|string
     */
    public function customer_add_do($data){
        //var_dump($data);exit;
        try {
            $res = self::insert($data);
            if($res){
                return true;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }


    }

}