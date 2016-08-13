<?php
/**
 * 后台基础控制器
 * User: fy
 * Date: 16-7-17
 * Time: 下午9:06
 */

namespace Admin\Controller;

use Think\Controller;
use Think\Model;

abstract class AdminController extends Controller
{

    const AJAX_IS_OPEN = true;

    protected $model;

    protected $model_name;

    public function _initialize()
    {
        /************************************
         * 验证是否登陆
         ************************************/
        if( !D('AdminUser')->checkLogin() ) {
            redirect(U('Admin/Public/login'));
        }

        /************************************
         * 校验条件
         ************************************/
        if( method_exists($this, 'middleware') ) {
            $this->middleware();
        }

        /************************************
         * 默认的基础模型
         ************************************/
        $this->model = empty($this->model_name) ? M() : D($this->model_name);
    }

    /**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  add.html.bak?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: add.html.bak?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: add.html.bak?r=5
     *
     * @param sting|Model  $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array        $base    基本的查询条件
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     *
     * @return array|false
     * 返回数据集
     */
    protected function lists ($model, $where=array(), $order='', $base = ['deleted_at'=>['eq', 0]], $field=true){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   M($model);
        }

        $OPT        =   new \ReflectionProperty($model, 'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        $options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
            if($val===''||$val===null){
                return false;
            }else{
                return true;
            }
        });
        if( empty($options['where'])){
            unset($options['where']);
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 30;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        /*
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        */
        $page->parameter=I('get.');
        $p =$page->show();
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);

        return $model->field($field)->select();
    }


    /**
     * 获取商品列表
     * @param string $type
     * @param bool $return 是否需要把值返回
     */
    protected function getGoodsList($type='', $return = false )
    {
        $modal_title = '请选择商品';

        $param = [
            'goods_id' => I('goods_id', '0'),
            'storage_house' => I('storage_house', '0'),
            'category' => I('category', '0'),
            'type' => I('type', $type)
        ];

        if( !empty($param['goods_id']) ) {
            $map['g.id'] = $param['goods_id'];
        }

        if( !empty($param['storage_house']) ) {
            $map['g.storage_house'] = $param['storage_house'];
        }

        if( !empty($param['category']) ) {
            $map['g.category'] = $param['category'];
        }

        if( !empty($param['type']) ) {
            $map['g.type'] = $param['type'];
        }

        $map['g.deleted_at'] = '0000-00-00 00:00:00';

        $pre = C('DB_PREFIX');

        $model = D('Goods')->alias('g')
            ->join(' left join '.$pre.'invoice_info as inv on g.id = inv.goods_id ');

        $field = 'g.id, g.name, g.type, g.category, g.spec, g.storage_house, g.min_inventory, g.max_inventory, g.unit, g.purchase_price, g.sale_price, g.wholesale_price, g.vip_price, g.discount_rate_1, g.discount_rate_2, g.memo, g.is_alarm, g.st_quantity, g.st_unit_cost, g.st_amount, g.created_at, ';

        $field .= 'sum(inv.qty) + g.st_quantity as total_qty';

        $_list = $model->where($map)->order('g.id asc')->group(' g.id ')->field($field)->select();

        $goods_category = C('goods_category');

        $unit_list = C('unit_list');

        $goods_storage_house = C('goods_storage_house');

        $paramstr = http_build_query($param);

        if( $return ) {
            return [
                '_list' => $_list,
                'goods_category' => $goods_category,
                'unit_list' => $unit_list,
                'goods_storage_house' => $goods_storage_house,
                'param' => $param,
                'paramstr' => $paramstr,
            ];
        }

        $this->assign(compact('modal_title', '_list', 'goods_category', 'unit_list', 'goods_storage_house', 'param', 'paramstr'));

    }


    /**
     * excle导出
     * @param  [type] $expTitle     [标题]
     * @param  [type] $expCellName  [列名称]
     * @param  [type] $expTableData [数据]
     */
    public function exportExcel($expTitle, $expCellName, $expTableData, $fileName = ''){
        $xlsTitle = iconv("UTF-8", "GB2312//IGNORE", $expTitle);//文件名称
        $fileName = empty($fileName) ? date('YmdHis') : $fileName;    //or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();

        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }

        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}