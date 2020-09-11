<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>支付订单列表</span>
            </div>
            <div>
                <el-form :inline="true" :model="searchData" class="demo-form-inline">
                    <el-form-item label="appid">
                        <el-input v-model="searchData.app_id" placeholder="请输入小程序appid"></el-input>
                    </el-form-item>
                    <el-form-item label="open_id">
                        <el-input v-model="searchData.open_id" placeholder="请输入用户openid"></el-input>
                    </el-form-item>
                    <el-form-item label="订单号">
                        <el-input v-model="searchData.out_trade_no" placeholder="请输入支付订单号"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="searchEvent">查询</el-button>
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
                            prop="mch_id"
                            label="mch_id"
                            align="center"
                            min-width="100">
                    </el-table-column>
                    <el-table-column
                            label="result_code"
                            align="center"
                            min-width="180">
                        <template slot-scope="scope">
                            <div>{{ scope.row.result_code }}</div>
                            <div>{{ scope.row.err_code }}</div>
                            <div>{{ scope.row.err_code_des }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="总支付金额"
                            align="center"
                            min-width="100">
                        <template slot-scope="scope">
                            <div>{{ scope.row.total_fee/100 }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="支付现金"
                            align="center"
                            min-width="100">
                        <template slot-scope="scope">
                            <div>{{ scope.row.cash_fee/100 }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="open_id"
                            label="open_id"
                            align="center"
                            min-width="250">
                    </el-table-column>
                    <el-table-column
                            prop="out_trade_no"
                            label="订单号"
                            align="center"
                            min-width="250">
                    </el-table-column>
                    <el-table-column
                            prop="notify_url"
                            label="回调地址"
                            align="center"
                            min-width="250">
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="创建时间"
                            min-width="180">
                        <template slot-scope="scope">
                            {{scope.row.create_time}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="更新时间"
                            min-width="180">
                        <template slot-scope="scope">
                            {{scope.row.update_time}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            fixed="right"
                            label="操作"
                            align="center"
                            min-width="100">
                        <template slot-scope="scope">
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
                    open_id: "",
                    app_id: "",
                    out_trade_no: ""
                },
                users: [],
                page: 1,
                limit: 20,
                totalPages: 0,
                totalItems: 0
            },
            mounted() {
                this.getOrders();
            },
            methods: {
                deleteEvent(row) {
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
                            _this.httpPost('{:urlx("wechat/wxpay/deleteOrder")}', postData, function (res) {
                                if (res.status) {
                                    _this.$message.success('删除成功');
                                    _this.getOrders();
                                } else {
                                    _this.$message.error(res.msg);
                                }
                            })
                        }
                    });

                },
                searchEvent() {
                    this.page = 1;
                    this.getOrders();
                },
                currentChangeEvent(page) {
                    this.page = page;
                    this.getOrders();
                },
                getOrders: function () {
                    var _this = this;
                    var where = Object.assign({
                        page: this.page,
                        limit: this.limit
                    }, this.searchData);
                    $.ajax({
                        url: "{:urlx('wechat/wxpay/orders')}",
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