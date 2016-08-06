<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-8-5
 * Time: 下午6:35
 */

namespace Admin\Model;


class InvoiceInfoModel extends CommonModel
{

    protected $_validate = array(
        array('inv_id', 'require', '单据自增号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_no', 'require', '单据编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bus_id', 'require', '供应商编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_type', 'require', '单据类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_date', 'require', '单据日期不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('goods_id', 'require', '商品编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('amount', 'require', '购货金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('price', 'require', '商品购货单价不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('qty', 'require', '购货数量不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),
        array('amount', 'getInvoiceInfoAmountQty', self::MODEL_BOTH, 'callback'),
        array('qty', 'getInvoiceInfoAmountQty', self::MODEL_BOTH, 'callback'),
    );


    /**
     * 根据 $trans_type 判断单据商品信息中商品数量 或者 金额 的 正负
     * 正 ： 采购 退销
     * 负 ： 退货 销售
     * @param $qty
     * @param $trans_type
     */
    public function getInvoiceInfoAmountQty($amount_qty)
    {
        $trans_type = I("inv")['trans_type'];
        
        $array = ['12'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($amount_qty);
        }

        return abs($amount_qty);
    }


}