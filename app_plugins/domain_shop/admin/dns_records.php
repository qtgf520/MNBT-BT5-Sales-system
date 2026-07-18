<?php
/**
 * 后台 - DNS 记录全局查看
 * AJAX gn：p_dns_record_delete（管理员可删任意记录）
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
$records = dns_record_list_all();
$providers = $DB->get_all_prepare("SELECT * FROM plg_dns_provider order by id asc") ?: [];
$providerMap = [];
foreach ($providers as $p) $providerMap[$p['id']] = $p['name'] . ' (' . $p['slug'] . ')';
?>
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header">
          <div class="card-title">DNS 解析记录（全局）<span class="badge badge-info ml-2"><?= count($records) ?></span></div>
        </header>
        <div class="card-body">
          <?php if (empty($records)): ?>
            <p class="text-muted">暂无 DNS 记录</p>
          <?php else: ?>
            <table class="table table-bordered table-hover table-striped">
              <thead>
                <tr>
                  <th>ID</th><th>用户</th><th>服务商</th>
                  <th>主域名</th><th>主机记录</th><th>类型</th>
                  <th>记录值</th><th>TTL</th>
                  <th>来源</th><th>创建时间</th><th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($records as $r): ?>
                  <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= htmlspecialchars($r['user']) ?></td>
                    <td><?= htmlspecialchars($providerMap[$r['provider_id']] ?? ('#' . $r['provider_id'])) ?></td>
                    <td><?= htmlspecialchars($r['domain']) ?></td>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($r['type']) ?></span></td>
                    <td><?= htmlspecialchars($r['value']) ?></td>
                    <td><?= (int)$r['ttl'] ?></td>
                    <td>
                      <?php if ($r['auto']): ?>
                        <span class="badge badge-secondary">自动</span>
                      <?php else: ?>
                        <span class="badge badge-primary">手动</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                    <td>
                      <button class="btn btn-xs btn-default" title="删除" onclick="delRecord(<?= (int)$r['id'] ?>)">
                        <i class="mdi mdi-window-close"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function delRecord(id) {
  if (!confirm('删除后本地与远程记录都会被清除\n是否继续？')) return;
  msloading('删除中...');
  $.post('ajax.php', { gn: 'p_dns_record_delete', id: id }, function (date) {
    msloadingde();
    var r = JSON.parse(date);
    if (r.qk == 1) {
      msalert(1, r.msg || '删除成功', 2000);
      setTimeout(function () { location.reload(); }, 800);
    } else {
      msalert(4, r.msg || '删除失败', 3000);
    }
  });
}
</script>
