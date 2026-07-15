<template>
  <div class="ql-page">
    <div class="ql-card toolbar">
      <div>
        <h3 class="ql-section-title" style="margin:0">站点统计</h3>
        <p class="ql-muted">访问量 · 流量 · 错误与排行</p>
      </div>
      <div class="actions">
        <el-radio-group v-model="range" size="default" @change="reloadAll">
          <el-radio-button value="today">今天</el-radio-button>
          <el-radio-button value="yesterday">昨天</el-radio-button>
          <el-radio-button value="7d">7天</el-radio-button>
          <el-radio-button value="30d">30天</el-radio-button>
        </el-radio-group>
        <el-button type="primary" round :loading="loading" @click="reloadAll">刷新</el-button>
      </div>
    </div>

    <el-row :gutter="16" v-loading="loading">
      <el-col :xs="12" :sm="6" v-for="c in cards" :key="c.key">
        <div class="ql-card metric">
          <div class="ql-muted">{{ c.label }}</div>
          <div class="val">{{ c.value }}</div>
        </div>
      </el-col>
    </el-row>

    <div class="ql-card mt">
      <h3 class="ql-section-title">访问趋势</h3>
      <div class="bars" v-if="trend.length">
        <div v-for="(t, i) in trendBars" :key="i" class="bar-col">
          <div class="bar" :style="{ height: t.h + '%' }" :title="t.tip" />
          <span>{{ t.label }}</span>
        </div>
      </div>
      <el-empty v-else description="暂无趋势数据" :image-size="72" />
    </div>

    <el-row :gutter="16" class="mt">
      <el-col :xs="24" :md="12">
        <div class="ql-card">
          <h3 class="ql-section-title">IP 排行</h3>
          <el-table :data="ipRank" size="small" stripe empty-text="暂无">
            <el-table-column prop="ip" label="IP" min-width="120" />
            <el-table-column prop="count" label="次数" width="80" />
            <el-table-column label="流量" width="100">
              <template #default="{ row }">{{ formatBytes(row.bytes) }}</template>
            </el-table-column>
          </el-table>
        </div>
      </el-col>
      <el-col :xs="24" :md="12">
        <div class="ql-card">
          <h3 class="ql-section-title">URI 排行</h3>
          <el-table :data="uriRank" size="small" stripe empty-text="暂无">
            <el-table-column prop="uri" label="URI" min-width="140" show-overflow-tooltip />
            <el-table-column prop="count" label="次数" width="80" />
          </el-table>
        </div>
      </el-col>
    </el-row>

    <div class="ql-card mt">
      <h3 class="ql-section-title">错误日志</h3>
      <el-table :data="errors" size="small" stripe empty-text="暂无错误">
        <el-table-column prop="time" label="时间" width="150" />
        <el-table-column prop="ip" label="IP" width="120" />
        <el-table-column prop="method" label="方法" width="70" />
        <el-table-column prop="uri" label="URI" min-width="140" show-overflow-tooltip />
        <el-table-column prop="status" label="状态" width="70" />
      </el-table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { siteStats } from '../api/panel'

const loading = ref(false)
const range = ref('today')
const overview = ref({})
const trend = ref([])
const ipRank = ref([])
const uriRank = ref([])
const errors = ref([])

function formatBytes(n) {
  n = Number(n) || 0
  if (n > 1024 * 1024 * 1024) return (n / 1024 / 1024 / 1024).toFixed(2) + ' GB'
  if (n > 1024 * 1024) return (n / 1024 / 1024).toFixed(1) + ' MB'
  if (n > 1024) return (n / 1024).toFixed(0) + ' KB'
  return n + ' B'
}

const cards = computed(() => {
  const o = overview.value || {}
  return [
    { key: 'pv', label: 'PV', value: o.pv ?? '—' },
    { key: 'uv', label: 'UV', value: o.uv ?? '—' },
    { key: 'bytes', label: '流量', value: formatBytes(o.total_bytes) },
    { key: 'err', label: '错误', value: o.error_count ?? '—' },
  ]
})

const trendBars = computed(() => {
  const arr = trend.value.map((x) => Number(x.pv ?? x.count ?? 0))
  const max = Math.max(...arr, 1)
  return trend.value.map((x, i) => ({
    label: String(x.time || x.hour || x.date || i + 1).slice(-5),
    h: Math.max(4, ((Number(x.pv ?? x.count ?? 0) / max) * 100)),
    tip: `PV ${x.pv ?? 0}`,
  }))
})

function pickData(res) {
  if (!res) return null
  // site_stats 直接透传插件结构
  if (res.raw?.status || res.raw?.data !== undefined) return res.raw
  if (res.data?.status || res.data?.data !== undefined) return res.data
  return res.data || res.raw
}

async function reloadAll() {
  loading.value = true
  const r = range.value
  const [ov, tr, ip, uri, er] = await Promise.all([
    siteStats('overview', r),
    siteStats('trend', r),
    siteStats('ip_rank', r, { page: 1, page_size: 10 }),
    siteStats('uri_rank', r, { page: 1, page_size: 10 }),
    siteStats('errors', r, { page: 1, page_size: 15 }),
  ])
  loading.value = false

  const ovd = pickData(ov)
  if (ovd) {
    overview.value = ovd.data || ovd
  }
  const trd = pickData(tr)
  if (trd) {
    const d = trd.data ?? trd
    trend.value = Array.isArray(d) ? d : []
  }
  const ipd = pickData(ip)
  if (ipd) {
    const d = ipd.data ?? ipd
    ipRank.value = Array.isArray(d) ? d : []
  }
  const urid = pickData(uri)
  if (urid) {
    const d = urid.data ?? urid
    uriRank.value = Array.isArray(d) ? d : []
  }
  const erd = pickData(er)
  if (erd) {
    const d = erd.data ?? erd
    errors.value = Array.isArray(d) ? d : []
  }
}

onMounted(reloadAll)
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
  gap: 10px;
  align-items: center;
}
.metric {
  margin-bottom: 16px;
  text-align: center;
}
.metric .val {
  margin-top: 8px;
  font-size: 22px;
  font-weight: 700;
  color: var(--ql-primary-dark);
}
.mt {
  margin-top: 4px;
  margin-bottom: 16px;
}
.bars {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  height: 160px;
}
.bar-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  height: 100%;
  gap: 6px;
}
.bar {
  width: 100%;
  max-width: 28px;
  border-radius: 8px 8px 4px 4px;
  background: linear-gradient(180deg, #38d9a9, #12b886);
  min-height: 4px;
}
.bar-col span {
  font-size: 10px;
  color: var(--ql-text-secondary);
}
</style>
