<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-17
 * Time: 下午5:53
 */

namespace Admin\Controller;


class ReportPurController extends ReportController
{
    /**
     * 采购明细表
     */
    public function purDetail()
    {
        list( $_list, $number, $total, $param, $paramstr ) = D('InvoiceInfo')->purDetail();

        $this->assign(compact('_list', 'number', 'total', 'param', 'paramstr'));

        $this->display('pur_detail');
    }


    /**
     * 采购汇总表(按商品)
     */
    public function purSummaryGoods()
    {

    }

    /**
     * 采购汇总表(按供应商)
     */
    public function purSummarySupply()
    {

    }
}