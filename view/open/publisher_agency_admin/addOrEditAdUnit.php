<div>
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>广告位设置</span>
            </div>
            <el-form :model="form" label-width="120px" size="small" style="max-width: 560px;">
                <el-form-item label="APPID">
                    <span>{{ form.authorizer_appid }}</span>
                </el-form-item>

                <el-form-item label="广告类型">
                    <el-select v-model="form.ad_slot" placeholder="请选择">
                        <el-option
                                v-for="item in ad_slot_types"
                                :key="item.value"
                                :label="item.name"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="广告名称">
                    <el-input v-model="form.name" placeholder="">
                    </el-input>
                </el-form-item>

                <el-form-item label="广告时长" v-if="form.ad_slot === 'SLOT_ID_WEAPP_REWARD_VIDEO'">
                    <el-radio-group v-model="form.video_range">
                        <template v-for="item in video_range">
                            <el-radio :label="item.value">{{ item.name }}</el-radio>
                        </template>
                    </el-radio-group>
                    <p style="font-size: 14px;color: gray">视频完播后，用户即可获得奖励并关闭。</p>
                </el-form-item>

                <el-form-item label="模板ID" v-if="form.ad_slot === 'SLOT_ID_WEAPP_TEMPLATE'">
                    <el-input v-model="form.tmpl_id" placeholder="">
                    </el-input>
                    <p style="font-size: 14px;color: gray">自定义创建模板，填入变现专区-原生模板管理自定义创建的模板ID。操作指引见：<a href="https://docs.qq.com/doc/DVlVCTmdKUFllcnpa">原生模板编辑能力指引</a></p>
                </el-form-item>

                <!--操作区域-->
                <el-form-item label="" style="margin-top: 10px;padding-top: 10px;">
                    <el-button type="primary" size="mini" @click="handleSubmit">确认</el-button>
                </el-form-item>
            </el-form>
        </el-card>
    </div>
</div>

<style>
    p {
        margin: 0;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                form: {
                    authorizer_appid: '',
                    ad_slot: '',
                    video_range: '6-15',
                    tmpl_id: ''
                },
                itemList: [],
                ad_slot_types: [
                    {name: 'Banner', value: 'SLOT_ID_WEAPP_BANNER'},
                    {name: '激励视频', value: 'SLOT_ID_WEAPP_REWARD_VIDEO'},
                    {name: '插屏广告', value: 'SLOT_ID_WEAPP_INTERSTITIAL'},
                    {name: '视频广告', value: 'SLOT_ID_WEAPP_VIDEO_FEEDS'},
                    {name: '视频贴片广告', value: 'SLOT_ID_WEAPP_VIDEO_BEGIN'},
                    {name: '模板广告', value: 'SLOT_ID_WEAPP_TEMPLATE'},
                ],
                video_range: [
                    {name: '6-15秒', value: '6-15'},
                    {name: '6-30秒', value: '6-30'},
                    {name: '6-60秒', value: '6-60'},
                    {name: '6-不限', value: '6-86400'},
                ]
            },
            mounted: function () {
                this.form.authorizer_appid = this.getUrlQuery('authorizer_appid');
            },
            methods: {
                handleSubmit: function () {
                    const data = this.form
                    data['_action'] = 'submit'
                    this.httpPost("/wechat/open.PublisherAgencyAdmin/addOrEditAdUnit", data, function (res) {
                        layer.msg(res.msg)
                    })
                },

            }
        })
    });
</script>