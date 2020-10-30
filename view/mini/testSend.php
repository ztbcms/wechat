<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3></h3>
        <div class="filter-container">
            <el-form :model="form" label-width="120px">
                <el-form-item label="APPID" required>
                    <el-input v-model="form.app_id" placeholder="请输入" style="width: 400px"></el-input>
                </el-form-item>
                <el-form-item label="模板ID" required>
                    <el-input v-model="form.template_id" placeholder="请输入" style="width: 400px"></el-input>
                </el-form-item>
                <el-form-item label="用户openid" required>
                    <el-input v-model="form.open_id" placeholder="请输入" style="width: 400px"></el-input>
                </el-form-item>
                <el-form-item label="小程序跳转页面">
                    <el-input v-model="form.page" placeholder="请输入" style="width: 400px"></el-input>
                </el-form-item>
                <el-form-item label="消息参数" required>
                    <p v-for="param in form.data_param">
                        {{param.name}}：
                        <el-input v-model="param.value" placeholder="请输入" style="width: 400px"></el-input>
                    </p>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="doEdit">发送</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>

<style>
    .filter-container {
        padding-bottom: 10px;
    }

</style>
<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                form: {
                    id: "{$_GET['id']}",
                    app_id: '',
                    template_id: '',
                    open_id: "",
                    page: "",
                    data_param: []//模板变量参数
                },
                tableKey: 0,
                pictureUploadStatus: 1
            },
            watch: {},
            filters: {},
            methods: {
                doEdit: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/wechat/Mini/testSend')}&action=doEdit",
                        type: "post",
                        data: this.form,
                        dataType: "json",
                        success: function (res) {
                            layer.msg(res.msg)
                        }
                    });
                },
                getDetail: function (id) {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/wechat/Mini/testSend')}?id=" + id + "&action=getDetail",
                        type: "get",
                        dataType: "json",
                        success: function (res) {
                            if (res.status) {
                                that.form = res.data
                            } else {
                                layer.msg(res.msg)
                            }
                        }
                    });
                }
            },
            mounted: function () {
                this.getDetail(this.form.id)
            }
        })
    })
</script>
