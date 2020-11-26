<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>消息模板列表</span>
            </div>
            <div>
                <el-form :inline="true" :model="searchData" class="demo-form-inline">
                    <el-form-item label="appid">
                        <el-input v-model="searchData.app_id" placeholder="请输入小程序appid"></el-input>
                    </el-form-item>
                    <el-form-item label="名称">
                        <el-input v-model="searchData.title" placeholder="请输入模板消息名称"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="searchEvent">查询</el-button>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="syncEvent">同步模板消息</el-button>
                    </el-form-item>
                </el-form>
            </div>
            <div>
                <el-table
                        :data="users"
                        border
                        style="width: 100%">
                    <el-table-column
                            prop="app_id"
                            align="center"
                            label="appid"
                            min-width="180">
                    </el-table-column>
                    <el-table-column
                            prop="title"
                            label="名称"
                            align="center"
                            min-width="120">
                    </el-table-column>
                    <el-table-column
                            prop="template_id"
                            label="template_id"
                            align="center"
                            min-width="180">
                    </el-table-column>
                    <el-table-column
                            label="内容"
                            align="center"
                            min-width="180">
                        <template slot-scope="scope">
                            <pre style="text-align: left;">{{scope.row.content}}</pre>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="示例"
                            align="center"
                            min-width="200">
                        <template slot-scope="scope">
                            <pre style="text-align: left;">{{scope.row.example}}</pre>
                        </template>
                    </el-table-column>
                    <el-table-column
                            fixed="right"
                            label="操作"
                            align="center"
                            width="220">
                        <template slot-scope="scope">
                            <el-button @click="testSendEvent(scope.row)" type="primary">发送测试</el-button>
                            <el-button @click="deleteEvent(scope.row)" type="danger">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="page-container">
                <el-pagination
                        background
                        :page-size="limit"
                        :page-count="totalPages"
                        :current-page="page"
                        :total="totalItems"
                        layout="prev, pager, next"
                        @current-change="currentChangeEvent">
                </el-pagination>
            </div>
        </el-card>
        <div>
            <el-dialog
                    title="发送模板消息测试"
                    :visible.sync="showDialogVisible"
                    width="500px">
                <div>
                    <el-form label-width="100px">
                        <el-form-item label="template_id">
                            {{ sendTestTemplate.template_id }}
                        </el-form-item>
                        <el-form-item label="用户openid">
                            <el-input v-model="touserOpenid" placeholder="请输入接收消息用户openid"></el-input>
                        </el-form-item>
                        <el-form-item label="跳转类型">
                            <el-radio v-model="templatePageType" label="web">网页</el-radio>
                            <el-radio v-model="templatePageType" label="mini">小程序</el-radio>
                        </el-form-item>
                        <el-form-item v-if="templatePageType =='web'" label="跳转路径">
                            <el-input v-model="templatePagePath" placeholder="请输入模板消息跳转网页路径"></el-input>
                        </el-form-item>
                        <el-form-item v-if="templatePageType =='mini'" label="小程序appid">
                            <el-input v-model="templateMiniAppid" placeholder="请输入模板消息跳转小程序appid"></el-input>
                        </el-form-item>
                        <el-form-item v-if="templatePageType =='mini'" label="小程序路径">
                            <el-input v-model="templatePagePath" placeholder="请输入模板消息跳转小程序路径"></el-input>
                        </el-form-item>
                        <el-form-item v-for="(item,index) in keywords" :label="item.title">
                            <el-input v-model="item.value"></el-input>
                        </el-form-item>
                    </el-form>
                </div>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="showDialogVisible = false">取 消</el-button>
                    <el-button type="primary" @click="submitTestTemplateEvent">确认发送</el-button>
                </div>
            </el-dialog>
        </div>
    </div>
    <style>
        .avatar {
            width: 60px;
            height: 60px;
        }

        .page-container {
            margin-top: 0px;
            text-align: center;
            padding: 10px;
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: "#app",
                data: {
                    searchData: {
                        title: "",
                        app_id: ""
                    },
                    keywords: [],
                    showDialogVisible: false,
                    users: [],
                    page: 1,
                    limit: 10,
                    totalPages: 0,
                    totalItems: 0,
                    sendTestTemplate: {},
                    touserOpenid: "",
                    templatePagePath: '',
                    templatePageType: 'web',
                    templateMiniAppid: ''

                },
                mounted:function() {
                    this.getTemplateList();
                },
                methods: {
                    submitTestTemplateEvent:function() {
                        if (!this.touserOpenid) {
                            this.$message.error('请输入接收用户的openid');
                            return;
                        }
                        if (!this.templatePagePath) {
                            this.$message.error('请输入跳转链接');
                            return;
                        }
                        const postData = {
                            keywords: this.keywords,
                            touser_openid: this.touserOpenid,
                            app_id: this.sendTestTemplate.app_id,
                            template_id: this.sendTestTemplate.template_id,
                            page: this.templatePagePath,
                            page_type: this.templatePageType,
                            mini_appid: this.templateMiniAppid
                        };
                        const _this = this;
                        this.httpPost("{:api_url('/wechat/office/sendTemplateMsg')}", postData, function (res) {
                            if (res.status) {
                                _this.$message.success('发送成功');
                            } else {
                                _this.$message.error(res.msg)
                            }
                        })
                    },
                    testSendEvent:function(row) {
                        this.showDialogVisible = true;
                        this.sendTestTemplate = row;
                        const example = row.example;
                        const exampleArray = example.split("\n");
                        const keywords = [];
                        for (const index in exampleArray) {
                            console.log('index', index)
                            if (exampleArray[index]) {
                                if (index === '0') {
                                    keywords.push({
                                        title: 'first',
                                        key: 'first',
                                        example: exampleArray[index],
                                        value: exampleArray[index]
                                    })
                                } else if (index === ((exampleArray.length - 1) + '')) {
                                    keywords.push({
                                        title: 'remark',
                                        key: 'remark',
                                        example: exampleArray[index],
                                        value: exampleArray[index]
                                    })
                                } else {
                                    const splitStr = exampleArray[index].split("：");
                                    keywords.push({
                                        title: splitStr[0],
                                        key: 'keyword' + (parseInt(index)),
                                        example: splitStr[1],
                                        value: splitStr[1]
                                    })
                                }

                            }
                        }
                        this.keywords = keywords;
                        console.log('keywords', keywords)
                    },
                    syncEvent:function() {
                        var _this = this;
                        this.httpGet("{:api_url('/wechat/office/syncTemplateList')}", {}, function (res) {
                            if (res.status) {
                                _this.$message.success("同步成功");
                                _this.getTemplateList();
                            } else {
                                _this.$message.error(res.msg);
                            }
                        })
                    },
                    deleteEvent:function(row) {
                        var postData = {
                            id: row.id
                        };
                        console.log('callback', postData);
                        var _this = this;
                        this.$confirm('是否确认删除该记录', '提示', {
                            callback: function (e) {
                                if (e !== 'confirm') {
                                    return;
                                }
                                _this.httpPost('{:api_url("/wechat/office/deleteTemplate")}', postData, function (res) {
                                    if (res.status) {
                                        _this.$message.success('删除成功');
                                        _this.getTemplateList();
                                    } else {
                                        _this.$message.error(res.msg);
                                    }
                                })
                            }
                        });

                    },
                    searchEvent:function() {
                        this.page = 1;
                        this.getTemplateList();
                    },
                    currentChangeEvent:function(page) {
                        this.page = page;
                        this.getTemplateList();
                    },
                    getTemplateList: function () {
                        var _this = this;
                        var where = Object.assign({
                            page: this.page,
                            limit: this.limit
                        }, this.searchData);
                        $.ajax({
                            url: "{:api_url('/wechat/office/templateList')}",
                            dataType: 'json',
                            type: 'get',
                            data: where,
                            success: function (res) {
                                console.log("res", res);
                                if (res.status) {
                                    _this.users = res.data.data;
                                    _this.page = res.data.current_page;
                                    _this.limit = res.data.per_page;
                                    _this.totalPages = res.data.last_page;
                                    _this.totalItems = res.data.total
                                }
                            }
                        })
                    }
                }
            })
        });
    </script>
</div>