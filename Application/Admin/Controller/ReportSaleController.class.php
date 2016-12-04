<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-17
 * Time: 下午6:07
 */

namespace Admin\Controller;


class ReportSaleController extends ReportController
{
    /**
     * 销货列表
     */
    public function saleIndex()
    {
        list( $_list, $number, $total, $param, $paramstr ) = D('Invoice')->getList('21');

        $this->assign(compact('_list', 'number', 'total', 'param', 'paramstr'));

        $this->display('sale_index');
    }

    /**
     * 销售明细表
     */
    public function saleDetail()
    {
        list( $_list, $number, $total, $param, $paramstr ) = D('InvoiceInfo')->saleDetail();

        $this->assign(compact('_list', 'number', 'total', 'param', 'paramstr'));

        $this->display('sale_detail');
    }

    /**
     * 销售汇总表(按商品)
     */
    public function saleSummaryGoods()
    {
        list( $_list, $number, $total, $param, $paramstr ) = D('Goods')->saleSummaryGoods();

        $this->assign(compact('_list', 'number', 'total', 'param', 'paramstr'));

        $this->display('sale_summary_goods');
    }

    /**
     * 销售汇总表(按客户)
     */
    public function saleSummaryCustomer()
    {
        list( $_list, $number, $total, $param, $paramstr ) = D('Business')->saleSummaryCustomer();

        $this->assign(compact('_list', 'number', 'total', 'param', 'paramstr'));

        $this->display('sale_summary_customer');
    }

    /**
     * 来往单位欠款表
     */
    public function contactDebt()
    {
        
    }
}