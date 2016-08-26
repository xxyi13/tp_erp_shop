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
//        array('bus_id', 'require', '供应商编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('total_amount', 'require', '总金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('amount', 'require', '折扣后的金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('rp_amount', 'require', '本次付款金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('acc_id', 'require', '结算账户不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('total_qty', 'require', '总的数量不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('pur_sale_id', 'require', '购货/销售人员不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_type', 'require', '单据类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_date', 'require', '单据日期不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('uid', 'getCurrentAdminUserId', self::MODEL_BOTH , 'function'),
        array('realname', 'getAdminUserRealnameById', self::MODEL_BOTH , 'function'),
        array('bill_date', 'format_datetime', self::MODEL_BOTH , 'function'),
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),

        array('total_amount', 'getInvoiceAmount', self::MODEL_BOTH, 'callback'),
        array('amount', 'getInvoiceAmount', self::MODEL_BOTH, 'callback'),
        array('rp_amount', 'getInvoiceAmount', self::MODEL_BOTH, 'callback'),
        array('arrears', 'getInvoiceArrears', self::MODEL_BOTH, 'callback'),
    );

    //  单据自赠编号
    public $inv_id;

    /**
     * 保存单据信息
     * @param array $inputs
     * @param string $model
     */
    public function addData($inputs = [])
    {
        $trans_type_data = [
            'addInvoiceAllData' => [11, 12, 21, 22],
            'addInvoiceInfoData' => [31, 32, 33],
            'addAccountInfoData' => [41, 42, 43, 44],
        ];
        if ( in_array($inputs['inv']['trans_type'], $trans_type_data['addInvoiceInfoData']) ) {
            return $this->addInvoiceInfoData($inputs);
        } else if ( in_array($inputs['inv']['trans_type'], $trans_type_data['addAccountInfoData']) ) {
            return $this->addAccountInfoData($inputs);
        } else {
            return $this->addInvoiceAllData($inputs);
        }
    }

    protected function addInvoice($inputs)
    {
        $this->startTrans();	//	开启事务

        $this->inv_id = $this->add($inputs);

        if( !$this->inv_id ) {
            $this->rollback();

            return $this->output(self::STATUS_ERROR, '添加单据信息失败');
        }

        return true;
    }

    protected function addInvoiceInfo($inputs)
    {
        foreach ($inputs as $key=>&$value) {
            $value['inv_id'] = $this->inv_id;
        }

        $invoice_info_model = D('InvoiceInfo');

        if( !$invoice_info_model->addAll($inputs) ) {
            $this->rollback();

            return $this->output(self::STATUS_ERROR, '添加单据商品详情信息失败');
        }

        return true;
    }

    protected function addAccountInfo($inputs)
    {
        if( empty($inputs) || !isset($inputs[0]) || empty($inputs[0]) ) {
            $this->rollback();

            $this->output(self::STATUS_ERROR, '账户金额详情信息不能为空');
        }

        foreach ($inputs as $key=>&$value) {
            $value['inv_id'] = $this->inv_id;
        }

        $account_info_model = D('AccountInfo');

        if( !$account_info_model->addAll($inputs) ) {
            $this->rollback();

            return $this->output(self::STATUS_ERROR, '记录单据账户金额详情信息失败');
        }

        return true;
    }
    /**
     * 记录单据 购货 销货 涉及 账单 商品
     * @param array $inputs
     * @return array
     */
    public function addInvoiceAllData($inputs = [])
    {
        if( empty($inputs['data']) || !isset($inputs['data'][0]) ) {
            $this->output(self::STATUS_ERROR, '商品详情信息不能为空');
        }

        $re = $this->addInvoice($inputs['inv']);
        if( !$re ) {
            return $re;
        }

        /* 记录单据商品信息*/
        $re = $this->addInvoiceInfo($inputs['data']);
        if( !$re ) {
            return $re;
        }

        /* 记录单据账户金额详情信息*/
        if( !isset($inputs['account']) || empty($inputs['account']) || empty($inputs['account'][0]) ) {
            $inputs['account'][0] = [
                'inv_id' => $this->inv_id,
                'bus_id' => $inputs['inv']['bus_id'],
                'bill_no' => $inputs['inv']['bill_no'],
                'bill_type' => $inputs['inv']['bill_type'],
                'bill_date' => $inputs['inv']['bill_date'],
                'trans_type' => $inputs['inv']['trans_type'],
                'acc_id' => $inputs['inv']['acc_id'],
                'payment' => $inputs['inv']['rp_amount'],
                'way_id' => 0,
                'memo' => '',
                'settlement' => isset($inputs['account'][0]['settlement']) ? $inputs['account'][0]['settlement'] : 0
            ];
        }

        $re = $this->addAccountInfo($inputs['account']);

        if( !$re ) {
            return $re;
        }

        $this->commit();

        return $this->output(self::STATUS_SUCCESS, "单据添加成功");
    }

    /**
     * 记录仓库库存信息 不记录账户详情
     */
    public function addInvoiceInfoData($inputs = [])
    {
        if( empty($inputs['data']) || !isset($inputs['data'][0]) ) {
            $this->output(self::STATUS_ERROR, '商品详情信息不能为空');
        }

        $re = $this->addInvoice($inputs['inv']);
        if( !$re ) {
            return $re;
        }

        /* 记录单据商品信息*/
        $re = $this->addInvoiceInfo($inputs['data']);
        if( !$re ) {
            return $re;
        }

        $this->commit();

        return $this->output(self::STATUS_SUCCESS, "单据添加成功");
    }

    /**
     * 添加账单金额信息 不记录商品详情
     */
    public function addAccountInfoData($inputs = [])
    {
        if( empty($inputs['data']) || !isset($inputs['data'][0]) ) {
            $this->output(self::STATUS_ERROR, '商品详情信息不能为空');
        }

        $re = $this->addInvoice($inputs['inv']);
        if( !$re ) {
            return $re;
        }

        /* 记录单据账户金额详情信息*/
        $re = $this->addAccountInfo($inputs['account']);
        if( !$re ) {
            return $re;
        }

        $this->commit();

        return $this->output(self::STATUS_SUCCESS, "单据添加成功");
    }


    /**
     * 根据 $trans_type 判断该单据的 总金额 折扣后金额 本次付款金额 的正负
     * 正 ： 退货 销售
     * 负 ： 采购 退销
     * @param $amount
     * @param $trans_type
     */
    public function getInvoiceAmount($amount)
    {
        $trans_type = I("inv")['trans_type'];

        $array = ['11', '22'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($amount);
        }

        return abs($amount);
    }

    /**
     * 根据 $trans_type 判断该单据的 欠款 的正负
     * 正 ： 退货 销售
     * 负 ： 采购 退销
     * @param $amount
     * @param $trans_type
     */
    public function getInvoiceArrears($amount)
    {
        $trans_type = I("inv")['trans_type'];

        $array = ['11', '22'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($amount);
        }

        return abs($amount);
    }


    /**
     * 获取单据列表
     */
    public function getList($bill_type = '')
    {
        list($map, $param, $paramstr) = $this->setMapDeleted('inv')->setMapBillType('inv', $bill_type)->setMapBillDate('inv')->setMapTransType('inv')->setMapBusId('inv')->setMapBillNo('inv')->getMapParam();

        $fields = 'inv.*, bus.name as bus_name, acc.name as acc_name, user.realname as pur_sale_realname';

        $list = $this->alias('inv')->join(' left join '.C('DB_PREFIX').'business as bus on bus.id = inv.bus_id ')->join(' left join '.C('DB_PREFIX').'account as acc on acc.id = inv.acc_id ')->join(' left join '.C('DB_PREFIX').'pur_sale_user as user on user.id = inv.pur_sale_id ')->where($map)->order('inv.id asc')->field($fields)->select();

        $total = ['qty'=>0, 'amount'=>0, 'rp_amount'=>0, 'arrears'=>0];

        foreach ($list as $key=>&$value) {
            $value['trans_type_name'] = getValue(C('trans_type'), $value['trans_type'], '未知');
            $value['total_amount'] = abs($value['total_amount']);
            $value['amount'] = abs($value['amount']);
            $value['rp_amount'] = abs($value['rp_amount']);
            $value['arrears'] = abs($value['arrears']);

            $total['amount'] += $value['amount'];       //  折扣后总金额
            $total['rp_amount'] += $value['rp_amount']; //  付款总金额
            $total['arrears'] += $value['arrears'];     //  欠款总额
        }

        return [$list, count($list) + 1, $total, $param, $paramstr];
    }


    /**
     * 账款明细单
     */
    public function accountDetail($bill_type = [], $bus_type = '')
    {
        list($map1, $param, $paramstr) = $this->setMapDeleted()->setMapBusId('', 'id')->getMapParam();
        $map1['type'] = $bus_type;

        $bus_list = D('Business')->where($map1)->field(true)->order('id asc')->select();

        list($map2, $param, $paramstr) = $this->setMapDeleted()->setMapBusId()->setMapBillType('', $bill_type)->setMapBillDate()->getMapParam();


        $inv_list = $this->where($map2)->field(true)->order('id asc')->select();

        $n = 0;
        $_list = [];
        $total['amount_1'] = 0;
        $total['amount_2'] = 0;
        $total['amount_3'] = 0;
        foreach ($bus_list as $key=>$value) {
            $_list[$n]['bus_name'] = $value['name'];
            $_list[$n]['bill_date'] = '';
            $_list[$n]['bill_no'] = '';
            $_list[$n]['trans_type_name'] = $value['name'];
            $_list[$n]['amount_1'] = $value['st_receive_money'];
            $_list[$n]['amount_2'] = '';
            $_list[$n]['amount_3'] = $value['st_receive_money'];

            $xiaoji = [
                'amount_1' => $_list[$n]['amount_1'],
                'amount_2' => $_list[$n]['amount_2'],
                'amount_3' => $_list[$n]['amount_3']
            ];
            foreach ($inv_list as $k=>$v) {
                ++$n;
                $_list[$n]['bus_name'] = $value['name'];
                $_list[$n]['bill_date'] = $v['bill_date'];
                $_list[$n]['bill_no'] = $v['bill_no'];
                $_list[$n]['trans_type_name'] = getValue(C('trans_type'), $v['trans_type'], '未知');
                $_list[$n]['amount_1'] = $v['bill_type'] == 'PUR' || $v['bill_type'] == 'SALE' ? $v['amount'] : '';
                $_list[$n]['amount_2'] = $v['bill_type'] == 'PAYMENT' || $v['bill_type'] == 'RECEIPT' ? $v['amount'] : '';
                $_list[$n]['amount_3'] = $_list[$n]['amount_3'] + $v['amount'];

                $xiaoji['amount_1'] += $_list[$n]['amount_1'];
                $xiaoji['amount_2'] += $_list[$n]['amount_2'];
                $xiaoji['amount_3'] += $_list[$n]['amount_3'];
            }
            ++$n;

            $_list[$n]['bus_name'] = '';
            $_list[$n]['bill_date'] = '';
            $_list[$n]['bill_no'] = '小计';
            $_list[$n]['trans_type_name'] = '';
            $_list[$n]['amount_1'] = $xiaoji['amount_1'];
            $_list[$n]['amount_2'] = $xiaoji['amount_2'];
            $_list[$n]['amount_3'] = $xiaoji['amount_3'];


            $total['amount_1'] += $xiaoji['amount_1'];
            $total['amount_2'] += $xiaoji['amount_2'];
            $total['amount_3'] += $xiaoji['amount_3'];

            $n++;
        }

        return [$_list, count($_list) + 1, $total, $param, $paramstr];
    }

    /**
     * 应付账款明细单
     */
    public function accountPayDetail($bill_type = [])
    {
        return $this->accountDetail($bill_type, 2);
    }

    /**
     * 应收账款明细单
     */
    public function accountProceedsDetail($bill_type = [])
    {
        return $this->accountDetail($bill_type, 1);
    }

}