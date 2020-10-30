<div id="app" v-cloak>
    <el-card>

        <div slot="header" class="clearfix">
            <span>小程序直播间列表</span>
        </div>

        <div>
            <el-form :inline="true" :model="searchData" class="demo-form-inline">

                <el-form-item label="appid">
                    <el-input v-model="searchData.app_id" placeholder="请输入小程序appid"></el-input>
                </el-form-item>

                <el-form-item label="直播间名称">
                    <el-input v-model="searchData.title" placeholder="请输入直播间名称名称"></el-input>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="searchEvent">筛选</el-button>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="doSync">同步直播间</el-button>
                </el-form-item>
            </el-form>
        </div>

        <div class="modal fade" id="paramModel" tabindex="-1"
             role="dialog" aria-labelledby="myModalLabel"
             style="margin-bottom: 20px;"
        >
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel"> 查看回放json </h4>
                    </div>
                    <div class="modal-body">
                        <code>{</code><br>
                        </tba>
                        <div v-for="(item,key) in param" :key="key">
                            &nbsp;&nbsp;<code>"{{ key }}" : </code>
                            <template v-if="'object' !== typeof(param[key])">
                                <code>"{{ param[key] }}",</code>
                            </template>
                            <template v-else>
                                <br>&emsp;&nbsp;&nbsp;&nbsp;<code>{</code>
                                <template v-for="(i,k) in param[key]" :k="k">
                                    <br>&emsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>"{{ k }}" : "{{ param[key][k]
                                        }}",</code>
                                </template>
                                <br>&emsp;&nbsp;&nbsp;&nbsp;<code>}</code>
                            </template>
                        </div>
                        <code>}</code><br>
                    </div>
                </div>
            </div>
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
                        prop="live_name"
                        label="直播间名称"
                        align="center"
                        min-width="120">
                </el-table-column>

                <el-table-column
                        prop="roomid"
                        label="房间号"
                        align="center"
                        min-width="120">
                </el-table-column>

                <el-table-column
                        label="直播状态"
                        align="center">
                    <template slot-scope="scope">
                        <template v-if="scope.row.live_status == 101">
                            直播中
                        </template>

                        <template v-if="scope.row.live_status == 102">
                            未开始
                        </template>

                        <template v-if="scope.row.live_status == 103">
                            已结束
                        </template>

                        <template v-if="scope.row.live_status == 104">
                            禁播
                        </template>

                        <template v-if="scope.row.live_status == 105">
                            暂停中
                        </template>

                        <template v-if="scope.row.live_status == 106">
                            异常
                        </template>

                        <template v-if="scope.row.live_status == 107">
                            已过期
                        </template>
                    </template>
                </el-table-column>

                <el-table-column
                        prop="start_time"
                        label="计划开始时间"
                        align="center"
                        min-width="120">
                </el-table-column>

                <el-table-column
                        prop="end_time"
                        label="计划结束时间"
                        align="center"
                        min-width="120">
                </el-table-column>

                <el-table-column
                        prop="anchor_name"
                        label="主播名"
                        align="center"
                        min-width="120">
                </el-table-column>

                <el-table-column
                        fixed="right"
                        label="操作"
                        align="center"
                        width="220">
                    <template slot-scope="scope">
                        <el-button @click="getPlaybacks(scope.row)" type="primary">查看回放</el-button>
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
                users: [],
                page: 1,
                limit: 10,
                totalPages: 0,
                totalItems: 0,
                param: []
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                //列表
                getList: function () {
                    var _this = this;
                    var where = Object.assign({
                        page: this.page,
                        limit: this.limit,
                        action : "ajaxList"
                    }, this.searchData);
                    $.ajax({
                        url: "{:api_url('/wechat/Mini/live')}",
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
                },
                doSync: function () {
                    var that = this;
                    this.httpGet("{:api_url('/wechat/Mini/live')}", {
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
                getPlaybacks: function (row) {
                    var that = this;
                    this.httpGet("{:api_url('/wechat/Mini/live')}", {
                        app_id: row.app_id,
                        roomId: row.roomid,
                        action : 'playbacks'
                    }, function (res) {
                        if (res.status) {
                            that.param = res.data;
                        } else {
                            that.$message.error(res.msg);
                        }
                    })
                },
                searchEvent: function () {
                    this.page = 1;
                    this.getList();
                },
                currentChangeEvent: function (page) {
                    this.page = page;
                    this.getList();
                }
            }
        })
    });
</script>