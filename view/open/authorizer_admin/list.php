<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>授权账号</span>
        </div>

        <div style="margin-bottom: 20px;">
            <el-alert
                    style="margin-bottom: 15px;"
                    title=""
                    type="info"
                    :closable="false">
                <p style="font-weight: bold">
                    授权帐号指的是获得公众号或者小程序管理员授权的帐号，服务商可为授权帐号提供代开发、代运营等服务</p>
            </el-alert>
        </div>

        <div class="filter-container" style="margin-top: 15px;">
            <el-form :inline="true" :model="searchForm" size="small">
                <el-form-item label="APPID">
                    <el-input v-model="searchForm.appid" placeholder="不支持模糊搜索"></el-input>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input v-model="searchForm.name" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-button type="primary" @click="search">查询</el-button>
                    <el-button type="success" @click="handleBatchSyncAuthorizerInfo">批量拉取授权用户</el-button>
                </el-form-item>
            </el-form>
        </div>

        <el-tabs v-model="searchForm.account_type" @tab-click="handleSwitchAccountType">
            <el-tab-pane label="小程序" name="1"></el-tab-pane>
            <el-tab-pane label="公众号" name="0"></el-tab-pane>
        </el-tabs>

        <el-table
                :data="lists"
                highlight-current-row
                style="width: 100%">

            <el-table-column
                    label="账号"
                    min-width="100">
                <template slot-scope="props">
                    <p style="font-weight: bold;margin: 0;">{{props.row.name}}</p>
                    <p style="margin: 0;">{{props.row.authorizer_appid}}</p>
                </template>

            </el-table-column>

            <el-table-column
                    label="账号类型"
                    min-width="80">
                <template slot-scope="props">
                    <template v-if="props.row.account_type == 0">
                        <span>公众号</span>
                    </template>
                    <template v-else>
                        <span>小程序</span>
                    </template>
                </template>
            </el-table-column>

            <el-table-column
                    label="认证状态"
                    min-width="80">
                <template slot-scope="props">
                    <template v-if="props.row.is_verify == 0">
                        <span style="color: red;">未认证</span>
                    </template>
                    <template v-else>
                        <span>已认证</span>
                    </template>
                </template>
            </el-table-column>

            <el-table-column
                    prop="account_status_text"
                    label="运营状态"
                    min-width="80">
                <template slot-scope="props">
                    <template v-if="props.row.account_status_text == '正常'">
                        <span>{{ props.row.account_status_text }}</span>
                    </template>
                    <template v-else>
                        <span style="color: red;">{{ props.row.account_status_text }}</span>
                    </template>
                </template>
            </el-table-column>

            <el-table-column
                    prop="authorization_status"
                    label="授权状态"
                    min-width="80">
                <template slot-scope="props">
                    <template v-if="props.row.authorization_status == 0">
                        <span style="color: red">未授权</span>
                    </template>
                    <template v-else>
                        <span>正常</span>
                    </template>
                </template>
            </el-table-column>

            <el-table-column
                    v-if="searchForm.account_type === '1'"
                    label="小程序版本"
                    min-width="100">
                <template slot-scope="props">
                    <p  style="margin:0;font-size: 12px;">
                        线上:
                        <span v-if="props.row.versionInfo && props.row.versionInfo.release_info" style="font-weight: bold;">{{ props.row.versionInfo.release_info.version }}</span>
                        <span v-else>-</span>

                    </p>
                    <p style="margin:0;">
                        <el-button @click="handleMiniProgramVersion(props.row)" type="text" size="mini">版本管理
                        </el-button>
                    </p>
                </template>
            </el-table-column>

            <el-table-column
                    fixed="right"
                    width="200"
                    align="center"
                    label="操作">
                <template slot-scope="props">
                    <el-button @click="handleSyncAuthorizerInfo(props.row)" type="text" size="mini">同步账号详情
                    </el-button>
                    <el-button @click="handleViewAuthorizerInfo(props.row)" type="text" size="mini">查看详情</el-button>
                    <!--小程序 S-->
                    <template v-if="props.row.account_type == 1">
                        <el-button @click="handleMiniProgramDomain(props.row)" type="text" size="mini">域名管理
                        </el-button>
                        <el-button @click="handleMiniProgramAnalysis(props.row)" type="text" size="mini">数据分析
                        </el-button>
                        <el-button @click="handleMiniProgramPrivacySetting(props.row)" type="text" size="mini">隐私指引
                        </el-button>
                    </template>
                    <!--小程序 E-->
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
                    appid: '',
                    name: '',
                    account_type: '1',
                },
                lists: [],
                totalCount: 0,
                pageSize: 10,
                pageCount: 0,
                currentPage: 1,
            },
            mounted: function () {
                var that = this
                that.getList();
            },
            methods: {
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
                        appid: this.searchForm.appid,
                        name: this.searchForm.name,
                        account_type: this.searchForm.account_type,
                    }
                    this.httpGet("/wechat/open.AuthorizerAdmin/list", data, function (res) {
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
                handleSyncAuthorizerInfo: function (item) {
                    let that = this
                    const data = {
                        appid: item['authorizer_appid'],
                        _action: 'syncAuthorizerInfo',
                    }
                    this.httpPost("/wechat/open.AuthorizerAdmin/list", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.getList()
                        }
                    })
                },
                handleViewAuthorizerInfo: function (item) {
                    layer.open({
                        type: 2,
                        title: '账号详情',
                        content: "{:api_url('/wechat/open.AuthorizerAdmin/detail')}" + '?authorizer_appid=' + item['authorizer_appid'],
                        area: ['70%', '80%'],
                    })
                },
                handleSwitchAccountType: function () {
                    this.search()
                },
                // 小程序版本管理
                handleMiniProgramVersion: function (item) {
                    let title = item['name'] + '_版本管理'
                    let url = "{:api_url('/wechat/open.MiniProgramCodeAdmin/version')}" + '?authorizer_appid=' + item['authorizer_appid']
                    this.openNewIframeByUrl(title, url)
                },
                // 小程序域名管理
                handleMiniProgramDomain: function (item) {
                    let title = item['name'] + '_域名管理'
                    let url = "{:api_url('/wechat/open.MiniProgramDomainAdmin/index')}" + '?authorizer_appid=' + item['authorizer_appid']
                    this.openNewIframeByUrl(title, url)
                },
                // 小程序数据分析
                handleMiniProgramAnalysis: function (item) {
                    let title = item['name'] + '_数据分析'
                    let url = "{:api_url('/wechat/open.MiniProgramAnalysisAdmin/index')}" + '?authorizer_appid=' + item['authorizer_appid']
                    this.openNewIframeByUrl(title, url)
                },
                // 全量拉取授权账号
                handleBatchSyncAuthorizerInfo: function () {
                    let that = this
                    layer.confirm('开始拉取数据？拉取耗时视乎数据量大小。', function (index) {
                        layer.close(index);
                        let offset = 0
                        that.doBatchSyncAuthorizerInfo(offset)
                    })
                },
                doBatchSyncAuthorizerInfo: function (offset = 0) {
                    let that = this
                    const data = {
                        _action: 'batchSyncAuthorizerInfo',
                        offset: offset,
                    }
                    that.httpPost("/wechat/open.AuthorizerAdmin/list", data, function (res) {
                        if (res.status) {
                            // Check has next page
                            if (offset + res.data.count < res.data.total_count) {
                                that.doBatchSyncAuthorizerInfo(offset + res.data.count)
                            } else {
                                layer.msg('拉取完成')
                                that.getList()
                            }
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
                // 隐私指引
                handleMiniProgramPrivacySetting: function(item){
                    let title = item['name'] + '_隐私指引设置'
                    let url = "{:api_url('/wechat/open.MiniProgramAdmin/privacySetting')}" + '?authorizer_appid=' + item['authorizer_appid']
                    this.openNewIframeByUrl(title, url)
                },
            }
        });
    })
</script>
