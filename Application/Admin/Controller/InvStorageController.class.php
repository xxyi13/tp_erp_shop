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

    }

    /**
     * 添加其他出库
     */
    public function addQtck()
    {

    }

    /**
     * 成本调整
     */
    public function addCbtz()
    {

    }


    /**
     * 保存数据
     */
    public function save()
    {

    }
}