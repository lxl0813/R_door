<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2019/12/23
 * Time: 16:34
 */
namespace app\index\controller;


class LangController{
    public function changeLang(){
        $lang = input('lang');
        switch ($lang) {
            case 'en':
                cookie('think_var', 'en_us');
                break;
            case 'zn':
                cookie('think_var', 'zh_cn');
                break;
            default:
                cookie('think_var', 'zh_cn');
                break;
        }
    }
};