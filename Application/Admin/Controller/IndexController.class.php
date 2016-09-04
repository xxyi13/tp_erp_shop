<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends AdminController
{
    public function index()
    {
        $this->display();
    }

    public function main()
    {
        //  资金余额

        //  客户欠款    供应商欠款

        //  本月销售收入  毛利

        //  本月采购金额

        $this->display();
    }

}