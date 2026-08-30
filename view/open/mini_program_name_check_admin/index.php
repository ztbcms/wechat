<div id="app" class="name-check-page" v-cloak>
    <el-card class="name-check-card" shadow="never">
        <div slot="header" class="card-header">
            <div>
                <div class="card-title">小程序名称检测</div>
                <div class="card-subtitle">逐项检测名称是否命中微信关键字策略</div>
            </div>
        </div>

        <el-alert
            class="scope-alert"
            title="检测账号需要是正常授权且通过 API 创建的小程序，最终名称是否可用以微信实际审核结果为准"
            type="info"
            :closable="false"
            show-icon>
        </el-alert>

        <el-form class="check-form" label-position="top" size="small">
            <el-form-item label="检测账号">
                <el-select
                    v-model="form.authorizer_appid"
                    v-loading="optionsLoading"
                    :disabled="queryLoading || authorizerOptions.length === 0"
                    filterable
                    placeholder="请选择授权小程序"
                    style="width: 100%;">
                    <el-option
                        v-for="item in authorizerOptions"
                        :key="item.authorizer_appid"
                        :label="item.name + ' / ' + item.authorizer_appid"
                        :value="item.authorizer_appid">
                    </el-option>
                </el-select>
                <div v-if="!optionsLoading && authorizerOptions.length === 0" class="form-tip form-tip-danger">
                    暂无正常授权的小程序，请先完成小程序授权
                </div>
            </el-form-item>

            <el-form-item label="待检测名称">
                <el-input
                    v-model="form.names"
                    type="textarea"
                    :autosize="{ minRows: 7, maxRows: 15 }"
                    :disabled="queryLoading"
                    placeholder="每行输入一个小程序名称，例如：&#10;示例商城&#10;示例服务助手">
                </el-input>
                <div class="form-tip">空行将自动忽略，名称会按照输入顺序逐个检测</div>
            </el-form-item>

            <div class="form-actions">
                <el-button
                    type="primary"
                    icon="el-icon-search"
                    :loading="queryLoading"
                    :disabled="optionsLoading || authorizerOptions.length === 0"
                    @click="handleCheck">
                    {{ queryLoading ? '正在检测' : '开始检测' }}
                </el-button>
            </div>
        </el-form>
    </el-card>

    <el-card class="result-card" shadow="never">
        <div slot="header" class="result-header">
            <span>检测结果</span>
            <span v-if="results.length > 0" class="result-count">共 {{ results.length }} 项</span>
        </div>

        <el-table :data="results" border stripe empty-text="暂无检测结果" style="width: 100%;">
            <el-table-column type="index" label="#" width="56" align="center"></el-table-column>
            <el-table-column prop="name" label="名称" min-width="150"></el-table-column>
            <el-table-column label="名称是否可用" width="130" align="center">
                <template slot-scope="scope">
                    <el-tag v-if="scope.row.availability === 'available'" type="success" size="small">可用</el-tag>
                    <el-tag v-else-if="scope.row.availability === 'conditional'" type="warning" size="small">
                        需补充材料
                    </el-tag>
                    <el-tag v-else-if="scope.row.availability === 'unavailable'" type="danger" size="small">
                        不可用
                    </el-tag>
                    <el-tag v-else type="danger" size="small">检测异常</el-tag>
                </template>
            </el-table-column>
            <el-table-column label="命中关键字策略" width="140" align="center">
                <template slot-scope="scope">
                    <span v-if="scope.row.hit_condition === null">-</span>
                    <span v-else>{{ scope.row.hit_condition ? '是' : '否' }}</span>
                </template>
            </el-table-column>
            <el-table-column label="结果说明" min-width="300">
                <template slot-scope="scope">
                    <div class="wording-cell">{{ scope.row.wording || '-' }}</div>
                </template>
            </el-table-column>
            <el-table-column label="接口详情" min-width="220">
                <template slot-scope="scope">
                    <span :class="scope.row.error ? 'error-text' : ''">{{ scope.row.error || '-' }}</span>
                </template>
            </el-table-column>
        </el-table>
    </el-card>
</div>

