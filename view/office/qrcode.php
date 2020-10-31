<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>参数二维码列表</span>
        </div>
        <div>
            <el-form :inline="true" :model="searchData" class="demo-form-inline">
                <el-form-item label="appid">
                    <el-input v-model="searchData.app_id" placeholder="请输入公众号appid"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="searchEvent">查询</el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="createCodeEvent">添加参数二维码</el-button>
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
                        label="参数二维码"
                        align="center"
                        min-width="100">
                    <template slot-scope="scope">
                        <img class="avatar" @click="showImageDialogVisible=true;showImageUrl=scope.row.qrcode_url"
                             :src="scope.row.qrcode_url" alt="">
                    </template>
                </el-table-column>
                <el-table-column
                        label="类型"
                        align="center"
                        min-width="100">
                    <template slot-scope="scope">
                        <div v-if="scope.row.type == 0">
                            临时
                        </div>
                        <div v-else>
                            永久
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="param"
                        label="参数"
                        align="center"
                        min-width="180">
                </el-table-column>
                <el-table-column
                        align="center"
                        label="过期时间"
                        min-width="180">
                    <template slot-scope="scope">
                        <div v-if="scope.row.expire_time == 0">
                            -
                        </div>
                        <div v-else>
                            {{scope.row.expire_time|getFormatDatetime}}
                        </div>
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
    <el-dialog
            :visible.sync="showImageDialogVisible"
            width="300px">
        <div>
            <img style="width: 100%;" :src="showImageUrl" alt="">
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button type="primary" @click="showImageDialogVisible = false">确 定</el-button>
        </div>
    </el-dialog>
    <div>
        <el-dialog
                title="添加参数二维码"
                :visible.sync="createDialogVisible"
                width="500px">
            <div>
                <el-form label-width="120px">
                    <el-form-item label="选择小公众号">
                        <el-select v-model="createAppId" placeholder="请选择公众号">
                            <el-option v-for="item in offices" :label="item.application_name"
                                       :value="item.app_id"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="二维码类型">
                        <el-radio v-model="createCodeType" label="0">临时</el-radio>
                        <el-radio v-model="createCodeType" label="1">永久</el-radio>
                    </el-form-item>
                    <el-form-item v-if="createCodeType=='0'" label="过期时间">
                        <el-input-number v-model="createExpireTime" :min="1" :max="30"
                                         label="过期时间"></el-input-number>
                    </el-form-item>
                    <el-form-item label="二维码参数">
                        <el-input v-model="createCodeParam" placeholder="请输入二维码参数"></el-input>
                        <div v-if="createCodeType=='0'" class="tip">
                            临时类二维码参数可以是32位内字符串
                        </div>
                        <div v-if="createCodeType=='1'" class="tip">
                            永久类二维码参数只可以数字1~100000
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button @click="createDialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitCreateCode">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</div>
<style>
    .avatar {
        width: 60px;
        height: 60px;
        cursor: pointer;
    }

    .page-container {
        margin-top: 0px;
        text-align: center;
        padding: 10px;
    }

    .tip {
        font-size: 12px;
        color: #666666;
    }
</style>
<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                showImageDialogVisible: false,
                showImageUrl: "",
                searchData: {
                    open_id: "",
                    app_id: "",
                    nick_name: ""
                },
                users: [],
                page: 1,
                limit: 20,
                totalPages: 0,
                totalItems: 0,
                createDialogVisible: false,
                createAppId: 0,
                createCodeType: "0",
                createCodeParam: '',
                createExpireTime: 1,
                offices: []

            },
            mounted: function () {
                this.getCodeList();
                this.getOffices();
            },
            methods: {
                submitCreateCode: function () {
                    const postData = {
                        app_id: this.createAppId,
                        type: this.createCodeType,
                        expire_time: this.createExpireTime,
                        param: this.createCodeParam,
                        action : 'createCode'
                    };
                    const _this = this;
                    this.httpPost("{:api_url('wechat/office/qrcode')}", postData, function (res) {
                        if (res.status) {
                            _this.$message.success("创建成功");
                            _this.page = 1;
                            _this.getCodeList();
                            _this.createDialogVisible = false
                        } else {
                            _this.$message.error(res.msg);
                        }
                    })
                },
                getOffices: function () {
                    const _this = this;
                    //获取公众号
                    this.httpGet('{:api_url("/wechat/Application/getApplicationList")}', {account_type: "office"}, function (res) {
                        _this.offices = res.data.data;
                        if (_this.offices.length > 0) {
                            _this.createAppId = _this.offices[0].app_id;
                        }
                    })
                },
                createCodeEvent: function () {
                    this.createDialogVisible = true
                },
                deleteEvent: function (row) {
                    var postData = {
                        id: row.id,
                        action : 'delQrcode'
                    };
                    console.log('callback', postData);
                    var _this = this;
                    this.$confirm('是否确认删除该记录', '提示', {
                        callback: function (e) {
                            if (e !== 'confirm') {
                                return;
                            }
                            _this.httpPost("{:api_url('wechat/office/qrcode')}", postData, function (res) {
                                if (res.status) {
                                    _this.$message.success('删除成功');
                                    _this.getCodeList();
                                } else {
                                    _this.$message.error(res.msg);
                                }
                            })
                        }
                    });

                },
                searchEvent: function () {
                    this.page = 1;
                    this.getCodeList();
                },
                currentChangeEvent: function (page) {
                    this.page = page;
                    this.getCodeList();
                },
                getCodeList: function () {
                    var _this = this;
                    var where = Object.assign({
                        page: this.page,
                        limit: this.limit,
                        action : "ajaxList"
                    }, this.searchData);
                    $.ajax({
                        url: "{:api_url('wechat/office/qrcode')}",
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

