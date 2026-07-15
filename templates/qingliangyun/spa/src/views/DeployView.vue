<template>
  <div class="ql-page">
    <div class="ql-card toolbar">
      <div>
        <h3 class="ql-section-title" style="margin:0">一键部署</h3>
        <p class="ql-muted">部署将覆盖网站文件与数据库，请谨慎操作</p>
      </div>
      <el-button round :loading="loading" @click="load">刷新</el-button>
    </div>

    <el-row :gutter="16" v-loading="loading">
      <el-col v-for="item in list" :key="item.id" :xs="24" :sm="12" :lg="8">
        <div class="ql-card pack">
          <div class="pack-head">
            <h4>{{ item.name }}</h4>
            <el-tag v-if="Number(item.jg) > 0" type="warning" effect="light" round size="small">
              ¥{{ item.jg }}
            </el-tag>
            <el-tag v-else type="success" effect="light" round size="small">免费</el-tag>
          </div>
          <p class="ql-muted desc">{{ item.jc || '暂无简介' }}</p>
          <div class="req" v-if="item.sxpz">
            <span v-if="item.sxpz.webMB">空间 ≥ {{ item.sxpz.webMB }}MB</span>
            <span v-if="item.sxpz.sqlMB">数据库 ≥ {{ item.sxpz.sqlMB }}MB</span>
          </div>
          <el-button type="primary" round class="deploy-btn" @click="openDeploy(item)">
            部署
          </el-button>
        </div>
      </el-col>
      <el-col v-if="!loading && !list.length" :span="24">
        <el-empty description="暂无部署程序包" />
      </el-col>
    </el-row>

    <el-dialog v-model="visible" :title="`部署 · ${current?.name || ''}`" width="520px" destroy-on-close>
      <el-alert
        type="warning"
        :closable="false"
        show-icon
        title="部署将清空站点文件并可能覆盖数据库，请确认已备份。"
        style="margin-bottom: 16px"
      />
      <el-form v-if="formFields.length" label-position="top">
        <el-form-item v-for="f in formFields" :key="f.name" :label="f.label || f.name">
          <el-input v-model="formData[f.name]" :placeholder="f.placeholder || f.name" />
        </el-form-item>
      </el-form>
      <p v-else class="ql-muted">该程序无需额外参数</p>
      <template #footer>
        <el-button @click="visible = false">取消</el-button>
        <el-button type="primary" :loading="deploying" @click="onDeploy">确认部署</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { deployList, deployForm, deployRun } from '../api/panel'

const loading = ref(false)
const deploying = ref(false)
const list = ref([])
const visible = ref(false)
const current = ref(null)
const formFields = ref([])
const formData = ref({})

async function load() {
  loading.value = true
  const res = await deployList()
  loading.value = false
  if (res.ok) list.value = res.data?.list || []
}

async function openDeploy(item) {
  current.value = item
  formFields.value = []
  formData.value = {}
  const res = await deployForm(item.id)
  if (res.ok) {
    let fields = res.data?.form ?? res.raw?.form ?? []
    if (typeof fields === 'string') {
      try {
        fields = JSON.parse(fields)
      } catch {
        fields = []
      }
    }
    if (!Array.isArray(fields)) fields = []
    formFields.value = fields
    fields.forEach((f) => {
      formData.value[f.name] = f.value || ''
    })
  }
  visible.value = true
}

async function onDeploy() {
  try {
    await ElMessageBox.confirm('确认执行一键部署？此操作不可撤销。', '最终确认', {
      type: 'error',
      confirmButtonText: '确认部署',
    })
  } catch {
    return
  }
  deploying.value = true
  const payload = { id: current.value.id, ...formData.value }
  const res = await deployRun(payload)
  deploying.value = false
  if (res.ok) {
    ElMessage.success(res.message || '部署完成')
    visible.value = false
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
.pack {
  margin-bottom: 16px;
  min-height: 180px;
  display: flex;
  flex-direction: column;
}
.pack-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.pack-head h4 {
  margin: 0;
  font-size: 16px;
}
.desc {
  flex: 1;
  margin: 12px 0;
  line-height: 1.5;
  font-size: 13px;
}
.req {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  font-size: 12px;
  color: var(--ql-text-secondary);
  margin-bottom: 12px;
}
.deploy-btn {
  width: 100%;
}
</style>
