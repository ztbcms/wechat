<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>提交审核</span>
            </div>
            <el-form :model="form" label-width="120px" size="small" style="max-width: 760px;">
                <el-form-item label="APPID">
                    <span>{{ form.authorizer_appid }}</span>
                </el-form-item>
                <el-form-item label="审核项" required>
                    <template v-for="item in itemList">
                        <div>
                            <el-checkbox v-model="item.selected">类目:{{ item.first_class }}/{{ item.second_class }}
                                <template v-if="item.third_class">/{{ item.third_class }}</template>
                            </el-checkbox>
                            <el-input v-model="item.tag" size="mini"
                                      placeholder="选填,小程序的标签，用空格分隔，标签至多 10 个，标签长度至多 20"></el-input>
                        </div>
                    </template>
                </el-form-item>
                <el-form-item label="版本说明">
                    <el-input
                            v-model="form.version_desc"
                            type="textarea"
                            :rows="4"
                            placeholder=""
                    >
                    </el-input>
                </el-form-item>
                <el-divider content-position="left">审核补充资料</el-divider>
                <el-form-item label="审核反馈内容">
                    <el-input
                            v-model="form.feedback_info"
                            type="textarea"
                            :rows="3"
                            maxlength="200"
                            show-word-limit
                            placeholder="仅上一审核版本被驳回时生效，最多200字"
                    >
                    </el-input>
                </el-form-item>
                <el-form-item label="反馈图片">
                    <el-upload
                            ref="feedbackUpload"
                            action="/wechat/open.MiniProgramCodeAdmin/submitAudit"
                            name="media"
                            accept=".png,.jpeg,.jpg,.gif"
                            :data="feedbackUploadData"
                            :file-list="feedbackStuffFiles"
                            :limit="5"
                            :before-upload="beforeFeedbackUpload"
                            :on-success="handleFeedbackUploadSuccess"
                            :on-error="handleFeedbackUploadError"
                            :on-remove="handleFeedbackRemove"
                            :on-exceed="handleFeedbackExceed"
                    >
                        <el-button size="mini" type="primary">上传反馈图片</el-button>
                        <div slot="tip" class="el-upload__tip">
                            仅上一审核版本被驳回时生效，最多5张，支持PNG/JPEG/JPG/GIF，单张不超过2MB
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="提审页面截图">
                    <el-upload
                            ref="previewImageUpload"
                            action="/wechat/open.MiniProgramCodeAdmin/submitAudit"
                            name="media"
                            accept=".png,.jpeg,.jpg,.gif"
                            :data="auditMediaUploadData"
                            :file-list="previewImageFiles"
                            :before-upload="beforePreviewImageUpload"
                            :on-success="handlePreviewImageUploadSuccess"
                            :on-error="handlePreviewImageUploadError"
                            :on-remove="handlePreviewImageRemove"
                    >
                        <el-button size="mini" type="primary">上传页面截图</el-button>
                        <div slot="tip" class="el-upload__tip">
                            用于辅助审核人员核验类目资质和页面内容，支持PNG/JPEG/JPG/GIF，单张不超过2MB
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="操作录屏">
                    <el-upload
                            ref="previewVideoUpload"
                            action="/wechat/open.MiniProgramCodeAdmin/submitAudit"
                            name="media"
                            accept=".mp4"
                            :data="auditMediaUploadData"
                            :file-list="previewVideoFiles"
                            :before-upload="beforePreviewVideoUpload"
                            :on-success="handlePreviewVideoUploadSuccess"
                            :on-error="handlePreviewVideoUploadError"
                            :on-remove="handlePreviewVideoRemove"
                    >
                        <el-button size="mini" type="primary">上传操作录屏</el-button>
                        <div slot="tip" class="el-upload__tip">
                            支持MP4，单个不超过10MB；截图和录屏的mediaid有效期为3天，请上传后及时提交
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="隐私相关接口" required>
                    <el-radio-group v-model="form.privacy_api_not_use">
                        <el-radio label="1">未使用</el-radio>
                        <el-radio label="0">使用</el-radio>
                        <el-button @click="handlePrivacyCheck" type="text">隐私接口检测</el-button>
                    </el-radio-group>
                    <p style="font-size: 12px;margin: 0px;line-height: 15px;">*
                        提审核前可通过该隐私接口检测，获取代码配置的地理位置以及其他隐私相关接口是否已经申请权限或者已经在ext.json里声明，便于开发者在提审核之前发现问题并解决问题，以提高审核通过率。</p>

                </el-form-item>

                <!--操作区域-->
                <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                    <el-button
                            type="primary"
                            size="mini"
                            :loading="submitting"
                            :disabled="uploadingCount > 0 || submitting"
                            @click="handleSubmit"
                    >确认</el-button>
                    <span v-if="uploadingCount > 0" class="upload-status">素材上传中，请稍候</span>
                </el-form-item>
            </el-form>
        </el-card>
    </div>
