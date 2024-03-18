<div>
    <div id="app" v-cloak style="width: 80%; margin: 0 auto;">
        <!--线上版本 S-->
        <el-card>
            <div slot="header" class="clearfix">
                <span>线上版本</span>
            </div>
            <template v-if="release_info">
                <div class="version_content">
                    <div class="version_code">
                        <p class="text-secondary">版本号</p>
                        <p style="font-size: 20px;padding-top: 14px;">{{ release_info.version }}</p>
                    </div>
                    <div class="submit_info">
                        <div class="submit_info-item"><label
                                    class="submit_info-title text-secondary">发布时间</label><span
                                    class="submit_info-value">{{ release_info.time }}</span>
                        </div>
                        <div class="submit_info-item"><label class="submit_info-title text-secondary">备注</label><span
                                    class="submit_info-value">{{ release_info.desc }}</span></div>
                    </div>
                    <div class="action">
                        <p>
                            <el-button type="text" @click="handleRevertCodeRelease">版本回退</el-button>
                        </p>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="text-secondary text-center">暂无线上版本</div>
            </template>

        </el-card>
        <!--线上版本 E-->

        <!--审核版本 S-->
        <el-card>
            <div slot="header" class="clearfix">
                <span>审核版本</span>
            </div>
            <template v-if="audit_info">
                <div class="version_content">
                    <div class="version_code">
                        <p class="text-secondary">版本号</p>
                        <p style="font-size: 20px;padding-top: 14px;">{{ audit_info.user_version }}</p>
                    </div>
                    <div class="submit_info">
                        <div class="submit_info-item"><label class="submit_info-title text-secondary">审核状态</label>
                            <span class="submit_info-value">
                                <template v-if="audit_info.status == 0">审核成功</template>
                                <template v-if="audit_info.status == 1">审核被拒绝</template>
                                <template v-if="audit_info.status == 2">审核中</template>
                                <template v-if="audit_info.status == 3">已撤回</template>
                                <template v-if="audit_info.status == 4">审核延后</template>
                            </span>
                        </div>
                        <template v-if="audit_info.status == 1">
                            <div class="submit_info-item">
                                <label class="submit_info-title text-secondary">拒绝原因</label>
                                <span class="submit_info-value">{{ audit_info.reason }}</span>
                            </div>
                        </template>
                        <div class="submit_info-item">
                            <label class="submit_info-title text-secondary">提审时间</label>
                            <span class="submit_info-value">{{ audit_info.submit_audit_time }}</span>
                        </div>
                        <div class="submit_info-item">
                            <label class="submit_info-title text-secondary">备注</label>
                            <span class="submit_info-value">{{ audit_info.user_desc }}</span>
                        </div>
                    </div>
                    <div class="action">
                        <p v-if="audit_info.status == 0">
                            <el-button type="text" @click="handleRelease">提交发布</el-button>
                        </p>
                        <p v-if="audit_info.status == 2">
                            <el-button type="text" @click="handleUndoAudit">撤回审核</el-button>
                        </p>
                        <p v-if="audit_info.status == 2">
                            <el-button type="text" @click="handleSpeedupCodeAudit">加急审核</el-button>
                        </p>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="text-secondary text-center">暂无审核版本</div>
            </template>
        </el-card>
        <!--审核版本 E-->

        <!--开发版本 S-->
        <el-card>
            <div slot="header" class="clearfix">
                <span>开发版本</span>
            </div>
            <template v-if="exp_info">
                <div class="version_content">
                    <div class="version_code">
                        <p class="text-secondary">版本号</p>
                        <p style="font-size: 20px;padding-top: 14px;">{{ exp_info.version }}</p>
                    </div>
                    <div class="submit_info">
                        <div class="submit_info-item"><label
                                    class="submit_info-title text-secondary">发布时间</label><span
                                    class="submit_info-value">{{ exp_info.time }}</span>
                        </div>
                        <div class="submit_info-item"><label class="submit_info-title text-secondary">备注</label><span
                                    class="submit_info-value">{{ exp_info.desc }}</span></div>
                    </div>
                    <div class="action">
                        <p>
                            <el-button type="text" @click="handleSubmitCode">上传代码</el-button>
                        </p>
                        <p>
                            <el-button type="text" @click="handleGetTrialQRCode">体验版二维码</el-button>
                        </p>
                        <p>
                            <el-button type="text" @click="handleSubmitAudit">提交审核</el-button>
                        </p>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="text-secondary text-center">尚未提交体验版</div>
                <div class="text-center">
                    <el-button type="primary" size="mini" @click="handleSubmitCode">立即上传</el-button>
                </div>
            </template>

        </el-card>
        <!--开发版本 E-->
    </div>

</div>

