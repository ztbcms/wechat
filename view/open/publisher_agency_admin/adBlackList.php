<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>广告屏蔽设置</span>
        </div>

        <el-tabs v-model="activeTab" @tab-click="handleTabChange">
            <el-tab-pane label="屏蔽广告主" name="advertiser"></el-tab-pane>
            <el-tab-pane label="屏蔽广告行业" name="category"></el-tab-pane>
        </el-tabs>

        <!-- 屏蔽广告主 -->
        <template v-if="activeTab === 'advertiser'">
            <div class="blacklist-section">
                <div class="section-header">
                    <h3>屏蔽广告主</h3>
                    <p class="section-desc">添加需要屏蔽的推广对象，该推广对象投放的广告将不会出现在你的小程序或小游戏中。</p>
                </div>

                <el-tabs v-model="advertiserType" type="card" @tab-click="handleAdvertiserTypeChange">
                    <el-tab-pane label="公众号" name="biz"></el-tab-pane>
                    <el-tab-pane label="iOS应用" name="ios"></el-tab-pane>
                    <el-tab-pane label="安卓应用" name="android"></el-tab-pane>
                    <el-tab-pane label="小程序/小游戏" name="weapp"></el-tab-pane>
                </el-tabs>

                <p class="count-info">
                    已屏蔽 <span class="count-num">{{ getCurrentBlacklistCount() }}</span> 个{{ advertiserTypeLabel }}，最多可屏蔽{{ getMaxCount() }}个。
                </p>

                <div class="card-list">
                    <!-- 添加按钮 -->
                    <div class="card-item add-card" @click="handleAddAdvertiser">
                        <i class="el-icon-plus"></i>
                    </div>
                    <!-- 已屏蔽列表 -->
                    <div class="card-item" v-for="(item, index) in getCurrentBlacklist()" :key="item.id">
                        <div class="card-content">
                            <img v-if="item.icon || item.url" :src="item.icon || item.url" class="card-icon" />
                            <div v-else class="card-icon-placeholder"></div>
                            <div class="card-info">
                                <div class="card-name" :title="item.name">{{ item.name }}</div>
                                <div class="card-id">{{ item.id }}</div>
                            </div>
                        </div>
                        <el-button type="text" icon="el-icon-delete" class="delete-btn" @click="handleDeleteAdvertiser(item)"></el-button>
                    </div>
                </div>
            </div>
        </template>

        <!-- 屏蔽广告行业 -->
        <template v-if="activeTab === 'category'">
            <div class="blacklist-section">
                <div class="section-header">
                    <h3>屏蔽广告行业</h3>
                    <p class="section-desc">添加需要屏蔽的广告行业，该行业广告主投放的广告将不会出现在你的小程序或小游戏中。</p>
                </div>

                <p class="count-info">
                    已屏蔽 <span class="count-num">{{ categoryBlacklist.length }}</span> 个行业，最多可屏蔽6个。
                </p>

                <div class="card-list">
                    <!-- 添加按钮 -->
                    <div class="card-item add-card category-card" @click="handleAddCategory">
                        <i class="el-icon-plus"></i>
                    </div>
                    <!-- 已屏蔽行业列表 -->
                    <div class="card-item category-card" v-for="(item, index) in categoryBlacklist" :key="item">
                        <div class="card-content">
                            <span class="category-name">{{ getCategoryLabel(item) }}</span>
                        </div>
                        <el-button type="text" icon="el-icon-delete" class="delete-btn" @click="handleDeleteCategory(item)"></el-button>
                    </div>
                </div>
            </div>
        </template>
    </el-card>

    <!-- 添加广告主对话框 -->
    <el-dialog title="添加屏蔽广告主" :visible.sync="addAdvertiserDialogVisible" width="400px">
        <el-form :model="addAdvertiserForm" label-width="80px">
            <el-form-item :label="getAddFormLabel()">
                <el-input v-model="addAdvertiserForm.id" :placeholder="getAddFormPlaceholder()"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="addAdvertiserDialogVisible = false">取 消</el-button>
            <el-button type="primary" @click="submitAddAdvertiser" :loading="submitLoading">确 定</el-button>
        </div>
    </el-dialog>

    <!-- 添加行业对话框 -->
    <el-dialog title="添加屏蔽行业" :visible.sync="addCategoryDialogVisible" width="500px">
        <el-checkbox-group v-model="selectedCategories">
            <el-checkbox v-for="(label, key) in categoryMap" :key="key" :label="key" :disabled="categoryBlacklist.includes(key)">
                {{ label }}
            </el-checkbox>
        </el-checkbox-group>
        <div slot="footer" class="dialog-footer">
            <el-button @click="addCategoryDialogVisible = false">取 消</el-button>
            <el-button type="primary" @click="submitAddCategory" :loading="submitLoading">确 定</el-button>
        </div>
    </el-dialog>
</div>

