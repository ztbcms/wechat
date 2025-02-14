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

    <!--  qrcode  -->
    <script src="/statics/admin/qrcode/qrcode.js"></script>

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
            <template v-if="status == 1">
                <div class="loading_continer">
                    <div class="loading"></div>
                </div>
            </template>
            <template v-if="status == 2">
                <svg t="1682653612574" class="icon" viewBox="0 0 1024 1024" version="1.1"
                     xmlns="http://www.w3.org/2000/svg" p-id="3533" width="100" height="100">
                    <path d="M512.5 128.2c51.9 0 102.2 10.1 149.5 30.2 45.7 19.3 86.8 47 122.1 82.3s63 76.4 82.3 122.1c20 47.3 30.2 97.6 30.2 149.5s-10.1 102.2-30.2 149.5c-19.3 45.7-47 86.8-82.3 122.1s-76.4 63-122.1 82.3c-47.3 20-97.6 30.2-149.5 30.2S410.3 886.1 363 866.1c-45.7-19.3-86.8-47-122.1-82.3s-63-76.4-82.3-122.1c-20-47.3-30.2-97.6-30.2-149.5s10.1-102.2 30.2-149.5c19.3-45.7 47-86.8 82.3-122.1s76.4-63 122.1-82.3c47.3-19.9 97.6-30.1 149.5-30.1m0-64c-247.4 0-448 200.6-448 448s200.6 448 448 448 448-200.6 448-448-200.6-448-448-448z"
                          fill="#fa5151" p-id="3534"></path>
                    <path d="M512.5 737.7m-50 0a50 50 0 1 0 100 0 50 50 0 1 0-100 0Z" fill="#fa5151" p-id="3535"></path>
                    <path d="M576.5 300.8c0-35.4-28.7-64.1-64-64.1s-64 28.7-64 64v0.1l24 301.6c0.5 21.6 18.2 39 40 39s39.4-17.4 40-39l24-301.6z"
                          fill="#fa5151" p-id="3536"></path>
                </svg>
                <p class="msg">获取微信二维码失败</p>
                <button class="weui-btn weui-btn_primary weui-btn_mini" @click="getQrcode">重新获取</button>
            </template>
            <template v-if="status == 3">
                <div id="qrcode"></div>
                <p class="msg">使用微信扫一扫登录</p>
            </template>
            <template v-if="status == 4">
                <svg t="1682653612574" class="icon" viewBox="0 0 1024 1024" version="1.1"
                     xmlns="http://www.w3.org/2000/svg" p-id="3533" width="100" height="100">
                    <path d="M512.5 128.2c51.9 0 102.2 10.1 149.5 30.2 45.7 19.3 86.8 47 122.1 82.3s63 76.4 82.3 122.1c20 47.3 30.2 97.6 30.2 149.5s-10.1 102.2-30.2 149.5c-19.3 45.7-47 86.8-82.3 122.1s-76.4 63-122.1 82.3c-47.3 20-97.6 30.2-149.5 30.2S410.3 886.1 363 866.1c-45.7-19.3-86.8-47-122.1-82.3s-63-76.4-82.3-122.1c-20-47.3-30.2-97.6-30.2-149.5s10.1-102.2 30.2-149.5c19.3-45.7 47-86.8 82.3-122.1s76.4-63 122.1-82.3c47.3-19.9 97.6-30.1 149.5-30.1m0-64c-247.4 0-448 200.6-448 448s200.6 448 448 448 448-200.6 448-448-200.6-448-448-448z"
                          fill="#fa5151" p-id="3534"></path>
                    <path d="M512.5 737.7m-50 0a50 50 0 1 0 100 0 50 50 0 1 0-100 0Z" fill="#fa5151" p-id="3535"></path>
                    <path d="M576.5 300.8c0-35.4-28.7-64.1-64-64.1s-64 28.7-64 64v0.1l24 301.6c0.5 21.6 18.2 39 40 39s39.4-17.4 40-39l24-301.6z"
                          fill="#fa5151" p-id="3536"></path>
                </svg>
                <p class="msg">二维码已失效</p>
                <button class="weui-btn weui-btn_primary weui-btn_mini" @click="getQrcode">重新获取</button>
            </template>
            <template v-if="status == 5">
                <svg class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1429"
                     width="100" height="100">
                    <path d="M511.396232 14.306221c278.295908-0.465617 500.87635 220.72309 500.850767 497.724479-0.025583 277.006506-222.646959 498.179863-500.917284 497.657962-277.651207-0.516784-498.66083-220.144906-499.571598-496.450427C10.847349 236.436396 232.818908 14.776955 511.396232 14.306221zM947.137321 528.158457c8.872313-226.070014-165.161125-437.629141-417.715045-448.967691-234.052026-10.504532-442.234148 170.620617-452.441913 416.66101-9.378864 226.105831 165.125309 437.624024 417.612712 448.957458C728.501834 955.308649 936.428122 774.311418 947.137321 528.158457z"
                          p-id="1430" fill="#07c160"></path>
                    <path d="M286.58492 511.80045c47.92277 38.134573 94.371938 75.097427 141.03089 112.224014 96.700025-126.990736 226.622615-214.777515 351.106181-311.503123 6.134892 8.202029 11.604617 15.513756 17.949293 23.986968-147.549533 125.36875-244.786809 286.272803-328.551881 450.46176-80.132234-75.941678-161.26222-152.835057-245.032409-232.23049C247.631679 538.146205 266.13869 525.625703 286.58492 511.80045z"
                          p-id="1431" fill="#07c160"></path>
                </svg>
                <p class="msg">扫码成功</p>
                <p class="msg">请点击确认登录</p>
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

    .weui-btn {
        position: relative;
        display: block;
        width: 184px;
        margin-left: auto;
        margin-right: auto;
        padding: 8px 24px;
        box-sizing: border-box;
        font-weight: 700;
        font-size: 17px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        line-height: 1.88235294;
        border-radius: 8px;
        -webkit-tap-highlight-color: rgba(0,0,0,0);
        -webkit-user-select: none;
        user-select: none;
        border: 0;
    }

    .weui-btn_primary {
        background-color: #07c160;
    }

    .weui-btn_mini {
        display: inline-block;
        width: auto;
        line-height: calc((32 - 10) / 16);
        padding: 5px 12px;
        font-size: 16px;
        border-radius: 6px;
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
                redirect_url: "{$redirect_url}",
                appid: "{$appid}",
                status: 0,// 1拉取中 2拉取失败 3二维码生效中 5已扫码 4二维码已失效
            },
            mounted: function () {
                this.getQrcode()
            },
            methods: {
                getQrcode: function () {
                    this.status = 1
                    let that = this
                    if (this.intervalId) {
                        clearInterval(this.intervalId)
                    }
                    if (this.timeoutId) {
                        clearTimeout(this.timeoutId)
                    }
                    this.httpGet("{:api_url('/wechat/login.OfficeScanLogin/getLoginCode')}", {
                        appid: this.appid,
                    }, function (res) {
                        if (res.status) {
                            that.code = res.data.code
                            that.qrcode = res.data.qrcode
                            that.ttl = res.data.ttl
                            that.timoutId = setTimeout(that.doExipredCode, that.ttl * 1000)
                            that.intervalId = setInterval(that.checkCode, that.checkCodeRepeatTime)
                            that.status = 3
                            // 生成二维码
                            that.$nextTick(function () {
                                let el = document.getElementById("qrcode")
                                el.innerHTML = '';
                                let config = {
                                    text: res.data.qrcode,
                                    width: 200,
                                    height: 200,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.L,
                                }
                                new QRCode(el, config)
                            })
                        } else {
                            that.status = 2
                        }
                    })
                },
                checkCode: function () {
                    if (!this.code) return
                    let that = this
                    this.httpGet("{:api_url('/wechat/login.OfficeScanLogin/checkCode')}", {code: this.code}, function (res) {
                        if (res.status) {
                            if (res.data.token === '') {
                                that.status = 5
                                return
                            }
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
                },
                // 使二维码过期
                doExipredCode: function(){
                    this.code = ''
                    if (this.intervalId) {
                        clearInterval(this.intervalId)
                    }
                    if (this.timeoutId) {
                        clearTimeout(this.timeoutId)
                    }
                    this.status = 4
                }
            }
        })
    });
</script>


</body>

</html>
