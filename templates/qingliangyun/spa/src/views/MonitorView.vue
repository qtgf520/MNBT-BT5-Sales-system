<template>
  <div class="ql-page">
    <div class="ql-card toolbar">
      <div>
        <h3 class="ql-section-title" style="margin:0">监控任务</h3>
        <p class="ql-muted">URL / 资源阈值监控 · {{ taskCount }} / 5</p>
      </div>
      <div class="actions">
        <el-button round @click="load" :loading="loading">刷新</el-button>
        <el-button type="primary" round @click="openEdit()">添加监控</el-button>
      </div>
    </div>

    <div class="ql-card" v-loading="loading">
      <el-table :data="tasks" stripe empty-text="暂无监控任务" style="width:100%">
        <el-table-column prop="id" label="ID" width="64" />
        <el-table-column prop="name" label="名称" min-width="100" />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            {{ row.task_type === 'resource' ? '资源' : 'URL' }}
          </template>
        </el-table-column>
        <el-table-column label="对象" min-width="180" show-overflow-tooltip>
          <template #default="{ row }">
            <span v-if="row.task_type === 'resource'">
              {{ resourceName(row.resource_type) }} &gt; {{ row.resource_threshold }}%
            </span>
            <span v-else>{{ row.url }}</span>
          </template>
        </el-table-column>
        <el-table-column label="间隔" width="90">
          <template #default="{ row }">
            {{ row.task_type === 'resource' ? '3分钟' : `${row.interval_seconds}s` }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.enabled === 'true' ? 'success' : 'info'" size="small" round>
              {{ row.enabled === 'true' ? '启用' : '停用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="最近" width="100">
          <template #default="{ row }">
            <span :class="row.last_status === 'ok' ? 'ok' : 'bad'">
              {{ row.last_status === 'ok' ? '正常' : '异常' }}
            </span>
            / {{ row.last_code || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="openEdit(row)">修改</el-button>
            <el-button link type="warning" @click="onToggle(row)">
              {{ row.enabled === 'true' ? '停用' : '启用' }}
            </el-button>
            <el-button link type="danger" @click="onDel(row)">删除</el-button>
            <el-button link @click="$router.push(`/monitor-log?id=${row.id}`)">日志</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <el-dialog v-model="visible" :title="form.id ? '修改监控' : '添加监控'" width="640px" destroy-on-close>
      <el-form label-position="top">
        <el-form-item label="任务名称">
          <el-input v-model="form.name" placeholder="例如：官网可用性" />
        </el-form-item>
        <el-form-item label="任务类型">
          <el-radio-group v-model="form.task_type">
            <el-radio-button value="url">URL 监控</el-radio-button>
            <el-radio-button value="resource">资源监控</el-radio-button>
          </el-radio-group>
        </el-form-item>
        <template v-if="form.task_type === 'url'">
          <el-form-item label="URL">
            <el-input v-model="form.url" placeholder="https://example.com/" />
          </el-form-item>
          <el-row :gutter="12">
            <el-col :span="8">
              <el-form-item label="方法">
                <el-select v-model="form.method" style="width:100%">
                  <el-option label="GET" value="GET" />
                  <el-option label="POST" value="POST" />
                  <el-option label="HEAD" value="HEAD" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="间隔(秒≥15)">
                <el-input-number v-model="form.interval_seconds" :min="15" style="width:100%" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="超时(秒)">
                <el-input-number v-model="form.timeout_seconds" :min="1" :max="30" style="width:100%" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="12">
            <el-col :span="8">
              <el-form-item label="失败次数告警">
                <el-input-number v-model="form.fail_threshold" :min="1" style="width:100%" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="状态码规则">
                <el-select v-model="form.status_rule" style="width:100%">
                  <el-option label="等于" value="eq" />
                  <el-option label="不等于" value="neq" />
                  <el-option label="包含" value="in" />
                  <el-option label="不包含" value="not_in" />
                  <el-option label="范围" value="range" />
                  <el-option label="≥" value="gte" />
                  <el-option label="≤" value="lte" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="状态码值">
                <el-input v-model="form.status_value" placeholder="200" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="12">
            <el-col :span="8">
              <el-form-item label="内容规则">
                <el-select v-model="form.content_rule" style="width:100%">
                  <el-option label="不检测" value="none" />
                  <el-option label="包含" value="contains" />
                  <el-option label="不包含" value="not_contains" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="16">
              <el-form-item label="内容关键词">
                <el-input v-model="form.content_value" />
              </el-form-item>
            </el-col>
          </el-row>
        </template>
        <template v-else>
          <el-row :gutter="12">
            <el-col :span="12">
              <el-form-item label="资源类型">
                <el-select v-model="form.resource_type" style="width:100%">
                  <el-option label="网页空间" value="web" />
                  <el-option label="数据库空间" value="sql" />
                  <el-option label="本月流量" value="traffic" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="超过百分比告警">
                <el-input-number v-model="form.resource_threshold" :min="1" :max="100" style="width:100%" />
              </el-form-item>
            </el-col>
          </el-row>
        </template>
        <el-row :gutter="12">
          <el-col :span="12">
            <el-form-item label="邮件通知">
              <el-switch v-model="form.notify_email" active-value="true" inactive-value="false" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="启用任务">
              <el-switch v-model="form.enabled" active-value="true" inactive-value="false" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <el-button @click="visible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="onSave">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { monitorList, monitorSave, monitorDel, monitorToggle } from '../api/panel'

const loading = ref(false)
const saving = ref(false)
const tasks = ref([])
const taskCount = ref(0)
const visible = ref(false)
const form = reactive(emptyForm())

function emptyForm() {
  return {
    id: 0,
    name: '',
    task_type: 'url',
    url: '',
    resource_type: 'web',
    resource_threshold: 80,
    method: 'GET',
    interval_seconds: 60,
    timeout_seconds: 10,
    fail_threshold: 1,
    status_rule: 'eq',
    status_value: '200',
    content_rule: 'none',
    content_value: '',
    notify_email: 'true',
    enabled: 'true',
  }
}

function resourceName(t) {
  return { web: '网页空间', sql: '数据库', traffic: '流量' }[t] || t
}

function openEdit(row) {
  Object.assign(form, emptyForm())
  if (row) {
    Object.keys(form).forEach((k) => {
      if (row[k] !== undefined && row[k] !== null) form[k] = row[k]
    })
    form.id = row.id || 0
    form.interval_seconds = Number(row.interval_seconds) || 60
    form.timeout_seconds = Number(row.timeout_seconds) || 10
    form.fail_threshold = Number(row.fail_threshold) || 1
    form.resource_threshold = Number(row.resource_threshold) || 80
  } else if (taskCount.value >= 5) {
    ElMessage.warning('每个用户最多 5 个监控任务')
    return
  }
  visible.value = true
}

async function load() {
  loading.value = true
  const res = await monitorList()
  loading.value = false
  if (res.ok) {
    tasks.value = res.data?.tasks || []
    taskCount.value = res.data?.task_count ?? tasks.value.length
  }
}

async function onSave() {
  saving.value = true
  const res = await monitorSave({ ...form })
  saving.value = false
  if (res.ok) {
    ElMessage.success(res.message)
    visible.value = false
    await load()
  }
}

async function onToggle(row) {
  const en = row.enabled === 'true' ? 'false' : 'true'
  const res = await monitorToggle(row.id, en)
  if (res.ok) {
    ElMessage.success(res.message)
    await load()
  }
}

async function onDel(row) {
  try {
    await ElMessageBox.confirm('确定删除该监控任务？', '删除', { type: 'warning' })
  } catch {
    return
  }
  const res = await monitorDel(row.id)
  if (res.ok) {
    ElMessage.success(res.message)
    await load()
  }
}

onMounted(load)
</script>

<style scoped>
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.toolbar p {
  margin: 6px 0 0;
}
.actions {
  display: flex;
  gap: 8px;
}
.ok {
  color: var(--ql-primary-dark);
  font-weight: 600;
}
.bad {
  color: #e03131;
  font-weight: 600;
}
</style>
