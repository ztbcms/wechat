<div>
    <div id="app" v-cloak>
        <el-card>

            <el-form style="width: 800px" :model="form" label-width="150px">
                <el-form-item label="应用APP_ID" required>
                    <el-input v-model="form.app_id"></el-input>
                    <div class="el-tip">微信公众号或小程序</div>
                </el-form-item>
                <el-form-item label="用户open_id" required>
                    <el-input v-model="form.open_id"></el-input>
                    <div class="el-tip">对应微信公众号或小程序的用户open_id</div>
                </el-form-item>
                <el-form-item label="付款金额" required>
                    <el-input v-model="form.price"></el-input>
                    <div class="el-tip">最低金额为0.01</div>
                </el-form-item>
                <el-form-item label="付款说明" required>
                    <el-input v-model="form.description"></el-input>
                    <div class="el-tip"></div>
                </el-form-item>

                <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                    <el-button type="primary" @click="onSubmit">确定</el-button>
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
                        app_id: "",
                        open_id: "",
                        price: "",
                        description: "",
                    },
                },
                mounted: function () {
                },
                methods: {
                    onSubmit: function () {
                        var that = this
                        var data = {
                            _action: 'submit',
                            form: this.form,
                        }
                        this.httpPost("{:api_url('/wechat/Wxmchpay/createMchpay')}", data, function (res) {
                            layer.msg(res.msg)
                        })
                    },
                }
            })
        });
    </script>
</div>