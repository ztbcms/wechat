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
        prop="from_user_name"
        label="发送用户openid"
        align="center"
        min-width="150">
    </el-table-column>
    <el-table-column
        prop="to_user_name"
        label="接收者"
        align="center"
        min-width="150">
    </el-table-column>
    <el-table-column
        prop="msg_id"
        label="消息id"
        align="center"
        min-width="100">
    </el-table-column>
    <el-table-column
            prop="title"
            label="标题"
            align="center"
            min-width="100">
    </el-table-column>
    <el-table-column
            prop="description"
            label="介绍"
            align="center"
            min-width="100">
    </el-table-column>
    <el-table-column
            prop="url"
            label="链接"
            align="center"
            min-width="250">
    </el-table-column>
    <el-table-column
        align="center"
        label="创建时间"
        min-width="150">
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