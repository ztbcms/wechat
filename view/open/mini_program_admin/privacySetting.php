<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>小程序域名管理</span>
            </div>

            <div style="margin-bottom: 20px;">
                <el-alert
                        style="margin-bottom: 15px;"
                        title=""
                        type="info"
                        :closable="false">
                    <p style="font-weight: bold">
                        参考文档 <a href="https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/product/privacy_setting.html">配置小程序用户隐私保护指引</a>
                        、<a href="https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/privacy-management/setPrivacySetting.html">设置小程序用户隐私保护指引</a>
                    </p>
                </el-alert>
            </div>

            <el-tabs v-model="tabName" @tab-click="handleClickTab">

                <el-tab-pane label="现网版" name="1">
                    <el-form :model="prd_form" label-width="160px" size="small" style="width: 50%;">
                        <el-form-item label="当前配置(JSON)">
                            <el-input
                                    v-model="prd_form.json"
                                    type="textarea"
                                    :autosize="{minRows: 8}"
                                    placeholder="">
                            </el-input>
                        </el-form-item>


                        <!--操作区域-->
                        <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                            <el-button type="primary" size="mini" @click="handleSubmit">保存并提交
                            </el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>

                <el-tab-pane label="开发版" name="2">
                    <el-form :model="dev_form" label-width="160px" size="small" style="width: 50%;">
                        <el-form-item label="当前配置(JSON)">
                            <el-input
                                    v-model="dev_form.json"
                                    type="textarea"
                                    :autosize="{minRows: 8}"
                                    placeholder="">
                            </el-input>
                        </el-form-item>


                        <!--操作区域-->
                        <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                            <el-button type="primary" size="mini" @click="handleSubmit">保存并提交
                            </el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>

            </el-tabs>
        </el-card>
    </div>

</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                authorizer_appid: '',
                tabName: '1',
                dev_form: {
                    json: '',
                },
                prd_form: {
                    json: '',
                },
            },
            mounted: function () {
                this.authorizer_appid = this.getUrlQuery('authorizer_appid');
                this.getPrivacySetting()
            },
            methods: {
                handleClickTab: function () {
                    this.getPrivacySetting()
                },
                getPrivacySetting: function () {
                    let that = this
                    const data = {
                        _action: 'getPrivacySetting',
                        authorizer_appid: this.authorizer_appid,
                        privacy_ver: this.tabName,
                    }
                    this.httpGet("/wechat/open.MiniProgramAdmin/privacySetting", data, function (res) {
                        if (res.status) {
                            if (that.tabName === '1') {
                                that.prd_form.json = JSON.stringify(res.data.setting, null, 4)
                            }
                            if (that.tabName === '2') {
                                that.dev_form.json = JSON.stringify(res.data.setting, null, 4)
                            }
                        } else {
                            layer.alert(res.msg)
                        }
                    })
                },
                handleSubmit: function () {
                    let that = this
                    const data = {
                        _action: 'setPrivacySetting',
                        authorizer_appid: this.authorizer_appid,
                        privacy_ver: this.tabName,
                        json: that.tabName === '1' ? this.prd_form.json : this.dev_form.json,
                    }
                    this.httpPost("/wechat/open.MiniProgramAdmin/privacySetting", data, function (res) {
                        if (res.status) {
                            layer.msg(res.msg)
                        } else {
                            // 高亮错误的项
                            layer.alert(res.msg)
                        }
                    })
                },
            }
        })
    });
</script>

<style>
    p {
        margin: 0;
    }
</style>