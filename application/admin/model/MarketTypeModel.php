<?php
namespace app\admin\model;
use think\Model;

class MarketTypeModel extends Model{
    public static function MarketSelect() {
        return self::order('order')->all()->toArray();
    }
}