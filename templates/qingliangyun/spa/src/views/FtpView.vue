<template>
  <div class="ql-page">
    <div class="ql-card toolbar">
      <div>
        <h3 class="ql-section-title" style="margin:0">在线文件管理</h3>
        <p class="ql-muted">完整文件管理器功能较多，当前以独立页面打开以保证兼容</p>
      </div>
      <div class="actions">
        <el-button type="primary" round @click="openFull">打开完整文件管理</el-button>
        <el-button round @click="openInFrame">嵌入打开</el-button>
      </div>
    </div>
    <div v-if="showFrame" class="ql-card frame-card">
      <iframe class="frame" src="./ftp.php?_ql=1" title="文件管理" />
    </div>
    <div v-else class="ql-card tip">
      <el-result icon="info" title="文件管理" sub-title="支持浏览、上传、编辑、压缩等完整能力">
        <template #extra>
          <el-button type="primary" round @click="openFull">进入文件管理</el-button>
        </template>
      </el-result>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showFrame = ref(false)

function openFull() {
  window.open('./ftp.php', '_blank')
}

function openInFrame() {
  showFrame.value = true
}
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
.frame-card {
  padding: 0;
  overflow: hidden;
}
.frame {
  width: 100%;
  height: 75vh;
  border: 0;
  display: block;
}
</style>
