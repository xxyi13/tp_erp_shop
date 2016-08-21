<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-21
 * Time: 上午6:39
 */

namespace Admin\Controller;


class ReportAccountController extends ReportController
{

    /**
     * 资金明细表
     */
    public function accountDetail()
    {
        list( $_list, $number, $total, $param, $paramstr ) = D('AccountInfo')->accountDetail('11');
        
        $this->assign(compact('_list', 'number', 'total', 'param', 'paramstr'));

        $this->display('acc_detail');
    }
}