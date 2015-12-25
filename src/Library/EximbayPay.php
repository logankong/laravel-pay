<?php

// +----------------------------------------------------------------------
// | date: 2015-12-25
// +----------------------------------------------------------------------
// | EximbayPay.php: EximbayPay 支付
// +----------------------------------------------------------------------
// | Author: yangyifan <yangyifanphp@gmail.com>
// +----------------------------------------------------------------------

namespace Yangyifan\Pay\Library;

use Yangyifan\Pay\PayInterface;

class EximbayPay implements PayInterface
{
    private $config;

    /**
     * 构造方法
     *
     * @author yangyifan <yangyifanphp@gmail.com>
     */
    public function __construct()
    {
        $this->config = $this->mergeAlipayConfig();
    }

    /**
     * 组合EximbayPay配置信息
     *
     * @param $config
     * @author yangyifan <yangyifanphp@gmail.com>
     */
    private function mergeAlipayConfig()
    {
        return config('pay.eximbay');
    }

    /**
     * 创建并发起EximbayPay支付
     *
     * @param $order_sn 订单编号
     * @param $total_price 订单支付总金额
     * @param $subject 商品标题
     * @param $product 商品描述
     * @return mixed
     * @author yangyifan <yangyifanphp@gmail.com>
     */
    public function createPay($order_sn, $price, $params)
    {
        //发起支付
        $this->initiatePayment($this->mergePayParams($params['order_sn'], $params['price'], $params));
    }

    /**
     * 获得EximbayPay签名
     *
     * @param $params
     * @return string
     * @author yangyifan <yangyifanphp@gmail.com>
     */
    private function sign($order_sn, $price_kr)
    {
        return hash("sha256", $this->config['secretKey'] .'?'. http_build_query([
                'mid'   => $this->config['mid'],
                'ref'   => $order_sn,
                'cur'   => $this->config['cur'],
                'amt'   => $price_kr,
            ]));
    }

    /**
     * 组合支付参数
     *
     * @param $order_sn 订单编号
     * @param $price 价格必须为韩元
     * @param $params
     * @return array
     * @author yangyifan <yangyifanphp@gmail.com>
     */
    private function mergePayParams($order_sn, $price_kr, $params)
    {


        $params = [
            'ref'                   => $order_sn,//订单编号、
            'secretKey'             => $this->config['secretKey'],//seretKey
            'mid'                   => $this->config['mid'],//mid
            'cur'                   => $this->config['cur'],//货币
            'product'               => $params['product'],//商品名称
            'buyer'                 => $params['user_lastname'] . $params['user_firstname'],//购买者姓名全称
            'tel'                   => $params['user_phone'],//用户手机
            'email'                 => $params['user_email'],//用户邮箱
            'amt'                   => $price_kr,//总金额（韩币）
            'dm_item_0_product'     => $this->config['product_name'],//项目名称
            'dm_item_0_quantity'    => "1",// 需要确认
            'dm_item_0_unitPrice'   => $price_kr,
            'dm_shipTo_phoneNumber' => $params['user_phone'],//用户手机,
            'dm_shipTo_firstName'   => $params['user_firstname'],//用户名
            'dm_shipTo_lastName'    => $params['user_lastname'],//用户姓
            'fgkey'                 => $this->sign($order_sn, $price_kr),//签名
            'returnurl'             => config('pay.eximbay_url.returnurl'),//url 地址
            'statusurl'             => config('pay.eximbay_url.statusurl'),//url地址
            "ver"                   => "170",
            "txntype"               => "SALE",
            "dm_shipTo_country"     => "US",
            "dm_shipTo_city"        => "NOTHING",
            "dm_shipTo_state"       => "NOTHING",
            "dm_shipTo_street1"     => "NOTHING",
            "dm_shipTo_postalCode"  => "NOTHING",
            "visitorid"             => "",
            "shop"                  => "在首尔旅游网",
            "lang"                  => "CN",
            "charset"               => "UTF-8",
            "param1"                => "merchant option1",
            "param2"                => "merchant option2",
            "param3"                => "merchant option3",
            "displaytype"           => "P",
            "directToReturn"        => "N",
            "autoclose"             => "N",
        ];
        return $params;
    }

    /**
     * 发起支付
     *
     * @param $param
     * @return string
     * @author yangyifan <yangyifanphp@gmail.com>
     */
    private function initiatePayment($param)
    {
        echo ( new EximbaySubmit($this->config) )->buildRequestForm($param, "post", "确认");
    }



}