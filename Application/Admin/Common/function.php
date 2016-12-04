<?php

/**
 * 获取当前用户的Uid
 * @return mixed
 */
function getCurrentAdminUserId ()
{
    return session('user_auth.uid');
}

/**
 * 获取管理用户的姓名
 * @param string $uid
 * @return mixed|string
 */
function getAdminUserRealnameById( $uid = '' )
{
    return empty($uid) ? session('user_auth.realname') : '';
}

/**
 * 获取某账户的现余额
 * @param string $amount
 */
function getAccountAmount($acc_id, $amount = '0')
{
    return $amount + D('AccountInfo')->where(['acc_id'=>$acc_id, 'deleted_at'=>['eq', 0]])->sum('payment');
}