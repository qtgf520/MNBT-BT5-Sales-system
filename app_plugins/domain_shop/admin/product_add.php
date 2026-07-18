<?php
/**
 * 后台 - 添加域名商品
 * 迁移自 templates/default/admin/add.php 的 $set=='ym' 分支
 * AJAX gn：p_domain_addym
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
global $DB;
$bt_list = $DB->get_all_prepare("SELECT * FROM MN_bt order by id desc limit 100") ?: [];
?>
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">添加售卖域名</div></header>
        <div class="card-body">
          <div class="form-group">
            <label>域名</label>
            <input type="text" id="ym" class="form-control" placeholder="请填写要出售二级的域名" required/>
            <small>不能带 http:// 和 /</small>
          </div><br/>

          <div class="form-group">
            <label>将此域名绑定到</label>
            <select class="form-control" id="btdh">
              <option value="-00-">点我选择宝塔</option>
              <?php foreach ($bt_list as $res): ?>
                <option value="<?= htmlspecialchars($res['btdh']) ?>"><?= htmlspecialchars($res['btdh']) ?></option>
              <?php endforeach; ?>
            </select>
            <small>请将域名 A 记录到该宝塔的 IP，主机记录为 *</small>
          </div><br/>

          <div class="form-group">
            <label>解析一次的价格</label>
            <input type="number" id="jg" class="form-control" placeholder="请填写对该域名解析一次的价格" required/>
            <small>填写 0 即为免费</small>
          </div><br/>

          <div class="form-group">
            <label>域名介绍</label>
            <input type="text" id="js" class="form-control" placeholder="如：该域名是国内备案域名" required/>
          </div><br/>

          <div class="form-group">
            <label class="btn-block">是否上架</label>
            <div class="col-xs-6">
              <div class="custom-control custom-switch custom-info">
                <input type="checkbox" class="custom-control-input" id="ymsxj" checked>
                <label class="custom-control-label" for="ymsxj"></label>
              </div>
            </div>
          </div>

          <button class="btn btn-primary form-control" type="button" onclick="tjym()">
            <i class="mdi mdi-checkbox-marked-circle-outline"></i> 确认添加
          </button>

          <div class="panel-footer" style="margin-top:15px;">
            <span class="glyphicon glyphicon-info-sign"></span>
            注意：域名解析到服务器 IP 时要 A 记录！主机号为 *<br/>
            只有选择的宝塔才会显示该域名的二级售卖
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function tjym() {
  var url = ym.value, bt = btdh.value, je = jg.value, ymjs = js.value, kg = ymsxj.checked;
  if (url == "" || bt == "-00-" || je == "" || ymjs == "") {
    msalert(3, '表单不能为空！', 2000); return;
  }
  msloading('正在加载中');
  $.post('ajax.php', {
    gn: 'p_domain_addym', url: url, bt: bt, jg: je, ymjs: ymjs, kg: kg
  }, function (date) {
    var jsoe = JSON.parse(date);
    var qk = jsoe.code;
    msloadingde();
    if (qk == '添加成功') {
      msalert(1, '添加成功！', 2000);
      window.location.href = "plugin.php?p=domain_shop&page=products";
    } else {
      msalert(4, qk, 2000);
    }
  });
}
</script>
