<div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>红包申请列表</span>
            </div>
            <el-alert type="success">
                <slot name="title">
                    <p>1. 红包若发放失败，将会间隔一定时间再次发送，最多7次。</p>
                    <p>2. 发放了7次仍然失败，请检查返回结果的提示</p>
                </slot>
            </el-alert>
            <div style="margin-top: 8px">
                <el-form :inline="true" :model="searchData" class="demo-form-inline">
                    <el-form-item label="appid">
                        <el-input v-model="searchData.app_id" placeholder="请输入小程序appid"></el-input>
                    </el-form-item>
                    <el-form-item label="open_id">
                        <el-input v-model="searchData.open_id" placeholder="请输入用户openid"></el-input>
                    </el-form-item>
                    <el-form-item label="订单号">
                        <el-input v-model="searchData.mch_billno" placeholder="请输入商户订单号"></el-input>
                    </el-form-item>
                    <el-form-item label="处理状态">
                        <el-select v-model="searchData.status" placeholder="请选择">
                            <el-option value="" label="全部"></el-option>
                            <el-option value="0" label="待处理"></el-option>
                            <el-option value="1" label="已处理"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="searchEvent">查询</el-button>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="handleEvent">触发红包发放处理</el-button>
                    </el-form-item>
                </el-form>
            </div>
            <div>
                <el-table
                        :data="users"
                        style="width: 100%">
                    <el-table-column
                            prop="app_id"
                            align="center"
                            label="app_id"
                            min-width="180">
                    </el-table-column>
                    <el-table-column
                            prop="mch_billno"
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
                            <div>{{ scope.row.total_amount/100 }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="红包信息"
                            align="center"
                            min-width="250">
                        <template slot-scope="scope">
                            <div><b>发送者</b>：{{ scope.row.send_name }}</div>
                            <div><b>祝福语</b>：{{ scope.row.wishing }}</div>
                            <div><b>活动名称</b>：{{ scope.row.act_name }}</div>
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
                            <div v-if="scope.row.status==1">已处理</div>
                            <div v-else>待处理</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="下次处理时间"
                            min-width="180">
                        <template slot-scope="scope">
                            {{scope.row.next_process_time}}
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
                            <el-button @click="detailEvent(scope.row.send_result)" type="primary">结果</el-button>
                            <el-button @click="deleteEvent(scope.row)" type="danger">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="page-container">
                <el-pagination
                        background
                        :page-size="limit"
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
            <el-form ref="form" size="small" label-width="140px">
                <div v-for="(item,key) in resultDetail">
                    <el-form-item v-if="item" :label="key">
                        {{ item }}
                    </el-form-item>
                </div>
            </el-form>
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
                    out_trade_no: "",
                    status: ""
                },
                users: [],
                page: 1,
                limit: 20,
                totalItems: 0,
                resultDetail: {},
                detailDialogVisible: false
            },
            mounted:function() {
                this.getRedpacks();
            },
            methods: {
                handleEvent:function() {
                    var _this = this;
                    this.httpPost('{:api_url("/wechat/Wxpay/handleRedpack")}', {}, function (res) {
                        if (res.status) {
                            _this.$message.success('处理成功');
                            _this.getRedpacks();
                        } else {
                            this.$message.error(res.msg);
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
                            _this.httpPost('{:api_url("/wechat/wxpay/deleteRedpack")}', postData, function (res) {
                                if (res.status) {
                                    _this.$message.success('删除成功');
                                    _this.getRedpacks();
                                } else {
                                    _this.$message.error(res.msg);
                                }
                            })
                        }
                    });

                },
                detailEvent:function(redpack_result) {
                    if (redpack_result) {
                        this.resultDetail = JSON.parse(redpack_result);
                    }
                    this.detailDialogVisible = true;
                },
                searchEvent:function() {
                    this.page = 1;
                    this.getRedpacks();
                },
                currentChangeEvent:function(page) {
                    this.page = page;
                    this.getRedpacks();
                },
                getRedpacks: function () {
                    var _this = this;
                    var where = Object.assign({
                        page: this.page,
                        limit: this.limit
                    }, this.searchData);
                    $.ajax({
                        url: "{:api_url('/wechat/wxpay/redpacks')}",
                        dataType: 'json',
                        type: 'get',
                        data: where,
                        success: function (res) {
                            console.log("res", res);
                            if (res.status) {
                                _this.users = res.data.data;
                                _this.page = res.data.current_page;
                                _this.limit = res.data.per_page;
                                _this.totalItems = res.data.total
                            }
                        }
                    })
                }
            }
        })
    });
</script>