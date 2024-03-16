<script src="/statics/admin/qrcode/qrcode.js"></script>
<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>欢迎使用微信第三方开放平台</span>
        </div>

        <div style="margin-bottom: 20px;">
            <el-steps :active="3" style="max-width: 360px">
                <el-step title="配置" description="开放平台参数设置"></el-step>
                <el-step title="授权" description="用户扫码授权"></el-step>
                <el-step title="开发" description="🚀 Happy Hacking"></el-step>
            </el-steps>
        </div>

        <el-divider></el-divider>

        <div>
            <h3>PC授权入口</h3>
            <div style="display: flex;padding-bottom: 12px;">
                <div style="margin: 0 12px;text-align: center;">
                    <div id="auth_pc_all_url"></div>
                    <div><p style="padding-top: 12px">公众号+小程序</p></div>
                </div>
                <div style="margin: 0 12px;text-align: center;">
                    <div id="auth_pc_office_url"></div>
                    <div><p style="padding-top: 12px">仅公众号</p></div>
                </div>
                <div style="margin: 0 12px;text-align: center;">
                    <div id="auth_pc_mini_url"></div>
                    <div><p style="padding-top: 12px">仅小程序</p></div>
                </div>
            </div>
            <div><p style="padding-bottom: 4px">公众号+小程序：{{ urls.auth_pc_all_url }}</p></div>
            <div><p style="padding-bottom: 4px">仅公众号：{{ urls.auth_pc_office_url }}</p></div>
            <div><p style="padding-bottom: 4px">仅小程序：{{ urls.auth_pc_mini_url }}</p></div>

            <h3>手机版授权入口</h3>
            <div style="display: flex;padding-bottom: 12px;">
                <div style="margin: 0 12px;text-align: center;">
                    <div id="auth_h5_all_url"></div>
                    <div><p style="padding-top: 12px">公众号+小程序</p></div>
                </div>
                <div style="margin: 0 12px;text-align: center;">
                    <div id="auth_h5_office_url"></div>
                    <div><p style="padding-top: 12px">仅公众号</p></div>
                </div>
                <div style="margin: 0 12px;text-align: center;">
                    <div id="auth_h5_mini_url"></div>
                    <div><p style="padding-top: 12px">仅小程序</p></div>
                </div>
            </div>
            <div><p style="padding-bottom: 4px">公众号+小程序：{{ urls.auth_h5_all_url }}</p></div>
            <div><p style="padding-bottom: 4px">仅公众号：{{ urls.auth_h5_office_url }}</p></div>
            <div><p style="padding-bottom: 4px">仅小程序：{{ urls.auth_h5_mini_url }}</p></div>
        </div>
    </el-card>
</div>
<style>
    p {
        margin: 0;
    }
</style>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                urls: {
                    auth_pc_all_url: '{$auth_pc_all_url|raw}',
                    auth_pc_office_url: '{$auth_pc_office_url|raw}',
                    auth_pc_mini_url: '{$auth_pc_mini_url|raw}',
                    auth_h5_all_url: '{$auth_h5_all_url|raw}',
                    auth_h5_office_url: '{$auth_h5_office_url|raw}',
                    auth_h5_mini_url: '{$auth_h5_mini_url|raw}',
                }
            },
            mounted: function () {
                this.initQrcode()
            },
            methods: {
                initQrcode: function () {
                    let that = this
                    let config = {
                        text: "",
                        width: 128,
                        height: 128,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    }

                    let keys = ['auth_pc_all_url', 'auth_pc_office_url', 'auth_pc_mini_url', 'auth_h5_all_url', 'auth_h5_office_url', 'auth_h5_mini_url']
                    keys.forEach(function (key) {
                        config['text'] = that.urls[key]
                        new QRCode(document.getElementById(key), config)
                    })
                },
            }
        });
    })
</script>
