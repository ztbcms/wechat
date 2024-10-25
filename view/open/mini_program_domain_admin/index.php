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
                        type="success"
                        :closable="false">
                    <p style="font-weight: bold">
                        请确保以下的域名均在微信开放平台-第三方平台-开发设置-服务器域名白名单中，否则会无法添加域名。
                    </p>
                </el-alert>
            </div>

            <el-tabs v-model="tabName" @tab-click="handleClickTab">
                <el-tab-pane label="服务器域名" name="server_domain">
                    <el-form :model="server_domain_form" label-width="160px" size="small" style="max-width: 500px;">
                        <el-form-item label="request 合法域名">
                            <el-input
                                    v-model="server_domain_form.requestdomain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="socket 合法域名">
                            <el-input
                                    v-model="server_domain_form.wsrequestdomain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="uploadFile 合法域名">
                            <el-input
                                    v-model="server_domain_form.uploaddomain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="downloadFile 合法域名">
                            <el-input
                                    v-model="server_domain_form.downloaddomain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="udp 合法域名">
                            <el-input
                                    v-model="server_domain_form.udpdomain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="tcp 合法域名">
                            <el-input
                                    v-model="server_domain_form.tcpdomain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>

                        <!--操作区域-->
                        <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                            <el-button type="primary" size="mini" @click="handleSubmitServerDomain">保存并提交
                            </el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane label="业务域名" name="jump_domain">
                    <el-steps :active="3" align-center style="margin-top: 12px;">
                        <el-step title="设置业务域名" description=""></el-step>
                        <el-step title="生成校验文件" description=""></el-step>
                        <el-step title="部署校验文件" description="放置在根域名目录下"></el-step>
                    </el-steps>
                    <el-divider></el-divider>
                    <h5>设置域名</h5>
                    <el-form :model="jump_domain_form" label-width="100px" size="small" style="max-width: 500px;">
                        <el-form-item label="业务域名">
                            <el-input
                                    v-model="jump_domain_form.webviewdomain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>

                        <!--操作区域-->
                        <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                            <el-button type="primary" size="mini" @click="handleSubmitJumpDomain">
                                保存并提交
                            </el-button>
                            <el-button type="success" size="mini" @click="handleGetConfirmFile">
                                生成校验文件
                            </el-button>
                        </el-form-item>

                        <el-form-item label="校验文件名" v-if="jump_domain_confirm_file.file_name">
                            <el-input
                                    v-model="jump_domain_confirm_file.file_name"
                                    type="input">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="校验文件内容" v-if="jump_domain_confirm_file.file_content">
                            <el-input
                                    v-model="jump_domain_confirm_file.file_content"
                                    type="textarea"
                                    :autosize="{minRows: 1}"
                                    placeholder="每行一个域名">
                            </el-input>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane label="DNS预解析域名" name="prefetch_domain">
                    <el-form :model="prefetch_domain_form" label-width="160px" size="small" style="max-width: 500px;">
                        <el-form-item label="DNS预解析域名">
                            <el-input
                                    v-model="prefetch_domain_form.prefetch_dns_domain"
                                    type="textarea"
                                    :autosize="{minRows: 2}"
                                    placeholder="每行一个域名">
                            </el-input>
                            <p>总共可配置域名个数: {{ prefetch_domain_form.size_limit }}</p>
                        </el-form-item>

                        <!--操作区域-->
                        <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                            <el-button type="primary" size="mini" @click="handleSubmitPrefetchDomain">保存并提交
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
                tabName: 'server_domain',
                server_domain_form: {
                    requestdomain: '',
                    wsrequestdomain: '',
                    uploaddomain: '',
                    downloaddomain: '',
                    udpdomain: '',
                    tcpdomain: '',
                },
                jump_domain_form: {
                    webviewdomain: '',
                },
                jump_domain_confirm_file: {
                    file_name: '',
                    file_content: '',
                },
                prefetch_domain_form: {
                    prefetch_dns_domain: '',
                    size_limit: '-',
                }
            },
            mounted: function () {
                this.authorizer_appid = this.getUrlQuery('authorizer_appid');
                this.getServerDomain()
            },
            methods: {
                handleClickTab: function () {
                    if (this.tabName === 'server_domain') {
                        this.getServerDomain()
                    } else if (this.tabName === 'jump_domain') {
                        this.getJumpDomain()
                    } else if (this.tabName === 'prefetch_domain') {
                        this.getPrefetchDomain()
                    }
                },
                getServerDomain: function () {
                    let that = this
                    const data = {
                        _action: 'getServerDomain',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpGet("/wechat/open.MiniProgramDomainAdmin/index", data, function (res) {
                        if (res.status) {
                            that.server_domain_form.requestdomain = res.data.requestdomain.join('\n')
                            that.server_domain_form.wsrequestdomain = res.data.wsrequestdomain.join('\n')
                            that.server_domain_form.uploaddomain = res.data.uploaddomain.join('\n')
                            that.server_domain_form.downloaddomain = res.data.downloaddomain.join('\n')
                            that.server_domain_form.udpdomain = res.data.udpdomain.join('\n')
                            that.server_domain_form.tcpdomain = res.data.tcpdomain.join('\n')
                        } else {
                            layer.alert(res.msg)
                        }
                    })
                },
                handleSubmitServerDomain: function () {
                    let that = this
                    const data = {
                        _action: 'setServerDomain',
                        authorizer_appid: this.authorizer_appid,
                        requestdomain: that.server_domain_form.requestdomain.trim().split('\n'),
                        wsrequestdomain: that.server_domain_form.wsrequestdomain.trim().split('\n'),
                        uploaddomain: that.server_domain_form.uploaddomain.trim().split('\n'),
                        downloaddomain: that.server_domain_form.downloaddomain.trim().split('\n'),
                        udpdomain: that.server_domain_form.udpdomain.trim().split('\n'),
                        tcpdomain: that.server_domain_form.tcpdomain.trim().split('\n'),
                    }
                    this.httpPost("/wechat/open.MiniProgramDomainAdmin/index", data, function (res) {
                        if (res.status) {
                            layer.msg(res.msg)
                        } else {
                            // 高亮错误的项
                            layer.alert(res.msg)
                        }
                    })
                },
                getJumpDomain: function () {
                    let that = this
                    const data = {
                        _action: 'getJumpDomain',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpGet("/wechat/open.MiniProgramDomainAdmin/index", data, function (res) {
                        if (res.status) {
                            that.jump_domain_form.webviewdomain = res.data.webviewdomain.join('\n')
                        } else {
                            layer.alert(res.msg)
                        }
                    })
                },
                handleSubmitJumpDomain: function () {
                    let that = this
                    const data = {
                        _action: 'setJumpDomain',
                        authorizer_appid: this.authorizer_appid,
                        webviewdomain: that.jump_domain_form.webviewdomain.split('\n'),
                    }
                    this.httpPost("/wechat/open.MiniProgramDomainAdmin/index", data, function (res) {
                        if (res.status) {
                            layer.msg(res.msg)
                        } else {
                            // 高亮错误的项
                            layer.alert(res.msg)
                        }
                    })
                },
                handleGetConfirmFile: function () {
                    let that = this
                    const data = {
                        _action: 'getJumpDomainConfirmFile',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.MiniProgramDomainAdmin/index", data, function (res) {
                        if (res.status) {
                            that.jump_domain_confirm_file.file_name = res.data.file_name
                            that.jump_domain_confirm_file.file_content = res.data.file_content
                        } else {
                            // 高亮错误的项
                            layer.alert(res.msg)
                        }
                    })
                },
                getPrefetchDomain: function () {
                    let that = this
                    const data = {
                        _action: 'getPrefetchDomain',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpGet("/wechat/open.MiniProgramDomainAdmin/index", data, function (res) {
                        if (res.status) {
                            let urls = res.data.prefetch_dns_domain.map(function (item) {
                                return item['url']
                            })
                            that.prefetch_domain_form.prefetch_dns_domain = urls.join('\n')
                            that.prefetch_domain_form.size_limit = res.data.size_limit
                        } else {
                            layer.alert(res.msg)
                        }
                    })
                },
                handleSubmitPrefetchDomain: function () {
                    let that = this
                    const data = {
                        _action: 'setPrefetchDomain',
                        authorizer_appid: this.authorizer_appid,
                        prefetch_dns_domain: that.prefetch_domain_form.prefetch_dns_domain.split('\n'),
                    }
                    this.httpPost("/wechat/open.MiniProgramDomainAdmin/index", data, function (res) {
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