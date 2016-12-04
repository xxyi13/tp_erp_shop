<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-14
 * Time: 上午12:45
 */

namespace Admin\Controller;


class InvAccountController extends InvController
{

    //  单据编号前缀
    protected $bill_no_prefix;

    protected $bill_type = '';


    protected function getAddPage()
    {
        $this->getInvInfo();

        $trlist = [1,2,3,4,5,6,7,8,9,10];

        $this->assign(compact('bill_type', 'trlist'));
    }
    /**
     * 收款单
     */
    public function addReceipt()
    {
        $this->getAddPage();

        $this->display('receipt');
    }

    /**
     * 付款单
     */
    public function addPayment()
    {
        $this->getAddPage();

        $this->display('payment');
    }


    /**
     * 其他收入单
     */
    public function addQtsr()
    {
        $this->getAddPage();

        $this->display('qtsr');
    }

    /**
     * 其他支出单
     */
    public function addQtzc()
    {
        $this->getAddPage();

        $this->display('qtzc');
    }
}