<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>小程序流量主代运营</span>
        </div>

        <div style="margin-bottom: 20px;">
            <p>默认分账比例：<span>{{ defaultShareRatio }}</span>%
                <el-button type="text" @click="handleSyncDefaultShareRatio">同步</el-button>
                <el-button type="text" @click="handleSetDefaultShareRatio">设置</el-button>
            </p>
            <p style="font-size: 12px">*
                服务商更改分账比例，不代表立即生效；需要由商家在权限集授权流程中进行确认，才视为实际生效。</p>
        </div>

        <el-divider></el-divider>

        <div class="filter-container" style="margin-top: 15px;">
            <el-form :inline="true" :model="searchForm" size="small">

                <el-form-item label="APPID">
                    <el-input v-model="searchForm.appid" placeholder="不支持模糊搜索"></el-input>
                </el-form-item>

                <el-form-item label="">
                    <el-button type="primary" @click="search">查询</el-button>
                    <el-button type="success" @click="handleAddAuthorizer">添加小程序</el-button>
                    <el-button type="success" @click="handleViewAgencyAdData">服务商广告数据</el-button>
                    <el-button type="success" @click="handleViewAgencySettlement">服务商结算数据</el-button>
                </el-form-item>
            </el-form>
        </div>

        <el-table
                :data="lists"
                highlight-current-row
                style="width: 100%">

            <el-table-column
                    label="小程序/APPID"
                    min-width="100">
                <template slot-scope="props">
                    <div>{{ props.row.authorizerInfo.name }}</div>
                    <div>{{ props.row.authorizer_appid }}</div>
                </template>
            </el-table-column>

            <el-table-column
                    label="流量主功能"
                    min-width="100">
                <template slot-scope="props">
                    <div>
                        <template v-if="props.row.publisher_status == 0">
                            <span>未开通</span>
                        </template>
                        <template v-else>
                            <span>已开通</span>
                        </template>
                    </div>
                    <div v-if="props.row.publisher_status == 0">
                        <el-button type="text" @click="handleSyncPublisherStatus(props.row)">检测条件</el-button>
                        <el-button type="text" @click="handleCreatePublisher(props.row)">开通流量主</el-button>
                    </div>
                </template>
            </el-table-column>

            <el-table-column
                    label="分成比例"
                    min-width="120">
                <template slot-scope="props">
                    <p>生效：{{ props.row.share_ratio }}%
                        <el-button type="text" @click="handleSyncAuthorizerShareRatio(props.row)">同步</el-button>
                    </p>
                    <p>自定义：{{ props.row.custom_share_ratio }}%
                        <el-button type="text" @click="handleSyncAuthorizerCustomShareRatio(props.row)">同步</el-button>
                        <el-button type="text" @click="handleSetAuthorizerCustomShareRatio(props.row)">设置</el-button>
                    </p>
                </template>
            </el-table-column>

            <el-table-column
                    prop=""
                    label="广告位"
                    min-width="60">
                <template slot-scope="props">
                    <el-button type="text" @click="handleViewAdPos(props.row)">查看</el-button>
                </template>
            </el-table-column>

            <el-table-column
                    prop=""
                    label="广告数据"
                    min-width="60">
                <template slot-scope="props">
                    <el-button @click="handleViewItemAdData(props.row)" type="text">查看</el-button>
                </template>
            </el-table-column>

            <el-table-column
                    label="结算数据"
                    min-width="60">
                <template slot-scope="props">
                    <el-button @click="handleViewItemSettlement(props.row)" type="text">查看</el-button>
                </template>
            </el-table-column>

            <el-table-column
                    fixed="right"
                    width="100"
                    align="center"
                    label="操作">
                <template slot-scope="props">
                    <el-button @click="handleDelAuthorizer(props.row)" type="text" size="mini" style="color: red;">删除
                    </el-button>
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
<style>
    p {
        margin: 0;
    }
