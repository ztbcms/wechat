<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>公众号用户列表</span>
            </div>
            <div>
                <el-form :inline="true" :model="searchData" class="demo-form-inline">
                    <el-form-item label="appid">
                        <el-input v-model="searchData.app_id" placeholder="请输入小程序appid"></el-input>
                    </el-form-item>
                    <el-form-item label="open_id">
                        <el-input v-model="searchData.open_id" placeholder="请输入用户openid"></el-input>
                    </el-form-item>
                    <el-form-item label="昵称">
                        <el-input v-model="searchData.nick_name" placeholder="请输入用户昵称"></el-input>
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
                        label="头像"
                        align="center"
                        min-width="100">
                        <template slot-scope="scope">
                            <img class="avatar" :src="scope.row.avatar_url" alt="">
                        </template>
                    </el-table-column>
                    <el-table-column
                        prop="nick_name"
                        label="昵称"
                        align="center"
                        min-width="180">
                    </el-table-column>
                    <el-table-column
                        prop="country"
                        label="国家"
                        align="center"
                        min-width="100">
                    </el-table-column>
                    <el-table-column
                        prop="province"
                        label="省份"
                        align="center"
                        min-width="100">
                    </el-table-column>
                    <el-table-column
                        prop="city"
                        label="城市"
                        align="center"
                        min-width="100">
                    </el-table-column>
                    <el-table-column
                        prop="language"
                        label="语言"
                        align="center"
                        min-width="100">
                    </el-table-column>
                    <el-table-column
                        prop="open_id"
                        label="open_id"
                        align="center"
                        min-width="250">
                    </el-table-column>
                    <el-table-column
                        prop="union_id"
                        label="union_id"
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
                    nick_name: ""
                },
                users: [],
                page: 1,
                limit: 20,
                totalPages: 0,
                totalItems: 0
            },
            mounted() {
                this.getRefunds();
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
                            _this.httpPost('{:urlx("wechat/office/deleteUser")}', postData, function (res) {
                                if (res.status) {
                                    _this.$message.success('删除成功');
                                    _this.getRefunds();
                                } else {
                                    _this.$message.error(res.msg);
                                }
                            })
                        }
                    });
                },
                searchEvent() {
                    this.page = 1;
                    this.getRefunds();
                },
                currentChangeEvent(page) {
                    this.page = page;
                    this.getRefunds();
                },
                getRefunds: function () {
                    var _this = this;
                    var where = Object.assign({
                        page: this.page,
                        limit: this.limit
                    }, this.searchData);
                    $.ajax({
                        url: "{:urlx('wechat/office/users')}",
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