<div id="app" v-cloak>

    <el-card>
        <div slot="header" class="clearfix">
            <span>小程序广告位</span>
        </div>
        <div>
            <el-button type="primary" size="mini" @click="handleAddAdUnit">添加广告位</el-button>
        </div>
        <el-tabs v-model="searchForm.ad_slot" @tab-click="handleSwitchAdSlot">
            <el-tab-pane label="封面广告" name="SLOT_ID_WEAPP_COVER"></el-tab-pane>
            <el-tab-pane label="Banner" name="SLOT_ID_WEAPP_BANNER"></el-tab-pane>
            <el-tab-pane label="激励视频" name="SLOT_ID_WEAPP_REWARD_VIDEO"></el-tab-pane>
            <el-tab-pane label="插屏广告" name="SLOT_ID_WEAPP_INTERSTITIAL"></el-tab-pane>
            <el-tab-pane label="视频广告" name="SLOT_ID_WEAPP_VIDEO_FEEDS"></el-tab-pane>
            <el-tab-pane label="视频贴片广告" name="SLOT_ID_WEAPP_VIDEO_BEGIN"></el-tab-pane>
            <el-tab-pane label="模板广告" name="SLOT_ID_WEAPP_TEMPLATE"></el-tab-pane>
        </el-tabs>
        <!--封面广告 S-->
        <template v-if="searchForm.ad_slot == 'SLOT_ID_WEAPP_COVER'">
            <div>
                <p style="font-size: 15px;">当前状态：
                    <template v-if="coverAdposStatus.status == 1">
                        <span>开启中</span>
                        <el-button type="text" @click="handleSetCoverAdposStatus(4)">关闭</el-button>
                    </template>
                    <!--PS.0表示未设置过，默认关闭-->
                    <template v-if="coverAdposStatus.status == 4 || coverAdposStatus.status == 0">
                        <span>关闭中</span>
                        <el-button type="text" @click="handleSetCoverAdposStatus(1)">开启</el-button>
                    </template>

                </p>
            </div>
        </template>
        <!--封面广告 E-->
        <!--非封面广告 S-->
        <template v-else>
            <el-table
                    :data="lists"
                    highlight-current-row
                    style="width: 100%">
                <el-table-column
                        label="广告单元名称"
                        min-width="120">
                    <template slot-scope="props">
                        <p style="font-size: 15px;">{{ props.row.ad_unit_name }}</p>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="ad_unit_id"
                        label="广告单元ID"
                        min-width="80">
                </el-table-column>
                <el-table-column
                        label="开关状态"
                        min-width="80">
                    <template slot-scope="props">
                        <p style="font-size: 15px;">
                            <el-switch
                                    v-model="props.row.ad_unit_status"
                                    active-value="1"
                                    inactive-value="2"
                                    disabled>
                            </el-switch>
                            <template v-if="props.row.ad_unit_status == 1">
                                <el-button type="text" @click="handleUpdateAdUnitStatus(props.row)">关闭</el-button>
                            </template>
                            <template v-if="props.row.ad_unit_status == 2">
                                <el-button type="text" @click="handleUpdateAdUnitStatus(props.row)">开启</el-button>
                            </template>
                        </p>
                    </template>
                </el-table-column>
                <el-table-column
                        label="广告单元代码"
                        min-width="80">
                    <template slot-scope="props">
                        <el-button type="text" @click="handleAdUnitCode(props.row)">查看</el-button>
                    </template>
                </el-table-column>

                <el-table-column
                        v-if="lists.lenght > 0 && lists[0]['ad_slot'] === 'SLOT_ID_WEAPP_REWARD_VIDEO'"
                        label="广告时长"
                        min-width="100">
                    <template slot-scope="props">
                        <p style="font-size: 15px;">{{ props.row.video_duration_min }}-
                            <template v-if="props.row.video_duration_max == 86400">不限</template>
                            <template v-else>{{ props.row.video_duration_max }}秒</template>
                        </p>
                    </template>
                </el-table-column>

                <el-table-column
                        v-if="lists.lenght > 0 && lists[0]['ad_slot'] === 'SLOT_ID_WEAPP_TEMPLATE'"
                        label="模板 ID"
                        min-width="100">
                    <template slot-scope="props">
                        <p style="font-size: 15px;">{{ props.row.tmpl_id }}</p>
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
        </template>
        <!--非封面广告 E-->
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
                    ad_slot: 'SLOT_ID_WEAPP_COVER'
                },
                lists: [],
                currentPage: 1,
                // 封面广告
                coverAdposStatus: {
                    status: 0,
                }
            },
            mounted: function () {
                this.searchForm.authorizer_appid = this.getUrlQuery('authorizer_appid')
                this.fetchData();
            },
            methods: {
                fetchData: function () {
                    if (this.searchForm.ad_slot === 'SLOT_ID_WEAPP_COVER') {
                        this.getCoverAdposStatus()
                    } else {
                        this.search()
                    }
                },
                search: function () {
                    this.currentPage = 1
                    this.getData()
                },
                getData: function () {
                    var that = this
                    var data = this.searchForm
                    data['_action'] = 'getData'
                    data['page'] = this.currentPage
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/adUnits", data, function (res) {
                        if (!res.status) {
                            layer.alert(res.msg)
                            return
                        }
                        that.lists = []
                        for (let i = 0; i < res.data.list.length; i++) {
                            let item = res.data.list[i]
                            item['ad_unit_status'] = item['ad_unit_status'] + ''
                            that.lists.push(item)
                        }
                        that.currentPage = res.data.page
                    })
                },
                currentPageChange: function (e) {
                    this.currentPage = e;
                    this.getData();
                },
                handleSwitchAdSlot: function () {
                    this.fetchData()
                },
                formatNumber: function (num) {
                    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                },
                // 拉取封面广告位状态
                getCoverAdposStatus: function () {
                    var that = this
                    var data = {
                        authorizer_appid: this.searchForm.authorizer_appid,
                        _action: 'getCoverAdposStatus'
                    }
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/adUnits", data, function (res) {
                        if (!res.status) {
                            layer.alert(res.msg)
                            return
                        }
                        that.coverAdposStatus = res.data
                    })
                },
                // 设置封面广告位状态
                setCoverAdposStatus: function (status) {
                    var that = this
                    var data = {
                        authorizer_appid: this.searchForm.authorizer_appid,
                        _action: 'setCoverAdposStatus',
                        status: status,
                    }
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/adUnits", data, function (res) {
                        if (!res.status) {
                            layer.alert(res.msg)
                            return
                        }
                        that.coverAdposStatus.status = status
                    })
                },
                // 设置封面广告位状态
                handleSetCoverAdposStatus: function (status) {
                    this.setCoverAdposStatus(status)
                },
                // 设置广告开关
                handleUpdateAdUnitStatus: function (item) {
                    let that = this
                    const data = {
                        _action: 'setAdUnitStatus',
                        authorizer_appid: that.searchForm.authorizer_appid,
                        ad_unit_id: item.ad_unit_id,
                        ad_unit_name: item.ad_unit_name,
                        ad_unit_status: item.ad_unit_status === 1 ? 2 : 1,
                    }
                    that.httpPost("/wechat/open.PublisherAgencyAdmin/adUnits", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.getData()
                        }
                    })
                },
                // 添加广告单元
                handleAddAdUnit: function () {
                    let that = this
                    layer.open({
                        type: 2,
                        title: '添加广告位',
                        content: "{:api_url('/wechat/open.PublisherAgencyAdmin/addOrEditAdUnit')}" + '?authorizer_appid=' + this.searchForm['authorizer_appid'],
                        area: ['70%', '80%'],
                        end: function () {
                            that.getData()
                        },
                    })
                },
                // 查看广告单元代码
                handleAdUnitCode: function (item) {
                    let that = this
                    const data = {
                        _action: 'getAdunitCode',
                        authorizer_appid: that.searchForm.authorizer_appid,
                        ad_unit_id: item.ad_unit_id,
                    }
                    that.httpPost("/wechat/open.PublisherAgencyAdmin/adUnits", data, function (res) {
                        if (res.status) {
                            layer.open({
                                type: 1,
                                title: '广告单元代码',
                                content: '<pre style="padding: 20px;">' + res.data.code + '</pre>',
                                area: ['70%', '80%'],
                            })
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
            }
        });
    })
</script>
