<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-6
 * Time: 下午3:37
 */

namespace Admin\Controller;


class InvSaleController extends InvController
{
    //  单据编号前缀
    protected $bill_no_prefix = 2;

    protected $bill_type = 'SALE';

    public function index()
    {
        
    }
}