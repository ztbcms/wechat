<div>
    <!-- 菜单管理页面：左侧手机预览模拟器，右侧菜单配置区 -->
    <div id="app" v-cloak>
        <el-row :gutter="12">
            <el-col :span="8">
                <el-card shadow="never" style="height: 720px">
                    <div slot="header" class="clearfix">
                        <span>手机预览</span>
                    </div>
                    <div class="phone">
                        <!-- 顶部状态栏（返回箭头、时间、用户头像） -->
                        <div class="phone-header">
                            <i class="el-icon-arrow-left"></i>
                            <span class="phone-time">12:12</span>
                            <i class="el-icon-user"></i>
                        </div>
                        <div class="phone-screen">
                            <!-- 子菜单浮层：当主菜单存在子菜单时显示，可点击选择子菜单 -->
                            <div v-if="activeMainHasSubs" class="submenu-pop">
                                <div
                                    v-for="(sub, si) in activeMain.sub_button"
                                    :key="'sub-'+si"
                                    class="submenu-item"
                                    :class="{active: selected.subIndex===si}"
                                    @click="selectSub(si)"
                                >{{ sub.name || '未命名' }}</div>
                            </div>
                        </div>
                        <div class="phone-menu">
                            <!-- 底部主菜单栏：最多 3 个，支持选择/移动/删除 -->
                            <div
                                v-for="(m, mi) in editor.button"
                                :key="'m-'+mi"
                                class="menu-item"
                                :class="{active: selected.mainIndex===mi}"
                                @click="selectMain(mi)"
                            >
                                <span class="menu-name">{{ m.name || '未命名' }}</span>
                                <!-- 选中菜单时的操作按钮组：左移/删除/右移（根据可移动性显示） -->
                                <div v-if="selected.mainIndex===mi" class="op-group">
                                    <el-button circle size="mini" icon="el-icon-arrow-left" v-if="canMoveLeft" @click.stop="moveLeft"></el-button>
                                    <el-button circle size="mini" type="danger" icon="el-icon-delete" @click.stop="removeSelected"></el-button>
                                    <el-button circle size="mini" icon="el-icon-arrow-right" v-if="canMoveRight" @click.stop="moveRight"></el-button>
                                </div>
                            </div>
                            <!-- 新增主菜单入口：当不足 3 个时显示 -->
                            <div v-if="editor.button.length<3" class="menu-item add" @click="addMain">
                                <i class="el-icon-plus"></i>
                            </div>
                        </div>
                    </div>
                </el-card>
            </el-col>
            <el-col :span="16">
                <el-card shadow="never" style="min-height: 720px">
                    <div slot="header" class="clearfix">
                        <span>菜单信息</span>
                        <div style="float:right">
                            <!-- 顶部操作：载入远端、删除远端、发布到公众号 -->
                            <el-button size="mini" @click="loadRemote">载入公众号当前菜单</el-button>
                            <el-button size="mini" type="warning" @click="deleteRemote">删除公众号菜单</el-button>
                            <el-button size="mini" type="primary" @click="publish">发布到公众号</el-button>
                        </div>
                    </div>
                    <el-alert
                        title="提示：一级最多3个，二级最多5个；主菜单含子菜单时只能编辑名称"
                        type="info"
                        :closable="false"
                        show-icon
                        style="margin-bottom: 12px"
                    ></el-alert>
                    <el-form :model="formRef" label-width="120px" size="small">
                        <el-form-item label="APPID">
                            <span>{{ appid }}</span>
                        </el-form-item>
                        <el-form-item label="当前编辑">
                            <el-tag v-if="selected.mainIndex>-1 && selected.subIndex===-1" type="success">主菜单 {{ selected.mainIndex+1 }}</el-tag>
                            <el-tag v-if="selected.mainIndex>-1 && selected.subIndex>-1" type="success">子菜单 {{ selected.subIndex+1 }}</el-tag>
                            <el-tag v-if="selected.mainIndex===-1" type="info">未选择</el-tag>
                            <!-- 在选中主菜单时可新增子菜单（最多 5 个） -->
                            <el-button v-if="selected.mainIndex>-1 && activeMain.sub_button.length<5" size="mini" style="margin-left:8px" @click="addSub">新增子菜单</el-button>
                        </el-form-item>
                        <template v-if="selected.mainIndex>-1">
                            <el-form-item label="名称" required>
                                <el-input v-model="activeItem.name" maxlength="60" show-word-limit placeholder="请输入菜单名称"></el-input>
                                <div class="helper">
                                    <span v-if="selected.subIndex>-1">长度约束：≤8个汉字或≤16个字母/数字</span>
                                    <span v-else>长度约束：≤4个汉字或≤8个字母</span>
                                </div>
                                <el-alert v-if="nameInvalid" title="标题长度或字符不合法" type="error" :closable="false" style="margin-top:6px"></el-alert>
                            </el-form-item>
                            <template v-if="!activeMainHasSubs || selected.subIndex>-1">
                                <!-- 类型与跳转配置：主菜单有子菜单时不可编辑，仅子菜单支持完整配置 -->
                                <el-form-item label="类型" required>
                                    <el-radio-group v-model="activeItem.type">
                                        <el-radio label="view">跳转链接</el-radio>
                                        <el-radio label="miniprogram">微信小程序</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <template v-if="activeItem.type==='view'">
                                    <el-form-item label="URL" required>
                                        <el-input v-model="activeItem.url" placeholder="https://..." ></el-input>
                                    </el-form-item>
                                </template>
                                <template v-else-if="activeItem.type==='miniprogram'">
                                    <el-form-item label="小程序appid" required>
                                        <el-input v-model="activeItem.appid" placeholder="wx..." ></el-input>
                                    </el-form-item>
                                    <el-form-item label="页面路径" required>
                                        <el-input v-model="activeItem.pagepath" placeholder="pages/index/index" ></el-input>
                                    </el-form-item>
                                    <el-form-item label="备用URL" required>
                                        <el-input v-model="activeItem.url" placeholder="https://..." ></el-input>
                                    </el-form-item>
                                </template>
                            </template>
                            <template v-else>
                                <el-alert title="含有子菜单：主菜单仅可编辑名称" type="warning" :closable="false"></el-alert>
                            </template>
                        </template>
                        <template v-else>
                            <el-empty description="请选择左侧菜单或新增"></el-empty>
                        </template>
                    </el-form>
                </el-card>
            </el-col>
        </el-row>
    </div>
