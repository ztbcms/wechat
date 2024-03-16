<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>账号详情</span>
            </div>
            <el-form :model="form" label-width="100px" size="small">
                <el-form-item label="名称">
                    <span>{{ form.name }}</span>
                </el-form-item>
                <el-form-item label="类型">
                    <span v-if="form.account_type == 0">公众号</span>
                    <span v-if="form.account_type == 1">小程序</span>
                </el-form-item>

                <el-form-item label="账号信息">
                    <div v-if="form.authorizer_info">
                        <p>原始 ID： {{ form.authorizer_info.user_name }}</p>
                        <p>主体名称： {{ form.authorizer_info.principal_name }}</p>
                        <p>帐号介绍： {{ form.authorizer_info.signature }}</p>

                        <!--小程序相关 S-->
                        <template v-if="form.authorizer_info.MiniProgramInfo">
                            <!--小程序配置信息 S-->
                            <p><span style="font-weight: bold;">小程序配置信息</span>： </p>
                            <div style="margin-left: 12px;">
                                <p>request合法域名： {{ form.authorizer_info.MiniProgramInfo.network.RequestDomain }}</p>
                                <p>socket合法域名： {{ form.authorizer_info.MiniProgramInfo.network.WsRequestDomain
                                    }}</p>
                                <p>uploadFile合法域名： {{ form.authorizer_info.MiniProgramInfo.network.UploadDomain
                                    }}</p>
                                <p>downloadFile合法域名： {{ form.authorizer_info.MiniProgramInfo.network.DownloadDomain
                                    }}</p>
                                <p>udp合法域名： {{ form.authorizer_info.MiniProgramInfo.network.UDPDomain }}</p>
                                <p>tcp合法域名： {{ form.authorizer_info.MiniProgramInfo.network.TCPDomain }}</p>
                            </div>
                            <!--小程序配置信息 E-->

                            <p><span style="font-weight: bold;">类目信息</span>(一级类目/二级类目)： </p>
                            <div style="margin-left: 12px;">
                                <template v-for="(item, index) in form.authorizer_info.MiniProgramInfo.categories"
                                          :key="index">
                                    <p>{{ item.first }} / {{ item.second }}</p>
                                </template>
                            </div>
                        </template>
                        <!--小程序相关 E-->

                    </div>
                </el-form-item>

                <el-form-item label="授权信息">
                    <div v-if="form.authorization_info">
                        <p>AppID： {{ form.authorization_info.authorizer_appid }}</p>
                        <p>刷新令牌： <span
                                    style="font-weight: bold;">{{ form.authorization_info.authorizer_refresh_token }}</span>
                        </p>
                        <p>权限集id列表： </p>
                        <div style="margin-left: 8px;">
                            <template v-for="(item, index) in form.authorization_info.func_info" :key="index">
                                <span>{{ item.funcscope_category.id }}</>
                            </template>
                            <p>具体参考：<a
                                        href="https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/product/third_party_authority_instructions.html">权限集列表说明</a>
                            </p>
                        </div>
                    </div>
                </el-form-item>

                <!--操作区域-->
                <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                </el-form-item>
            </el-form>
        </el-card>
    </div>

</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                authorizer_appid: '',
                form: {
                    name: '',
                    account_type: '',
                },
            },
            mounted: function () {
                this.authorizer_appid = this.getUrlQuery('authorizer_appid');
                this.getDetail()
            },
            methods: {
                getDetail: function () {
                    let that = this;
                    if (!this.authorizer_appid) {
                        return
                    }
                    const data = {
                        _action: 'getDetail',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpGet("/wechat/open.AuthorizerAdmin/detail", data, function (res) {
                        that.form = res.data
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