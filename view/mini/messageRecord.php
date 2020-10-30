<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>订阅消息发送日志</span>
        </div>
        <div>
            <el-form :inline="true" :model="searchData" class="demo-form-inline">
                <el-form-item label="appid">
                    <el-input v-model="searchData.app_id" placeholder="请输入小程序appid"></el-input>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input v-model="searchData.open_id" placeholder="请输入模板消息名称"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="searchEvent">筛选</el-button>
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
                        label="appid">
                </el-table-column>
                <el-table-column
                        prop="open_id"
                        label="open_id"
                        align="center"
                        min-width="120">
                </el-table-column>
                <el-table-column
                        prop="template_id"
                        label="template_id"
                        align="center"
                        min-width="120">
                </el-table-column>
                <el-table-column
                        prop="page"
                        label="page"
                        align="center"
                        min-width="120">
                </el-table-column>

                <el-table-column
                        label="变量参数"
                        align="center"
                        min-width="180">
                    <template slot-scope="scope">
                        <p style="text-align: left;">{{scope.row.data}}</p>
                    </template>
                </el-table-column>
                <el-table-column
                        label="结果"
                        align="center"
                        min-width="200">
                    <template slot-scope="scope">
                        <p style="text-align: left;">{{scope.row.result}}</p>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="create_time"
                        label="创建时间"
                        align="center"
                        min-width="120">
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
    .avatar {
        width: 60px;
        height: 60px;
    }

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
                        url: "{:api_url('/wechat/Mini/messageRecord')}",
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