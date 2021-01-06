<?php
namespace app\admin\model;
use think\Model;

class MarketType extends Model{
    public static function MarketSelect() {
        return self::order('order')->all()->toArray();
    }
}