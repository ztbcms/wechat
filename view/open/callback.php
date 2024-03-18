<div id="app" v-cloak>
    <template v-if="auth_status == 1">
        <el-result icon="success" title="授权成功" sub-title="">
        </el-result>
    </template>
    <template v-else>
        <el-result icon="error" title="授权失败" :sub-title="msg">
        </el-result>
    </template>
</div>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                auth_status: "{$auth_status}",
                msg: "{$msg}"
            },
            mounted: function () {
            },
            methods: {}
        });
    })
</script>
