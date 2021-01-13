<?php


namespace app\admin\service;


class DataToTreeService
{
    public function getToTree($menu,$pid=0){
        $menuTree=[];
        foreach($menu as $key=>$val){
            if($val["pid"]==$pid){
                if($son=$this->getToTree($menu,$val['id'])){
                    $val["son"]=$son;
                }
                $menuTree[]=$val;
            }
        }
        return $menuTree;
    }
}