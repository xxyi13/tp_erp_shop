<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-5
 * Time: 下午8:12
 */

namespace Admin\Model;


class AccountInfoModel extends CommonModel
{

    protected $_validate = array(
        array('inv_id', 'require', '单据自增号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_no', 'require', '单据编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bus_id', 'require', '供应商编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_type', 'require', '单据类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_date', 'require', '单据日期不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('acc_id', 'require', '结算账户不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('payment', 'getAccountPayment', self::MODEL_BOTH, 'callback'),
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),
    );

    
    /**
     * 根据 $trans_type 判断账户详情信息的金额的 正负
     * @param $rp_amount
     * @param $trans_type
     */
    public function getAccountPayment($payment)
    {
        $trans_type = I("inv")['trans_type'];

        $array = ['11'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($payment);
        }

        return abs($payment);
    }
}
