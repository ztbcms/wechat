<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>小程序隐私保护指引</span>
            </div>

            <div style="margin-bottom: 20px;">
                <el-alert
                        style="margin-bottom: 15px;"
                        title=""
                        type="info"
                        :closable="false">
                    <p style="font-weight: bold">
                        参考文档 <a
                                href="https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/product/privacy_setting.html">配置小程序用户隐私保护指引</a>
                        、<a href="https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/privacy-management/setPrivacySetting.html">设置小程序用户隐私保护指引</a>
                    </p>
                    <p>说明：</p>
                    <p>
                        1、开发版指的是通过setprivacysetting接口已经配置的用户隐私保护指引内容，但是还没发布到现网，还没正式生效的版本。</p>
                    <p>2、现网版本指的是已经在小程序现网版本已经生效的用户隐私保护指引内容。</p>
                    <p>
                        3、如果小程序已有一个现网版，可以通过该接口（privacy_ver=1）直接修改owner_setting里除了ext_file_media_id之外的信息，修改后即可生效。</p>
                    <p>4、如果需要修改其他信息，则只能修改开发版（privacy_ver=2），然后提交代码审核，审核通过之后发布生效。</p>
                    <p>5、当该小程序还没有现网版的隐私保护指引时却传了privacy_ver=1，则会出现 86074 报错</p>
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
                            <div v-if="privacy_list.length > 0">
                                <p>代码检测出来的用户信息类型:</p>
                                <p>
                                    <template v-for="(item, index) in privacy_list">
                                        <span>{{ item }}</span><span>{{ privacy_desc_map[item] }}</span>
                                        <template v-if="index < privacy_list.length - 1">
                                            <span>、</span>
                                        </template>
                                    </template>
                                </p>
                            </div>
                        </el-form-item>


                        <!--操作区域-->
                        <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                            <el-button type="primary" size="mini" @click="handleSubmit">保存配置</el-button>
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
                            <div v-if="privacy_list.length > 0">
                                <p>代码检测出来的用户信息类型:</p>
                                <p>
                                    <template v-for="(item, index) in privacy_list">
                                        <span>{{ item }}</span><span>{{ privacy_desc_map[item] }}</span>
                                        <template v-if="index < privacy_list.length - 1">
                                            <span>、</span>
                                        </template>
                                    </template>
                                </p>
                            </div>
                        </el-form-item>


                        <!--操作区域-->
                        <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                            <div>
                                <el-button type="primary" size="mini" @click="handleSubmit">保存配置
                                </el-button>
                                <el-button type="success" size="mini" @click="handleSubmitAudit">提交审核
                                </el-button>
                            </div>
                            <p>* 配置后，需重新提交代码审核，审核通过且需要重新发布上线后才会在小程序端生效。</p>
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
                // 代码检测出来的用户信息类型
                privacy_list: [],
                privacy_desc_map: {},
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
                            that.privacy_list = res.data.privacy_list
                            let map = {}
                            if (res.data.privacy_desc && res.data.privacy_desc.privacy_desc_list) {
                                for (let i = 0; i < res.data.privacy_desc.privacy_desc_list.length; i++) {
                                    let item = res.data.privacy_desc.privacy_desc_list[i]
                                    map[item['privacy_key']] = item['privacy_desc']
                                }
                            }
                            that.privacy_desc_map = map

                        } else {
                            layer.alert(res.msg)
                        }
                    })
                },
                // 提交审核
                handleSubmitAudit: function () {
                    layer.open({
                        type: 2,
                        title: '',
                        content: "{:api_url('wechat/open.MiniProgramCodeAdmin/submitAudit')}?authorizer_appid=" + this.authorizer_appid,
                        area: ['70%', '80%'],
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