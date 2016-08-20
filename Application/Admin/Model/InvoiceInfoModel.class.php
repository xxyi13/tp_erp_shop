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
//        array('inv_id', 'require', '单据自增号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_no', 'require', '单据编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bus_id', 'require', '供应商编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_type', 'require', '单据类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_date', 'require', '单据日期不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
//        array('goods_id', 'require', '商品编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('amount', 'require', '购货金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('price', 'require', '商品购货单价不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
//        array('qty', 'require', '购货数量不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),
        array('amount', 'getInvoiceInfoAmount', self::MODEL_BOTH, 'callback'),
        array('qty', 'getInvoiceInfoQty', self::MODEL_BOTH, 'callback'),
    );


    /**
     * 根据 $trans_type 判断单据商品信息中 金额 的 正负
     * 正 ： 退货 销售
     * 负 ： 采购 退销
     * @param $qty
     * @param $trans_type
     */
    public function getInvoiceInfoAmount($amount)
    {
        $trans_type = I("inv")['trans_type'];

        $array = ['11', '22'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($amount);
        }

        return abs($amount);
    }

    /**
     * 根据 $trans_type 判断单据商品信息中商品数量 的 正负
     * 正 ： 采购 退销
     * 负 ： 退货 销售
     * @param $qty
     * @param $trans_type
     */
    public function getInvoiceInfoQty($qty)
    {
        $trans_type = I("inv")['trans_type'];

        $array = ['12', '21', '32'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($qty);
        }

        return abs($qty);
    }


    public function getGoodsTotalQtyById($goods_id)
    {
        return $this->where(['goods_id'=>$goods_id, 'deleted_at'=>['eq', 0]])->sum('qty');
    }

    /**
     * 获取明细表
     * @return array
     */
    protected function getReportDetail($bill_type = '')
    {
        list($map, $param, $paramstr) = $this->setMapDeleted('inv_info')->setMapDeleted('goods')->setMapDeleted('bus')->setMapBillType('inv_info', $bill_type)->setMapBillDate('inv_info')->setMapGoodsCategory('goods')->setMapTransType('inv_info')->setMapBusId('inv_info')->setMapBillNo('inv_info')->getMapParam();

        $fields = 'inv_info.id, bill_date, bill_no, trans_type, pur_sale_id, bus_id, bus.name as bus_name, goods_id, goods.name as goods_name, goods.spec, goods.unit, goods.storage_house, inv_info.qty, inv_info.price, inv_info.amount';

        $model = $this->alias('inv_info')->join(' left join goods on inv_info.goods_id = goods.id ')->join(' left join business as bus on bus.id = inv_info.bus_id ');

        $list = $model->where($map)->field($fields)->order('inv_info.id desc')->select();

        $total = ['qty'=>0, 'amount'=>0];

        foreach ($list as $key=>&$value) {
            $value['trans_type_name'] = getValue(C('trans_type'), $value['trans_type'], '未知');
            $value['unit_name'] = getValue(C('unit_list'), $value['unit'], '未知');
            $value['storage_house_name'] = getValue(C('goods_storage_house'), $value['storage_house'], '默认仓库');
            $value['pur_sale_realname'] = D('PurSaleUser')->getPurSaleUserById($value['pur_sale_id'], 'realname');
            $value['amount'] = abs($value['amount']);
            $value['qty'] = abs($value['qty']);

            $total['qty'] += $value['qty'];
            $total['amount'] += $value['amount'];
        }

        return [$list, count($list) + 1, $total, $param, $paramstr];
    }

    /**
     * 获取采购明细表
     */
    public function purDetail()
    {
        return $this->getReportDetail('11');
    }

    /**
     * 销售明细表
     */
    public function saleDetail()
    {
        return $this->getReportDetail('21');
    }

    /**
     * 记录单据 购货 销货 涉及 账单 商品
     * @param array $inputs
     * @return array
     */
    public function addInvoiceData($inputs = [])
    {
        /*  单据信息 */
        $inputs['inv'] = $this->create($inputs['inv']);

        if( !$inputs['inv'] ) {
            return $this->output(self::STATUS_ERROR, $this->getError());
        }

        $inputs['inv']['bus_id'] = getValue($inputs['inv'], 'bus_id', '0');
        $inputs['inv']['total_amount'] = getValue($inputs['inv'], 'total_amount', '0');
        $inputs['inv']['amount'] = getValue($inputs['inv'], 'amount', '0');
        $inputs['inv']['rp_amount'] = getValue($inputs['inv'], 'rp_amount', '0');
        $inputs['inv']['acc_id'] = getValue($inputs['inv'], 'acc_id', '0');
        $inputs['inv']['total_qty'] = getValue($inputs['inv'], 'total_qty', '0');
        $inputs['inv']['pur_sale_id'] = getValue($inputs['inv'], 'pur_sale_id', '0');
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
                return $this->output(self::STATUS_ERROR, '第' . $k . '条商品信息：'. $invoice_info_model->getError());
            }
            $value['inv_info_id'] = $invoice_info_model->add($value);
            if( $value['inv_info_id'] === false ) {
                $this->rollback();
                return $this->output(self::STATUS_ERROR, '添加第' . $k . '条商品信息失败');
            }
        }
        /**
         * 判断是否需要记录 账单
         */
        $record_account_trans_type = ['11', '12', '21', '22'];      //  根据trans_type判断是否需要记录账单
        if( !in_array($inputs['inv']['trans_type'], $record_account_trans_type) ) {
            //  不记录账单直接返回
            $this->commit();
            return $this->output(self::STATUS_SUCCESS, "操作成功");
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
}