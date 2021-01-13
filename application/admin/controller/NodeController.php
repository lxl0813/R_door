<?php


namespace app\admin\controller;


use app\admin\service\DataToTreeService;

class NodeController extends CommonController
{
    public function index()
    {
        $node   =   (new DataToTreeService())->getToTree((new \app\admin\model\NodeModel())->all()->toArray());
        return view('',['node'=>$node]);
    }
}