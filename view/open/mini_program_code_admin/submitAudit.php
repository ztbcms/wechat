<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>提交审核</span>
            </div>
            <el-form :model="form" label-width="120px" size="small" style="max-width: 560px;">
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
                form: {
                    authorizer_appid: '',
                    version_desc: '',
                    privacy_api_not_use: '1',
                },
                itemList: [
                    // {
                    //     first_class: '工具',
                    //     second_class: '查询',
                    //     third_class: '办公文件',
                    //     selected: true,
                    //     tag: '',
                    // }
                ],
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
                handleSubmit: function () {
                    const data = this.form
                    data['_action'] = 'submitAudit'
                    data['item_list'] = []
                    this.itemList.forEach(function (item, index) {
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
                            data['item_list'].push(it)
                        }
                    })
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/submitAudit", data, function (res) {
                        layer.msg(res.msg)
                    })
                },
                handlePrivacyCheck: function () {
                    const data = this.form
                    data['_action'] = 'privacyCheck'
                    this.httpPost("/wechat/open.MiniProgramCodeAdmin/submitAudit", data, function (res) {
                        if(res.status){
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

<style>
    p {
        margin: 0;
    }
</style>