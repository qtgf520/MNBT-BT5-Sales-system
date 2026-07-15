import { apiGn } from './http'

export const monitorList = () => apiGn('monitor_list')
export const monitorSave = (data) => apiGn('monitor_save', data)
export const monitorDel = (id) => apiGn('monitor_del', { id })
export const monitorToggle = (id, enabled) => apiGn('monitor_toggle', { id, enabled })
export const monitorLogList = (params) => apiGn('monitor_log_list', params)

export const noticeList = (params) => apiGn('notice_list', params)
export const noticeRead = (id = 0) => apiGn('notice_read', { id })

export const backupList = () => apiGn('backup_list')
export const backupAdd = (id) => apiGn('databaseadd', { id })
export const backupDel = (id) => apiGn('databasedel', { id })
export const backupRestore = (user, filename) => apiGn('databaserestore', { user, filename })
export const backupDownload = (filename) => apiGn('databasedownload', { filename })
export const backupWipe = () => apiGn('Delalldatabase')

export const deployList = () => apiGn('deploy_list')
export const deployForm = (id) => apiGn('yjbsform', { id })
export const deployRun = (data) => apiGn('yjbs', data)

export const setInit = (section) => apiGn('set_init', { section, gn_section: section })
export const passList = () => apiGn('pass_list')

export const siteStats = (act, range = 'today', extra = {}) =>
  apiGn('site_stats', { act, range, ...extra }, { silent: true })

export const domainList = () => apiGn('urllist')
export const domainAdd = (data) => apiGn('tjurl', data)
export const domainDel = (data) => apiGn('scurl', data)
export const domainEdit = (data) => apiGn('seturl', data)
export const sellDomains = () => apiGn('erurl')

export const phpChange = (php) => apiGn('phpxg', { php })
export const setDefaultDoc = (ml) => apiGn('xgmrwd', { ml })
export const setRunPath = (wb) => apiGn('setyxml', { wb })
export const loadRewriteTpl = (xz) => apiGn('hqjt', { xz })
export const saveRewrite = (wb) => apiGn('setwjt', { wb })

export const getSsl = () => apiGn('getssl')
export const setSsl = (key, pem) => apiGn('setssl', { key, pem })
export const closeSsl = () => apiGn('clossl')
export const forceHttps = (qk) => apiGn('httpsqz', { qk })
export const applySsl = (list, type = true) => {
  const data = { type }
  const arr = Array.isArray(list) ? list : String(list).split(',').filter(Boolean)
  arr.forEach((d, i) => {
    data[`list[${i}]`] = d
  })
  return apiGn('sqssl', data)
}
export const listUrl = () => apiGn('listurl')

export const getFdl = () => apiGn('getfdl')
export const setFdl = (data) => apiGn('fdlkg', data)
export const setGzip = (data) => apiGn('setgzip', data)
export const cacheAdd = (data) => apiGn('cacheadd', data)
export const cacheEdit = (data) => apiGn('cacheedit', data)
export const cacheDel = (suffix) => apiGn('cachedel', { suffix, ext: suffix })
export const changePass = (ftp, sql) => apiGn('xgpass', { ftp, sql })
export const setDbAccess = (dataAccess) => apiGn('databaseaq1', { dataAccess })
export const addPassDir = (data) => apiGn('tjmmfw', data)
export const delPassDir = (mb) => apiGn('scmmfw', { mb })
