<div id="app" v-cloak>

    <el-card>
        <div slot="header" class="clearfix">
            <span>小程序广告数据</span>
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

        <h4 style="margin: 0;">汇总数据</h4>
        <el-row>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>拉取量</h5>
                    <p class="text-regular">{{ formatNumber(summary.req_succ_count) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>曝光量</h5>
                    <p class="text-regular">{{ formatNumber(summary.exposure_count) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>曝光率</h5>
                    <p class="text-regular">{{ summary.exposure_rate }}%</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>点击量</h5>
                    <p class="text-regular">{{ formatNumber(summary.click_count) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>点击率</h5>
                    <p class="text-regular">{{ summary.click_rate }}%</p>
                </div>
            </el-col>
            <el-col :span="4">
                <div style="text-align: center">
                    <h5>收入(元)</h5>
                    <p class="text-regular">{{ formatNumber(summary.publisher_income) }}</p>
                </div>
            </el-col>
            <el-col :span="4">
                <div style="text-align: center">
                    <h5>eCPM(元)</h5>
                    <p class="text-regular">{{ summary.ecpm }}</p>
                </div>
            </el-col>
        </el-row>
        <el-divider></el-divider>
        <h4 style="margin: 0px 0px 8px;">广告指标明细</h4>
        <el-tabs v-model="searchForm.ad_slot" @tab-click="handleSwitchAdSlot">
            <el-tab-pane label="整体" name="0"></el-tab-pane>
            <el-tab-pane label="封面广告" name="SLOT_ID_WEAPP_COVER"></el-tab-pane>
            <el-tab-pane label="Banner" name="SLOT_ID_WEAPP_BANNER"></el-tab-pane>
            <el-tab-pane label="激励视频" name="SLOT_ID_WEAPP_REWARD_VIDEO"></el-tab-pane>
            <el-tab-pane label="插屏广告" name="SLOT_ID_WEAPP_INTERSTITIAL"></el-tab-pane>
            <el-tab-pane label="视频广告" name="SLOT_ID_WEAPP_VIDEO_FEEDS"></el-tab-pane>
            <el-tab-pane label="视频贴片广告" name="SLOT_ID_WEAPP_VIDEO_BEGIN"></el-tab-pane>
            <el-tab-pane label="模板广告" name="SLOT_ID_WEAPP_TEMPLATE"></el-tab-pane>
        </el-tabs>
        <el-table
                :data="lists"
                highlight-current-row
                style="width: 100%">
            <el-table-column
                    prop="date"
                    label="日期"
                    min-width="80">
            </el-table-column>
            <el-table-column
                    prop="req_succ_count"
                    label="拉取数"
                    sortable
                    align="right"
                    min-width="60">
                <template slot-scope="props">
                    <div>{{ formatNumber(props.row.req_succ_count) }}</div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="exposure_count"
                    label="曝光量"
                    sortable
                    align="right"
                    min-width="60">
                <template slot-scope="props">
                    <div>{{ formatNumber(props.row.exposure_count) }}</div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="exposure_rate"
                    label="曝光率"
                    sortable
                    align="right"
                    min-width="60">
                <template slot-scope="props">
                    <div>{{ props.row.exposure_rate }}%</div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="click_count"
                    label="点击量"
                    sortable
                    align="right"
                    min-width="60">
                <template slot-scope="props">
                    <div>{{ formatNumber(props.row.click_count) }}</div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="click_rate"
                    label="点击率"
                    sortable
                    align="right"
                    min-width="60">
                <template slot-scope="props">
                    <div>{{ props.row.click_rate }}%</div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="publisher_income"
                    label="收入(元)"
                    sortable
                    align="right"
                    min-width="60">
                <template slot-scope="props">
                    <div>{{ formatNumber(props.row.publisher_income) }}</div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="ecpm"
                    label="eCPM(元)"
                    sortable
                    align="right"
                    min-width="60">
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
                    ad_slot: ''
                },
                lists: [],
                summary: {
                    "req_succ_count": '-',
                    "exposure_count": '-',
                    "exposure_rate": '-',
                    "click_count": '-',
                    "click_rate": '-',
                    "publisher_income": '-',
                    "ecpm": '-'
                },
                currentPage: 1,
            },
            mounted: function () {
                this.searchForm.authorizer_appid = this.getUrlQuery('authorizer_appid')
                this.searchForm.date = [this.getDate(7), this.getDate(1)];
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
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/adData", data, function (res) {
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
                handleSwitchAdSlot: function () {
                    this.getData()
                },
                formatNumber: function (num) {
                    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
            }
        });
    })
</script>
