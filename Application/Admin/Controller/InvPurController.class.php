<?php
/**
 * 采购
 * User: fy
 * Date: 16-7-29
 * Time: 下午1:59
 */

namespace Admin\Controller;


class InvPurController extends InvController
{
    //  单据编号前缀
    const BILL_NO_PREFIX = 1;

    public function index()
    {

    }

    /**
     * 新建购货单
     */
    public function add()
    {
        $trans_type = I('trans_type', '0');

        if( empty($trans_type) ) {
            $this->error("请求地址,缺少必要参数");
        }

        //  获取采购单据号
        $bill_no = $this->getBillNo( self::BILL_NO_PREFIX );

        $this->assign(compact('trans_type', 'bill_no'));

        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->display();
    }

    /**
     * 保存数据
     */
    public function save()
    {
        $inputs = I('');
        
        $inputs['inv']['bill_type'] = 'PUR';

        $inputs['inv']['__hash__'] = $inputs['__hash__'];
        
        if( !empty( (float)$inputs['inv']['arrears']) ) {
            $this->error( "暂不允许欠款" );
        }

        if( empty($inputs['data']) ) {
            $this->error( "请添加商品信息" );
        }
        
        if( $this->model->addInvoiceData( $inputs ) ) {
            $this->success($this->model->getErrorMsg(), Cookie('__forward__'), self::AJAX_IS_OPEN);
        }

        $this->error($this->model->getErrorMsg(), '', self::AJAX_IS_OPEN);

    }

}