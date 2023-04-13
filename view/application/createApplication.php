<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item><a href="{:url('wechat/application/index')}">应用列表</a></el-breadcrumb-item>
                    <el-breadcrumb-item>{{form.id>0?'编辑应用':'添加应用'}}</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
            <el-form style="width: 800px" :model="form" label-width="150px">
                <el-form-item label="名称" required>
                    <el-input v-model="form.application_name"></el-input>
                </el-form-item>
                <el-form-item label="类型" required>
                    <el-radio-group v-model="form.account_type">
                        <el-radio label="office">公众号</el-radio>
                        <el-radio label="mini">小程序</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="应用别名" required>
                    <el-input v-model="form.alias"></el-input>
                    <div class="el-tip">调用时方法时使用，避免更换了app_id需要修改代码</div>
                </el-form-item>
                <el-form-item label="app_id" required>
                    <el-input v-model="form.app_id"></el-input>
                </el-form-item>
                <el-form-item label="secret" required>
                    <el-input v-model="form.secret"></el-input>
                </el-form-item>
                <el-form-item v-if="form.account_type == 'office'" label="令牌(Token)">
                    <el-input v-model="form.token"></el-input>
                    <div class="el-tip">接受服务推送消息需要配置令牌token(从微信公众号『开发-基本配置』-『服务器配置』中获取)</div>
                </el-form-item>
                <el-form-item v-if="form.account_type == 'office'" label="消息加解密密钥(EncodingAESKey)">
                    <el-input v-model="form.aes_key"></el-input>
                    <div class="el-tip">消息加解密密钥(从微信公众号『开发-基本配置』-『服务器配置』中获取)</div>
                </el-form-item>
                <el-form-item label="微信支付商户号">
                    <el-input v-model="form.mch_id" placeholder="请输入微信支付商户号（mch_id）"></el-input>
                </el-form-item>
                <el-form-item label="微信支付APIv2密钥">
                    <el-input v-model="form.mch_key" placeholder="请输入API v2 密钥"></el-input>
                </el-form-item>
                <el-form-item label="微信支付API证书公钥">
                    <el-input type="textarea"
                              v-model="form.cert_path"
                              :autosize="{ minRows: 2, maxRows: 6 }"
                              placeholder="请输入证书公钥（Certificate），登录商户平台下载 API 证书"></el-input>
                </el-form-item>
                <el-form-item label="微信支付API证书私钥">
                    <el-input type="textarea"
                              v-model="form.key_path"
                              :autosize="{ minRows: 2, maxRows: 6 }"
                              placeholder="请输入证书私钥(Private Key)，登录商户平台下载 API 证书"></el-input>
                </el-form-item>
                <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                    <el-button type="primary" @click="submitEvent">确定</el-button>
                    <el-button type="default" @click="cancelEvent">取消</el-button>
                </el-form-item>
            </el-form>
        </el-card>
    </div>
    <script>
        $(document).ready(function () {
            new Vue({
                el: "#app",
                data: {
                    form: {
                        id: '',
                        application_name: "",
                        account_type: "office",
                        app_id: "",
                        secret: "",
                        mch_id: "",
                        mch_key: "",
                        cert_path: "",
                        key_path: "",
                        token: "",
                        aes_key: ""
                    },
                },
                mounted: function () {
                    this.form.id = this.getUrlQuery('id');
                    this.getDetail()
                },
                methods: {
                    cancelEvent: function () {
                        location.href = "{:api_url('/wechat/application/index')}";
                    },
                    uploadSuccessKey: function (res) {
                        if (res.status) {
                            this.form.key_path = this.key_path
                        } else {
                            layer.msg(res.msg)
                        }
                    },
                    uploadSuccessCert: function (res) {
                        if (res.status) {
                            this.form.cert_path = this.cert_path
                        } else {
                            layer.msg(res.msg)
                        }
                    },
                    getDetail: function () {
                        var _this = this;
                        if (!this.form.id) {
                            return
                        }
                        $.ajax({
                            url: "{:api_url('/wechat/application/getApplicationDetail')}",
                            data: {
                                id: _this.form.id
                            },
                            dataType: 'json',
                            type: 'get',
                            success: function (res) {
                                console.log("res", res);
                                if (res.status) {
                                    _this.form = res.data;
                                }
                            }
                        })
                    },
                    submitEvent: function () {
                        var _this = this;
                        $.ajax({
                            url: "{:api_url('/wechat/application/createApplication')}",
                            data: _this.form,
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                if (res.status) {
                                    layer.msg('操作成功');
                                    setTimeout(function () {
                                        window.parent.layer.closeAll();
                                        location.href = "{:api_url('/wechat/application/index')}";
                                    }, 2000);
                                } else {
                                    layer.msg(res.msg)
                                }
                            }
                        })
                    }
                }
            })
        });
    </script>
</div>