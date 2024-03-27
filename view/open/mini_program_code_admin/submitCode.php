<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>上传代码</span>
            </div>
            <el-form :model="form" label-width="100px" size="small" style="max-width: 500px;">
                <el-form-item label="APPID">
                    <span>{{ form.authorizer_appid }}</span>
                </el-form-item>
                <el-form-item label="代码模板" required>
                    <el-select v-model="form.template_id" placeholder="请选择代码模板" @change="onTemplateChange">
                        <el-option
                                v-for="item in templateList"
                                :key="item.template_id"
                                :label="item.user_version"
                                :value="item.template_id">
                            <p style="margin: 0">
                                <span style="margin-right: 8px">{{ item.user_version }}</span>
                                <template v-if="item.template_type == 0">
                                    <span style="margin-right: 8px">普通版</span>
                                </template>
                                <template v-else>
                                    <span style="margin-right: 8px">标准版</span>
                                </template>
                                <span>ID:{{ item.template_id }}</span>
                            </p>
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="ext.json">
                    <el-input
                            v-model="form.ext_json"
                            type="textarea"
                            :autosize="{ minRows: 4}"
                            placeholder="请输入 JSON 格式内容"
                    >
                    </el-input>
                </el-form-item>
                <el-form-item label="版本号" required>
                    <el-input
                            v-model="form.user_version"
                            placeholder=""
                    >
                    </el-input>
                </el-form-item>
                <el-form-item label="版本描述" required>
                    <el-input
                            v-model="form.user_desc"
                            placeholder=""
                    >
                    </el-input>
                </el-form-item>
                <!--操作区域-->
                <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                    <el-button type="primary" size="mini" @click="handleSubmit">确认</el-button>
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
                    authorizer_appid: '',
                    template_id: '',
                    ext_json: '',
                    user_version: '',
                    user_desc: '',
                },
                templateList: [
                    // {
                    //     template_id: '1',
                    //     user_version: '1.0.1',
                    //     template_type: 0,
                    // }
                ],
            },
            mounted: function () {
                this.form.authorizer_appid = this.getUrlQuery('authorizer_appid');
                this.getTemplateList()
                this.getLastSubmitInfo()
            },
            methods: {
                getTemplateList: function () {
                    let that = this;
                    const data = {
                        _action: 'getTemplateList',
                    }
                    this.httpGet("/wechat/open.MiniProgramCodeAdmin/submitCode", data, function (res) {
                        if(res.status){
                            that.templateList = res.data
                        }
                    })
                },
                getLastSubmitInfo: function () {
                    let that = this;
                    const data = {
                        _action: 'getLastSubmitInfo',
                        authorizer_appid: this.form.authorizer_appid
                    }
                    this.httpGet("/wechat/open.MiniProgramCodeAdmin/submitCode", data, function (res) {
                        if(res.status && res.data){
                            that.form.ext_json = res.data.ext_json || ''
                        }
                    })
                },
                handleSubmit: function () {
                    let that = this;
                    const data = this.form
                    data['_action'] = 'submitCode'
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/submitCode", data, function (res) {
                       layer.msg(res.msg)
                    })
                },
                // 模板选择时
                onTemplateChange: function(){
                    let selectItem = null
                    for(let i=0;i<this.templateList.length;i++){
                        if(this.templateList[i].template_id == this.form.template_id){
                            selectItem = this.templateList[i]
                            break
                        }
                    }
                    this.form.user_version = selectItem['user_version']
                    this.form.user_desc = 'Release ' + selectItem['user_version']
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