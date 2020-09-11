<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>应用列表</span>
            </div>
            <div>
                <el-button @click="addEvent" type="primary">添加应用</el-button>
            </div>
            <div style="margin-top: 10px">
                <el-table
                        :data="applications"
                        border
                        style="width: 100%">
                    <el-table-column
                            prop="application_name"
                            align="center"
                            label="名称"
                            min-width="100">
                    </el-table-column>
                    <el-table-column
                            label="类型"
                            align="center"
                            min-width="80">
                        <template slot-scope="scope">
                            <span v-if="scope.row.account_type=='mini'">小程序</span>
                            <span v-else>公众号</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="开发信息"
                            align="center"
                            min-width="240">
                        <template slot-scope="scope">
                            <div style="text-align: left">
                                <p>APP_ID : {{ scope.row.app_id }}</p>
                                <p>SECRET : {{ scope.row.secret }}</p>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column
                            label="微信支付信息"
                            align="center"
                            min-width="240">
                        <template slot-scope="scope">
                            <div style="text-align: left">
                                <p>mch_id : {{ scope.row.mch_id }}</p>
                                <p>key : {{ scope.row.mch_key }}</p>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="创建时间"
                            prop="create_time"
                            min-width="180">
                    </el-table-column>
                    <el-table-column
                            label="操作"
                            align="center"
                            min-width="180">
                        <template slot-scope="scope">
                            <template v-if="scope.row.account_type == 'office'">
                                <el-button @click="showOauthUrl(scope.row.app_id)" type="text" size="small">授权地址
                                </el-button>
                                <el-button @click="showOauthBase(scope.row.app_id)" type="text" size="small">静默授权地址
                                </el-button>
                            </template>
                            <el-button @click="editEvent(scope.row)" type="text" size="small">编辑</el-button>
                            <el-button @click="deleteEvent(scope.row)" type="text" size="small" style="color: red">删除
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="pagination-container">
                    <el-pagination
                            background
                            layout="prev, pager, next, jumper"
                            :total="total"
                            v-show="total>0"
                            :current-page.sync="form.page"
                            :page-size.sync="form.limit"
                            @current-change="getList"
                    >
                    </el-pagination>
                </div>
            </div>
        </el-card>

    </div>
    <style>
        .pagination-container {
            text-align: center;
            padding: 32px 16px;
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: "#app",
                data: {
                    applications: [],
                    form: {
                        page: 1,
                        limit: 10,
                    },
                    total: 0
                },
                mounted() {
                    this.getList()
                },
                methods: {
                    deleteEvent: function (item) {
                        var _this = this;
                        this.$confirm('是否确认删除"' + item.application_name + '" ？').then(() => {
                            _this.doDeleteItem(item)
                        }).catch()
                    },
                    doDeleteItem: function (item) {
                        var _this = this;
                        //确认删除
                        $.ajax({
                            url: "{:urlx('wechat/application/deleteApplication')}",
                            data: {id: item.id},
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                if (res.status) {
                                    layer.msg('删除成功');
                                    _this.getList()
                                } else {
                                    layer.msg(res.msg)
                                }
                            }
                        })
                    },
                    editEvent: function (editItem) {
                        location.href = "{:urlx('wechat/application/createApplication')}?id=" + editItem.id;
                    },
                    getList: function () {
                        var _this = this;
                        $.ajax({
                            url: "{:urlx('wechat/application/getApplicationList')}",
                            data: this.form,
                            dataType: 'json',
                            type: 'get',
                            success: function (res) {
                                if (res.status) {
                                    _this.applications = res.data.data;
                                    _this.total = res.data.total;
                                    _this.form.page = res.data.current_page;
                                    _this.form.limit = res.data.per_page
                                }
                            }
                        })
                    },

                    addEvent: function () {
                        location.href = "{:urlx('wechat/application/createApplication')}"
                    },
                    showOauthUrl: function (app_id) {
                        var urlObj = window.Ztbcms.parserUrl(window.location.href);
                        console.log(urlObj);
                        layer.alert(urlObj.protocol + '//' + urlObj.host + "{:urlx('wechat/index/oauth',[],false)}/appid/" + app_id)
                    },
                    showOauthBase: function (app_id) {
                        var urlObj = window.Ztbcms.parserUrl(window.location.href);
                        console.log(urlObj);
                        layer.alert(urlObj.protocol + '//' + urlObj.host + "{:urlx('wechat/index/oauthBase',[],false)}/appid/" + app_id)
                    }
                }
            })
        })
    </script>
</div>