<style>
    p {
        margin: 0;
    }

    .blacklist-section {
        padding: 20px;
        background: #f5f7fa;
        border-radius: 8px;
    }

    .section-header h3 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 500;
    }

    .section-desc {
        color: #909399;
        font-size: 13px;
        margin-bottom: 20px;
    }

    .count-info {
        margin: 15px 0;
        font-size: 14px;
        color: #606266;
    }

    .count-num {
        color: #67c23a;
        font-weight: bold;
    }

    .card-list {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-item {
        width: 220px;
        height: 80px;
        background: #fff;
        border: 1px solid #e4e7ed;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 15px;
        box-sizing: border-box;
        position: relative;
    }

    .card-item.category-card {
        width: 160px;
        height: 60px;
    }

    .add-card {
        border: 1px dashed #dcdfe6;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .add-card:hover {
        border-color: #409eff;
        color: #409eff;
    }

    .add-card i {
        font-size: 24px;
        color: #c0c4cc;
    }

    .add-card:hover i {
        color: #409eff;
    }

    .card-content {
        display: flex;
        align-items: center;
        flex: 1;
        overflow: hidden;
    }

    .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        margin-right: 10px;
        object-fit: cover;
    }

    .card-icon-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        margin-right: 10px;
        background: #f0f0f0;
    }

    .card-info {
        flex: 1;
        overflow: hidden;
    }

    .card-name {
        font-size: 14px;
        color: #303133;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card-id {
        font-size: 12px;
        color: #909399;
        margin-top: 4px;
    }

    .category-name {
        font-size: 14px;
        color: #303133;
    }

    .delete-btn {
        color: #909399;
        padding: 5px;
    }

    .delete-btn:hover {
        color: #f56c6c;
    }

    .el-checkbox {
        width: 45%;
        margin-bottom: 10px;
    }
</style>

