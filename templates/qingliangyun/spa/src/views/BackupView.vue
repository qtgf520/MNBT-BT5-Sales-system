<template>
  <div class="ql-page">
    <div class="ql-card toolbar">
      <div>
        <h3 class="ql-section-title" style="margin:0">SQL 数据备份</h3>
        <p class="ql-muted">共 {{ count }} 条备份记录</p>
      </div>
      <div class="actions">
        <el-button round :loading="loading" @click="load">刷新</el-button>
        <el-button type="primary" round :loading="acting" @click="onBackup">立即备份</el-button>
        <el-button type="danger" plain round :loading="acting" @click="onWipe">清空全部表</el-button>
      </div>
    </div>
    <div class="ql-card" v-loading="loading">
      <el-table :data="list" stripe empty-text="暂无备份">
        <el-table-column prop="name" label="名称" min-width="140" show-overflow-tooltip />
        <el-table-column prop="size" label="大小" width="100" />
        <el-table-column prop="addtime" label="时间" width="160" />
        <el-table-column prop="ps" label="备注" min-width="100" show-overflow-tooltip />
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="onRestore(row)">恢复</el-button>
            <el-button link @click="onDownload(row)">下载</el-button>
            <el-button link type="danger" @click="onDel(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  backupList,
  backupAdd,
  backupDel,
  backupRestore,
  backupDownload,
  backupWipe,
} from '../api/panel'

const loading = ref(false)
const acting = ref(false)
const list = ref([])
const count = ref(0)
const dbId = ref('')
const dbUser = ref('')

async function load() {
  loading.value = true
  const res = await backupList()
  loading.value = false
  if (res.ok) {
    list.value = res.data?.list || []
    count.value = res.data?.count || list.value.length
    dbId.value = res.data?.db_id || ''
    dbUser.value = res.data?.user || ''
  }
}

async function onBackup() {
  acting.value = true
  const res = await backupAdd(dbId.value)
  acting.value = false
  if (res.ok) {
    ElMessage.success(res.message || '备份成功')
    await load()
  }
}

async function onDel(row) {
  try {
    await ElMessageBox.confirm(`删除备份 ${row.name || row.filename}？`, '删除', { type: 'warning' })
  } catch {
    return
  }
  const res = await backupDel(row.id)
  if (res.ok) {
    ElMessage.success(res.message)
    await load()
  }
}

async function onRestore(row) {
  try {
    await ElMessageBox.confirm('恢复将覆盖当前数据库，是否继续？', '恢复备份', { type: 'warning' })
  } catch {
    return
  }
  acting.value = true
  const res = await backupRestore(dbUser.value, row.filename || row.name)
  acting.value = false
  if (res.ok) ElMessage.success(res.message || '恢复成功')
}

async function onDownload(row) {
  const res = await backupDownload(row.filename || row.name)
  if (res.ok) {
    const url = res.raw?.url || res.data?.url
    if (url) window.open(url, '_blank')
    else ElMessage.success(res.message || '已请求下载')
  }
}

async function onWipe() {
  try {
    await ElMessageBox.confirm('将删除数据库全部表，不可恢复！', '危险操作', {
      type: 'error',
      confirmButtonText: '确认清空',
    })
  } catch {
    return
  }
  acting.value = true
  const res = await backupWipe()
  acting.value = false
  if (res.ok) ElMessage.success(res.message)
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
.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
</style>
