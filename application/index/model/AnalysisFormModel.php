<?php


namespace app\index\model;

use think\Model;

class AnalysisFormModel extends Model
{
    static function analysisFormWithValue($param)
    {
        $result    =    self::alias('af')
                        ->join('analysis_form_field_value affv','a.id=affv.form_field_id')
                        ->where(['af.platform_name'=>$param['platform_name'],'af.analysis_name_id'=>$param['analysis_name_id']])
                        ->select();
        return $result;
    }
}