<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                authorizer_appid: '',
                activeTab: 'advertiser',
                advertiserType: 'biz',
                // 广告主黑名单数据
                blacklistBiz: [],
                blacklistWeapp: [],
                blacklistIos: [],
                blacklistAndroid: [],
                // 行业黑名单数据
                categoryBlacklist: [],
                // 行业枚举映射
                categoryMap: {
                    'CHESS': '棋牌游戏',
                    'ADULT_SUPPLIES': '成人用品',
                    'MEDICAL_HEALTH': '医疗健康',
                    'INSURANCE': '保险',
                    'SECURITES': '证券',
                    'LOAN': '贷款',
                    'LIVING_SERVICES_BEAUTY': '生活服务（丽人）',
                    'LIVING_SERVICES_ENTERTAINMENT': '生活服务（休闲娱乐）',
                    'LIVING_SERVICES_OTHERS': '生活服务（其他）',
                    'FOOD_INDUSTRY': '餐饮',
                    'RETAIL_AND_GENERAL_MERCHANDISE': '零售和百货',
                    'FOOD_AND_DRINK': '食品饮料',
                    'TECHNICAL_SERVICE': '通讯与IT服务'
                },
                // 添加广告主对话框
                addAdvertiserDialogVisible: false,
                addAdvertiserForm: {
                    id: ''
                },
                // 添加行业对话框
                addCategoryDialogVisible: false,
                selectedCategories: [],
                submitLoading: false
            },
            computed: {
                advertiserTypeLabel: function () {
                    const labels = {
                        'biz': '公众号',
                        'ios': 'iOS应用',
                        'android': '安卓应用',
                        'weapp': '小程序/小游戏'
                    }
                    return labels[this.advertiserType]
                }
            },
            mounted: function () {
                this.authorizer_appid = this.getUrlQuery('authorizer_appid')
                this.getBlackList()
                this.getAmsCategoryBlackList()
            },
            methods: {
                handleTabChange: function () {
                    // Tab 切换时可以刷新数据
                },
                handleAdvertiserTypeChange: function () {
                    // 广告主类型切换
                },
                // 获取当前类型的屏蔽列表
                getCurrentBlacklist: function () {
                    switch (this.advertiserType) {
                        case 'biz':
                            return this.blacklistBiz
                        case 'ios':
                            return this.blacklistIos
                        case 'android':
                            return this.blacklistAndroid
                        case 'weapp':
                            return this.blacklistWeapp
                        default:
                            return []
                    }
                },
                getCurrentBlacklistCount: function () {
                    return this.getCurrentBlacklist().length
                },
                getMaxCount: function () {
                    if (this.advertiserType === 'biz' || this.advertiserType === 'weapp') {
                        return 20
                    }
                    return 10
                },
                getCategoryLabel: function (key) {
                    return this.categoryMap[key] || key
                },
                getAddFormLabel: function () {
                    const labels = {
                        'biz': '微信号',
                        'ios': 'APPID',
                        'android': '包名',
                        'weapp': '原始ID'
                    }
                    return labels[this.advertiserType]
                },
                getAddFormPlaceholder: function () {
                    const placeholders = {
                        'biz': '请输入公众号微信号',
                        'ios': '请输入iOS应用APPID',
                        'android': '请输入安卓应用的应用宝包名',
                        'weapp': '请输入小程序/小游戏原始ID'
                    }
                    return placeholders[this.advertiserType]
                },
                getTypeValue: function () {
                    const types = {
                        'biz': 1,
                        'ios': 2,
                        'android': 3,
                        'weapp': 4
                    }
                    return types[this.advertiserType]
                },
                // 获取屏蔽的广告主列表
                getBlackList: function () {
                    var that = this
                    var data = {
                        _action: 'getBlackList',
                        authorizer_appid: this.authorizer_appid
                    }
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/adBlackList", data, function (res) {
                        if (!res.status) {
                            layer.msg(res.msg)
                            return
                        }
                        that.blacklistBiz = res.data.blacklist_biz || []
                        that.blacklistWeapp = res.data.blacklist_weapp || []
                        that.blacklistIos = res.data.blacklist_ios || []
                        that.blacklistAndroid = res.data.blacklist_android || []
                    })
                },
                // 获取屏蔽的行业列表
                getAmsCategoryBlackList: function () {
                    var that = this
                    var data = {
                        _action: 'getAmsCategoryBlackList',
                        authorizer_appid: this.authorizer_appid
                    }
                    this.httpGet("/wechat/open.PublisherAgencyAdmin/adBlackList", data, function (res) {
                        if (!res.status) {
                            layer.msg(res.msg)
                            return
                        }
                        var amsCategory = res.data.ams_category || ''
                        that.categoryBlacklist = amsCategory ? amsCategory.split('|') : []
                    })
                },
                // 添加广告主
                handleAddAdvertiser: function () {
                    if (this.getCurrentBlacklistCount() >= this.getMaxCount()) {
                        layer.msg('已达到最大屏蔽数量限制')
                        return
                    }
                    this.addAdvertiserForm.id = ''
                    this.addAdvertiserDialogVisible = true
                },
                submitAddAdvertiser: function () {
                    var that = this
                    if (!this.addAdvertiserForm.id.trim()) {
                        layer.msg('请输入' + this.getAddFormLabel())
                        return
                    }
                    this.submitLoading = true
                    var data = {
                        _action: 'setBlackList',
                        authorizer_appid: this.authorizer_appid,
                        op: 1,
                        list: JSON.stringify([{type: this.getTypeValue(), id: this.addAdvertiserForm.id.trim()}])
                    }
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/adBlackList", data, function (res) {
                        that.submitLoading = false
                        if (res.status) {
                            layer.msg('添加成功')
                            that.addAdvertiserDialogVisible = false
                            that.getBlackList()
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
                // 删除广告主
                handleDeleteAdvertiser: function (item) {
                    var that = this
                    layer.confirm('确定要移除屏蔽的广告主"' + item.name + '"吗？', function (index) {
                        var data = {
                            _action: 'setBlackList',
                            authorizer_appid: that.authorizer_appid,
                            op: 2,
                            list: JSON.stringify([{type: that.getTypeValue(), id: item.id}])
                        }
                        that.httpPost("/wechat/open.PublisherAgencyAdmin/adBlackList", data, function (res) {
                            if (res.status) {
                                layer.msg('删除成功')
                                that.getBlackList()
                            } else {
                                layer.msg(res.msg)
                            }
                        })
                        layer.close(index)
                    })
                },
                // 添加行业
                handleAddCategory: function () {
                    if (this.categoryBlacklist.length >= 6) {
                        layer.msg('已达到最大屏蔽数量限制')
                        return
                    }
                    this.selectedCategories = []
                    this.addCategoryDialogVisible = true
                },
                submitAddCategory: function () {
                    var that = this
                    if (this.selectedCategories.length === 0) {
                        layer.msg('请选择要屏蔽的行业')
                        return
                    }
                    // 合并已有和新选择的行业
                    var newCategories = [...new Set([...this.categoryBlacklist, ...this.selectedCategories])]
                    if (newCategories.length > 6) {
                        layer.msg('最多只能屏蔽6个行业')
                        return
                    }
                    this.submitLoading = true
                    var data = {
                        _action: 'setAmsCategoryBlackList',
                        authorizer_appid: this.authorizer_appid,
                        ams_category: newCategories.join('|')
                    }
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/adBlackList", data, function (res) {
                        that.submitLoading = false
                        if (res.status) {
                            layer.msg('添加成功')
                            that.addCategoryDialogVisible = false
                            that.categoryBlacklist = newCategories
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
                // 删除行业
                handleDeleteCategory: function (category) {
                    var that = this
                    var categoryLabel = this.getCategoryLabel(category)
                    layer.confirm('确定要移除屏蔽的行业"' + categoryLabel + '"吗？', function (index) {
                        var newCategories = that.categoryBlacklist.filter(function (item) {
                            return item !== category
                        })
                        var data = {
                            _action: 'setAmsCategoryBlackList',
                            authorizer_appid: that.authorizer_appid,
                            ams_category: newCategories.join('|')
                        }
                        that.httpPost("/wechat/open.PublisherAgencyAdmin/adBlackList", data, function (res) {
                            if (res.status) {
                                layer.msg('删除成功')
                                that.categoryBlacklist = newCategories
                            } else {
                                layer.msg(res.msg)
                            }
                        })
                        layer.close(index)
                    })
                }
            }
        });
    })
</script>
