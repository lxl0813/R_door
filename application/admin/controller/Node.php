<?php


namespace app\admin\controller;


use app\admin\service\DataToTree;

class Node extends Common
{
    public function index()
    {
        $node   =   (new DataToTree())->getToTree((new \app\admin\model\Node())->all()->toArray());
        return view('',['node'=>$node]);
    }
}