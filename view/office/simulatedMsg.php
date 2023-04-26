<div>
    <div id="app" v-cloak>
        <el-card>
            <el-form style="width: 800px" :model="form" label-width="150px">
                <el-form-item label="公众号AppID" required>
                    <el-input v-model="form.app_id"></el-input>
                </el-form-item>
                <el-form-item label="消息内容" required>
                    <el-input type="textarea"
                              v-model="form.msg"
                              :autosize="{ minRows: 4 }"
                              placeholder=""></el-input>
                    <p>具体参数请参考公众号开发文档-
                        <a href="https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_standard_messages.html" target="_blank">接收普通消息</a>、
                        <a href="https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html" target="_blank">接收事件推送</a></p>
                    <p>消息内容示例：</p>
                    <el-input type="textarea"
                              v-model="sample"
                              :autosize="{ minRows: 4 }"
                              placeholder=""></el-input>
                </el-form-item>

                <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                    <el-button type="primary" @click="submitEvent">确定</el-button>
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
                        msg: "",
                    },
                    sample: `<xml>
  <ToUserName>gh123123</ToUserName>
  <FromUserName>wx123</FromUserName>
  <CreateTime>1348831860</CreateTime>
  <MsgType>text</MsgType>
  <Content>This is test</Content>
  <MsgId>11122</MsgId>
  <MsgDataId>111</MsgDataId>
  <Idx>2222</Idx>
</xml>`
                },
                mounted: function () { },
                methods: {
                    submitEvent: function () {
                        var _this = this;
                        var form = this.form;
                        form['_action'] = 'submit'
                        this.httpPost("{:api_url('/wechat/office/simulatedMsg')}", form, function(res){
                            if (res.status) {
                                layer.msg(res.msg);
                                setTimeout(function () {
                                    window.parent.layer.closeAll();
                                }, 2000);
                            } else {
                                layer.alert(res.msg)
                            }
                        })
                    }
                }
            })
        });
    </script>
</div>