<?php
/**
 * ajax 控制器
 * User: fy
 * Date: 16-7-17
 * Time: 下午9:08
 */

namespace Admin\Controller;

use Think\Controller;

class AjaxController extends Controller
{
    public function _initialize()
    {
        if( !IS_AJAX ) {
            return false;
        }
    }

    public function getBusiness($type)
    {
        $list = D('Business')->getBusiness($type, 'id, name, contact_name');

        $list[1] = $list[0];
        $list[0] = ['id'=>'0', 'name'=>'请选择商家', 'contact_name'=>''];

        $return['message'] = "";
        $return['value'] = $list;
        $return['code'] = 200;
        $return['redirect'] = "";

        $this->ajaxReturn($return);
    }


    public function getPurSaleUser()
    {
        $list = D('PurSaleUser')->getPurSaleUser('id, realname');

        $return['message'] = "";
        $return['value'] = $list;
        $return['code'] = 200;
        $return['redirect'] = "";

        $this->ajaxReturn($return);
    }

    public function getGoods($type = '')
    {
        $list = D('Goods')->getGoodsList($type);
        $list[0] = [
            'id' => '0',
            'name' => '请选择商品',
            'spec' => '规格型号',
            'unit' => '单位',
            'total_qty' => '剩余库存'
        ];
        $return['message'] = "";
        $return['value'] = $list;
        $return['code'] = 200;
        $return['redirect'] = "";

        $this->ajaxReturn($return);
    }
}