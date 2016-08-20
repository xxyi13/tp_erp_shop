<?php
/**
 * 公共数据模型
 * User: fy
 * Date: 16-7-17
 * Time: 下午9:57
 */

namespace Admin\Model;


use Think\Model;

abstract class CommonModel extends Model
{

    const STATUS_SUCCESS = true;

    const STATUS_ERROR = false;

    protected $error_msg;

    /**
     * 保存数据
     */
    public function saveData($inputs = [], $model = '')
    {
        $method = I('get.method', '');

        $this->startTrans();	//	开启事务

        $model = !is_object($model) || empty($model) ? $this : $model;

        $inputs = empty($inputs) ? $model->create() : $inputs;

        if( !$inputs ){
            return $this->output(self::STATUS_ERROR, $this->getError());
        }

        if( empty($method) ) {
            $method = isset($inputs[$model->getPk()]) && !empty($inputs[$model->getPk()]) ? 'edit' : 'add';
        }

        if( $method == 'add' ) {
            $re = $model->add($inputs);
        } else {
            $re = $model->save($inputs);
        }
        

        if( $re !== false ) {
            $this->commit();
        }else{
            $this->rollback();
        }
        
        return $this->output($re);
    }

    /**
     * 输出信息
     * @param $code
     * @param string $msg
     * @param string $redirect
     * @return array
     */
    protected function output($status = false, $msg = '')
    {
        $this->error_msg = $msg;
        
        if( empty($msg) ) {
            $this->error_msg = $status ? "操作成功" : "操作失败";
        }

        return $status;
    }

    /**
     * 返回错误信息
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->error_msg;
    }


    /********************************************************************************************/
    
    //  搜索传递参数
    protected $param = [];

    //  组装查询条件
    protected $map = [];

    /**
     * 获取搜索传递的参数
     * @return array
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * 获取组装好的查询条件
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * 获取搜索传递的参数 并转化为 url字符串
     */
    public function getParamString()
    {
        return http_build_query($this->param);
    }

    /**
     * 获取 param map 等全部信息
     */
    public function getMapParam()
    {
        return [
            $this->getMap(),
            $this->getParam(),
            $this->getParamString()
        ];
    }

    /**
     * 设置 单据类型
     * @param string $db_prefix
     * @param $key
     * @return $this
     */
    public function setMapBillType($db_prefix = '', $key)
    {
        if( empty($db_prefix) ) {
            $this->map['bill_type'] = C('bill_type')[$key];
        } else {
            $this->map[$db_prefix.'.bill_type'] = C('bill_type')[$key];
        }

        return $this;
    }

    /**
     * 设置 是否删除
     * @param string $db_prefix
     * @param string $val
     * @return $this
     */
    public function setMapDeleted($db_prefix = '', $val = '0')
    {
        if( empty($db_prefix) ) {
            $this->map['deleted_at'] = ['eq', $val];
        } else {
            $this->map[$db_prefix.'.deleted_at'] = ['eq', $val];
        }

        return $this;
    }

    /**
     * 获取单据日期的条件
     * @param array $param
     * @return array
     */
    public function setMapBillDate($db_prefix = '')
    {
        $this->param['start_date'] = format_datetime( I('start_date', '') );
        $this->param['end_date'] = format_datetime( I('end_date', '') );

        if( empty($this->param['start_date']) ) {
            $this->param['start_date'] = date('Y-m-01', strtotime(date("Y-m-d")));
        }

        if( empty($this->param['end_date']) ) {
//            date('Y-m-d', strtotime("$this->param['start_date'] +1 month -1 day"));
            $this->param['end_date'] = date('Y-m-d', time());
        }

        if( empty($db_prefix) ) {
            $this->map['bill_date'] = [
                ['egt', $this->param['start_date']],
                ['elt', $this->param['end_date']]
            ];
        } else {
            $this->map[$db_prefix.'.bill_date'] = [
                ['egt', $this->param['start_date']],
                ['elt', $this->param['end_date']]
            ];
        }

        return $this;
    }

    /**
     * 设置条件：商品分类
     * @param string $db_prefix
     * @return $this
     */
    public function setMapGoodsCategory($db_prefix = '')
    {
        $this->param['category'] = I('category', '');
        if( !empty($this->param['category']) ) {
            if( empty($db_prefix) ) {
                $this->map['category'] = $this->param['category'];
            } else {
                $this->map[$db_prefix.'.category'] = $this->param['category'];
            }
        }
        return $this;
    }

    /**
     * 设置条件：业务类型
     * @param string $db_prefix
     * @return $this
     */
    public function setMapTransType($db_prefix = '')
    {
        $this->param['trans_type'] = I('trans_type', '');
        if( !empty($this->param['trans_type']) ) {
            if( empty($db_prefix) ) {
                $this->map['trans_type'] = $this->param['trans_type'];
            } else {
                $this->map[$db_prefix.'.trans_type'] = $this->param['trans_type'];
            }
        }
        return $this;
    }

    /**
     * 设置条件：商家编号
     * @param string $db_prefix
     * @return $this
     */
    public function setMapBusId($db_prefix = '')
    {
        $this->param['bus_id'] = I('bus_id', '');
        if( !empty($this->param['bus_id']) ) {
            if( empty($db_prefix) ) {
                $this->map['bus_id'] = $this->param['bus_id'];
            } else {
                $this->map[$db_prefix.'.bus_id'] = $this->param['bus_id'];
            }
        }
        return $this;
    }


    /**
     * 设置条件：商家编号
     * @param string $db_prefix
     * @return $this
     */
    public function setMapBillNo($db_prefix = '')
    {
        $this->param['bill_no'] = I('bill_no', '');
        if( !empty($this->param['bill_no']) ) {
            if( empty($db_prefix) ) {
                $this->map['bill_no'] = $this->param['bill_no'];
            } else {
                $this->map[$db_prefix.'.bill_no'] = $this->param['bill_no'];
            }
        }
        return $this;
    }
}