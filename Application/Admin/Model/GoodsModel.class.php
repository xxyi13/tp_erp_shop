<?php
/**
 * 商品模型
 * User: fy
 * Date: 16-7-25
 * Time: 下午10:52
 */

namespace Admin\Model;


class GoodsModel extends CommonModel
{

    protected $_validate = array(
        array('name', 'require', '商品名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('min_inventory', 'require', '商品最低库存不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('max_inventory', 'require', '商品最高库存不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('purchase_price', 'require', '商品采购价格不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('sale_price', 'require', '商品零售价格不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('bar_code', 'getBarCode', self::MODEL_INSERT, 'function'),
        array('created_at', 'datetime', self::MODEL_INSERT, 'function'),
        array('updated_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('deleted_at', '0000-00-00 00:00:00', self::MODEL_INSERT),
        array('updated_at', 'datetime', self::MODEL_UPDATE, 'function'),
    );


    public function getGoodsList($type = '')
    {
        $map = [];
        if( !empty($type) ) {
            if($type == 1) {
                $map['type'] = array('in', ['1','3']);
            } else {
                $map['type'] = $type;
            }
        }
        $list = $this->where($map)->field('id, name, spec, unit, st_quantity')->select();

        foreach ($list as $key=>&$value) {
            $value['total_qty'] = $value['st_quantity'] + D('InvoiceInfo')->where(['goods_id'=>$value['id']])->sum('qty');
        }

        return $list;
    }

    /**
     * 按商品查询 明细表
     * @param string $bill_type
     * @return array
     */
    public function getSummaryGoods($bill_type = '')
    {
        list($map, $param, $paramstr) = $this->setMapDeleted('goods')->setMapDeleted('inv_info')->setMapBillType('inv_info', $bill_type)->setMapBillDate('inv_info')->setMapGoodsCategory('goods')->setMapBusId('inv_info')->getMapParam();

        $fields = 'goods.id as goods_id, goods.name as goods_name, goods.spec, goods.unit, goods.storage_house, inv_info.price, sum(inv_info.qty) as qty, sum(inv_info.amount) as amount';

        $model = $this->alias('goods')->join(' left join '.C('DB_PREFIX').'invoice_info as inv_info on inv_info.goods_id = goods.id ');

        $list = $model->where($map)->field($fields)->order('goods.id asc')->group('goods.id')->select();

        $total = ['qty'=>0, 'amount'=>0];

        foreach ($list as $key=>&$value) {
            $value['unit_name'] = getValue(C('unit_list'), $value['unit'], '未知');
            $value['storage_house_name'] = getValue(C('goods_storage_house'), $value['storage_house'], '默认仓库');
            $value['amount'] = abs($value['amount']);
            $value['qty'] = abs($value['qty']);

            $total['qty'] += $value['qty'];
            $total['amount'] += $value['amount'];
        }

        return [$list, count($list) + 1, $total, $param, $paramstr];
    }

    /**
     * 采购汇总表(按商品)
     */
    public function purSummaryGoods()
    {
        return $this->getSummaryGoods('11');
    }

    /**
     * 销售汇总表(按商品)
     */
    public function saleSummaryGoods()
    {
        return $this->getSummaryGoods('21');
    }

}