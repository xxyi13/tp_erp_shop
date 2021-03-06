<?php
/**
 * Created by PhpStorm.
 * User: fy
 * Date: 16-7-30
 * Time: 下午5:14
 */

namespace Admin\Model;


class PurSaleUserModel extends CommonModel
{
    public function getPurSaleUser( $field = true )
    {
        return $this->where(['deleted_at'=>['eq', 0]])->field($field)->select();
    }

    public function getPurSaleUserById($id, $field = '')
    {
        if(empty($field)) {
            return $this->field(true)->find($id);
        }

        return $this->where(['id'=>$id])->getField($field);
    }

}