</div>

<style>
    p {
        margin: 0;
    }

    .upload-status {
        margin-left: 10px;
        color: #e6a23c;
        font-size: 12px;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                form: {
                    authorizer_appid: '',
                    version_desc: '',
                    feedback_info: '',
                    privacy_api_not_use: '1',
                },
                itemList: [],
                feedbackStuffFiles: [],
                previewImageFiles: [],
                previewVideoFiles: [],
                uploadingCount: 0,
                submitting: false,
            },
            computed: {
                feedbackUploadData: function () {
                    return {
                        _action: 'uploadAuditFeedbackImage',
                        authorizer_appid: this.form.authorizer_appid,
                    }
                },
                auditMediaUploadData: function () {
                    return {
                        _action: 'uploadMediaToCodeAudit',
                        authorizer_appid: this.form.authorizer_appid,
                    }
                },
            },
            mounted: function () {
                this.form.authorizer_appid = this.getUrlQuery('authorizer_appid');
                this.getCategoryList()
            },
            methods: {
                getCategoryList: function () {
                    let that = this
                    const data = {
                        _action: 'getCategoryList',
                        authorizer_appid: this.form.authorizer_appid,
                    }
                    this.httpGet("/wechat/open.MiniProgramCodeAdmin/submitAudit", data, function (res) {
                        if (res.status) {
                            res.data.forEach(function (item) {
                                that.itemList.push({
                                    first_class: item['first_class'],
                                    first_id: item['first_id'],
                                    second_class: item['second_class'],
                                    second_id: item['second_id'],
                                    third_class: item['third_class'] || '',
                                    third_id: item['third_id'] || '',
                                    selected: true,
                                    tag: '',
                                })
                            })
                        }
                    })
                },
                getFileExtension: function (fileName) {
                    const index = fileName.lastIndexOf('.')
                    return index === -1 ? '' : fileName.substring(index + 1).toLowerCase()
                },
                validateImageBeforeUpload: function (file, label) {
                    const extension = this.getFileExtension(file.name)
                    if (['png', 'jpeg', 'jpg', 'gif'].indexOf(extension) === -1) {
                        layer.alert(label + '仅支持PNG、JPEG、JPG、GIF格式')
                        return false
                    }
                    if (!file.size) {
                        layer.alert(label + '不能为空文件')
                        return false
                    }
                    if (file.size > 2 * 1024 * 1024) {
                        layer.alert(label + '大小不能超过2MB')
                        return false
                    }
                    return true
                },
                beforeFeedbackUpload: function (file) {
                    if (!this.form.authorizer_appid) {
                        layer.alert('缺少小程序APPID')
                        return false
                    }
                    if (!this.validateImageBeforeUpload(file, '审核反馈图片')) {
                        return false
                    }
                    this.startUpload(file)
                    return true
                },
                beforePreviewImageUpload: function (file) {
                    if (!this.form.authorizer_appid) {
                        layer.alert('缺少小程序APPID')
                        return false
                    }
                    if (!this.validateImageBeforeUpload(file, '提审图片')) {
                        return false
                    }
                    this.startUpload(file)
                    return true
                },
                beforePreviewVideoUpload: function (file) {
                    if (!this.form.authorizer_appid) {
                        layer.alert('缺少小程序APPID')
                        return false
                    }
                    if (this.getFileExtension(file.name) !== 'mp4') {
                        layer.alert('操作录屏仅支持MP4格式')
                        return false
                    }
                    if (!file.size) {
                        layer.alert('操作录屏不能为空文件')
                        return false
                    }
                    if (file.size > 10 * 1024 * 1024) {
                        layer.alert('操作录屏大小不能超过10MB')
                        return false
                    }
                    this.startUpload(file)
                    return true
                },
                startUpload: function (file) {
                    file.auditUploadPending = true
                    this.uploadingCount++
                },
                finishUpload: function (file) {
                    const rawFile = file.raw || file
                    if (!rawFile.auditUploadPending) {
                        return
                    }
                    rawFile.auditUploadPending = false
                    this.uploadingCount = Math.max(0, this.uploadingCount - 1)
                },
                removeFailedUpload: function (refName, file) {
                    const that = this
                    this.$nextTick(function () {
                        if (that.$refs[refName]) {
                            that.$refs[refName].handleRemove(file)
                        }
                    })
                },
                handleFeedbackUploadSuccess: function (response, file, fileList) {
                    this.finishUpload(file)
                    if (!response.status || !response.data || !response.data.media_id) {
                        layer.alert(response.msg || '审核反馈图片上传失败')
                        this.removeFailedUpload('feedbackUpload', file)
                        return
                    }
                    this.$set(file, 'mediaId', response.data.media_id)
                    this.feedbackStuffFiles = fileList.slice()
                },
                handlePreviewImageUploadSuccess: function (response, file, fileList) {
                    this.finishUpload(file)
                    if (!response.status || !response.data || !response.data.mediaid || response.data.type !== 'image') {
                        layer.alert(response.msg || '提审图片上传失败')
                        this.removeFailedUpload('previewImageUpload', file)
                        return
                    }
                    this.$set(file, 'mediaId', response.data.mediaid)
                    this.previewImageFiles = fileList.slice()
                },
                handlePreviewVideoUploadSuccess: function (response, file, fileList) {
                    this.finishUpload(file)
                    if (!response.status || !response.data || !response.data.mediaid || response.data.type !== 'video') {
                        layer.alert(response.msg || '操作录屏上传失败')
                        this.removeFailedUpload('previewVideoUpload', file)
                        return
                    }
                    this.$set(file, 'mediaId', response.data.mediaid)
                    this.previewVideoFiles = fileList.slice()
                },
                handleFeedbackUploadError: function (error, file) {
                    this.finishUpload(file)
                    layer.alert('审核反馈图片上传失败，请检查网络后重试')
                    this.removeFailedUpload('feedbackUpload', file)
                },
                handlePreviewImageUploadError: function (error, file) {
                    this.finishUpload(file)
                    layer.alert('提审图片上传失败，请检查网络后重试')
                    this.removeFailedUpload('previewImageUpload', file)
                },
                handlePreviewVideoUploadError: function (error, file) {
                    this.finishUpload(file)
                    layer.alert('操作录屏上传失败，请检查网络后重试')
                    this.removeFailedUpload('previewVideoUpload', file)
                },
                handleFeedbackRemove: function (file, fileList) {
                    this.finishUpload(file)
                    this.feedbackStuffFiles = fileList.slice()
                },
                handlePreviewImageRemove: function (file, fileList) {
                    this.finishUpload(file)
                    this.previewImageFiles = fileList.slice()
                },
                handlePreviewVideoRemove: function (file, fileList) {
                    this.finishUpload(file)
                    this.previewVideoFiles = fileList.slice()
                },
                handleFeedbackExceed: function () {
                    layer.alert('审核反馈图片最多上传5张')
                },
                getUploadedMediaIds: function (fileList) {
                    return fileList.filter(function (item) {
                        return item.status === 'success' && item.mediaId
                    }).map(function (item) {
                        return item.mediaId
                    })
                },
                handleSubmit: function () {
                    if (this.uploadingCount > 0) {
                        layer.alert('素材正在上传，请等待上传完成后再提交')
                        return
                    }
                    if (Array.from(this.form.feedback_info || '').length > 200) {
                        layer.alert('审核反馈内容最多填写200个字符')
                        return
                    }

                    const data = {
                        _action: 'submitAudit',
                        authorizer_appid: this.form.authorizer_appid,
                        version_desc: this.form.version_desc,
                        feedback_info: this.form.feedback_info,
                        privacy_api_not_use: this.form.privacy_api_not_use,
                        feedback_stuff_ids: this.getUploadedMediaIds(this.feedbackStuffFiles),
                        preview_pic_id_list: this.getUploadedMediaIds(this.previewImageFiles),
                        preview_video_id_list: this.getUploadedMediaIds(this.previewVideoFiles),
                        item_list: [],
                    }
                    this.itemList.forEach(function (item) {
                        if (item.selected) {
                            let it = {
                                first_class: item.first_class,
                                first_id: item.first_id,
                                second_class: item.second_class,
                                second_id: item.second_id,
                                tag: item.tag
                            }
                            if (item.third_class) {
                                it['third_class'] = item.third_class
                                it['third_id'] = item.third_id
                            }
                            data.item_list.push(it)
                        }
                    })
                    if (!data.item_list.length) {
                        layer.alert('请至少选择一个审核项')
                        return
                    }

                    const that = this
                    this.submitting = true
                    $.ajax({
                        url: "/wechat/open.MiniProgramCodeAdmin/submitAudit",
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function (res) {
                            if (res.status) {
                                layer.msg(res.msg)
                            } else {
                                layer.alert(res.msg)
                            }
                        },
                        error: function () {
                            layer.alert('提交审核失败，请检查网络后重试')
                        },
                        complete: function () {
                            that.submitting = false
                        }
                    })
                },
                handlePrivacyCheck: function () {
                    const data = {
                        _action: 'privacyCheck',
                        authorizer_appid: this.form.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/submitAudit", data, function (res) {
                        if (res.status) {
                            layer.msg(res.msg)
                        } else {
                            layer.alert(res.msg)
                        }
                    })
                },
            }
        })
    });
</script>
