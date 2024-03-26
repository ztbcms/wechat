<div id="app" v-cloak>

    <el-card>
        <div slot="header" class="clearfix">
            <span>数据分析</span>
        </div>

        <div class="filter-container" style="margin-bottom: -15px;">
            <el-form :inline="true" :model="searchForm" size="small">
                <el-date-picker
                        v-model="searchForm.date"
                        type="date"
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

        <h4 style="margin: 0;">用户访问小程序数据日趋势</h4>
        <el-row>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>打开次数</h5>
                    <p class="text-regular">{{ formatNumber(dailyVisitTrend.session_cnt) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>访问次数</h5>
                    <p class="text-regular">{{ formatNumber(dailyVisitTrend.visit_pv) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>访问人数</h5>
                    <p class="text-regular">{{ formatNumber(dailyVisitTrend.visit_uv) }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>新用户数</h5>
                    <p class="text-regular">{{ formatNumber(dailyVisitTrend.visit_uv_new) }}</p>
                </div>
            </el-col>

            <el-col :span="3">
                <div style="text-align: center">
                    <h5>人均停留时长 (秒)</h5>
                    <p class="text-regular">{{ dailyVisitTrend.stay_time_uv }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>次均停留时长 (秒)</h5>
                    <p class="text-regular">{{ dailyVisitTrend.stay_time_session }}</p>
                </div>
            </el-col>
            <el-col :span="3">
                <div style="text-align: center">
                    <h5>平均访问深度</h5>
                    <p class="text-regular">{{ dailyVisitTrend.visit_depth }}</p>
                </div>
            </el-col>

        </el-row>


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
                dailyVisitTrend: {
                    "session_cnt": '-',
                    "visit_pv": '-',
                    "visit_uv": '-',
                    "visit_uv_new": '-',
                    "stay_time_uv": '-',
                    "stay_time_session": '-',
                    "visit_depth": '-'
                },
                currentPage: 1,
            },
            mounted: function () {
                this.searchForm.authorizer_appid = this.getUrlQuery('authorizer_appid')
                this.searchForm.date = this.getDate(1);
                this.search();
            },
            methods: {
                search: function () {
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
                    data['_action'] = 'getDailyVisitTrend'
                    this.httpGet("/wechat/open.MiniProgramAnalysisAdmin/index", data, function (res) {
                        if (!res.status) {
                            layer.alert(res.msg)
                            return
                        }
                        that.dailyVisitTrend = res.data
                    })
                },
                formatNumber: function (num) {
                    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
            }
        });
    })
</script>
