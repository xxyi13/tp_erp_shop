<?php
/**
 * 仓库
 * User: fy
 * Date: 16-8-7
 * Time: 下午1:57
 */

namespace Admin\Controller;


class InvStorageController extends InvController
{

    /**
     * 盘点
     */
    public function inventory($type = '')
    {
        $this->getGoodsList();
        
        $this->display();
    }

    /**
     * 添加其他入库
     */
    public function addQtrk()
    {
        $this->getInvInfo();
        
        $this->display('qtrk');
    }

    /**
     * 添加其他出库
     */
    public function addQtck()
    {
        $this->getInvInfo();

        $this->display('qtck');
    }

    /**
     * 成本调整
     */
    public function addCbtz()
    {
        $this->getInvInfo();

        $this->display('cbtz');
    }


    /**
     * 导出盘点系统库存
     */
    public function exportInventory()
    {
        $data = $this->getGoodsList('', true);

        $storage_qty = I('d1'); //  盘点库存

        $deficit = I('d2'); //  盘亏盘赢

        foreach ($data['_list'] as $key=>&$value) {
            $value['category'] = $data['goods_category'][$value['category']];
            $value['unit'] = $data['unit_list'][$value['unit']];
            $value['storage_house'] = empty($data['goods_storage_house'][$value['storage_house']]) ? 0 : $data['goods_storage_house'][$value['storage_house']];
            $value['storage_qty'] = isset($storage_qty[$value['id']]) ? $storage_qty[$value['id']] : '未知';
            $value['deficit'] = isset($deficit[$value['id']]) ? $deficit[$value['id']] : '未知';
        }

        $category = I('category', 0);
        $expTitle = '时间：'.date('Y-m-d').'导出盘点系统库存 分类：'.empty($category) ? '所有' : $data['goods_category'][$category];

        $xlsCell  = array(
            array('id','编号'),
            array('name','商品名称'),
            array('category','商品类别'),
            array('spec','规格型号'),
            array('storage_house','仓库'),
            array('unit','计量单位'),
            array('total_qty','剩余库存'),
            array('storage_qty','盘点库存'),
            array('deficit','盘亏盘赢'),
            array('shijichuqin','实际出勤(天)')
        );

        $expTableData = $data['_list'];

        $this->exportExcel($expTitle, $xlsCell, $expTableData);

    }

}