</style>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                searchForm: {
                    appid: '',
                },
                lists: [],
                totalCount: 0,
                pageSize: 10,
                pageCount: 0,
                currentPage: 1,
                defaultShareRatio: '-',
            },
            mounted: function () {
                this.handleSyncDefaultShareRatio()
                this.getList();
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
                    }
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
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
                // 查询服务商默认分成比例
                handleSyncDefaultShareRatio: function () {
                    let that = this
                    const data = {
                        _action: 'syncDefaultShareRatio',
                    }
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.defaultShareRatio = res.data.share_ratio
                        }
                    })
                },
                handleSetDefaultShareRatio: function () {
                    let that = this
                    layer.prompt({
                        title: '设置默认分成比例',
                    }, function (value, index, elem) {
                        const data = {
                            _action: 'setDefaultShareRatio',
                            share_ratio: value,
                        }
                        that.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                            layer.msg(res.msg)
                            if (res.status) {
                                that.defaultShareRatio = value
                            }
                            layer.close(index);
                        })
                    })
                },
                handleAddAuthorizer: function () {
                    let that = this
                    layer.prompt({
                        title: '输入小程序 APPID',
                    }, function (value, index, elem) {
                        const data = {
                            _action: 'addAuthorizer',
                            authorizer_appid: value,
                        }
                        that.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                            layer.msg(res.msg)
                            if (res.status) {
                                that.getList()
                            }
                            layer.close(index);
                        })
                    })
                },
                handleDelAuthorizer: function (item) {
                    let that = this
                    layer.confirm('确定删除该小程序？', function (index) {
                        const data = {
                            _action: 'delAuthorizer',
                            authorizer_appid: item.authorizer_appid,
                        }
                        that.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                            layer.msg(res.msg)
                            if (res.status) {
                                that.getList()
                            }
                        })
                    })
                },
                handleSyncAuthorizerShareRatio: function (item) {
                    let that = this
                    const data = {
                        _action: 'syncAuthorizerShareRatio',
                        authorizer_appid: item.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.getList()
                        }
                    })
                },
                handleSyncAuthorizerCustomShareRatio: function (item) {
                    let that = this
                    const data = {
                        _action: 'syncAuthorizerCustomShareRatio',
                        authorizer_appid: item.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            that.getList()
                        }
                    })
                },
                handleSetAuthorizerCustomShareRatio: function (item) {
                    let that = this
                    layer.prompt({
                        title: '设置小程序分成比例',
                    }, function (value, index, elem) {
                        const data = {
                            _action: 'setAuthorizerCustomShareRatio',
                            authorizer_appid: item.authorizer_appid,
                            share_ratio: value,
                        }
                        that.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                            layer.msg(res.msg)
                            if (res.status) {
                                that.getList()
                            }
                            layer.close(index);
                        })
                    })
                },
                // 查看小程序广告数据
                handleViewItemAdData: function (item) {
                    layer.open({
                        type: 2,
                        title: '广告数据',
                        content: '/wechat/open.PublisherAgencyAdmin/adData?authorizer_appid=' + item.authorizer_appid,
                        area: ['90%', '90%'],
                    })
                },
                // 查看服务商广告数据
                handleViewAgencyAdData: function () {
                    layer.open({
                        type: 2,
                        title: '广告数据',
                        content: '/wechat/open.PublisherAgencyAdmin/agencyAdData',
                        area: ['90%', '90%'],
                    })
                },
                // 查看小程序结算数据
                handleViewItemSettlement: function (item) {
                    layer.open({
                        type: 2,
                        title: '结算数据',
                        content: '/wechat/open.PublisherAgencyAdmin/settlement?authorizer_appid=' + item.authorizer_appid,
                        area: ['90%', '90%'],
                    })
                },
                // 服务商结算数据
                handleViewAgencySettlement: function () {
                    layer.open({
                        type: 2,
                        title: '结算数据',
                        content: '/wechat/open.PublisherAgencyAdmin/agencySettlement',
                        area: ['90%', '90%'],
                    })
                },
                // 查看广告位
                handleViewAdPos: function (item) {
                    let title = item['name'] + '_广告位管理'
                    let url = "{:api_url('/wechat/open.PublisherAgencyAdmin/adUnits')}" + '?authorizer_appid=' + item['authorizer_appid']
                    this.openNewIframeByUrl(title, url)
                },
                // 同步流量主状态
                handleSyncPublisherStatus: function (item) {
                    let that = this
                    const data = {
                        _action: 'syncPublisherStatus',
                        authorizer_appid: item.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            setTimeout(function () {
                                that.getList()
                            }, 1000)
                        }
                    })
                },
                // 同步流量主状态
                handleCreatePublisher: function (item) {
                    let that = this
                    const data = {
                        _action: 'createPublisher',
                        authorizer_appid: item.authorizer_appid,
                    }
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/list", data, function (res) {
                        layer.msg(res.msg)
                        if (res.status) {
                            setTimeout(function () {
                                that.getList()
                            }, 1000)
                        }
                    })
                },
            }
        });
    })
</script>
