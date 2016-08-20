<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-7-20
 * Time: 下午5:08
 */

namespace Admin\Model;


class BusinessModel extends CommonModel
{
    protected $_validate = array(
        array('name', 'require', '商家名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('contact_name', 'require', '联系人姓名不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('contact_mobile', 'require', '联系人电话不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('address', 'require', '商家地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('type', 'require', '商家类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('level', 'require', '商家等级不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('settlement_type', 'require', '结算方式不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('settlement_date', 'require', '结算日期不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('settlement_date', 'format_datetime', self::MODEL_BOTH , 'function'),
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),
    );

    /**
     * 获取商家信息
     * @param $type
     * @param bool $field
     * @param string $id
     * @return mixed
     */
    public function getBusiness( $type, $field = true)
    {
        $map['type'] = $type;

        return $this->where($map)->field($field)->select();
    }

    public function getBusinessById($id, $field = '')
    {
        if(empty($field)) {
            return $this->field(true)->find($id);
        }

        return $this->where(['id'=>$id])->getField($field);
    }

    /**
     * 采购汇总表(按供应商)
     */
    public function purSummarySupply()
    {
        list($map, $param, $paramstr) = $this->setMapBillDate('inv_info')->setMapBusId('inv_info')->getMapParam();

        $map['bus.deleted_at'] = array('eq','0');

        $map['inv_info.bill_type'] = 'PUR';

        $fields = 'bus.id as bus_id, bus.name as bus_name, inv_info.price, sum(inv_info.qty) as qty, sum(inv_info.amount) as amount';

        $model = $this->alias('bus')->join(' left join '.C('DB_PREFIX').'invoice_info as inv_info on inv_info.bus_id = bus.id ');

        $list = $model->where($map)->field($fields)->order('bus.id asc')->group('bus.id')->select();

        $total = ['qty'=>0, 'amount'=>0];

        foreach ($list as $key=>&$value) {
            $value['storage_house_name'] = getValue(C('goods_storage_house'), $value['storage_house'], '默认仓库');
            $value['amount'] = abs($value['amount']);

            $total['qty'] += $value['qty'];
            $total['amount'] += $value['amount'];
        }

        return [$list, count($list) + 1, $total, $param, $paramstr];
    }
}