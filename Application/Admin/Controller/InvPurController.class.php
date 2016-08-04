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

        $this->display();
    }

    /**
     * 保存数据
     */
    public function save()
    {
        dump(I(''));
    }

}