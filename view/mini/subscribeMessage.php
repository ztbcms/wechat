<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>小程序消息模板列表</span>
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
                    <el-button type="primary" @click="searchEvent">筛选</el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="doSync">同步模板消息</el-button>
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
                        label="类型"
                        align="center">
                    <template slot-scope="scope">
                        <template v-if="scope.row.type == 2">
                            一次性订阅
                        </template>
                        <template v-if="scope.row.type == 3">
                            长期订阅
                        </template>
                    </template>
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
</div>
<style>
    .page-container {
        margin-top: 0;
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
                templatePagePath: ''
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                testSendEvent: function (row) {
                    var that = this;
                    layer.open({
                        type: 2,
                        title: '操作',
                        content: "{:api_url('/wechat/Mini/testSend')}?id=" + row.id,
                        area: ['90%', '90%']
                    })
                },
                doSync: function () {
                    var that = this;
                    this.httpGet("{:api_url('/wechat/Mini/subscribeMessage')}", {
                        action : 'doSync'
                    }, function (res) {
                        if (res.status) {
                            that.$message.success("同步成功");
                            that.getList();
                        } else {
                            that.$message.error(res.msg);
                        }
                    })
                },
                deleteEvent: function (row) {
                    var postData = {
                        id: row.id,
                        action : 'deleteTemplate'
                    };
                    var _this = this;
                    this.$confirm('是否确认删除该记录', '提示', {
                        callback: function (e) {
                            if (e !== 'confirm') {
                                return;
                            }
                            _this.httpPost("{:api_url('/wechat/Mini/subscribeMessage')}", postData, function (res) {
                                if (res.status) {
                                    _this.$message.success('删除成功');
                                    _this.getList();
                                } else {
                                    _this.$message.error(res.msg);
                                }
                            })
                        }
                    });

                },
                searchEvent: function () {
                    this.page = 1;
                    this.getList();
                },
                currentChangeEvent: function (page) {
                    this.page = page;
                    this.getList();
                },
                getList: function () {
                    var _this = this;
                    var where = Object.assign({
                        page: this.page,
                        limit: this.limit,
                        action : 'ajaxList'
                    }, this.searchData);
                    $.ajax({
                        url: "{:api_url('/wechat/Mini/subscribeMessage')}",
                        dataType: 'json',
                        type: 'get',
                        data: where,
                        success: function (res) {
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
