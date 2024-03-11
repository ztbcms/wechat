<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>授权事件日志</span>
        </div>
        <div style="margin-bottom: 20px;">
            <el-alert
                style="margin-bottom: 15px;"
                title=""
                type="info"
                :closable="false">
                <p style="font-weight: bold">授权事件URL配置： /wechat/open/wxcallback_component</p>
                <p style="font-weight: bold">⚠️注意：1、需要开启 Ticket 推送服务 2、Ticket每 10 分钟推送 1 次</p>
            </el-alert>
            <div class="filter-container" style="margin-top: 15px;">
                <el-form :inline="true" :model="searchForm" size="small">
                    <el-form-item label="推送时间">
                        <el-date-picker
                            v-model="searchForm.receive_time"
                            type="daterange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            value-format="yyyy-MM-dd"
                        >
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="">
                        <el-button type="primary" @click="search">查询</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <el-table
            :data="lists"
            highlight-current-row
            style="width: 100%">
            <el-table-column
                prop="receive_time"
                label="推送时间"
                width="180">
            </el-table-column>
            <el-table-column
                prop="authorizer_appid"
                label="授权账号 APPID"
                width="180">
            </el-table-column>
            <el-table-column
                prop="info_type"
                label="事件类型"
                width="180">
            </el-table-column>
            <el-table-column
                prop="body"
                label="推送内容"
                min-width="200">
                <template slot-scope="scope">
                    <span>{{ scope.row.body }}</span>
                </template>
            </el-table-column>
        </el-table>
        <div style="margin-top: 20px">
            <el-pagination
                background
                @current-change="currentPageChange"
                layout="prev, pager, next, total"
                :current-page="currentPage"
                :page-count="totalCount"
                :page-size="pageSize"
                :total="totalCount">
            </el-pagination>
        </div>
    </el-card>
</div>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                searchForm: {
                    receive_time: [],
                },
                lists: [],
                totalCount: 0,
                pageSize: 10,
                pageCount: 0,
                currentPage: 1,
            },
            mounted: function () {
                var that = this
                this.searchForm.receive_time = [that.getDate(0), that.getDate(0)];

                that.getList();
            },
            methods: {
                getDate: function (offset = 0) {
                    const today = new Date();
                    const targetDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - offset);

                    const year = targetDate.getFullYear();
                    const month = String(targetDate.getMonth() + 1).padStart(2, '0');
                    const day = String(targetDate.getDate()).padStart(2, '0');

                    return `${year}-${month}-${day}`;
                },
                // 搜索
                search: function () {
                    this.currentPage = 1;
                    this.getList();
                },

                getList: function () {
                    var that = this
                    var data = {
                        page: this.currentPage,
                        _action: 'getList',
                        receive_time: this.searchForm.receive_time,
                    }
                    this.httpGet("/wechat/open.WxcallbackAdmin/component", data, function (res) {
                        that.lists = res.data.items
                        that.totalCount = res.data.total_items
                        that.currentPage = res.data.page
                        that.pageSize = res.data.limit
                        that.pageCount = res.data.total_pages
                    })
                },
                currentPageChange: function (e) {
                    this.currentPage = e;
                    this.getList();
                },
            }
        });
    })
</script>