<script>
    $(document).ready(function () {
        // 体验版二维码缓存
        let cacheTrialQRCode = null
        new Vue({
            el: "#app",
            data: {
                authorizer_appid: '',
                exp_info: null,
                release_info: null,
                audit_info: null,
            },
            mounted: function () {
                this.authorizer_appid = this.getUrlQuery('authorizer_appid');
                this.getVersionInfo()
            },
            methods: {
                getVersionInfo: function () {
                    let that = this
                    const data = {
                        _action: 'getVersionInfo',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpGet("/wechat/open.MiniProgramCodeAdmin/version", data, function (res) {
                        that.exp_info = res.data.exp_info
                        that.release_info = res.data.release_info
                        that.audit_info = res.data.audit_info
                    })
                },
                // 上传代码
                handleSubmitCode: function () {
                    let that = this
                    layer.open({
                        type: 2,
                        title: '',
                        content: "{:api_url('wechat/open.MiniProgramCodeAdmin/submitCode')}?authorizer_appid=" + this.authorizer_appid,
                        area: ['70%', '80%'],
                        end: function () {
                            that.getVersionInfo()
                        }
                    })
                },
                // 获取体验二维码
                handleGetTrialQRCode: function () {
                    if (cacheTrialQRCode) {
                        this.previewImage('体验版二维码', cacheTrialQRCode)
                        return
                    }
                    let that = this
                    const data = {
                        _action: 'getTrialQRCode',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpGet("/wechat/open.MiniProgramCodeAdmin/version", data, function (res) {
                        if (!res.status) {
                            layer.msg(res.msg)
                        } else {
                            cacheTrialQRCode = res.data.img_url
                            that.previewImage('体验版二维码', cacheTrialQRCode)
                        }
                    })
                },
                previewImage: function (img_alt, img_url) {
                    layer.photos({
                        photos: {
                            "title": "", //相册标题
                            "id": 1, //相册id
                            "start": 0, //初始显示的图片序号，默认0
                            "data": [   //相册包含的图片，数组格式
                                {
                                    "alt": img_alt,
                                    "pid": 1, //图片id
                                    "src": img_url, //原图地址
                                    "thumb": img_url, //缩略图地址
                                }
                            ]
                        }
                        , anim: 5, //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
                    })
                },
                // 撤回审核
                handleUndoAudit: function () {
                    let that = this
                    layer.confirm('是否撤回审核？', {title: '提示'}, function (index) {
                        layer.close(index);
                        that.doUndoAudit()
                    });
                },
                doUndoAudit: function () {
                    if (!this.audit_info) return
                    let that = this
                    const data = {
                        _action: 'undoAudit',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/version", data, function (res) {
                        if (!res.status) {
                            layer.msg(res.msg)
                        } else {
                            that.getVersionInfo()
                        }
                    })
                },
                // 加速审核
                handleSpeedupCodeAudit: function () {
                    let that = this
                    layer.confirm('是否确认加速审核？加速额度有限，请务必慎用!', {title: '提示'}, function (index) {
                        layer.close(index);
                        that.doSpeedupCodeAudit()
                    });
                },
                doSpeedupCodeAudit: function () {
                    if (!this.audit_info) return
                    let that = this
                    const data = {
                        _action: 'speedupCodeAudit',
                        authorizer_appid: this.authorizer_appid,
                        auditid: this.audit_info.auditid,
                    }
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/version", data, function (res) {
                        layer.msg(res.msg)
                    })
                },
                // 确认发布
                handleRelease: function () {
                    let that = this
                    layer.confirm('是否确认发布？', {title: '提示'}, function (index) {
                        layer.close(index);
                        that.doRelease()
                    });
                },
                doRelease: function () {
                    if (!this.audit_info) return
                    let that = this
                    const data = {
                        _action: 'release',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/version", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.getVersionInfo()
                        }
                    })
                },
                // 回滚版本
                handleRevertCodeRelease: function () {
                    let that = this
                    layer.confirm('是否确认回滚到上一个版本？', {title: '提示'}, function (index) {
                        layer.close(index);
                        that.doRevertCodeRelease()
                    });
                },
                doRevertCodeRelease: function () {
                    let that = this
                    const data = {
                        _action: 'revertCodeRelease',
                        authorizer_appid: this.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/version", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.getVersionInfo()
                        }
                    })
                },
                // 提交审核
                handleSubmitAudit: function () {
                    let that = this
                    layer.open({
                        type: 2,
                        title: '',
                        content: "{:api_url('wechat/open.MiniProgramCodeAdmin/submitAudit')}?authorizer_appid=" + this.authorizer_appid,
                        area: ['70%', '80%'],
                        end: function () {
                            that.getVersionInfo()
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

    .text-regular {
        color: #606266;
    }

    .text-center {
        text-align: center;
    }

    .text-secondary {
        color: #909399;
    }

    .el-card {
        margin-bottom: 24px;
    }

    .version_content {
        display: flex;
    }

    .version_content .version_code {
        min-width: 180px;
    }

    .version_content .submit_info {
        flex: 1
    }

    .version_content .submit_info .submit_info-item {
        padding-top: 14px;
    }

    .version_content .submit_info .submit_info-item:first-child {
        padding-top: 0;
    }

    .version_content .submit_info .submit_info-title {
        display: inline-block;
        width: 80px;
    }

    .action .el-button {
        padding-top: 6px;
        padding-bottom: 6px;
        min-width: 100px;
        text-align: left;
    }
</style>