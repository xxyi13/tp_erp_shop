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
    protected $bill_no_prefix = 11;

    protected $bill_type = 'PUR';
    
}