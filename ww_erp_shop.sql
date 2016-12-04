-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2016-12-04 13:10:19
-- 服务器版本： 5.5.52-MariaDB-1ubuntu0.14.04.1
-- PHP Version: 7.1.0RC6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ww_erp_shop`
--
CREATE DATABASE IF NOT EXISTS `ww_erp_shop` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `ww_erp_shop`;

-- --------------------------------------------------------

--
-- 表的结构 `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` smallint(5) UNSIGNED NOT NULL COMMENT '账户自增编号',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '账户名称',
  `account_number` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '账户名对应的账户号',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '账户类型',
  `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `amount_date` date NOT NULL COMMENT '建账余额日期',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='账户基本信息表';

--
-- 插入之前先把表清空（truncate） `account`
--

TRUNCATE TABLE `account`;
-- --------------------------------------------------------

--
-- 表的结构 `account_info`
--

DROP TABLE IF EXISTS `account_info`;
CREATE TABLE `account_info` (
  `id` int(10) UNSIGNED NOT NULL,
  `inv_id` int(10) UNSIGNED NOT NULL COMMENT 'invoice 单据表的自增编号',
  `bill_no` char(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '单据编号',
  `bus_id` smallint(5) UNSIGNED NOT NULL COMMENT '商家编号',
  `bill_type` char(5) COLLATE utf8_unicode_ci NOT NULL COMMENT '单据类型',
  `bill_date` date NOT NULL COMMENT '单据日期',
  `acc_id` smallint(5) UNSIGNED NOT NULL COMMENT '结算账户编号',
  `payment` float(10,2) NOT NULL COMMENT '收款|付款金额 采购退回为正	',
  `way_id` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结算方式',
  `cate` tinyint(3) NOT NULL DEFAULT '0' COMMENT '收入支出类别',
  `settlement` char(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '结算号',
  `trans_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1 购货 2 退货 3 销售 4 退销 5 其他入库 6 其他出库 7 盘盈 8 盘亏',
  `memo` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='账户资金流动明细表';

--
-- 插入之前先把表清空（truncate） `account_info`
--

TRUNCATE TABLE `account_info`;
-- --------------------------------------------------------

--
-- 表的结构 `admin_user`
--

DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '用户自增编号',
  `realname` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户姓名',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户邮箱',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户秘密',
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户手机号',
  `salary` double(8,2) NOT NULL DEFAULT '0.00' COMMENT '用户薪资',
  `entry_date` date NOT NULL COMMENT '用户入职日期',
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 插入之前先把表清空（truncate） `admin_user`
--

TRUNCATE TABLE `admin_user`;
--
-- 转存表中的数据 `admin_user`
--

INSERT INTO `admin_user` (`id`, `realname`, `email`, `password`, `mobile`, `salary`, `entry_date`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '冯炎', 'guiguoershao@163.com', 'c3fcd3d76192e4007dfb496cca67e13b', '0', 0.00, '0000-00-00', '9Nija8sOp5V8FFkY1LvaOYyw2j8lDnGHvYnrj62mCGpM8J1ibaH2WEkRxKQs', NULL, '2016-07-14 08:11:19', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `business`
--

DROP TABLE IF EXISTS `business`;
CREATE TABLE `business` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '商家自增编号',
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '商家名称',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商家类型 0:未知 1:客户 2:供应商',
  `level` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商家等级 0:普通商家',
  `settlement_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商家结算方式 0:未知 1 日结 2 周结 3 月结',
  `settlement_date` date NOT NULL COMMENT '商家结算日期',
  `st_receive_money` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '供应商-期初应付款 客户-期初应收款',
  `st_period_receive_money` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '供应商-期初预付款 客户-期初预收款',
  `contact_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '联系人姓名',
  `contact_mobile` char(11) COLLATE utf8_unicode_ci NOT NULL COMMENT '联系人电话',
  `address` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '商家地址',
  `memo` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '商家描述',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='商家表(供应商和客户)';

--
-- 插入之前先把表清空（truncate） `business`
--

TRUNCATE TABLE `business`;
-- --------------------------------------------------------

--
-- 表的结构 `goods`
--

DROP TABLE IF EXISTS `goods`;
CREATE TABLE `goods` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '商品自增编号',
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '商品名称',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品类型 0:未知 1:成品 2:半成品',
  `category` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品分类 0:未知 ',
  `spec` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '商品规格型号',
  `bar_code` char(13) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '商品条形码',
  `storage_house` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '存储仓库编号 0:默认仓库',
  `min_inventory` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '最低库存 预警',
  `max_inventory` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '最高库存 预警',
  `unit` char(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '计量单位',
  `purchase_price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '采购价格',
  `sale_price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '零售价格',
  `wholesale_price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '批发价格',
  `vip_price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT 'vip价格',
  `discount_rate_1` tinyint(2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '折扣率1 %',
  `discount_rate_2` tinyint(2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '折扣率2 %',
  `memo` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '商品描述',
  `is_alarm` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否开启库存预警 1:默认开启',
  `st_quantity` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '期初-数量',
  `st_unit_cost` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '期初-单位成本',
  `st_amount` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '期初-总价',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='商品表';

--
-- 插入之前先把表清空（truncate） `goods`
--

TRUNCATE TABLE `goods`;
-- --------------------------------------------------------

--
-- 表的结构 `invoice`
--

DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增编号',
  `bus_id` smallint(5) NOT NULL DEFAULT '0' COMMENT '供应商编号',
  `bill_no` char(20) CHARACTER SET utf8 NOT NULL COMMENT '单据编号',
  `uid` smallint(5) NOT NULL DEFAULT '0' COMMENT '创建此单据人编号',
  `realname` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '创建此单据人姓名',
  `total_amount` float(10,2) NOT NULL COMMENT '购货总金额 ',
  `dis_rate` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣率',
  `dis_amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣金额',
  `amount` float(10,2) NOT NULL COMMENT '折扣后金额 ',
  `rp_amount` float(10,2) NOT NULL COMMENT '本次付款 ',
  `acc_id` smallint(5) NOT NULL DEFAULT '0' COMMENT '结算账户编号',
  `arrears` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '本次欠款',
  `bill_date` date NOT NULL COMMENT '单据日期',
  `total_qty` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '总数量  + 购货 推销  - 销货 退货',
  `bill_type` char(5) CHARACTER SET utf8 NOT NULL COMMENT '单据类型　PO 采购订单　OI 其他入库　OO　其它出库 PUR 采购入库 BAL　初期余额 SALE 销货单',
  `hx_state_code` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未付款 1部分付款 2全部付款 ',
  `trans_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1 购货 2 退货 3 销售 4 退销 5 其他入库 6 其他出库 7 盘盈 8 盘亏',
  `pur_sale_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '销售或者购货人员编号',
  `customer_free` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '客户承担费用',
  `payment` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '本次预收款(未启用)',
  `memo` varchar(200) CHARACTER SET utf8 NOT NULL COMMENT '单据备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='单据表';

--
-- 插入之前先把表清空（truncate） `invoice`
--

TRUNCATE TABLE `invoice`;
-- --------------------------------------------------------

--
-- 表的结构 `invoice_info`
--

DROP TABLE IF EXISTS `invoice_info`;
CREATE TABLE `invoice_info` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增编号',
  `inv_id` int(10) NOT NULL DEFAULT '0' COMMENT 'invoice表的编号',
  `bus_id` smallint(5) NOT NULL DEFAULT '0' COMMENT '供应商编号',
  `bill_no` char(20) CHARACTER SET utf8 NOT NULL COMMENT '单据编号',
  `bill_type` char(5) CHARACTER SET utf8 NOT NULL COMMENT '单据类型',
  `bill_date` date NOT NULL COMMENT '单据日期 ',
  `trans_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1 购货 2 退货 3 销售 4 退销 5 其他入库 6 其他出库 7 盘盈 8 盘亏',
  `goods_id` smallint(5) NOT NULL DEFAULT '0' COMMENT '商品编号',
  `category` tinyint(3) NOT NULL DEFAULT '0' COMMENT '商品类别',
  `storage_house` tinyint(3) NOT NULL DEFAULT '0' COMMENT '仓库编号',
  `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '购货金额',
  `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品单价',
  `qty` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '数量　+ 购货 推销  - 销货 退货',
  `dis_amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣金额',
  `dis_rate` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣率',
  `pur_sale_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '销售或者购货人员编号',
  `memo` varchar(200) CHARACTER SET utf8 NOT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 插入之前先把表清空（truncate） `invoice_info`
--

TRUNCATE TABLE `invoice_info`;
-- --------------------------------------------------------

--
-- 表的结构 `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 插入之前先把表清空（truncate） `password_resets`
--

TRUNCATE TABLE `password_resets`;
-- --------------------------------------------------------

--
-- 表的结构 `pur_sale_user`
--

DROP TABLE IF EXISTS `pur_sale_user`;
CREATE TABLE `pur_sale_user` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '用户自增编号',
  `realname` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户姓名',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户邮箱',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户秘密',
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户手机号',
  `salary` double(8,2) NOT NULL DEFAULT '0.00' COMMENT '用户薪资',
  `entry_date` date NOT NULL COMMENT '用户入职日期',
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 插入之前先把表清空（truncate） `pur_sale_user`
--

TRUNCATE TABLE `pur_sale_user`;
--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_info`
--
ALTER TABLE `account_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `business`
--
ALTER TABLE `business`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `goods`
--
ALTER TABLE `goods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_info`
--
ALTER TABLE `invoice_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `pur_sale_user`
--
ALTER TABLE `pur_sale_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `account`
--
ALTER TABLE `account`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '账户自增编号';
--
-- 使用表AUTO_INCREMENT `account_info`
--
ALTER TABLE `account_info`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户自增编号', AUTO_INCREMENT=2;
--
-- 使用表AUTO_INCREMENT `business`
--
ALTER TABLE `business`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商家自增编号';
--
-- 使用表AUTO_INCREMENT `goods`
--
ALTER TABLE `goods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品自增编号';
--
-- 使用表AUTO_INCREMENT `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增编号';
--
-- 使用表AUTO_INCREMENT `invoice_info`
--
ALTER TABLE `invoice_info`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增编号';
--
-- 使用表AUTO_INCREMENT `pur_sale_user`
--
ALTER TABLE `pur_sale_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户自增编号';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
