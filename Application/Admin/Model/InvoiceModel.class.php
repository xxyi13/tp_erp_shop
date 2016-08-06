<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-4
 * Time: 下午4:56
 */

namespace Admin\Model;


class InvoiceModel extends CommonModel
{

    protected $_validate = array(
        array('bill_no', 'require', '单据编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bus_id', 'require', '供应商编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('total_amount', 'require', '总金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('amount', 'require', '折扣后的金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('rp_amount', 'require', '本次付款金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('acc_id', 'require', '结算账户不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('total_qty', 'require', '总的数量不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('pur_sale_id', 'require', '购货/销售人员不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_type', 'require', '单据类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_date', 'require', '单据日期不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('uid', 'getCurrentAdminUserId', self::MODEL_BOTH , 'function'),
        array('realname', 'getCurrentAdminUserId', self::MODEL_BOTH , 'function'),
        array('bill_date', 'format_datetime', self::MODEL_BOTH , 'function'),
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),

        array('total_amount', 'getInvoiceAmount', self::MODEL_BOTH, 'callback'),
        array('amount', 'getInvoiceAmount', self::MODEL_BOTH, 'callback'),
        array('rp_amount', 'getInvoiceAmount', self::MODEL_BOTH, 'callback'),
    );

    public function addInvoiceData($inputs = [])
    {
        /*  单据信息 */
        $inputs['inv'] = $this->create($inputs['inv']);

        if( !$inputs['inv'] ) {
            return $this->output(self::STATUS_ERROR, $this->getError());
        }

        $this->startTrans();	//	开启事务


        /*  商品信息 */

        $invoice_info_model = D('InvoiceInfo');

        $inv_id = $this->add($inputs['inv']);

        if( $inv_id === false ) {
            $this->rollback();

            return $this->output(self::STATUS_ERROR, '添加单据信息失败');
        }

        foreach ($inputs['data'] as $key=>&$value) {
            $value['inv_id'] = $inv_id;
            $value['bus_id'] = $inputs['inv']['bus_id'];
            $value['bill_no'] = $inputs['inv']['bill_no'];
            $value['bill_type'] = $inputs['inv']['bill_type'];
            $value['bill_date'] = $inputs['inv']['bill_date'];
            $value['trans_type'] = $inputs['inv']['trans_type'];
            $value['pur_sale_id'] = $inputs['inv']['pur_sale_id'];

            $value = $invoice_info_model->create($value);

            $k = $key + 1;

            if( !$value ) {
                return $this->output(self::STATUS_ERROR, '第' . $k . '条商品信息：'. $this->getError());
            }

            $value['inv_info_id'] = $invoice_info_model->add($value);

            if( $value['inv_info_id'] === false ) {
                $this->rollback();

                return $this->output(self::STATUS_ERROR, '添加第' . $k . '条商品信息失败');
            }
        }

        /*  账户记录 */
        $account_info_model = D('AccountInfo');

        $account_info_data = [
            'inv_id' => $inv_id,
            'bus_id' => $inputs['inv']['bus_id'],
            'bill_no' => $inputs['inv']['bill_no'],
            'bill_type' => $inputs['inv']['bill_type'],
            'bill_date' => $inputs['inv']['bill_date'],
            'trans_type' => $inputs['inv']['trans_type'],
            'acc_id' => $inputs['inv']['acc_id'],
            'payment' => $inputs['inv']['rp_amount'],
            'way_id' => 0,
            'memo' => '',
        ];
        
        $account_info_data = $account_info_model->create($account_info_data);

        if( !$account_info_data ) {
            $this->rollback();

            return $this->output(self::STATUS_ERROR, '添加账户记录信息失败:' . $account_info_model->getError());
        }

        $account_info_id = $account_info_model->add($account_info_data);

        if( $account_info_id === false ) {
            $this->rollback();

            return $this->output(self::STATUS_ERROR, '添加账户记录信息失败');
        }

        $this->commit();

        return $this->output(self::STATUS_SUCCESS, "操作成功");
    }

    /**
     * 根据 $trans_type 判断该单据的 总金额 折扣后金额 本次付款金额 的正负
     * 正 ： 采购 退销
     * 负 ： 退货 销售
     * @param $amount
     * @param $trans_type
     */
    public function getInvoiceAmount($amount)
    {
        $trans_type = I("inv")['trans_type'];
        
        $array = ['12'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($amount);
        }

        return abs($amount);
    }


}