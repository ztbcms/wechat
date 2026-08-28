<div id="app" v-cloak class="operation-center">
    <section class="operation-hero">
        <div class="operation-hero__identity">
            <span class="operation-hero__eyebrow">MINI PROGRAM / OPERATIONS</span>
            <h1>运维中心</h1>
            <p>集中查看性能趋势、实时日志与 JS 异常，快速定位小程序线上问题</p>
        </div>
        <div class="operation-hero__account">
            <span class="status-pulse"></span>
            <div>
                <strong>{{ authorizerName || '未命名小程序' }}</strong>
                <code>{{ authorizerAppid }}</code>
            </div>
        </div>
        <div class="operation-hero__grid" aria-hidden="true"></div>
    </section>

    <el-card class="operation-panel" shadow="never">
        <el-tabs v-model="activeTab" class="operation-tabs" @tab-click="handleTabChange">
            <el-tab-pane name="performance">
                <span slot="label"><i class="el-icon-odometer"></i> 性能数据</span>
                <div class="tab-heading">
                    <div>
                        <span class="tab-heading__index">01</span>
                        <h2>性能数据</h2>
                        <p>按时间、终端环境和访问来源检查关键耗时指标</p>
                    </div>
                    <el-button
                            type="text"
                            icon="el-icon-refresh"
                            :loading="sceneLoading"
                            @click="loadSceneOptions(true)">
                        刷新访问来源
                    </el-button>
                </div>

                <el-form :inline="true" :model="performance.form" size="small" class="query-form">
                    <el-form-item label="性能指标">
                        <el-select v-model="performance.form.cost_time_type" style="width: 170px">
                            <el-option label="启动总耗时" value="1"></el-option>
                            <el-option label="下载耗时" value="2"></el-option>
                            <el-option label="初次渲染耗时" value="3"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="时间范围">
                        <el-date-picker
                                v-model="performance.form.time_range"
                                type="datetimerange"
                                value-format="timestamp"
                                range-separator="至"
                                start-placeholder="开始时间"
                                end-placeholder="结束时间"
                                :clearable="false"
                                style="width: 365px">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="系统平台">
                        <el-select v-model="performance.form.device" style="width: 130px">
                            <el-option label="全部" value="@_all"></el-option>
                            <el-option label="iOS" value="1"></el-option>
                            <el-option label="Android" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="performance.form.cost_time_type === '1'" label="下载代码包">
                        <el-select v-model="performance.form.is_download_code" style="width: 130px">
                            <el-option label="全部" value="@_all"></el-option>
                            <el-option label="是" value="1"></el-option>
                            <el-option label="否" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="performance.form.cost_time_type === '1' || performance.form.cost_time_type === '2'" label="访问来源">
                        <el-select
                                v-model="performance.form.scene"
                                filterable
                                :loading="sceneLoading"
                                style="width: 220px">
                            <el-option
                                    v-for="item in sceneOptions"
                                    :key="item.value"
                                    :label="item.name + ' · ' + item.value"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="performance.form.cost_time_type === '2'" label="网络环境">
                        <el-select v-model="performance.form.networktype" style="width: 130px">
                            <el-option label="全部" value="@_all"></el-option>
                            <el-option label="Wi-Fi" value="wifi"></el-option>
                            <el-option label="4G" value="4g"></el-option>
                            <el-option label="3G" value="3g"></el-option>
                            <el-option label="2G" value="2g"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button
                                type="primary"
                                icon="el-icon-search"
                                :loading="performance.loading"
                                @click="queryPerformance">
                            查询性能
                        </el-button>
                    </el-form-item>
                </el-form>

                <div class="result-strip">
                    <span><i class="el-icon-data-line"></i> 查询结果</span>
                    <strong>{{ performance.items.length }}</strong>
                    <small>条时间序列数据</small>
                </div>
                <el-table
                        v-loading="performance.loading"
                        :data="performance.items"
                        empty-text="设置查询条件后获取性能数据"
                        class="operation-table">
                    <el-table-column label="日期" min-width="170">
                        <template slot-scope="scope">
                            <span class="mono-value">{{ formatPerformanceDate(scope.row.ref_date) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="指标" min-width="180">
                        <template slot-scope="scope">
                            <el-tag size="small" effect="plain">{{ getPerformanceType(scope.row.cost_time_type) }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="平均耗时" min-width="180">
                        <template slot-scope="scope">
                            <span class="metric-value">{{ formatNumber(scope.row.cost_time) }}</span>
                            <span class="metric-unit">ms</span>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tab-pane>

            <el-tab-pane name="realtime">
                <span slot="label"><i class="el-icon-document"></i> 实时日志</span>
                <div class="tab-heading">
                    <div>
                        <span class="tab-heading__index">02</span>
                        <h2>实时日志</h2>
                        <p>检索最近 3 天的终端日志，沿 Trace ID 还原一次启动链路</p>
                    </div>
                    <el-tag type="warning" effect="plain">最近 3 天</el-tag>
                </div>

                <el-form :inline="true" :model="realtime.form" size="small" class="query-form">
                    <el-form-item label="日期">
                        <el-date-picker
                                v-model="realtime.form.date"
                                type="date"
                                value-format="yyyyMMdd"
                                format="yyyy-MM-dd"
                                :picker-options="realtimeDatePickerOptions"
                                :clearable="false"
                                style="width: 150px">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="开始时间">
                        <el-time-picker
                                v-model="realtime.form.begin_time"
                                value-format="HH:mm:ss"
                                format="HH:mm:ss"
                                :clearable="false"
                                style="width: 140px">
                        </el-time-picker>
                    </el-form-item>
                    <el-form-item label="结束时间">
                        <el-time-picker
                                v-model="realtime.form.end_time"
                                value-format="HH:mm:ss"
                                format="HH:mm:ss"
                                :clearable="false"
                                style="width: 140px">
                        </el-time-picker>
                    </el-form-item>
                    <el-form-item label="日志等级">
                        <el-select v-model="realtime.form.level" style="width: 130px">
                            <el-option label="全部" value=""></el-option>
                            <el-option label="Info 及以上" value="2"></el-option>
                            <el-option label="Warn 及以上" value="4"></el-option>
                            <el-option label="Error" value="8"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button
                                type="primary"
                                icon="el-icon-search"
                                :loading="realtime.loading"
                                @click="queryRealtimeLogs(true)">
                            查询日志
                        </el-button>
                    </el-form-item>
                </el-form>

                <el-collapse class="advanced-query">
                    <el-collapse-item name="advanced">
                        <template slot="title">
                            <i class="el-icon-set-up"></i>
                            高级筛选
                            <span>按链路、页面或用户进一步定位</span>
                        </template>
                        <el-form :inline="true" :model="realtime.form" size="small" class="advanced-query__form">
                            <el-form-item label="Trace ID">
                                <el-input v-model.trim="realtime.form.trace_id" clearable placeholder="启动链路唯一 ID" style="width: 250px"></el-input>
                            </el-form-item>
                            <el-form-item label="页面路径">
                                <el-input v-model.trim="realtime.form.url" clearable placeholder="pages/index/index" style="width: 230px"></el-input>
                            </el-form-item>
                            <el-form-item label="OpenID">
                                <el-input v-model.trim="realtime.form.openid" clearable placeholder="用户 OpenID" style="width: 230px"></el-input>
                            </el-form-item>
                            <el-form-item label="FilterMsg">
                                <el-input v-model.trim="realtime.form.filter_msg" clearable placeholder="开发者过滤标记" style="width: 230px"></el-input>
                            </el-form-item>
                        </el-form>
                    </el-collapse-item>
                </el-collapse>

                <el-table
                        v-loading="realtime.loading"
                        :data="realtime.items"
                        empty-text="设置条件后查询实时日志"
                        class="operation-table log-table">
                    <el-table-column type="expand">
                        <template slot-scope="scope">
                            <div class="log-message-list">
                                <div v-for="(message, index) in scope.row.msg || []" :key="index" class="log-message">
                                    <span class="log-message__time">{{ formatTimestamp(message.time) }}</span>
                                    <el-tag :type="getLogLevelInfo(message.level).type" size="mini">
                                        {{ getLogLevelInfo(message.level).text }}
                                    </el-tag>
                                    <pre>{{ formatLogMessage(message.msg) }}</pre>
                                </div>
                                <div v-if="!scope.row.msg || scope.row.msg.length === 0" class="empty-inline">暂无日志内容</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="时间" min-width="165">
                        <template slot-scope="scope"><span class="mono-value">{{ formatTimestamp(scope.row.timestamp) }}</span></template>
                    </el-table-column>
                    <el-table-column label="等级" width="100" align="center">
                        <template slot-scope="scope">
                            <el-tag :type="getLogLevelInfo(scope.row.level).type" size="small">{{ getLogLevelInfo(scope.row.level).text }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="平台" width="90">
                        <template slot-scope="scope">{{ getPlatformName(scope.row.platform) }}</template>
                    </el-table-column>
                    <el-table-column prop="url" label="页面路径" min-width="190" show-overflow-tooltip></el-table-column>
                    <el-table-column label="版本" min-width="150">
                        <template slot-scope="scope">
                            <div class="version-stack">
                                <span>基础库 {{ scope.row.libraryVersion || '—' }}</span>
                                <span>客户端 {{ scope.row.clientVersion || '—' }}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="traceid" label="Trace ID" min-width="210" show-overflow-tooltip></el-table-column>
                    <el-table-column prop="id" label="OpenID" min-width="190" show-overflow-tooltip></el-table-column>
                    <el-table-column label="操作" width="80" align="center">
                        <template slot-scope="scope">
                            <el-dropdown trigger="click" @command="handleLogCopyCommand($event, scope.row)">
                                <el-button type="text">复制<i class="el-icon-arrow-down el-icon--right"></i></el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item command="traceid" :disabled="!scope.row.traceid">Trace ID</el-dropdown-item>
                                    <el-dropdown-item command="openid" :disabled="!scope.row.id">OpenID</el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="table-pagination">
                    <el-pagination
                            background
                            layout="prev, pager, next, total"
                            :current-page="realtime.page"
                            :page-size="realtime.page_size"
                            :total="realtime.total"
                            @current-change="handleRealtimePageChange">
                    </el-pagination>
                </div>
            </el-tab-pane>

            <el-tab-pane name="jsError">
                <span slot="label"><i class="el-icon-warning-outline"></i> JS 错误</span>
                <div class="tab-heading">
                    <div>
                        <span class="tab-heading__index">03</span>
                        <h2>JS 错误</h2>
                        <p>按影响用户与出现次数聚合异常，再下钻查看终端明细</p>
                    </div>
                    <div class="severity-key"><span></span> 聚合异常 / 详情下钻</div>
                </div>

                <el-form :inline="true" :model="jsError.form" size="small" class="query-form">
                    <el-form-item label="日期范围">
                        <el-date-picker
                                v-model="jsError.form.date_range"
                                type="daterange"
                                value-format="yyyy-MM-dd"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                :clearable="false"
                                style="width: 260px">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="错误类型">
                        <el-select v-model="jsError.form.err_type" style="width: 150px">
                            <el-option label="全部" value="0"></el-option>
                            <el-option label="业务代码错误" value="1"></el-option>
                            <el-option label="插件错误" value="2"></el-option>
                            <el-option label="系统框架错误" value="3"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="小程序版本">
                        <el-input v-model.trim="jsError.form.app_version" placeholder="0 代表全部" style="width: 140px"></el-input>
                    </el-form-item>
                    <el-form-item label="关键词">
                        <el-input v-model.trim="jsError.form.keyword" clearable placeholder="搜索错误信息" style="width: 180px"></el-input>
                    </el-form-item>
                    <el-form-item label="OpenID">
                        <el-input v-model.trim="jsError.form.openid" clearable style="width: 190px"></el-input>
                    </el-form-item>
                    <el-form-item label="排序">
                        <el-select v-model="jsError.form.orderby" style="width: 100px">
                            <el-option label="UV" value="uv"></el-option>
                            <el-option label="PV" value="pv"></el-option>
                        </el-select>
                        <el-select v-model="jsError.form.desc" style="width: 100px; margin-left: 6px">
                            <el-option label="降序" value="1"></el-option>
                            <el-option label="升序" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button
                                type="primary"
                                icon="el-icon-search"
                                :loading="jsError.loading"
                                @click="queryJsErrors(true)">
                            查询错误
                        </el-button>
                    </el-form-item>
                </el-form>

                <el-table
                        v-loading="jsError.loading"
                        :data="jsError.items"
                        empty-text="设置条件后查询 JS 错误"
                        class="operation-table error-table">
                    <el-table-column label="错误信息" min-width="430">
                        <template slot-scope="scope">
                            <div class="error-summary">
                                <span class="error-summary__mark">JS</span>
                                <div>
                                    <strong>{{ scope.row.errorMsg || '未知错误' }}</strong>
                                    <code>{{ scope.row.errorMsgMd5 || '—' }}</code>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="uv" label="UV" width="90" sortable></el-table-column>
                    <el-table-column prop="pv" label="PV" width="90" sortable></el-table-column>
                    <el-table-column prop="uvPercent" label="UV 占比" width="110"></el-table-column>
                    <el-table-column prop="pvPercent" label="PV 占比" width="110"></el-table-column>
                    <el-table-column label="操作" width="110" fixed="right" align="center">
                        <template slot-scope="scope">
                            <el-button type="text" icon="el-icon-view" @click="openErrorDetail(scope.row)">查看详情</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="table-pagination">
                    <el-pagination
                            background
                            layout="prev, pager, next, total"
                            :current-page="jsError.page"
                            :page-size="jsError.page_size"
                            :total="jsError.total"
                            @current-change="handleJsErrorPageChange">
                    </el-pagination>
                </div>
            </el-tab-pane>
        </el-tabs>
    </el-card>

    <el-drawer
            title="JS 错误详情"
            :visible.sync="jsError.detail.visible"
            size="78%"
            direction="rtl"
            append-to-body
            custom-class="error-detail-drawer">
        <div class="detail-drawer__body">
            <div class="detail-context">
                <span class="error-summary__mark">JS</span>
                <div>
                    <span>当前聚合异常</span>
                    <strong>{{ jsError.detail.selected.errorMsg || '未知错误' }}</strong>
                    <code>{{ jsError.detail.selected.errorMsgMd5 || '—' }}</code>
                </div>
            </div>

            <el-form :inline="true" :model="jsError.detail.form" size="small" class="detail-filter">
                <el-form-item label="基础库版本">
                    <el-input v-model.trim="jsError.detail.form.sdk_version" placeholder="0 代表全部" style="width: 140px"></el-input>
                </el-form-item>
                <el-form-item label="系统类型">
                    <el-select v-model="jsError.detail.form.os_name" style="width: 120px">
                        <el-option label="全部" value="0"></el-option>
                        <el-option label="Android" value="1"></el-option>
                        <el-option label="iOS" value="2"></el-option>
                        <el-option label="其他" value="3"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="客户端版本">
                    <el-input v-model.trim="jsError.detail.form.client_version" placeholder="0 代表全部" style="width: 140px"></el-input>
                </el-form-item>
                <el-form-item label="排序">
                    <el-select v-model="jsError.detail.form.desc" style="width: 100px">
                        <el-option label="降序" value="1"></el-option>
                        <el-option label="升序" value="0"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" :loading="jsError.detail.loading" @click="queryErrorDetail(true)">刷新详情</el-button>
                </el-form-item>
            </el-form>

            <div v-loading="jsError.detail.loading" class="detail-list">
                <article v-for="(item, index) in jsError.detail.items" :key="index" class="detail-record">
                    <header>
                        <div>
                            <span class="detail-record__number">#{{ (jsError.detail.page - 1) * jsError.detail.page_size + index + 1 }}</span>
                            <strong>{{ item.TimeStamp || item.Ds || '时间未知' }}</strong>
                        </div>
                        <div class="detail-record__tags">
                            <el-tag size="mini" effect="plain">{{ getOsName(item.OsName) }}</el-tag>
                            <el-tag size="mini" type="info">{{ item.DeviceModel || '未知设备' }}</el-tag>
                            <span>出现 {{ item.Count || 1 }} 次</span>
                        </div>
                    </header>
                    <dl class="detail-meta">
                        <div><dt>小程序版本</dt><dd>{{ item.appVersion || '—' }}</dd></div>
                        <div><dt>基础库</dt><dd>{{ item.sdkVersion || '—' }}</dd></div>
                        <div><dt>客户端</dt><dd>{{ item.ClientVersion || '—' }}</dd></div>
                        <div><dt>页面路径</dt><dd>{{ item.route || '—' }}</dd></div>
                        <div><dt>OpenID</dt><dd class="mono-value">{{ item.openId || '—' }}</dd></div>
                    </dl>
                    <section class="stack-block">
                        <div class="stack-block__title">
                            <span>ERROR MESSAGE</span>
                            <el-button type="text" icon="el-icon-document-copy" @click="copyText(item.errorMsg || '')">复制</el-button>
                        </div>
                        <pre>{{ item.errorMsg || '—' }}</pre>
                    </section>
                    <section class="stack-block stack-block--dark">
                        <div class="stack-block__title">
                            <span>STACK TRACE</span>
                            <el-button type="text" icon="el-icon-document-copy" @click="copyText(item.errorStack || '')">复制堆栈</el-button>
                        </div>
                        <pre>{{ item.errorStack || '—' }}</pre>
                    </section>
                </article>
                <el-empty v-if="!jsError.detail.loading && jsError.detail.items.length === 0" description="暂无错误详情"></el-empty>
            </div>
            <div class="table-pagination">
                <el-pagination
                        background
                        layout="prev, pager, next, total"
                        :current-page="jsError.detail.page"
                        :page-size="jsError.detail.page_size"
                        :total="jsError.detail.total"
                        @current-change="handleErrorDetailPageChange">
                </el-pagination>
            </div>
        </div>
    </el-drawer>
</div>

<style>
    :root {
        --ops-ink: #18231f;
        --ops-muted: #67736e;
        --ops-line: #dfe6e2;
        --ops-paper: #f4f7f5;
        --ops-green: #087f5b;
        --ops-green-dark: #075f47;
        --ops-amber: #e7a21a;
        --ops-danger: #d94841;
        --ops-code: #101915;
    }

    .operation-center {
        min-height: 100vh;
        padding: 18px;
        box-sizing: border-box;
        color: var(--ops-ink);
        background-color: var(--ops-paper);
        background-image:
                linear-gradient(rgba(24, 35, 31, .025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(24, 35, 31, .025) 1px, transparent 1px);
        background-size: 28px 28px;
    }

    .operation-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 132px;
        padding: 26px 32px;
        box-sizing: border-box;
        overflow: hidden;
        color: #fff;
        background: var(--ops-ink);
        border-radius: 4px 4px 0 0;
    }

    .operation-hero::before {
        position: absolute;
        top: 0;
        left: 0;
        width: 7px;
        height: 100%;
        content: '';
        background: var(--ops-amber);
    }

    .operation-hero__identity,
    .operation-hero__account {
        position: relative;
        z-index: 2;
    }

    .operation-hero__eyebrow,
    .tab-heading__index,
    .detail-record__number,
    .stack-block__title span {
        font-family: "DIN Alternate", "Avenir Next Condensed", sans-serif;
        letter-spacing: .16em;
    }

    .operation-hero__eyebrow {
        display: block;
        margin-bottom: 5px;
        color: #8bc5b1;
        font-size: 11px;
        font-weight: 700;
    }

    .operation-hero h1 {
        margin: 0;
        font-family: "STSong", "Songti SC", serif;
        font-size: 31px;
        font-weight: 700;
        letter-spacing: .08em;
    }

    .operation-hero p {
        margin: 8px 0 0;
        color: #abb8b3;
        font-size: 13px;
    }

    .operation-hero__account {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-width: 280px;
        padding: 16px 18px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid rgba(255, 255, 255, .12);
        backdrop-filter: blur(4px);
    }

    .operation-hero__account strong,
    .operation-hero__account code {
        display: block;
    }

    .operation-hero__account strong {
        margin-bottom: 5px;
        font-size: 15px;
    }

    .operation-hero__account code {
        color: #91a59d;
        font-size: 12px;
    }

    .status-pulse {
        position: relative;
        width: 9px;
        height: 9px;
        margin-top: 5px;
        background: #43d19e;
        border-radius: 50%;
        box-shadow: 0 0 0 5px rgba(67, 209, 158, .13);
    }

    .operation-hero__grid {
        position: absolute;
        right: -45px;
        bottom: -75px;
        width: 360px;
        height: 230px;
        opacity: .13;
        transform: rotate(-12deg);
        background-image:
                linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px);
        background-size: 22px 22px;
    }

    .operation-panel {
        border: 0;
        border-radius: 0 0 4px 4px;
    }

    .operation-panel > .el-card__body {
        padding: 0 28px 28px;
    }

    .operation-tabs > .el-tabs__header {
        margin-bottom: 0;
    }

    .operation-tabs > .el-tabs__header .el-tabs__item {
        height: 58px;
        padding: 0 28px;
        line-height: 58px;
        font-weight: 600;
    }

    .operation-tabs .el-tabs__active-bar {
        height: 3px;
        background: var(--ops-green);
    }

    .operation-tabs .el-tabs__item.is-active,
    .operation-tabs .el-tabs__item:hover {
        color: var(--ops-green);
    }

    .tab-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 28px 0 18px;
    }

    .tab-heading > div:first-child {
        display: grid;
        grid-template-columns: 44px auto;
        column-gap: 12px;
    }

    .tab-heading__index {
        grid-row: 1 / span 2;
        color: var(--ops-amber);
        font-size: 18px;
        font-weight: 700;
    }

    .tab-heading h2 {
        margin: 0;
        font-size: 19px;
    }

    .tab-heading p {
        margin: 5px 0 0;
        color: var(--ops-muted);
        font-size: 13px;
    }

    .query-form {
        padding: 18px 18px 2px;
        background: #f7f9f8;
        border-top: 1px solid var(--ops-line);
        border-bottom: 1px solid var(--ops-line);
    }

    .query-form .el-form-item {
        margin-bottom: 16px;
    }

    .query-form .el-button--primary,
    .detail-filter .el-button--primary {
        background: var(--ops-green);
        border-color: var(--ops-green);
    }

    .result-strip {
        display: flex;
        align-items: baseline;
        gap: 7px;
        margin: 20px 0 9px;
        color: var(--ops-muted);
        font-size: 13px;
    }

    .result-strip span {
        margin-right: auto;
        color: var(--ops-ink);
        font-weight: 600;
    }

    .result-strip strong {
        color: var(--ops-green);
        font-family: "DIN Alternate", sans-serif;
        font-size: 24px;
    }

    .operation-table {
        border-top: 2px solid var(--ops-ink);
    }

    .operation-table th {
        color: #46524d;
        background: #f2f5f3 !important;
    }

    .mono-value,
    .operation-table code,
    .detail-meta dd,
    .operation-hero code {
        font-family: "SFMono-Regular", Consolas, "Liberation Mono", monospace;
    }

    .metric-value {
        color: var(--ops-green-dark);
        font-family: "DIN Alternate", sans-serif;
        font-size: 22px;
        font-weight: 700;
    }

    .metric-unit {
        margin-left: 5px;
        color: var(--ops-muted);
        font-size: 11px;
    }

    .advanced-query {
        margin: 12px 0 20px;
        border: 1px solid var(--ops-line);
    }

    .advanced-query .el-collapse-item__header {
        padding: 0 15px;
        border-bottom: 0;
    }

    .advanced-query .el-collapse-item__header i {
        margin-right: 8px;
    }

    .advanced-query .el-collapse-item__header span {
        margin-left: 10px;
        color: #96a19d;
        font-size: 12px;
    }

    .advanced-query__form {
        padding: 16px 16px 0;
        background: #fafbfa;
        border-top: 1px solid var(--ops-line);
    }

    .log-message-list {
        padding: 5px 18px 12px 54px;
    }

    .log-message {
        display: grid;
        grid-template-columns: 170px 80px minmax(0, 1fr);
        align-items: start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #e8ecea;
    }

    .log-message:last-child {
        border-bottom: 0;
    }

    .log-message__time {
        color: var(--ops-muted);
        font-family: "SFMono-Regular", Consolas, monospace;
        font-size: 12px;
    }

    .log-message pre,
    .stack-block pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .log-message pre {
        color: #34423c;
        font-family: "SFMono-Regular", Consolas, monospace;
        font-size: 12px;
        line-height: 1.65;
    }

    .empty-inline {
        color: #9aa5a0;
        text-align: center;
    }

    .version-stack span {
        display: block;
        color: var(--ops-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .table-pagination {
        display: flex;
        justify-content: flex-end;
        padding-top: 20px;
    }

    .severity-key {
        color: var(--ops-muted);
        font-size: 12px;
    }

    .severity-key span {
        display: inline-block;
        width: 8px;
        height: 8px;
        margin-right: 6px;
        background: var(--ops-danger);
        border-radius: 50%;
    }

    .error-summary {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .error-summary__mark {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        color: #fff;
        background: var(--ops-danger);
        font-family: "DIN Alternate", sans-serif;
        font-size: 11px;
        font-weight: 700;
    }

    .error-summary strong,
    .error-summary code {
        display: block;
    }

    .error-summary strong {
        display: -webkit-box;
        max-height: 42px;
        overflow: hidden;
        color: #2f3b36;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.55;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .error-summary code {
        margin-top: 5px;
        color: #9a6965;
        font-size: 11px;
    }

    .error-detail-drawer .el-drawer__header {
        margin-bottom: 0;
        padding: 22px 28px;
        color: #fff;
        background: var(--ops-ink);
        font-weight: 700;
    }

    .error-detail-drawer .el-drawer__close-btn {
        color: #fff;
    }

    .detail-drawer__body {
        height: calc(100vh - 66px);
        padding: 22px 28px 36px;
        overflow-y: auto;
        box-sizing: border-box;
        background: var(--ops-paper);
    }

    .detail-context {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 18px;
        background: #fff;
        border-left: 4px solid var(--ops-danger);
    }

    .detail-context span,
    .detail-context strong,
    .detail-context code {
        display: block;
    }

    .detail-context > div > span {
        color: var(--ops-muted);
        font-size: 11px;
    }

    .detail-context strong {
        max-width: 850px;
        margin: 4px 0;
        line-height: 1.5;
    }

    .detail-context code {
        color: #a66b67;
        font-size: 11px;
    }

    .detail-filter {
        margin: 14px 0;
        padding: 16px 16px 0;
        background: #fff;
        border: 1px solid var(--ops-line);
    }

    .detail-record {
        margin-bottom: 16px;
        padding: 20px;
        background: #fff;
        border: 1px solid var(--ops-line);
        box-shadow: 0 8px 24px rgba(24, 35, 31, .04);
    }

    .detail-record > header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--ops-line);
    }

    .detail-record__number {
        margin-right: 8px;
        color: var(--ops-green);
        font-size: 12px;
        font-weight: 700;
    }

    .detail-record__tags {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--ops-muted);
        font-size: 12px;
    }

    .detail-meta {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1px;
        margin: 14px 0;
        background: var(--ops-line);
    }

    .detail-meta div {
        min-width: 0;
        padding: 10px 12px;
        background: #f8faf9;
    }

    .detail-meta dt {
        margin-bottom: 4px;
        color: var(--ops-muted);
        font-size: 11px;
    }

    .detail-meta dd {
        margin: 0;
        overflow: hidden;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stack-block {
        margin-top: 10px;
        border: 1px solid var(--ops-line);
    }

    .stack-block__title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 38px;
        padding: 0 12px;
        background: #f3f6f4;
    }

    .stack-block__title span {
        color: var(--ops-muted);
        font-size: 10px;
        font-weight: 700;
    }

    .stack-block pre {
        max-height: 190px;
        padding: 14px;
        overflow: auto;
        color: #37433e;
        font-family: "SFMono-Regular", Consolas, monospace;
        font-size: 12px;
        line-height: 1.65;
    }

    .stack-block--dark {
        border-color: var(--ops-code);
    }

    .stack-block--dark .stack-block__title {
        color: #fff;
        background: #1c2823;
    }

    .stack-block--dark .stack-block__title span {
        color: #7fb49f;
    }

    .stack-block--dark .el-button--text {
        color: #79c8aa;
    }

    .stack-block--dark pre {
        color: #d7e2dd;
        background: var(--ops-code);
    }

    @media (max-width: 980px) {
        .operation-hero {
            align-items: flex-start;
            flex-direction: column;
            gap: 18px;
        }

        .operation-hero__account {
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
        }

        .detail-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<script>
    $(function () {
        new Vue({
            el: '#app',
            data: function () {
                var now = new Date();
                var sevenDaysAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6, 0, 0, 0);
                var todayText = this.formatDate(now, '');
                var jsStartDate = this.formatDate(sevenDaysAgo, '-');
                var jsEndDate = this.formatDate(now, '-');
                return {
                    authorizerAppid: {$authorizer_appid_json|raw},
                    authorizerName: {$authorizer_name_json|raw},
                    activeTab: 'performance',
                    sceneOptions: [{name: '全部', value: '@_all'}],
                    sceneLoading: false,
                    sceneLoaded: false,
                    realtimeDatePickerOptions: {
                        disabledDate: function (time) {
                            var end = new Date();
                            end.setHours(23, 59, 59, 999);
                            var start = new Date();
                            start.setDate(start.getDate() - 2);
                            start.setHours(0, 0, 0, 0);
                            return time.getTime() < start.getTime() || time.getTime() > end.getTime();
                        },
                    },
                    performance: {
                        loading: false,
                        form: {
                            cost_time_type: '1',
                            time_range: [sevenDaysAgo.getTime(), now.getTime()],
                            device: '@_all',
                            is_download_code: '@_all',
                            scene: '@_all',
                            networktype: '@_all',
                        },
                        items: [],
                        compare_items: [],
                    },
                    realtime: {
                        loading: false,
                        form: {
                            date: todayText,
                            begin_time: '00:00:00',
                            end_time: this.formatTimeOnly(now),
                            level: '',
                            trace_id: '',
                            url: '',
                            openid: '',
                            filter_msg: '',
                        },
                        items: [],
                        page: 1,
                        page_size: 20,
                        total: 0,
                    },
                    jsError: {
                        loading: false,
                        form: {
                            date_range: [jsStartDate, jsEndDate],
                            err_type: '0',
                            app_version: '0',
                            keyword: '',
                            openid: '',
                            orderby: 'uv',
                            desc: '1',
                        },
                        items: [],
                        page: 1,
                        page_size: 20,
                        total: 0,
                        detail: {
                            visible: false,
                            loading: false,
                            selected: {},
                            form: {
                                sdk_version: '0',
                                os_name: '0',
                                client_version: '0',
                                desc: '1',
                            },
                            items: [],
                            page: 1,
                            page_size: 10,
                            total: 0,
                        },
                    },
                };
            },
            mounted: function () {
                this.loadSceneOptions(false);
            },
            methods: {
                handleTabChange: function () {
                    if (this.activeTab === 'performance' && !this.sceneLoaded) {
                        this.loadSceneOptions(false);
                    }
                },
                loadSceneOptions: function (showSuccess) {
                    if (this.sceneLoading) {
                        return;
                    }
                    var that = this;
                    this.sceneLoading = true;
                    this.httpGet('/wechat/open.MiniProgramOperationAdmin/index', {
                        _action: 'getSceneList',
                        authorizer_appid: this.authorizerAppid,
                    }, function (res) {
                        that.sceneLoading = false;
                        if (!res.status) {
                            layer.alert(res.msg || '获取访问来源失败');
                            return;
                        }
                        var items = res.data.items || [];
                        that.sceneOptions = items.length > 0 ? items : [{name: '全部', value: '@_all'}];
                        if (!that.sceneOptions.some(function (item) { return item.value === '@_all'; })) {
                            that.sceneOptions.unshift({name: '全部', value: '@_all'});
                        }
                        that.sceneLoaded = true;
                        if (showSuccess) {
                            layer.msg('访问来源已刷新');
                        }
                    });
                },
                queryPerformance: function () {
                    var range = this.performance.form.time_range || [];
                    if (range.length !== 2) {
                        layer.msg('请选择性能查询时间范围');
                        return;
                    }
                    var that = this;
                    this.performance.loading = true;
                    this.httpGet('/wechat/open.MiniProgramOperationAdmin/index', {
                        _action: 'getPerformance',
                        authorizer_appid: this.authorizerAppid,
                        cost_time_type: this.performance.form.cost_time_type,
                        default_start_time: Math.floor(Number(range[0]) / 1000),
                        default_end_time: Math.floor(Number(range[1]) / 1000),
                        device: this.performance.form.device,
                        is_download_code: this.performance.form.cost_time_type === '1'
                            ? this.performance.form.is_download_code : '@_all',
                        scene: this.performance.form.cost_time_type === '1' || this.performance.form.cost_time_type === '2'
                            ? this.performance.form.scene : '@_all',
                        networktype: this.performance.form.cost_time_type === '2'
                            ? this.performance.form.networktype : '@_all',
                    }, function (res) {
                        that.performance.loading = false;
                        if (!res.status) {
                            layer.alert(res.msg || '获取性能数据失败');
                            return;
                        }
                        that.performance.items = res.data.items || [];
                        that.performance.compare_items = res.data.compare_items || [];
                    });
                },
                queryRealtimeLogs: function (resetPage) {
                    var beginTimestamp = this.combineDateTime(this.realtime.form.date, this.realtime.form.begin_time);
                    var endTimestamp = this.combineDateTime(this.realtime.form.date, this.realtime.form.end_time);
                    if (!beginTimestamp || !endTimestamp || beginTimestamp >= endTimestamp) {
                        layer.msg('日志开始时间必须早于结束时间');
                        return;
                    }
                    if (resetPage) {
                        this.realtime.page = 1;
                    }
                    var that = this;
                    this.realtime.loading = true;
                    this.httpGet('/wechat/open.MiniProgramOperationAdmin/index', {
                        _action: 'realtimeLogSearch',
                        authorizer_appid: this.authorizerAppid,
                        date: this.realtime.form.date,
                        begintime: beginTimestamp,
                        endtime: endTimestamp,
                        level: this.realtime.form.level,
                        trace_id: this.realtime.form.trace_id,
                        url: this.realtime.form.url,
                        openid: this.realtime.form.openid,
                        filter_msg: this.realtime.form.filter_msg,
                        page: this.realtime.page,
                        page_size: this.realtime.page_size,
                    }, function (res) {
                        that.realtime.loading = false;
                        if (!res.status) {
                            layer.alert(res.msg || '查询实时日志失败');
                            return;
                        }
                        that.realtime.items = res.data.items || [];
                        that.realtime.total = Number(res.data.total || 0);
                        that.realtime.page = Number(res.data.page || 1);
                        that.realtime.page_size = Number(res.data.page_size || 20);
                    });
                },
                handleRealtimePageChange: function (page) {
                    this.realtime.page = page;
                    this.queryRealtimeLogs(false);
                },
                queryJsErrors: function (resetPage) {
                    var range = this.jsError.form.date_range || [];
                    if (range.length !== 2) {
                        layer.msg('请选择错误查询日期范围');
                        return;
                    }
                    if (resetPage) {
                        this.jsError.page = 1;
                    }
                    var that = this;
                    this.jsError.loading = true;
                    this.httpGet('/wechat/open.MiniProgramOperationAdmin/index', {
                        _action: 'getJsErrList',
                        authorizer_appid: this.authorizerAppid,
                        start_time: range[0],
                        end_time: range[1],
                        err_type: this.jsError.form.err_type,
                        app_version: this.jsError.form.app_version || '0',
                        keyword: this.jsError.form.keyword,
                        openid: this.jsError.form.openid,
                        orderby: this.jsError.form.orderby,
                        desc: this.jsError.form.desc,
                        page: this.jsError.page,
                        page_size: this.jsError.page_size,
                    }, function (res) {
                        that.jsError.loading = false;
                        if (!res.status) {
                            layer.alert(res.msg || '查询 JS 错误失败');
                            return;
                        }
                        that.jsError.items = res.data.items || [];
                        that.jsError.total = Number(res.data.total || 0);
                        that.jsError.page = Number(res.data.page || 1);
                        that.jsError.page_size = Number(res.data.page_size || 20);
                    });
                },
                handleJsErrorPageChange: function (page) {
                    this.jsError.page = page;
                    this.queryJsErrors(false);
                },
                openErrorDetail: function (item) {
                    if (!item.errorMsgMd5 || !item.errorStackMd5) {
                        layer.alert('当前错误缺少详情查询标识');
                        return;
                    }
                    this.jsError.detail.selected = item;
                    this.jsError.detail.page = 1;
                    this.jsError.detail.items = [];
                    this.jsError.detail.total = 0;
                    this.jsError.detail.visible = true;
                    this.$nextTick(this.queryErrorDetail.bind(this, false));
                },
                queryErrorDetail: function (resetPage) {
                    var selected = this.jsError.detail.selected;
                    var range = this.jsError.form.date_range || [];
                    if (!selected.errorMsgMd5 || !selected.errorStackMd5 || range.length !== 2) {
                        return;
                    }
                    if (resetPage) {
                        this.jsError.detail.page = 1;
                    }
                    var that = this;
                    this.jsError.detail.loading = true;
                    this.httpGet('/wechat/open.MiniProgramOperationAdmin/index', {
                        _action: 'getJsErrDetail',
                        authorizer_appid: this.authorizerAppid,
                        start_time: range[0],
                        end_time: range[1],
                        error_msg_md5: selected.errorMsgMd5,
                        error_stack_md5: selected.errorStackMd5,
                        app_version: this.jsError.form.app_version || '0',
                        sdk_version: this.jsError.detail.form.sdk_version || '0',
                        os_name: this.jsError.detail.form.os_name,
                        client_version: this.jsError.detail.form.client_version || '0',
                        openid: this.jsError.form.openid,
                        desc: this.jsError.detail.form.desc,
                        page: this.jsError.detail.page,
                        page_size: this.jsError.detail.page_size,
                    }, function (res) {
                        that.jsError.detail.loading = false;
                        if (!res.status) {
                            layer.alert(res.msg || '查询 JS 错误详情失败');
                            return;
                        }
                        that.jsError.detail.items = res.data.items || [];
                        that.jsError.detail.total = Number(res.data.total || 0);
                        that.jsError.detail.page = Number(res.data.page || 1);
                        that.jsError.detail.page_size = Number(res.data.page_size || 10);
                    });
                },
                handleErrorDetailPageChange: function (page) {
                    this.jsError.detail.page = page;
                    this.queryErrorDetail(false);
                },
                handleLogCopyCommand: function (command, item) {
                    this.copyText(command === 'traceid' ? item.traceid : item.id);
                },
                copyText: function (text) {
                    var value = String(text || '');
                    if (!value) {
                        layer.msg('没有可复制的内容');
                        return;
                    }
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(value).then(function () {
                            layer.msg('已复制');
                        }).catch(function () {
                            layer.msg('复制失败，请手动复制');
                        });
                        return;
                    }
                    var textarea = document.createElement('textarea');
                    textarea.value = value;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    layer.msg('已复制');
                },
                combineDateTime: function (dateText, timeText) {
                    if (!/^\d{8}$/.test(dateText || '') || !/^\d{2}:\d{2}:\d{2}$/.test(timeText || '')) {
                        return 0;
                    }
                    var year = Number(dateText.slice(0, 4));
                    var month = Number(dateText.slice(4, 6)) - 1;
                    var day = Number(dateText.slice(6, 8));
                    var timeParts = timeText.split(':').map(Number);
                    return Math.floor(new Date(year, month, day, timeParts[0], timeParts[1], timeParts[2]).getTime() / 1000);
                },
                formatDate: function (date, separator) {
                    var pad = function (value) { return String(value).padStart(2, '0'); };
                    return date.getFullYear() + separator + pad(date.getMonth() + 1) + separator + pad(date.getDate());
                },
                formatTimeOnly: function (date) {
                    var pad = function (value) { return String(value).padStart(2, '0'); };
                    return pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
                },
                formatTimestamp: function (timestamp) {
                    var value = Number(timestamp);
                    if (!value) {
                        return '—';
                    }
                    var date = new Date(value * 1000);
                    return this.formatDate(date, '-') + ' ' + this.formatTimeOnly(date);
                },
                formatPerformanceDate: function (value) {
                    var text = String(value || '');
                    if (/^\d{8}$/.test(text)) {
                        return text.slice(0, 4) + '-' + text.slice(4, 6) + '-' + text.slice(6, 8);
                    }
                    return text || '—';
                },
                formatNumber: function (value) {
                    var number = Number(value);
                    return Number.isFinite(number) ? number.toLocaleString() : '—';
                },
                getPerformanceType: function (value) {
                    return {1: '启动总耗时', 2: '下载耗时', 3: '初次渲染耗时'}[Number(value)] || '未知指标';
                },
                getPlatformName: function (value) {
                    return {1: 'Android', 2: 'iOS'}[Number(value)] || '其他';
                },
                getOsName: function (value) {
                    return {0: '全部', 1: 'Android', 2: 'iOS', 3: '其他'}[Number(value)] || String(value || '未知');
                },
                getLogLevelInfo: function (value) {
                    var level = Number(value || 0);
                    if ((level & 8) === 8) {
                        return {text: 'Error', type: 'danger'};
                    }
                    if ((level & 4) === 4) {
                        return {text: 'Warn', type: 'warning'};
                    }
                    return {text: 'Info', type: 'info'};
                },
                formatLogMessage: function (message) {
                    if (typeof message === 'string') {
                        return message;
                    }
                    try {
                        return JSON.stringify(message, null, 2);
                    } catch (error) {
                        return String(message);
                    }
                },
            },
        });
    });
</script>
