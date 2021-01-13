<?php
/**
 * Created by PhpStorm.
 * User: 李小龙
 * Date: 2020/4/26
 * Time: 13:56
 */
namespace app\admin\controller;
use app\admin\model\AdminRolesModel;
use app\admin\model\CompanyModel;
use app\admin\model\FormTypeModel;
use app\admin\model\NodeModel;
use app\admin\model\RoleNodeModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Controller;
use think\Db;
use think\facade\Cookie;
use think\facade\View;


class CommonController extends Controller{
    public function __construct(){
        parent::__construct();
        if(!cookie("ranglei_admin")){
            $this->redirect("Login/index");
        }
        //管理员权限识别
        if(!$this->checkNode()){
            if(request()->isAjax()){
                return ["status"=>-1,"msg"=>"没有权限操作"];
            }else{
                $this->success("亲！您没有操作权限哦！",'Index/index');
            }
        }
        $menu=$this->getMenu();
        View::share("menu",$menu);
    }

    //查询当前登录管理员id
    public function admin_id(){
        return Cookie::get('ranglei_admin')['admin_id'];
    }

    //查询当前登录管理员信息
    public function cookie_admin(){
        $admin = Cookie::get('ranglei_admin')['admin_id'];
        $admin=Db::name('admin')->where(['id'=>$admin])->field('id , name , phone')->find();
        return $admin;
    }

    //查询询单管理员名称
    public function admin_infor(){
        $role_id = Db::name('role')->where(['role'=>'客户询单管理'])->value('id');
        $admin_id = Db::name('admin_role')->where(['role_id'=>$role_id])->field('admin_id')->select();
	if(!empty($admin_id)){
        	foreach ($admin_id as $k=>$v){
            	$admin[]=Db::name('admin')->where(['id'=>$v['admin_id']])->field('id,name')->find();
        	}
	}else{
	$admin=[];
	}
        return $admin;
    }


    //公司查询
    public function company(){
        return CompanyModel::CompanyAll();
    }

    //报表类型查询
    public function type(){
        return FormTypeModel::FormTypeSelect();
    }

    //银行查询
    public function bank(){
        return Db::name('bank')->select();
    }

    //询单excel生成
    public function excel($data){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('客户询单记录表');
        //合并A1和B1单元格
        $sheet->mergeCells('A1:B1');
        //设置A1内容居中
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        //设置A1内容的字体大小
        $sheet->getStyle('A1')->getFont()->setBold(true)->setName('Arial')->setSize(10);;
        //设置A列宽度
        $sheet->getColumnDimension('A')->setWidth(20);
        //设置B列宽度自适应
        $sheet->getColumnDimension('B')->setAutoSize(true);
        //设置B列左对齐
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ];
        $sheet->getStyle('B')->applyFromArray($styleArray);
        //填充单元格内容
        $sheet->setCellValue('A1', '客户询单记录');
        $sheet->setCellValue('A2', '询单编号');
        $sheet->setCellValue('B2', $data['sn']);
        $sheet->setCellValue('A3', '时间');
        $sheet->setCellValue('B3', $data['time']);
        $sheet->setCellValue('A4', '记录人');
        $sheet->setCellValue('B4', $data['admin_name']);
        $sheet->setCellValue('A5', '联系电话');
        $sheet->setCellValue('B5', $data['admin_phone']);
        $sheet->setCellValue('A6', '客户单位');
        $sheet->setCellValue('B6', $data['company']);
        $sheet->setCellValue('A7', '单位地址');
        $sheet->setCellValue('B7', $data['adress']);
        $sheet->setCellValue('A8', '联系人');
        $sheet->setCellValue('B8', $data['contacts']);
        $sheet->setCellValue('A9', '联系电话');
        $sheet->setCellValue('B9', $data['phone']);
        $sheet->setCellValue('A10', '微信');
        $sheet->setCellValue('B10', $data['wx']);
        $sheet->setCellValue('A11', '电子邮箱');
        $sheet->setCellValue('B11', $data['email']);
        $sheet->setCellValue('A12', '产品名称');
        $sheet->setCellValue('B12', $data['product_name']);
        $sheet->setCellValue('A13', '颜色');
        $sheet->setCellValue('B13', $data['product_color']);
        $sheet->setCellValue('A14', '原料配比');
        $sheet->setCellValue('B14', $data['raw_material_ratio']);
        $sheet->setCellValue('A15', '克重/旦数');
        $sheet->setCellValue('B15', $data['gram_weight']);
        $sheet->setCellValue('A16', '厚度/长度');
        $sheet->setCellValue('B16', $data['thickness']);
        $sheet->setCellValue('A17', '幅宽/包重');
        $sheet->setCellValue('B17', $data['width_of_cloth']);
        $sheet->setCellValue('A18', '生产工艺');
        $sheet->setCellValue('B18', $data['production_process']);
        $sheet->setCellValue('A19', '后处理');
        $sheet->setCellValue('B19', $data['post_treatment']);
        $sheet->setCellValue('A20', '工艺标准');
        $sheet->setCellValue('B20', $data['process_standard']);
        $sheet->setCellValue('A21', '纵向强力');
        $sheet->setCellValue('B21', $data['longitudinal_strength']);
        $sheet->setCellValue('A22', '横向强力');
        $sheet->setCellValue('B22', $data['lateral_strength']);
        $sheet->setCellValue('A23', '纵向拉伸');
        $sheet->setCellValue('B23', $data['longitudinal_stretch']);
        $sheet->setCellValue('A24', '横向拉伸');
        $sheet->setCellValue('B24', $data['transverse_stretch']);
        $sheet->setCellValue('A25', '纵向撕裂强力');
        $sheet->setCellValue('B25', $data['longitudinal_tear_strength']);
        $sheet->setCellValue('A26', '横向撕裂强力');
        $sheet->setCellValue('B26', $data['transverse_tear_strength']);
        $sheet->setCellValue('A27', '透气率');
        $sheet->setCellValue('B27', $data['permeability']);
        $sheet->setCellValue('A28', 'PH值');
        $sheet->setCellValue('B28', $data['ph']);
        $sheet->setCellValue('A29', '体密度');
        $sheet->setCellValue('B29', $data['bulk_density']);
        $sheet->setCellValue('A30', '报价');
        $sheet->setCellValue('B30', $data['price']);
        $sheet->setCellValue('A31', '备注');
        $sheet->setCellValue('B31', $data['remarks']);
        //保存
        $writer = new Xlsx($spreadsheet);
        $name=uniqid().".xlsx";
        $dir="../public/static/inquiry_excel/".date('Y/m/d').'/';
        if (!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $url=$dir.$name;
        $writer->save($url);
        $urls =substr($dir.$name,9);
        return $urls;
    }

