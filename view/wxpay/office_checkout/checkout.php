<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>订单支付</title>
    <!-- jQuery 2.x -->
    <script src="/statics/admin/jquery/jquery-2.2.3.min.js"></script>
    <!--  ztbcms工具类(必须在vue-common 前加载)  -->
    <script src="/statics/admin/ztbcms/ztbcms.js"></script>
    <!-- vue.js -->
    <script src="/statics/admin/vue/vue.js"></script>
    <script src="/statics/admin/vue/vue-common.js"></script>

    <!--weui-->
    <link rel="stylesheet" href="//res.wx.qq.com/t/wx_fed/weui-source/res/2.5.16/weui.min.css">
    <script>
        (function (vue) {
            //引入vue mixin
            vue.mixin(window.__vueCommon);
        })(window.Vue);
    </script>

    <style>
        /* vue相关  */
        [v-cloak] {
            display: none;
        }

        * {
            font-family: "Helvetica Neue", Helvetica, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "微软雅黑", Arial, sans-serif;
        }
    </style>
</head>
<body style="height: 100%;background-color: #F8F8F8">

<div id="app" v-cloak>
    <div class="page">
        <div class="weui-form">
            <div class="weui-form__text-area">
                <h2 class="weui-form__title">￥<span style="font-size: 36px">{{ order_info.pay_price }}</span></h2>
            </div>
            <div class="weui-form__control-area">
                <div class="weui-cells__group weui-cells__group_form">
                    <div class="weui-cells">
                        <label for="js_input1" class="weui-cell weui-cell_active">
                            <div class="weui-cell__hd"><span class="weui-label">商品名称</span></div>
                            <div class="weui-cell__bd">
                                <input v-model="order_info.order_desc" class="weui-input text-right"
                                       disabled/>
                            </div>
                        </label>
                        <label for="js_input2" class="weui-cell weui-cell_active">
                            <div class="weui-cell__hd"><span class="weui-label">订单号</span></div>
                            <div class="weui-cell__bd">
                                <input v-model="order_info.order_no" class="weui-input text-right"
                                       disabled/>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            <div class="weui-form__opr-area">
                <a @click="confirmPay" class="weui-btn weui-btn_primary">
                    <i v-if="loading" class="weui-mask-loading" style="color: white;font-size: 21px"></i>
                    <span v-else>确认支付</span>
                </a>
            </div>
        </div>

    </div>
</div>

<style>
    .page {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    .weui-form {
        max-width: 640px;
        margin: 0 auto;
    }

    .text-right {
        text-align: right;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                order_info_token: "{:input('order_token')}",
                order_info: {
                    order_no: "{$order_info['order_no']}",
                    order_desc: "{$order_info['order_desc']}",
                    pay_price: "{$order_info['pay_price']}",
                },
                payConfig: {},
                paid_success_url: "{$order_info['paid_success_url']}",
                loading: false,
            },
            mounted: function () {},
            methods: {
                confirmPay: function () {
                    this.fetchPayConfig()
                },
                fetchPayConfig: function () {
                    if (this.loading) return
                    var that = this
                    this.loading = true
                    var data = {
                        _action: 'getPayConfig',
                        order_token: this.order_info_token,
                    }
                    this.httpGet("{:api_url('/wechat/wxpay.OfficeCheckout/checkout')}", data, function (res) {
                        that.loading = false
                        if (res.status) {
                            that.requestPayment(res.data)
                        } else {
                            alert(res.msg)
                        }
                    })
                },
                requestPayment: function (payConfig) {
                    if (typeof WeixinJSBridge == "undefined") {
                        if (document.addEventListener) {
                            document.addEventListener('WeixinJSBridgeReady', this.doRequestPayment.bind(this, payConfig), false);
                        } else if (document.attachEvent) {
                            document.attachEvent('WeixinJSBridgeReady', this.doRequestPayment.bind(this, payConfig));
                            document.attachEvent('onWeixinJSBridgeReady', this.doRequestPayment.bind(this, payConfig));
                        }
                    } else {
                        this.doRequestPayment(payConfig);
                    }
                },
                doRequestPayment: function (payConfig) {
                    var that = this
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest', payConfig,
                        function (res) {
                            if (res.err_msg == "get_brand_wcpay_request:ok") {
                                //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                                alert('支付成功后')
                                window.location.replace(that.paid_success_url)
                            }
                        });
                }
            }
        })
    });
</script>


</body>

</html>
