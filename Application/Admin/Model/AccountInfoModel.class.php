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
//        array('inv_id', 'require', '单据自增号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_no', 'require', '单据编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bus_id', 'require', '商家编号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_type', 'require', '单据类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('bill_date', 'require', '单据日期不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('acc_id', 'require', '结算账户不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('payment', 'getAccountPayment', self::MODEL_BOTH, 'callback'),
        array('cate', 'getAccountCate', self::MODEL_BOTH, 'callback'),
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),
    );

    /**
     * 收出支出类别
     */
    public function getAccountCate($cate) {
        if( !empty($cate) ) {
            return $cate;
        }
        
        $trans_type = I("inv")['trans_type'];

        //  见配置 account_cate
        switch ($trans_type) {
            case '11' : 
                $cate = '21';
                break;

            case '12' :
                $cate = '12';
                break;

            case '21' :
                $cate = '11';
                break;

            case '22' :
                $cate = '22';
                break;
            
            default:
                $payment = $this->getAccountPayment();
                if( $payment > 0 ) {
                    $cate = 10;
                } else {
                    $cate = 20;
                }
                break;
        }

        return $cate;
    }
    
    /**
     * 根据 $trans_type 判断账户详情信息的金额的 正负
     * 正 ： 退货 销售
     * 负 ： 采购 退销
     * @param $rp_amount
     * @param $trans_type
     */
    public function getAccountPayment($payment = '')
    {
        if( empty($payment) ) {
            $payment = I('$payment', '');
        }

        $trans_type = I("inv")['trans_type'];

        $array = ['11', '22', '42', '44'];

        if( in_array($trans_type, $array) ) {
            return '-'.abs($payment);
        }

        return abs($payment);
    }

    /**
     * 资金明细表
     */
    public function accountDetail()
    {
        list($map, $param, $paramstr) = $this->alias('acc_info')->setMapBillDate('acc_info')->setMapDeleted('acc')->setMapDeleted('acc_info')->setMapAccId('acc')->getMapParam();


        $fields = 'acc.id as acc_id, acc.name as acc_name, acc.amount as st_amount, acc_info.bill_no, acc_info.bill_date , acc_info.payment , acc_info.way_id , acc_info.cate , acc_info.settlement , acc_info.trans_type, acc_info.memo, bus.name as bus_name ';

        $list = D('Account')->alias('acc')->join(' left join '.C('DB_PREFIX').'account_info as acc_info on acc.id = acc_info.acc_id ')->join(' left join business as bus on bus.id = acc_info.bus_id ')->where($map)->order('acc.id asc, acc_info.id asc')->field($fields)->select();


        $_list = $total = [];
        foreach ($list as $key=>$value) {
            $_list[$value['acc_id']]['acc_id'] = $value['acc_id'];
            $_list[$value['acc_id']]['acc_name'] = $value['acc_name'];
            $_list[$value['acc_id']]['st_amount'] = $value['st_amount'];
            $_list[$value['acc_id']]['trans_type_name'] = '期初余额';

            $_info[$key]['bill_no'] = $value['bill_no'];
            $_info[$key]['bill_date'] = $value['bill_date'];
            $_info[$key]['shouru'] = substr($value['cate'], 0 ,1) == 1 ? $value['payment'] : '0';
            $_info[$key]['zhichu'] = substr($value['cate'], 0 ,1) == 2 ? $value['payment'] : '0';
            $_info[$key]['payment'] = $value['payment'];
//            $_info[$key]['amount'] = $key == 0 ? $value['st_amount'] : $_info[$key]['amount'] - $value['payment'];

            $_info[$key]['way'] = C('account_way_id')[$value['way_id']];
            $_info[$key]['settlement'] = $value['bill_no'];
            $_info[$key]['trans_type_name'] = getValue(C('trans_type'), $value['trans_type'], '未知');
            $_info[$key]['bus_name'] = $value['bus_name'];

            $_list[$value['acc_id']]['_info'] = $_info;

            $total['shouru'] += $_list[$value['acc_id']]['_info'][$key]['shouru'];
            $total['zhichu'] += $_list[$value['acc_id']]['_info'][$key]['zhichu'];
//            $total['amount'] += $_list[$value['acc_id']]['_info'][$key]['amount'];
        }

        $number = 0;
        foreach ($_list as $key=>&$value) {
            $number += 1;
            $value['amount'] = $value['st_amount'];
            $value['_info'] = array_values($value['_info']);

            $total['amount'] += $value['st_amount'];
            foreach ($value['_info'] as $k=>&$v) {

                $number += 1;
                $v['amount'] = $k == 0 ? $value['amount'] + $v['payment'] : $value['_info'][$k-1]['amount'] + $v['payment'];

                $total['amount'] += $v['payment'];
            }
        }

        return [$_list, $number + 1, $total, $param, $paramstr];

    }
}