    /**
     * @return bool
     * User：李小龙
     *Date：2020/4/26
     * Time：14:07
     */

    //查询改管理员是否拥有所要前往页面的权限；
    public function checkNode(){
        //判断是否是配置文件里的超级管理员，false继续查询权限，true不需要
        if(in_array(\think\facade\Cookie::get("ranglei_admin")["admin_name"],config("app.super_admin"))){
            return true;
        }
        //获取要去往的控制器和方法(转换成开头大写的格式)，判断该控制器是否是不需要权限的
        $access=ucfirst(strtolower(request()->controller()))."/".ucfirst(strtolower(request()->action()));
        //var_dump($access);exit;
        //判断所要前往的路由是否在配置文件中；
        if(in_array($access,config("app.no_check_action"))){
            return true;
        }
        $admin_id=Cookie::get("ranglei_admin")["admin_id"];//获取当前管理员的id
        //获取当前登录管理员的权限
        $myNode=$this->getAdminNodeId($admin_id);
        //var_dump($access);exit;
        if(in_array($access,$myNode)){
            return true;
        }else{
            return false;
        }
    }

    //获取管理员权限
    public function getAdminNodeId($admin_id){
        $adminRoleModel=new AdminRolesModel();
        $role_id=$adminRoleModel->where("admin_id",$admin_id)->column("role_id");
        $roleNodeModel=new RoleNodeModel();
        $nodeModel=new NodeModel();
        $role=$roleNodeModel->where('role_id','in',$role_id)->select();
        $myNode=[];
        foreach($role as $key=>$val){
            $myNode[]=$nodeModel->where('id',$val->node_id)->find()->toArray();
        }
        $myAccess=[];
        foreach($myNode as $key=>$val){
            array_push($myAccess,ucfirst(strtolower($val["controller"]))."/".ucfirst(strtolower($val["action"])));
        }
        $myAccess=array_unique($myAccess);
        return $myAccess;
    }

    //取左侧菜单
    public function getMenu(){
        $admin_name=Cookie::get("ranglei_admin")["admin_name"];
        if(in_array($admin_name,config("app.super_admin"))){
            //取所有的权限
            $menu=(new NodeModel())->where("pid",0)->all()->toArray();
            foreach($menu as $k=>$v){
                $menu[$k]['url']=$v['controller']."/".$v['action'];
            }
        }else{
            $admin_id=Cookie::get("ranglei_admin")["admin_id"];
            //查询管理员的所拥有角色
            $role_id    =   (new AdminRolesModel())->where('admin_id',$admin_id)->column('role_id');
            $node_id    =   (new RoleNodeModel())->where('role_id','in',$role_id)->column('node_id');
            $menu       =   (new NodeModel())->where('id','in',$node_id)->where('pid','0')->field('controller,action,node')->select()->toArray();
            foreach($menu as $k=>$v){
                $menu[$k]['url']=$v['controller']."/".$v['action'];
            }
        }
        return $menu;
    }


}