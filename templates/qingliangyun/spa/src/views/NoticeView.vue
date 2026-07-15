<template>
  <div class="ql-page">
    <div class="ql-card toolbar">
      <div>
        <h3 class="ql-section-title" style="margin:0">通知日志</h3>
        <p class="ql-muted">系统与监控通知</p>
      </div>
      <el-button type="primary" round @click="markAll">全部已读</el-button>
    </div>

    <div class="ql-card filters">
      <el-form :inline="true" @submit.prevent>
        <el-form-item label="类型">
          <el-select v-model="query.type" clearable placeholder="全部" style="width:120px">
            <el-option label="监控" value="monitor" />
            <el-option label="到期" value="expire" />
            <el-option label="流量" value="traffic" />
          </el-select>
        </el-form-item>
        <el-form-item label="级别">
          <el-select v-model="query.level" clearable placeholder="全部" style="width:110px">
            <el-option label="info" value="info" />
            <el-option label="warning" value="warning" />
            <el-option label="error" value="error" />
          </el-select>
        </el-form-item>
        <el-form-item label="已读">
          <el-select v-model="query.read" clearable placeholder="全部" style="width:100px">
            <el-option label="未读" value="false" />
            <el-option label="已读" value="true" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="query.keyword" clearable placeholder="标题/内容" style="width:160px" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="search">查询</el-button>
        </el-form-item>
      </el-form>
    </div>

    <div class="ql-card" v-loading="loading">
      <el-table :data="logs" stripe empty-text="暂无通知">
        <el-table-column prop="id" label="ID" width="70" />
        <el-table-column prop="created_at" label="时间" width="160" />
        <el-table-column prop="type" label="类型" width="90" />
        <el-table-column prop="level" label="级别" width="90">
          <template #default="{ row }">
            <el-tag
              size="small"
              round
              :type="row.level === 'error' ? 'danger' : row.level === 'warning' ? 'warning' : 'info'"
            >
              {{ row.level }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="title" label="标题" min-width="140" show-overflow-tooltip />
        <el-table-column prop="content" label="内容" min-width="200" show-overflow-tooltip />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.is_read === 'true' ? 'info' : 'success'" size="small" round>
              {{ row.is_read === 'true' ? '已读' : '未读' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="90">
          <template #default="{ row }">
            <el-button
              v-if="row.is_read !== 'true'"
              link
              type="primary"
              @click="markOne(row)"
            >
              已读
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination
          background
          layout="total, prev, pager, next"
          :total="total"
          :page-size="query.page_size"
          :current-page="query.page"
          @current-change="onPage"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { noticeList, noticeRead } from '../api/panel'

const loading = ref(false)
const logs = ref([])
const total = ref(0)
const query = reactive({
  type: '',
  level: '',
  read: '',
  keyword: '',
  page: 1,
  page_size: 15,
})

async function load() {
  loading.value = true
  const res = await noticeList({ ...query })
  loading.value = false
  if (res.ok) {
    logs.value = res.data?.logs || []
    total.value = res.data?.total || 0
    query.page = res.data?.page || query.page
  }
}

function search() {
  query.page = 1
  load()
}

function onPage(p) {
  query.page = p
  load()
}

async function markOne(row) {
  const res = await noticeRead(row.id)
  if (res.ok) {
    ElMessage.success('已标记')
    await load()
  }
}

async function markAll() {
  const res = await noticeRead(0)
  if (res.ok) {
    ElMessage.success('全部已读')
    await load()
  }
}

onMounted(load)
</script>

<style scoped>
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 12px;
}
.toolbar p {
  margin: 6px 0 0;
}
.filters {
  margin-bottom: 16px;
}
.pager {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
