<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>企业支付列表</span>
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
                    <el-input v-model="searchData.partner_trade_no" placeholder="请输入订单号"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="searchEvent">查询</el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="createOrder">手动创建企业付款订单</el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleEvent">手动触发订单消费</el-button>
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
                        prop="partner_trade_no"
                        label="订单号"
                        align="center"
                        min-width="180">
                </el-table-column>
                <el-table-column
                        prop="open_id"
                        label="open_id"
                        align="center"
                        min-width="250">
                </el-table-column>
                <el-table-column
                        label="发放金额"
                        align="center"
                        min-width="100">
                    <template slot-scope="scope">
                        <div>{{ scope.row.amount/100 }}</div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="描述"
                        align="center"
                        min-width="250">
                    <template slot-scope="scope">
                        <div>{{ scope.row.description }}</div>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="process_count"
                        label="处理次数"
                        align="center"
                        min-width="100">
                </el-table-column>
                <el-table-column
                        label="处理状态"
                        align="center"
                        min-width="100">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status==1">已完成</div>
                        <div v-if="scope.row.status==0">未完成</div>
                    </template>
                </el-table-column>
                <el-table-column
                        align="center"
                        label="下次处理时间"
                        min-width="180">
                    <template slot-scope="scope">
                        {{scope.row.next_process_time|getFormatDatetime}}
                    </template>
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
                        min-width="200">
                    <template slot-scope="scope">
                        <el-button @click="detailEvent(scope.row.refund_result)" type="primary">结果</el-button>
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
    <el-dialog
            size="small"
            :visible.sync="detailDialogVisible"
            width="600px">
        <p>{{ resultDetail }}</p>
    </el-dialog>
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
                    partner_trade_no: ""
                },
                users: [],
                page: 1,
                limit: 20,
                totalPages: 0,
                totalItems: 0,
                resultDetail: {},
                detailDialogVisible: false
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                handleEvent: function () {
                    var that = this
                    layer.confirm('该操作会非常耗时，请在业务不繁忙时进行操作', {
                        title: '提示',
                        btn: ['继续执行', '取消'] //按钮
                    }, function () {
                        that.doHandleOrders()
                        layer.closeAll()
                    }, function () {
                        layer.closeAll()
                    });
                },
                doHandleOrders: function () {
                    var _this = this;
                    this.httpPost("{:api_url('/wechat/Wxmchpay/mchpays')}", {
                        action: 'handleMchpay'
                    }, function (res) {
                        if (res.status) {
                            _this.$message.success('处理成功');
                            _this.getList();
                        } else {
                            this.$message.error(res.msg);
                        }
                    })
                },
                createOrder: function () {
                    var that = this;
                    layer.open({
                        type: 2,
                        title: '创建企业付款订单',
                        content: "{:api_url('/wechat/Wxmchpay/createMchpay')}",
                        area: ['85%', '85%'],
                        end: function () {
                            that.getList()
                        }
                    })
                },
                deleteEvent: function (row) {
                    var postData = {
                        id: row.id,
                        action: 'deleteEvent'
                    };
                    var _this = this;
                    this.$confirm('是否确认删除该记录', '提示', {
                        callback: function (e) {
                            if (e !== 'confirm') {
                                return;
                            }
                            _this.httpPost("{:api_url('/wechat/Wxmchpay/mchpays')}", postData, function (res) {
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
                detailEvent: function (refund_result) {
                    this.resultDetail = refund_result;
                    this.detailDialogVisible = true;
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
                        action: 'ajaxList'
                    }, this.searchData);
                    $.ajax({
                        url: "{:api_url('/wechat/Wxmchpay/mchpays')}",
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

