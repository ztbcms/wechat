<div id="app" v-cloak class="embedded-page">
    <section class="embedded-hero">
        <div>
            <h2>半屏小程序管理</h2>
            <p class="embedded-subtitle">统一管理当前小程序的半屏调用关系与授权策略</p>
        </div>
        <div class="authorizer-chip">
            <span class="authorizer-chip__label">当前小程序</span>
            <strong>{{ authorizer.name || '加载中' }}</strong>
            <span>{{ authorizer.appid || authorizerAppid }}</span>
        </div>
    </section>

    <el-card class="authorization-card" shadow="never">
        <div class="authorization-layout">
            <div class="authorization-copy">
                <span class="section-index">01</span>
                <div>
                    <h3>作为半屏小程序时的授权方式</h3>
                    <p>控制其他小程序申请半屏调用当前小程序时的处理策略</p>
                </div>
            </div>
            <div class="authorization-control">
                <el-radio-group
                        v-model="authorizationFlag"
                        :disabled="!authorizationLoaded || settingLoading"
                        size="small">
                    <el-radio-button :label="0">管理员验证</el-radio-button>
                    <el-radio-button :label="1">自动通过</el-radio-button>
                    <el-radio-button :label="2">自动拒绝</el-radio-button>
                </el-radio-group>
                <el-button
                        type="primary"
                        size="small"
                        :loading="settingLoading"
                        :disabled="!authorizationChanged"
                        @click="handleSaveAuthorization">
                    保存设置
                </el-button>
            </div>
        </div>
        <div class="authorization-note" :class="authorizationNoteClass">
            <i :class="authorizationNoteIcon"></i>
            <span>{{ authorizationNote }}</span>
        </div>
    </el-card>

    <el-card class="relationship-card" shadow="never">
        <el-tabs v-model="activeTab" @tab-click="handleTabChange">
            <el-tab-pane name="embedded">
                <span slot="label"><i class="el-icon-position"></i> 调用的半屏小程序</span>
                <div class="tab-intro">
                    <div>
                        <h3>当前小程序作为调用方</h3>
                        <p>管理当前小程序可以通过半屏模式打开的小程序</p>
                    </div>
                    <div class="tab-actions">
                        <el-button size="small" icon="el-icon-refresh" @click="getEmbeddedList">刷新</el-button>
                        <el-button size="small" type="primary" icon="el-icon-plus" @click="openAddDialog">
                            添加半屏小程序
                        </el-button>
                    </div>
                </div>

                <el-table
                        v-loading="listLoading && activeTab === 'embedded'"
                        :data="embeddedList"
                        empty-text="暂无调用关系"
                        class="relationship-table">
                    <el-table-column label="小程序" min-width="220">
                        <template slot-scope="scope">
                            <div class="mini-program-cell">
                                <img v-if="scope.row.headimg" :src="scope.row.headimg" alt="" class="mini-program-avatar">
                                <div v-else class="mini-program-avatar mini-program-avatar--empty">
                                    <i class="el-icon-s-grid"></i>
                                </div>
                                <div>
                                    <strong>{{ getItemName(scope.row) }}</strong>
                                    <span>{{ scope.row.appid }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="reason" label="申请理由" min-width="180">
                        <template slot-scope="scope">
                            <span class="reason-text">{{ scope.row.reason || '—' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="申请时间" width="170">
                        <template slot-scope="scope">{{ formatTime(scope.row.create_time) }}</template>
                    </el-table-column>
                    <el-table-column label="状态" width="110" align="center">
                        <template slot-scope="scope">
                            <el-tag :type="getStatusInfo(scope.row.status).type" size="small">
                                {{ getStatusInfo(scope.row.status).text }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="90" align="center">
                        <template slot-scope="scope">
                            <el-button
                                    type="text"
                                    class="danger-action"
                                    :loading="actionLoadingKey === 'delete:' + scope.row.appid"
                                    @click="handleDeleteEmbedded(scope.row)">
                                删除
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="simple-pagination">
                    <el-button
                            size="small"
                            icon="el-icon-arrow-left"
                            :disabled="embeddedPage <= 1 || listLoading"
                            @click="changeEmbeddedPage(-1)">
                        上一页
                    </el-button>
                    <span>第 {{ embeddedPage }} 页</span>
                    <el-button
                            size="small"
                            :disabled="!embeddedHasNext || listLoading"
                            @click="changeEmbeddedPage(1)">
                        下一页 <i class="el-icon-arrow-right el-icon--right"></i>
                    </el-button>
                </div>
            </el-tab-pane>

            <el-tab-pane name="own">
                <span slot="label"><i class="el-icon-key"></i> 已授权调用方</span>
                <div class="tab-intro">
                    <div>
                        <h3>当前小程序作为半屏小程序</h3>
                        <p>查看并管理已经获得当前小程序半屏调用权限的账号</p>
                    </div>
                    <el-button size="small" icon="el-icon-refresh" @click="getOwnList">刷新</el-button>
                </div>

                <el-table
                        v-loading="listLoading && activeTab === 'own'"
                        :data="ownList"
                        empty-text="暂无已授权调用方"
                        class="relationship-table">
                    <el-table-column label="小程序" min-width="220">
                        <template slot-scope="scope">
                            <div class="mini-program-cell">
                                <img v-if="scope.row.headimg" :src="scope.row.headimg" alt="" class="mini-program-avatar">
                                <div v-else class="mini-program-avatar mini-program-avatar--empty">
                                    <i class="el-icon-s-grid"></i>
                                </div>
                                <div>
                                    <strong>{{ getItemName(scope.row) }}</strong>
                                    <span>{{ scope.row.appid }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="reason" label="申请理由" min-width="180">
                        <template slot-scope="scope">
                            <span class="reason-text">{{ scope.row.reason || '—' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="申请时间" width="170">
                        <template slot-scope="scope">{{ formatTime(scope.row.create_time) }}</template>
                    </el-table-column>
                    <el-table-column label="状态" width="110" align="center">
                        <template slot-scope="scope">
                            <el-tag :type="getStatusInfo(scope.row.status).type" size="small">
                                {{ getStatusInfo(scope.row.status).text }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="110" align="center">
                        <template slot-scope="scope">
                            <el-button
                                    type="text"
                                    class="danger-action"
                                    :loading="actionLoadingKey === 'authorize:' + scope.row.appid"
                                    @click="handleDeleteAuthorized(scope.row)">
                                取消授权
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="simple-pagination">
                    <el-button
                            size="small"
                            icon="el-icon-arrow-left"
                            :disabled="ownPage <= 1 || listLoading"
                            @click="changeOwnPage(-1)">
                        上一页
                    </el-button>
                    <span>第 {{ ownPage }} 页</span>
                    <el-button
                            size="small"
                            :disabled="!ownHasNext || listLoading"
                            @click="changeOwnPage(1)">
                        下一页 <i class="el-icon-arrow-right el-icon--right"></i>
                    </el-button>
                </div>
            </el-tab-pane>
        </el-tabs>
    </el-card>

    <el-dialog
            title="添加半屏小程序"
            :visible.sync="addDialogVisible"
            width="520px"
            :close-on-click-modal="false"
            @closed="resetAddForm">
        <div class="dialog-guide">
            <i class="el-icon-connection"></i>
            <p>提交后是否立即通过取决于目标半屏小程序的授权方式</p>
        </div>
        <el-form ref="addForm" :model="addForm" :rules="addRules" label-width="96px" size="small">
            <el-form-item label="小程序 AppID" prop="embedded_appid">
                <el-input v-model.trim="addForm.embedded_appid" placeholder="请输入目标半屏小程序 AppID"></el-input>
            </el-form-item>
            <el-form-item label="申请理由" prop="apply_reason">
                <el-input
                        v-model.trim="addForm.apply_reason"
                        type="textarea"
                        :rows="3"
                        maxlength="30"
                        show-word-limit
                        placeholder="选填，说明半屏调用场景">
                </el-input>
            </el-form-item>
        </el-form>
        <span slot="footer">
            <el-button size="small" @click="addDialogVisible = false">取消</el-button>
            <el-button size="small" type="primary" :loading="submitLoading" @click="submitAddEmbedded">
                提交申请
            </el-button>
        </span>
    </el-dialog>
</div>

<style>
    .embedded-page {
        --wechat-green: #07c160;
        --wechat-green-dark: #059b4c;
        --ink: #1f2d26;
        --muted: #6c7b72;
        --line: #e7ece9;
        --surface: #f5f8f6;
        padding: 18px;
        color: var(--ink);
    }

    .embedded-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 116px;
        margin-bottom: 16px;
        padding: 24px 28px;
        overflow: hidden;
        border-radius: 10px;
        background: linear-gradient(115deg, #15392a 0%, #0d6b3d 58%, #08a955 100%);
        color: #fff;
        box-shadow: 0 10px 28px rgba(13, 107, 61, .16);
    }

    .embedded-hero::after {
        content: '';
        position: absolute;
        right: -38px;
        bottom: -90px;
        width: 240px;
        height: 240px;
        border: 34px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
    }

    .embedded-hero h2 {
        margin: 0;
        font-size: 25px;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .embedded-subtitle {
        margin: 7px 0 0;
        color: rgba(255, 255, 255, .78);
        font-size: 13px;
    }

    .authorizer-chip {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        min-width: 230px;
        padding: 13px 18px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 8px;
        background: rgba(0, 0, 0, .13);
        backdrop-filter: blur(5px);
    }

    .authorizer-chip__label {
        margin-bottom: 4px;
        color: rgba(255, 255, 255, .6);
        font-size: 11px;
    }

    .authorizer-chip strong {
        margin-bottom: 2px;
        font-size: 15px;
    }

    .authorizer-chip > span:last-child {
        color: rgba(255, 255, 255, .72);
        font-size: 12px;
    }

    .authorization-card,
    .relationship-card {
        border-color: var(--line);
        border-radius: 8px;
    }

    .authorization-card {
        margin-bottom: 16px;
    }

    .authorization-layout,
    .authorization-copy,
    .authorization-control,
    .tab-intro,
    .tab-actions,
    .simple-pagination {
        display: flex;
        align-items: center;
    }

    .authorization-layout,
    .tab-intro {
        justify-content: space-between;
        gap: 20px;
    }

    .authorization-copy {
        gap: 14px;
    }

    .section-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: #e8f8ef;
        color: var(--wechat-green-dark);
        font-size: 13px;
        font-weight: 700;
    }

    .authorization-copy h3,
    .tab-intro h3 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 600;
    }

    .authorization-copy p,
    .tab-intro p {
        margin: 0;
        color: var(--muted);
        font-size: 12px;
    }

    .authorization-control {
        gap: 12px;
    }

    .authorization-note {
        margin-top: 14px;
        padding: 9px 12px;
        border-radius: 5px;
        background: var(--surface);
        color: var(--muted);
        font-size: 12px;
    }

    .authorization-note i {
        margin-right: 6px;
    }

    .authorization-note.is-warning {
        background: #fff8e7;
        color: #9a6700;
    }

    .authorization-note.is-danger {
        background: #fff1f0;
        color: #b42318;
    }

    .relationship-card .el-card__body {
        padding-top: 8px;
    }

    .relationship-card .el-tabs__item.is-active,
    .relationship-card .el-tabs__item:hover {
        color: var(--wechat-green-dark);
    }

    .relationship-card .el-tabs__active-bar {
        background-color: var(--wechat-green);
    }

    .tab-intro {
        min-height: 48px;
        padding: 8px 0 18px;
    }

    .tab-actions {
        gap: 8px;
    }

    .relationship-table {
        border-top: 1px solid var(--line);
    }

    .mini-program-cell {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .mini-program-avatar {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--surface);
    }

    .mini-program-avatar--empty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #8aa096;
        font-size: 17px;
    }

    .mini-program-cell strong,
    .mini-program-cell span {
        display: block;
    }

    .mini-program-cell strong {
        margin-bottom: 3px;
        font-size: 13px;
        font-weight: 600;
    }

    .mini-program-cell span {
        color: var(--muted);
        font-size: 12px;
    }

    .reason-text {
        color: #4d5c54;
        line-height: 1.6;
    }

    .danger-action {
        color: #d8463f !important;
    }

    .simple-pagination {
        justify-content: flex-end;
        gap: 12px;
        padding-top: 18px;
    }

    .simple-pagination span {
        color: var(--muted);
        font-size: 12px;
    }

    .dialog-guide {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: -4px 0 20px;
        padding: 11px 13px;
        border-left: 3px solid var(--wechat-green);
        background: #f1f8f4;
        color: #587064;
    }

    .dialog-guide i {
        color: var(--wechat-green-dark);
        font-size: 18px;
    }

    .dialog-guide p {
        margin: 0;
        font-size: 12px;
    }

    @media (max-width: 900px) {
        .embedded-hero,
        .authorization-layout,
        .tab-intro {
            align-items: flex-start;
            flex-direction: column;
        }

        .authorizer-chip,
        .authorization-control {
            width: 100%;
            box-sizing: border-box;
        }

        .authorization-control {
            align-items: flex-start;
        }
    }
</style>

<script>
    $(function () {
        new Vue({
            el: '#app',
            data: {
                authorizerAppid: '',
                authorizer: {
                    name: '',
                    appid: '',
                },
                activeTab: 'embedded',
                authorizationFlag: null,
                savedAuthorizationFlag: null,
                authorizationLoaded: false,
                embeddedList: [],
                ownList: [],
                embeddedLoaded: false,
                ownLoaded: false,
                embeddedPage: 1,
                ownPage: 1,
                pageSize: 10,
                embeddedHasNext: false,
                ownHasNext: false,
                addDialogVisible: false,
                addForm: {
                    embedded_appid: '',
                    apply_reason: '',
                },
                addRules: {
                    embedded_appid: [
                        {required: true, message: '请输入目标半屏小程序 AppID', trigger: 'blur'},
                    ],
                    apply_reason: [
                        {max: 30, message: '申请理由不能超过 30 个字符', trigger: 'blur'},
                    ],
                },
                listLoading: false,
                submitLoading: false,
                settingLoading: false,
                actionLoadingKey: '',
            },
            computed: {
                authorizationChanged: function () {
                    return this.authorizationLoaded
                        && !this.settingLoading
                        && this.authorizationFlag !== this.savedAuthorizationFlag;
                },
                authorizationNote: function () {
                    if (!this.authorizationLoaded) {
                        return '授权方式将在列表加载成功后显示';
                    }
                    if (this.authorizationFlag === 1) {
                        return '自动通过：其他小程序发起申请后将立即获得半屏调用权限';
                    }
                    if (this.authorizationFlag === 2) {
                        return '自动拒绝：其他小程序发起的半屏调用申请将被直接拒绝';
                    }
                    return '管理员验证：申请会发送给当前小程序管理员，确认后才会生效';
                },
                authorizationNoteClass: function () {
                    if (this.authorizationFlag === 1) {
                        return 'is-warning';
                    }
                    if (this.authorizationFlag === 2) {
                        return 'is-danger';
                    }
                    return '';
                },
                authorizationNoteIcon: function () {
                    return this.authorizationFlag === 2 ? 'el-icon-warning-outline' : 'el-icon-info';
                },
            },
            mounted: function () {
                this.authorizerAppid = this.getUrlQuery('authorizer_appid');
                if (!this.authorizerAppid) {
                    layer.alert('缺少参数 authorizer_appid');
                    return;
                }
                this.getEmbeddedList();
            },
            methods: {
                handleTabChange: function () {
                    if (this.activeTab === 'own' && !this.ownLoaded) {
                        this.getOwnList();
                    }
                    if (this.activeTab === 'embedded' && !this.embeddedLoaded) {
                        this.getEmbeddedList();
                    }
                },
                applyListData: function (data) {
                    this.authorizer = data.authorizer || this.authorizer;
                    this.authorizationFlag = Number(data.embedded_flag);
                    this.savedAuthorizationFlag = Number(data.embedded_flag);
                    this.authorizationLoaded = true;
                },
                getEmbeddedList: function () {
                    var that = this;
                    this.listLoading = true;
                    this.httpGet('/wechat/open.MiniProgramEmbeddedAdmin/index', {
                        _action: 'getEmbeddedList',
                        authorizer_appid: this.authorizerAppid,
                        page: this.embeddedPage,
                        page_size: this.pageSize,
                    }, function (res) {
                        that.listLoading = false;
                        if (!res.status) {
                            layer.alert(res.msg || '获取调用列表失败');
                            return;
                        }
                        that.applyListData(res.data);
                        that.embeddedList = res.data.items || [];
                        that.embeddedHasNext = Boolean(res.data.has_next);
                        that.embeddedLoaded = true;
                        if (that.embeddedList.length === 0 && that.embeddedPage > 1) {
                            that.embeddedPage -= 1;
                            that.getEmbeddedList();
                        }
                    });
                },
                getOwnList: function () {
                    var that = this;
                    this.listLoading = true;
                    this.httpGet('/wechat/open.MiniProgramEmbeddedAdmin/index', {
                        _action: 'getOwnList',
                        authorizer_appid: this.authorizerAppid,
                        page: this.ownPage,
                        page_size: this.pageSize,
                    }, function (res) {
                        that.listLoading = false;
                        if (!res.status) {
                            layer.alert(res.msg || '获取授权列表失败');
                            return;
                        }
                        that.applyListData(res.data);
                        that.ownList = res.data.items || [];
                        that.ownHasNext = Boolean(res.data.has_next);
                        that.ownLoaded = true;
                        if (that.ownList.length === 0 && that.ownPage > 1) {
                            that.ownPage -= 1;
                            that.getOwnList();
                        }
                    });
                },
                changeEmbeddedPage: function (offset) {
                    this.embeddedPage = Math.max(1, this.embeddedPage + offset);
                    this.getEmbeddedList();
                },
                changeOwnPage: function (offset) {
                    this.ownPage = Math.max(1, this.ownPage + offset);
                    this.getOwnList();
                },
                openAddDialog: function () {
                    this.addDialogVisible = true;
                },
                resetAddForm: function () {
                    this.addForm = {
                        embedded_appid: '',
                        apply_reason: '',
                    };
                    if (this.$refs.addForm) {
                        this.$refs.addForm.clearValidate();
                    }
                },
                submitAddEmbedded: function () {
                    var that = this;
                    this.$refs.addForm.validate(function (valid) {
                        if (!valid || that.submitLoading) {
                            return;
                        }
                        that.submitLoading = true;
                        that.httpPost('/wechat/open.MiniProgramEmbeddedAdmin/index', {
                            _action: 'addEmbedded',
                            authorizer_appid: that.authorizerAppid,
                            embedded_appid: that.addForm.embedded_appid,
                            apply_reason: that.addForm.apply_reason,
                        }, function (res) {
                            that.submitLoading = false;
                            if (!res.status) {
                                layer.alert(res.msg || '添加失败');
                                return;
                            }
                            layer.msg(res.msg || '添加成功');
                            that.addDialogVisible = false;
                            that.embeddedPage = 1;
                            that.getEmbeddedList();
                        });
                    });
                },
                handleDeleteEmbedded: function (item) {
                    var that = this;
                    var itemName = this.getItemName(item);
                    layer.confirm('确定删除“' + itemName + '”（' + item.appid + '）吗？', {title: '删除半屏小程序'}, function (index) {
                        layer.close(index);
                        that.actionLoadingKey = 'delete:' + item.appid;
                        that.httpPost('/wechat/open.MiniProgramEmbeddedAdmin/index', {
                            _action: 'deleteEmbedded',
                            authorizer_appid: that.authorizerAppid,
                            embedded_appid: item.appid,
                        }, function (res) {
                            that.actionLoadingKey = '';
                            if (!res.status) {
                                layer.alert(res.msg || '删除失败');
                                return;
                            }
                            layer.msg(res.msg || '删除成功');
                            that.getEmbeddedList();
                        });
                    });
                },
                handleDeleteAuthorized: function (item) {
                    var that = this;
                    var itemName = this.getItemName(item);
                    var message = '确定取消“' + itemName + '”（' + item.appid + '）的授权吗？取消后该小程序将不能继续半屏调用当前小程序。';
                    layer.confirm(message, {title: '取消半屏调用授权'}, function (index) {
                        layer.close(index);
                        that.actionLoadingKey = 'authorize:' + item.appid;
                        that.httpPost('/wechat/open.MiniProgramEmbeddedAdmin/index', {
                            _action: 'deleteAuthorizedEmbedded',
                            authorizer_appid: that.authorizerAppid,
                            authorized_appid: item.appid,
                        }, function (res) {
                            that.actionLoadingKey = '';
                            if (!res.status) {
                                layer.alert(res.msg || '取消授权失败');
                                return;
                            }
                            layer.msg(res.msg || '取消授权成功');
                            that.getOwnList();
                        });
                    });
                },
                handleSaveAuthorization: function () {
                    if (!this.authorizationChanged) {
                        return;
                    }
                    var that = this;
                    var labels = ['管理员验证', '自动通过', '自动拒绝'];
                    var message = '确定将授权方式设置为“' + labels[this.authorizationFlag] + '”吗？';
                    layer.confirm(message, {title: '设置授权方式'}, function (index) {
                        layer.close(index);
                        that.settingLoading = true;
                        that.httpPost('/wechat/open.MiniProgramEmbeddedAdmin/index', {
                            _action: 'setAuthorizedEmbedded',
                            authorizer_appid: that.authorizerAppid,
                            flag: that.authorizationFlag,
                        }, function (res) {
                            that.settingLoading = false;
                            if (!res.status) {
                                layer.alert(res.msg || '设置失败');
                                return;
                            }
                            layer.msg(res.msg || '设置成功');
                            if (that.activeTab === 'own') {
                                that.getOwnList();
                            } else {
                                that.getEmbeddedList();
                            }
                        });
                    });
                },
                getItemName: function (item) {
                    return item.nickname || item.name || item.appid || '未知小程序';
                },
                getStatusInfo: function (status) {
                    var map = {
                        1: {text: '待验证', type: 'warning'},
                        2: {text: '已通过', type: 'success'},
                        3: {text: '已拒绝', type: 'danger'},
                        4: {text: '已超时', type: 'info'},
                        5: {text: '已撤销', type: 'info'},
                        6: {text: '已取消授权', type: 'info'},
                    };
                    return map[Number(status)] || {text: String(status || '未知'), type: 'info'};
                },
                formatTime: function (timestamp) {
                    var number = Number(timestamp);
                    if (!number) {
                        return '—';
                    }
                    var date = new Date(number * 1000);
                    var pad = function (value) {
                        return String(value).padStart(2, '0');
                    };
                    return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
                        + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
                },
            },
        });
    });
</script>