<style>
    .name-check-page {
        --wechat-green: #07c160;
        --wechat-green-dark: #059a4c;
        --border-color: #e7e9ec;
        --muted-color: #8a9199;
        max-width: 1180px;
        margin: 0 auto;
    }

    .name-check-card,
    .result-card {
        border-color: var(--border-color);
    }

    .result-card {
        margin-top: 18px;
    }

    .card-header,
    .result-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-title {
        color: #1f252d;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
    }

    .card-subtitle {
        margin-top: 3px;
        color: var(--muted-color);
        font-size: 12px;
    }

    .card-mark {
        padding: 5px 9px;
        border: 1px solid rgba(7, 193, 96, 0.24);
        border-radius: 3px;
        color: var(--wechat-green-dark);
        background: rgba(7, 193, 96, 0.06);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
    }

    .scope-alert {
        margin-bottom: 22px;
    }

    .check-form {
        max-width: 760px;
    }

    .form-tip {
        margin-top: 7px;
        color: var(--muted-color);
        font-size: 12px;
        line-height: 1.5;
    }

    .form-tip-danger,
    .error-text {
        color: #f56c6c;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 2px;
    }

    .form-actions .el-button--primary {
        min-width: 112px;
        border-color: var(--wechat-green);
        background: var(--wechat-green);
    }

    .form-actions .el-button--primary:hover,
    .form-actions .el-button--primary:focus {
        border-color: var(--wechat-green-dark);
        background: var(--wechat-green-dark);
    }

    .result-header {
        color: #303640;
        font-size: 15px;
        font-weight: 600;
    }

    .result-count {
        color: var(--muted-color);
        font-size: 12px;
        font-weight: 400;
    }

    .wording-cell {
        white-space: normal;
        word-break: break-word;
        line-height: 1.65;
    }

    @media (max-width: 768px) {
        .name-check-page {
            max-width: none;
        }

        .card-mark {
            display: none;
        }
    }
</style>

<script>
    $(function() {
        new Vue({
            el: '#app',
            data: {
                authorizerOptions: [],
                form: {
                    authorizer_appid: '',
                    names: '',
                },
                results: [],
                optionsLoading: false,
                queryLoading: false,
            },
            mounted: function() {
                this.loadAuthorizerOptions();
            },
            methods: {
                /**
                 * 加载可用授权小程序
                 */
                loadAuthorizerOptions: function() {
                    var that = this;
                    that.optionsLoading = true;
                    $.ajax({
                        url: '/wechat/open.MiniProgramNameCheckAdmin/index',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            _action: 'getAuthorizerOptions',
                        },
                        success: function(res) {
                            if (!res.status) {
                                layer.alert(res.msg || '授权小程序加载失败');
                                return;
                            }
                            that.authorizerOptions = res.data.items || [];
                            if (!that.form.authorizer_appid && that.authorizerOptions.length > 0) {
                                that.form.authorizer_appid = that.authorizerOptions[0].authorizer_appid;
                            }
                        },
                        error: function() {
                            layer.alert('授权小程序加载失败');
                        },
                        complete: function() {
                            that.optionsLoading = false;
                        },
                    });
                },

                /**
                 * 解析名称输入
                 *
                 * @return {string[]}
                 */
                parseNames: function() {
                    return this.form.names
                        .split(/\r\n|\n|\r/)
                        .map(function(name) {
                            return name.trim();
                        })
                        .filter(function(name) {
                            return name !== '';
                        });
                },

                /**
                 * 开始逐项检测
                 */
                handleCheck: function() {
                    if (this.queryLoading) {
                        return;
                    }
                    if (!this.form.authorizer_appid) {
                        layer.msg('请选择检测账号');
                        return;
                    }

                    var names = this.parseNames();
                    if (names.length === 0) {
                        layer.msg('请输入待检测名称');
                        return;
                    }

                    this.results = [];
                    this.queryLoading = true;
                    this.checkNameAt(names, 0);
                },

                /**
                 * 按顺序检测单个名称
                 *
                 * @param {string[]} names 名称列表
                 * @param {number} index 当前索引
                 */
                checkNameAt: function(names, index) {
                    var that = this;
                    if (index >= names.length) {
                        that.queryLoading = false;
                        return;
                    }

                    var name = names[index];
                    $.ajax({
                        url: '/wechat/open.MiniProgramNameCheckAdmin/index',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _action: 'checkName',
                            authorizer_appid: that.form.authorizer_appid,
                            nick_name: name,
                        },
                        success: function(res) {
                            that.appendResult(name, res);
                        },
                        error: function() {
                            that.results.push({
                                name: name,
                                success: false,
                                availability: 'unknown',
                                hit_condition: null,
                                wording: '',
                                error: '请求名称检测接口失败',
                            });
                        },
                        complete: function() {
                            that.checkNameAt(names, index + 1);
                        },
                    });
                },

                /**
                 * 追加单项检测结果
                 *
                 * @param {string} name 名称
                 * @param {Object} response 接口响应
                 */
                appendResult: function(name, response) {
                    var data = response.data || {};
                    var success = response.status === true;
                    this.results.push({
                        name: name,
                        success: success,
                        availability: success ? (data.availability || 'unknown') : 'unknown',
                        hit_condition: success && data.hit_condition !== null && data.hit_condition !== undefined
                            ? Boolean(data.hit_condition)
                            : null,
                        wording: success ? (data.wording || '') : '',
                        error: success && !data.errcode ? '' : this.formatError(response),
                    });
                },

                /**
                 * 格式化接口错误
                 *
                 * @param {Object} response 接口响应
                 * @return {string}
                 */
                formatError: function(response) {
                    var data = response.data || {};
                    var message = data.errmsg || response.msg || '检测失败';
                    if (data.errcode === undefined || data.errcode === null) {
                        return message;
                    }
                    return '[' + data.errcode + '] ' + message;
                },
            },
        });
    });
</script>
