<template>
  <div class="ql-page">
    <div class="ql-card toolbar">
      <div>
        <h3 class="ql-section-title" style="margin:0">监控日志</h3>
        <p class="ql-muted">任务 #{{ taskId || '全部' }}</p>
      </div>
      <div class="actions">
        <el-button round @click="$router.push('/monitor')">返回任务</el-button>
        <el-button type="primary" round :loading="loading" @click="load">刷新</el-button>
      </div>
    </div>
    <div class="ql-card" v-loading="loading">
      <el-table :data="logs" stripe empty-text="暂无日志">
        <el-table-column prop="created_at" label="时间" width="160" />
        <el-table-column prop="url" label="URL" min-width="160" show-overflow-tooltip />
        <el-table-column label="结果" width="90">
          <template #default="{ row }">
            <el-tag :type="row.check_status === 'ok' ? 'success' : 'danger'" size="small" round>
              {{ row.check_status === 'ok' ? '正常' : '异常' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="http_code" label="状态码" width="90" />
        <el-table-column prop="response_time" label="耗时" width="90" />
        <el-table-column prop="error_message" label="错误" min-width="160" show-overflow-tooltip />
        <el-table-column label="通知" width="80">
          <template #default="{ row }">
            {{ row.notified === 'true' || row.notified === 1 ? '是' : '否' }}
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination
          background
          layout="total, prev, pager, next"
          :total="total"
          :page-size="pageSize"
          :current-page="page"
          @current-change="onPage"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { monitorLogList } from '../api/panel'

const route = useRoute()
const loading = ref(false)
const logs = ref([])
const total = ref(0)
const page = ref(1)
const pageSize = 20
const taskId = computed(() => Number(route.query.id) || 0)

async function load() {
  loading.value = true
  const res = await monitorLogList({ id: taskId.value, page: page.value, page_size: pageSize })
  loading.value = false
  if (res.ok) {
    logs.value = res.data?.logs || []
    total.value = res.data?.total || 0
    page.value = res.data?.page || page.value
  }
}

function onPage(p) {
  page.value = p
  load()
}

watch(taskId, () => {
  page.value = 1
  load()
})

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
.actions {
  display: flex;
  gap: 8px;
}
.pager {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
