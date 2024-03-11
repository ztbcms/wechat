<div id="app" v-cloak>

    <el-card>
        <div slot="header" class="clearfix">
            <span>小程序结算数据</span>
        </div>

        <div class="filter-container" style="margin-bottom: -15px;">
            <el-form :inline="true" :model="searchForm" size="small">
                <el-date-picker
                        v-model="searchForm.date"
                        type="daterange"
                        format="yyyy-MM-dd"
                        value-format="yyyy-MM-dd"
                        placeholder="选择日期"
                        size="small"
                        style="width: 260px;"
                        :clearable="false">
                </el-date-picker>

                <el-form-item label="">
                    <el-button type="primary" @click="search">查询</el-button>
                </el-form-item>
            </el-form>
        </div>

        <el-divider></el-divider>

        <h4 style="margin: 0;">账号收入</h4>
        <el-row>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>累计收入</h5>
                    <p class="text-regular">{{ formatNumber(summary.revenue_all) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>已结算金额</h5>
                    <p class="text-regular">{{ formatNumber(summary.settled_revenue_all) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>扣除金额</h5>
                    <p class="text-regular">{{ formatNumber(summary.penalty_all) }}</p>
                </div>
            </el-col>
        </el-row>
        <el-divider></el-divider>
        <h4 style="margin: 0px 0px 8px;">结算记录</h4>
        <el-table
                :data="lists"
                highlight-current-row
                style="width: 100%">
            <el-table-column
                    prop="slot_id"
                    label="结算日期区间"
                    min-width="100">
                <template slot-scope="props">
                    <div>{{ props.row.zone }}</div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="slot_id"
                    label="结算归属月份"
                    min-width="100">
                <template slot-scope="props">
                    <div><span>{{ props.row.month }}</span>/
                        <template v-if="props.row.order == 1">
                            <span>上半月</span>
                        </template>
                        <template v-if="props.row.order == 2">
                            <span>下半月</span>
                        </template>
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    label="结算进度"
                    min-width="60">
                <template slot-scope="props">
                    <div>
                        <template v-if="props.row.sett_status == 1">结算中</template>
                        <template v-if="props.row.sett_status == 2 || props.row.sett_status == 3">已结算</template>
                        <template v-if="props.row.sett_status == 4">付款中</template>
                        <template v-if="props.row.sett_status == 5">已付款</template>
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    label="结算收入"
                    min-width="60">
                <template slot-scope="props">
                    <div>{{ formatNumber(props.row.settled_revenue) }}</div>
                </template>
            </el-table-column>

        </el-table>
        <div style="margin-top: 20px">
            <el-pagination
                    background
                    @current-change="currentPageChange"
                    layout="prev,slot, next, jumper"
                    :current-page="currentPage">
                <span style="text-align: center;">{{ currentPage }}</span>
            </el-pagination>
        </div>
    </el-card>
</div>
<style>
    p {
        margin: 0;
    }

    .text-regular {
        color: #606266;
    }
</style>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                searchForm: {
                    authorizer_appid: '',
                    date: [],
                },
                lists: [],
                summary: {
                    "revenue_all": '-',
                    "penalty_all": '-',
                    "settled_revenue_all": '-',
                },
                currentPage: 1,
            },
            mounted: function () {
                this.searchForm.authorizer_appid = this.getUrlQuery('authorizer_appid')
                this.searchForm.date = [this.getDate(30), this.getDate(0)];
                this.search();
            },
            methods: {
                search: function () {
                    this.currentPage = 1
                    this.getData()
                },
                getDate: function (offset = 0) {
                    const today = new Date();
                    const targetDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - offset);

                    const year = targetDate.getFullYear();
                    const month = String(targetDate.getMonth() + 1).padStart(2, '0');
                    const day = String(targetDate.getDate()).padStart(2, '0');

                    return `${year}-${month}-${day}`;
                },
                getData: function () {
                    var that = this
                    var data = this.searchForm
                    data['_action'] = 'getData'
                    data['page'] = this.currentPage
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/settlement", data, function (res) {
                        if (!res.status) {
                            layer.alert(res.msg)
                            return
                        }
                        that.lists = res.data.list
                        that.summary = res.data.summary
                        that.currentPage = res.data.page
                    })
                },
                currentPageChange: function (e) {
                    this.currentPage = e;
                    this.getData();
                },
                formatNumber: function (num) {
                    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
            }
        });
    })
</script>
