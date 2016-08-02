<?php
/**
 * 采购
 * User: fy
 * Date: 16-7-29
 * Time: 下午1:59
 */

namespace Admin\Controller;


class InvPuController extends InvController
{
    public function index()
    {
        $tr_list = [1,2];

        $bill_no = $this->getBillNo('1');
        
        $goods_list = D('Goods')->getGoodsList();
        
        $this->assign(compact('tr_list', 'bill_no'));

        $this->display();
    }

}