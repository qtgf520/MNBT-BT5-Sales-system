import { createRouter, createWebHashHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import LayoutView from '../views/LayoutView.vue'
import DashboardView from '../views/DashboardView.vue'
import StatsView from '../views/StatsView.vue'
import MonitorView from '../views/MonitorView.vue'
import MonitorLogView from '../views/MonitorLogView.vue'
import NoticeView from '../views/NoticeView.vue'
import SettingsView from '../views/SettingsView.vue'
import BackupView from '../views/BackupView.vue'
import DeployView from '../views/DeployView.vue'
import FtpView from '../views/FtpView.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true, title: '登录' },
  },
  {
    path: '/',
    component: LayoutView,
    meta: { auth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'dashboard', component: DashboardView, meta: { title: '控制面板' } },
      { path: 'stats', name: 'stats', component: StatsView, meta: { title: '站点统计' } },
      { path: 'monitor', name: 'monitor', component: MonitorView, meta: { title: '监控任务' } },
      { path: 'monitor-log', name: 'monitor-log', component: MonitorLogView, meta: { title: '监控日志' } },
      { path: 'notice', name: 'notice', component: NoticeView, meta: { title: '通知日志' } },
      { path: 'settings/:gn?', name: 'settings', component: SettingsView, meta: { title: '站点设置' } },
      { path: 'backup', name: 'backup', component: BackupView, meta: { title: '数据备份' } },
      { path: 'deploy', name: 'deploy', component: DeployView, meta: { title: '一键部署' } },
      { path: 'ftp', name: 'ftp', component: FtpView, meta: { title: '文件管理' } },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const boot = window.__QL_BOOT__ || {}
  const loggedIn = !!boot.loggedIn
  document.title = `${to.meta.title || '控制面板'} · ${boot.siteName || '清凉云'}`

  if (to.meta.guest && loggedIn) {
    next('/dashboard')
    return
  }
  if (to.meta.auth && !loggedIn && to.path !== '/login') {
    if (to.name === 'login') {
      next()
      return
    }
    next('/login')
    return
  }
  next()
})

export default router