</div>

<script>
    $(document).ready(function(){
        new Vue({
            el: '#app',
            data: {
                appid: '',
                editor: {
                    button: []
                },
                selected: { mainIndex: -1, subIndex: -1 },
                formRef: {}
            },
            computed: {
                /**
                 * 当前选中的主菜单数据（保证存在 sub_button 数组）
                 */
                activeMain: function(){
                    if(this.selected.mainIndex<0) return { sub_button: [] };
                    const m = this.editor.button[this.selected.mainIndex];
                    if(!m.sub_button) this.$set(m, 'sub_button', []);
                    return m;
                },
                /**
                 * 是否存在子菜单，用于控制子菜单浮层展示
                 */
                activeMainHasSubs: function(){
                    return this.selected.mainIndex>-1 && this.activeMain.sub_button && this.activeMain.sub_button.length>0;
                },
                /**
                 * 当前正在编辑的菜单项：优先为子菜单，否则为主菜单
                 */
                activeItem: function(){
                    if(this.selected.mainIndex<0) return {};
                    if(this.selected.subIndex>-1){
                        return this.activeMain.sub_button[this.selected.subIndex];
                    }
                    return this.activeMain;
                },
                /**
                 * 是否可向左移动（主菜单或子菜单）
                 */
                canMoveLeft: function(){
                    if(this.selected.mainIndex<0) return false;
                    if(this.selected.subIndex>-1) return this.selected.subIndex>0;
                    return this.selected.mainIndex>0;
                },
                /**
                 * 是否可向右移动（主菜单或子菜单）
                 */
                canMoveRight: function(){
                    if(this.selected.mainIndex<0) return false;
                    if(this.selected.subIndex>-1) return this.selected.subIndex < this.activeMain.sub_button.length-1;
                    return this.selected.mainIndex < this.editor.button.length-1;
                },
                /**
                 * 名称是否不合法（根据主/子菜单不同规则校验）
                 */
                nameInvalid: function(){
                    if(this.selected.mainIndex<0) return false;
                    const name = (this.activeItem && this.activeItem.name) || '';
                    if(this.selected.subIndex>-1){
                        return !this.isSubNameValid(name);
                    }
                    return !this.isMainNameValid(name);
                }
            },
            mounted: function(){
                // 页面初始化：从 URL 读取 appid，并初始化编辑器数据
                this.appid = this.getUrlQuery('appid');
                if(!this.appid){
                    layer.msg('缺少 appid');
                }
                this.editor.button = [];
            },
            methods: {
                /**
                 * 选择主菜单
                 */
                selectMain: function(i){
                    this.selected.mainIndex = i; this.selected.subIndex = -1;
                },
                /**
                 * 选择子菜单
                 */
                selectSub: function(i){
                    this.selected.subIndex = i;
                },
                /**
                 * 新增主菜单（最多 3 个）
                 */
                addMain: function(){
                    if(this.editor.button.length>=3){ layer.msg('一级菜单最多3个'); return; }
                    this.editor.button.push({ name: '', type: 'view', url: '', sub_button: [] });
                    this.selectMain(this.editor.button.length-1);
                },
                /**
                 * 新增子菜单（最多 5 个）
                 */
                addSub: function(){
                    if(this.selected.mainIndex<0){ layer.msg('请先选择主菜单'); return; }
                    if(this.activeMain.sub_button.length>=5){ layer.msg('二级菜单最多5个'); return; }
                    this.activeMain.sub_button.push({ name: '', type: 'view', url: '' });
                    this.selected.subIndex = this.activeMain.sub_button.length-1;
                },
                /**
                 * 向左移动（子菜单或主菜单）
                 */
                moveLeft: function(){
                    if(this.selected.subIndex>-1){
                        const si=this.selected.subIndex; if(si<=0) return;
                        const arr=this.activeMain.sub_button; arr.splice(si-1,0,arr.splice(si,1)[0]);
                        this.selected.subIndex = si-1;
                    } else if(this.selected.mainIndex>-1){
                        const mi=this.selected.mainIndex; if(mi<=0) return;
                        const arr=this.editor.button; arr.splice(mi-1,0,arr.splice(mi,1)[0]);
                        this.selected.mainIndex = mi-1;
                    }
                },
                /**
                 * 向右移动（子菜单或主菜单）
                 */
                moveRight: function(){
                    if(this.selected.subIndex>-1){
                        const si=this.selected.subIndex; if(si>=this.activeMain.sub_button.length-1) return;
                        const arr=this.activeMain.sub_button; arr.splice(si+1,0,arr.splice(si,1)[0]);
                        this.selected.subIndex = si+1;
                    } else if(this.selected.mainIndex>-1){
                        const mi=this.selected.mainIndex; if(mi>=this.editor.button.length-1) return;
                        const arr=this.editor.button; arr.splice(mi+1,0,arr.splice(mi,1)[0]);
                        this.selected.mainIndex = mi+1;
                    }
                },
                /**
                 * 删除当前选中项（子菜单或主菜单）
                 */
                removeSelected: function(){
                    if(this.selected.subIndex>-1){
                        this.activeMain.sub_button.splice(this.selected.subIndex,1);
                        this.selected.subIndex = -1;
                    } else if(this.selected.mainIndex>-1){
                        this.editor.button.splice(this.selected.mainIndex,1);
                        this.selected.mainIndex = -1;
                    }
                },
                /**
                 * 发布到公众号：POST 调用 setMenu 接口
                 */
                publish: function(){
                    if(!this.appid){ layer.msg('缺少 appid'); return; }
                    if(!this.validateAll()){ return; }
                    const data = { button_json: JSON.stringify(this.editor.button) };
                    this.httpPost('/wechat/office.MenuAdmin/setMenu?appid='+encodeURIComponent(this.appid), data, function(res){
                        if(res && (res.errcode===0 || res.status===true)){
                            layer.msg('发布成功');
                        } else {
                            layer.msg((res && (res.errmsg||res.msg)) || '发布失败');
                        }
                    });
                },
                /**
                 * 删除公众号菜单：GET 调用 deleteMenu 接口
                 */
                deleteRemote: function(){
                    if(!this.appid){ layer.msg('缺少 appid'); return; }
                    this.httpGet('/wechat/office.MenuAdmin/deleteMenu?appid='+encodeURIComponent(this.appid), {}, function(res){
                        if(res && (res.errcode===0 || res.status===true)){
                            layer.msg('删除成功');
                        } else {
                            layer.msg((res && (res.errmsg||res.msg)) || '删除失败');
                        }
                    });
                },
                /**
                 * 载入公众号当前菜单：GET 调用 current 接口并映射到编辑器结构
                 */
                loadRemote: function(){
                    if(!this.appid){ layer.msg('缺少 appid'); return; }
                    let that=this;
                    this.httpGet('/wechat/office.MenuAdmin/getCurrentMenu?appid='+encodeURIComponent(this.appid), {}, function(res){
                        let mapped = that.mapRemoteToButtons(res);
                        if(mapped){ that.editor.button = mapped; that.selected={mainIndex:-1, subIndex:-1}; layer.msg('已载入公众号当前菜单'); }
                        else { layer.msg('载入失败或无菜单'); }
                    });
                },
                /**
                 * 将远端返回的 selfmenu_info/button 结构映射为 editor.button
                 * 仅保留 view/miniprogram 类型，剪裁至一级≤3、二级≤5
                 */
                mapRemoteToButtons: function(resp){
                    try{
                        if(resp && resp.selfmenu_info && resp.selfmenu_info.button){
                            const btns = resp.selfmenu_info.button;
                            const out=[];
                            btns.forEach(function(b){
                                if(b.sub_button && b.sub_button.list){
                                    const subs=[];
                                    b.sub_button.list.forEach(function(sb){
                                        if(sb.type==='view' || sb.type==='miniprogram'){
                                            const it={ name: sb.name||'', type: sb.type };
                                            if(sb.type==='view'){ it.url = sb.url||''; }
                                            if(sb.type==='miniprogram'){ it.appid=sb.appid||''; it.pagepath=sb.pagepath||''; it.url=sb.url||''; }
                                            subs.push(it);
                                        }
                                    });
                                    out.push({ name: b.name||'', sub_button: subs });
                                } else if(b.type==='view' || b.type==='miniprogram'){
                                    const it={ name: b.name||'', type: b.type };
                                    if(b.type==='view'){ it.url = b.url||''; }
                                    if(b.type==='miniprogram'){ it.appid=b.appid||''; it.pagepath=b.pagepath||''; it.url=b.url||''; }
                                    out.push(it);
                                }
                            });
                            return out.slice(0,3).map(function(x){ if(x.sub_button){ x.sub_button = x.sub_button.slice(0,5);} return x; });
                        }
                        // 兼容 create 接口结构
                        if(resp && resp.button && Array.isArray(resp.button)){
                            return resp.button;
                        }
                    }catch(e){
                        console.error(e);
                    }
                    return null;
                },
                /**
                 * 计算名称的单位长度（中文=2，ASCII字母/数字/其它=1）
                 * @param {String} s 文本
                 * @returns {Number} 单位长度
                 */
                computeUnits: function(s){
                    if(!s) return 0;
                    let units = 0;
                    for(let i=0;i<s.length;i++){
                        const ch = s[i];
                        if(/[\u4e00-\u9fa5]/.test(ch)) units += 2; else units += 1;
                    }
                    return units;
                },
                /**
                 * 是否为中文字符
                 * @param {String} ch 字符
                 */
                isChinese: function(ch){
                    return /[\u4e00-\u9fa5]/.test(ch);
                },
                /**
                 * 是否为英文字母
                 * @param {String} ch 字符
                 */
                isAsciiLetter: function(ch){
                    return /[A-Za-z]/.test(ch);
                },
                /**
                 * 是否为数字
                 * @param {String} ch 字符
                 */
                isDigit: function(ch){
                    return /[0-9]/.test(ch);
                },
                /**
                 * 校验主菜单标题长度：≤4汉字或≤8字母（单位≤8）
                 * @param {String} s 文本
                 */
                isMainNameValid: function(s){
                    return this.computeUnits(s) <= 8;
                },
                /**
                 * 校验子菜单标题：仅允许中文/英文/数字，且≤8汉字或≤16字母（单位≤16）
                 * @param {String} s 文本
                 */
                isSubNameValid: function(s){
                    if(!s) return true;
                    for(let i=0;i<s.length;i++){
                        const ch = s[i];
                        if(!(this.isChinese(ch) || this.isAsciiLetter(ch) || this.isDigit(ch))){
                            return false;
                        }
                    }
                    return this.computeUnits(s) <= 16;
                },
                /**
                 * 校验当前编辑内容与全部菜单项是否满足规则
                 * @returns {Boolean}
                 */
                validateAll: function(){
                    // 主菜单校验
                    for(let mi=0; mi<this.editor.button.length; mi++){
                        const m = this.editor.button[mi];
                        if(!this.isMainNameValid(m.name||'')){
                            layer.msg('主菜单'+(mi+1)+'标题长度不合法');
                            return false;
                        }
                        // 子菜单校验
                        if(m.sub_button && m.sub_button.length){
                            for(let si=0; si<m.sub_button.length; si++){
                                const sb = m.sub_button[si];
                                if(!this.isSubNameValid(sb.name||'')){
                                    layer.msg('子菜单'+(mi+1)+'-'+(si+1)+'标题长度或字符不合法');
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                }
            }
        });
    });
</script>

<style>
    [v-cloak]{ display:none; }
    .phone{ width: 100%; height: 630px; background: #f5f7fa; border-radius: 28px; padding: 12px 12px 0; box-sizing: border-box; border:1px solid #e4e7ed; position: relative; }
    .phone-header{ height: 32px; display:flex; align-items:center; justify-content: space-between; padding: 0 8px; color:#909399; }
    .phone-time{ font-weight: 600; }
    .phone-screen{ position: absolute; top: 44px; left: 12px; right: 12px; bottom: 82px; background: #fff; border:1px solid #ebeef5; border-radius: 6px; }
    .phone-menu{ position: absolute; left: 12px; right: 12px; bottom: 12px; height: 70px; display:flex; background: #fff; border:1px solid #ebeef5; border-radius: 6px; }
    .menu-item{ flex:1; display:flex; align-items:center; justify-content:center; position: relative; cursor: pointer; border-right:1px solid #f2f6fc; }
    .menu-item:last-child{ border-right: none; }
    .menu-item.active{ background: #f9fafc; }
    .menu-item.add{ color:#909399; }
    .menu-name{ font-size: 12px; color:#606266; }
    .op-group{ position: absolute; top: -36px; display:flex; gap:6px; }
    .submenu-pop{ position: absolute; left: 12px; right: 12px; bottom: 84px; background: #fff; border:1px solid #ebeef5; border-radius: 6px; padding: 6px; }
    .submenu-item{ padding: 6px 8px; border-radius: 4px; cursor: pointer; }
    .submenu-item.active{ background: #f5f7fa; }
    .helper{ font-size: 12px; color:#909399; margin-top:4px; }
</style>
