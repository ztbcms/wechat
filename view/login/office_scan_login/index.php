<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>扫码登录</title>
    <!-- jQuery 2.x -->
    <script src="/statics/admin/jquery/jquery-2.2.3.min.js"></script>
    <!--  ztbcms工具类(必须在vue-common 前加载)  -->
    <script src="/statics/admin/ztbcms/ztbcms.js"></script>
    <!-- vue.js -->
    <script src="/statics/admin/vue/vue.js"></script>
    <script src="/statics/admin/vue/vue-common.js"></script>

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
        <div class="qrcode_area">
            <template v-if="qrcode">
                <img :src="qrcode" style="width: 200px">
                <p class="msg">使用微信扫一扫登录</p>
            </template>
            <template v-else>
                <div class="loading_continer">
                    <div class="loading" style="width: 100px; height: 100px"></div>
                </div>
            </template>
        </div>
    </div>

</div>

<style>
    .page {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .qrcode_area {
        display: block;
        text-align: center
    }

    .loading_continer {
        width: 200px;
        height: 200px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loading {
        position: relative;
        width: 80px;
        height: 80px;
        border: 4px solid #07c160;
        border-top-color: rgba(0, 0, 0, 0.2);
        border-right-color: rgba(0, 0, 0, 0.2);
        border-bottom-color: rgba(0, 0, 0, 0.2);
        border-radius: 100%;

        animation: circle infinite 0.75s linear;
    }

    @keyframes circle {
        0% {
            transform: rotate(0);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .msg {
        font-size: 17px;
    }

</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                code: '',
                qrcode: '',
                ttl: 0,
                intervalId: 0,
                timeoutId: 0,
                checkCodeRepeatTime: 2 * 1000,
                redirect_url: "{$redirect_url}"
            },
            mounted: function () {
                this.getQrcode()
            },
            methods: {
                getQrcode: function () {
                    let that = this
                    if (this.intervalId) {
                        clearInterval(this.intervalId)
                    }
                    if (this.timeoutId) {
                        clearTimeout(this.timeoutId)
                    }
                    this.httpGet("{:api_url('/wechat/login.OfficeScanLogin/getLoginCode')}", {}, function (res) {
                        if (res.status) {
                            that.code = res.data.code
                            that.qrcode = res.data.qrcode
                            that.ttl = res.data.ttl
                            that.timoutId = setTimeout(that.getQrcode, that.ttl * 1000)
                            that.intervalId = setInterval(that.checkCode, that.checkCodeRepeatTime)
                        }
                    })
                },
                checkCode: function () {
                    if (!this.code) return
                    let that = this
                    this.httpGet("{:api_url('/wechat/login.OfficeScanLogin/checkCode')}", {code: this.code}, function (res) {
                        if (res.status) {
                            if (res.data.token) {
                                if (that.intervalId) {
                                    clearInterval(that.intervalId)
                                }
                                if (this.timeoutId) {
                                    clearTimeout(that.timeoutId)
                                }
                                var url = decodeURIComponent(that.redirect_url)
                                if (url.indexOf('?') === -1) {
                                    url += '?'
                                }
                                url += 'code=' + encodeURIComponent(res.data.token)
                                window.location.href = url
                            }
                        }
                    })
                }
            }
        })
    });
</script>


</body>

</html>
