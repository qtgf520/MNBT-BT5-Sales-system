<?php
/**
 * 用户端 - 域名绑定（迁自 templates/default/user/set.php 的 $set=='url' 分支）
 * AJAX gn：urllist / erurl / p_domain_tjurl / p_domain_seturl / p_domain_scurl
 * （由本插件 user/api/bind.php 注册）
 *
 * 注意：tjurl/scurl/seturl 改名为 p_domain_* 前缀，避免与 CDN 产品的
 * user/api/cdn.php 同名 gn 冲突（插件分发优先于 CDN 检查）。
 *
 * 购买二级域名的支付部分原 POST 到 user/pay.php?pay_lx=ymgm，
 * 现改为 POST 到插件路由 /domain/buy（mnbt_register_route）
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_theme_include('head');
global $yhc, $ssbt, $DB;
$cert = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<script type="text/javascript" src="<?= mnbt_asset_url('js/md5.js') ?>"></script>
<script type="text/javascript" src="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js') ?>"></script>
<script type="text/javascript" src="<?= mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js') ?>"></script>
<script type="text/javascript" src="<?= mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js') ?>"></script>

<div class="container" style="padding-top:5%;">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <h4>域名修改</br>
          <span class="h6"><?php if ($cert['als'] == 'false') {echo '请将域名A记录到 ' . $cert['btip'];} else {echo $cert['als'];} ?></span>
        </h4>
        <ul class="card-actions"></ul>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr><th>域名</th><th>端口</th><th>目录</th><th>操作</th></tr>
          </thead>
          <tbody id="urllist">
            <tr>
              <td><div class="row col-xs-7" style="display:none" id="ydk"><input type="text" class="form-control input-sm" id="ynk"/></div>
                <a href="#!" class="text-success" id="ymk">正在获取域名中，请稍后...</a></td>
              <td>80</td>
              <td></td>
              <td>
                <div class="btn-group">
                  <button type="button" class="btn btn-xs btn-default use-url" title="修改前缀" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></button>
                  <button type="button" class="btn btn-xs btn-default del-url" title="删除记录" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="custom-control custom-radio custom-control-inline">
          <input type="radio" id="urladdms" name="urladdms" class="custom-control-input" checked>
          <label class="custom-control-label" for="urladdms">自定义添加</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
          <input type="radio" id="urladdms2" name="urladdms" class="custom-control-input">
          <label class="custom-control-label" for="urladdms2">本站二级域名</label>
        </div>

        <div class="input-group mb-3">
          <input type="text" class="form-control" id="url" placeholder="请在此输入域名">
          <div class="input-group-append">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="ymqhq" style="display:none">请选择域名<span class="caret"></span></button>
            <ul class="dropdown-menu">
              <li><a href="#!" class="dropdown-item">a.a<br/>价格：元<br/>简介：</a></li>
            </ul>
            <button type="button" class="btn btn-default btn-primary" id="tjurl">添加</button>
          </div>
        </div>

        <div class="form-group">
          <select class="custom-select form-control-sm">
            <option>/</option>
          </select>
          <small><b>域名子目录，如果无特殊需求则推荐默认</b><br/>会自动显示主机文件中的目录<br/>如果设置了运行目录则会显示运行目录中的目录</small>
        </div>
      </div>
    </div>
  </div>

  <!-- 购买二级域名弹窗 -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">购置二级域名</h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <h5 id="ts"></h5><br/>
          <h5 id="hf"></h5><br/>
          <h5>支付完成后将自动添加该域名！</h5><br/>
          <h5>如果该前缀已经被其他主机绑定那我们将会为您随机化一个前缀</h5><br/>
          <h5><b>您可以随时修改您域名的前缀！</b></h5><br/>
          <h4 align="center">是否确认支付？</h4>
          <form action="index.php?_r=/domain/buy" method="post" target="_blank" role="form">
            <input type="hidden" name="urla" id="urla"/>
            <input type="hidden" name="urlb" id="urlb"/>
            <input type="hidden" name="urlzml" id="urlzml" value="/"/>
            <input type="hidden" name="pay_lx" value="ymgm"/>
            <label for="web_site_logo">请选择支付方式</label>
            <div class="example-box">
              <div class="row">
                <?php $__pay_methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : []; ?>
                <?php foreach ($__pay_methods as $__idx => $__m): ?>
                  <?php $__type = $__m['plugin'] . '__' . $__m['method']; ?>
                  <label class="lyear-radio radio-inline radio-primary col">
                    <input type="radio" name="type" value="<?= htmlspecialchars($__type) ?>" <?= $__idx === 0 ? 'checked' : '' ?>>
                    <i class="mdi <?= htmlspecialchars($__m['icon'] ?? 'mdi-payment') ?>"></i>
                    <span><?= htmlspecialchars($__m['display_name']) ?></span>
                  </label>
                <?php endforeach; ?>
                <?php if (empty($__pay_methods)): ?>
                  <label class="col text-muted">暂无可用支付方式，请联系管理员</label>
                <?php endif; ?>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
          <button type="submit" class="btn btn-primary" id="zfgn">确认支付</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
var bl_jg = 0;
urllist();
smurl();

$("#urllist").on("click", ".use-url", function () {
  var setlis = $(this.parentNode.parentNode.parentNode).children();
  var url = setlis[0].innerText, port = setlis[1].innerText, paths = setlis[2].innerText;
  var wz = url.indexOf('.');
  var qz = url.substring(0, wz);
  var qzh = url.substring(wz);
  var it_url = (qzh.substr(0, 1) == '.') ? qzh.slice(1) : qzh;
  var dirlist = '';
  let data = { gn: "urllist" };
  $.post('./ajax.php', data, function (date) {
    var arr = JSON.parse(date);
    $.each(arr.dir, function () {
      dirlist += (this == paths) ? '<option selected>' + this + '</option>' : '<option>' + this + '</option>';
    });
    $.confirm({
      title: '修改域名',
      content: '<div class="form-group p-1 mb-0">' +
        '  <label class="control-label">域名前缀</label>' +
        '  <input autofocus="" type="text" id="input-name" value=' + qz + ' placeholder="请输入您域名的新前缀" class="form-control">' +
        '<select class="custom-select form-control-sm" id="input-sel">' + dirlist + '</select>' +
        '</div>',
      buttons: {
        sayMyName: {
          text: '确定修改', btnClass: 'btn-primary',
          action: function () {
            var input = this.$content.find('input#input-name');
            var sel = this.$content.find('select#input-sel');
            if (!$.trim(input.val())) {
              $.alert({ content: "前缀不能为空！", type: 'red' });
              return false;
            }
            msloading('正在处理中，请稍后...');
            $.post('./ajax.php', {
              gn: "p_domain_seturl", zym: it_url, port: port,
              jqz: qz, xqz: input.val(), path: sel.val()
            }, function (date) {
              var qk = JSON.parse(date).code;
              if (qk == '添加成功') { msalert(1, '修改成功！', 2000); urllist(); }
              else msalert(4, qk, 2000);
              msloadingde();
            });
          }
        },
        '取消': function () {}
      }
    });
  });
});

function urllist() {
  msloading('正在加载中，请稍后...');
  $.post('./ajax.php', { gn: "urllist" }, function (date) {
    var arr = JSON.parse(date);
    var urllist = '', dirlist = '';
    $.each(arr.url, function () {
      urllist += '<tr><td><a target="_blank" href="http://' + this.name + ':' + this.port + '/" class="text-success">' + this.name + '</a></td>' +
        '<td>' + this.port + '</td><td>' + this.path + '</td>' +
        '<td><div class="btn-group">' +
        '<button type="button" class="btn btn-xs btn-default use-url" title="修改前缀" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></button>' +
        '<button type="button" class="btn btn-xs btn-default del-url" title="删除记录" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></button>' +
        '</div></td></tr>';
    });
    $.each(arr.dir, function () { dirlist += '<option>' + this + '</option>'; });
    $("#urllist").html(urllist);
    $(".custom-select").html(dirlist);
    $(function () { $('[data-toggle="tooltip"]').tooltip(); });
    msloadingde();
  });
}

function smurl() {
  msloading('正在加载中，请稍后...', '#ymqhq');
  $.post('./ajax.php', { gn: "erurl" }, function (date) {
    var arr = JSON.parse(date);
    var urllist = '';
    $.each(arr, function () {
      urllist += '<li><a href="#!" class="dropdown-item" onclick="$(\'#ymqhq\').html(\'' + this.url + '\');bl_jg=' + this.jg + ';">' + this.url + '<br/>价格：' + this.jg + '元<br/>简介：' + this.jj + '</a></li>';
    });
    $(".dropdown-menu").html(urllist);
    msloadingde('#ymqhq');
  });
}

$("#tjurl").on("click", function () {
  var ms = $("#urladdms").prop("checked");
  if (!ms) {
    var yer = $("#ymqhq").html();
    var ym = $("#url").val() + '.' + yer;
    if (yer.indexOf('请选择域名') != '-1') { msalert(3, '请选择域名和输入前缀！', 2000); return; }
    if (bl_jg > 0) {
      var p = /^[0-9a-zA-Z]{1,24}$/;
      if (!p.test($("#url").val())) { msalert(3, '只能输入数字和英文！', 2000); return; }
      $('#exampleModal').modal();
      zfs();
      return;
    }
  } else {
    var ym = $("#url").val();
  }
  if (ym == "") { msalert(3, '请填写域名！', 2000); return; }
  var urldir = $(".custom-select").val();
  msloading('正在处理中，请稍后...');
  $.post('./ajax.php', { gn: "p_domain_tjurl", url: ym, dirs: urldir }, function (date) {
    var qk = JSON.parse(date).code;
    if (qk == '添加成功') { msalert(1, '添加成功！', 2000); urllist(); }
    else msalert(4, qk, 2000);
    msloadingde();
  });
});

$("#urllist").on("click", ".del-url", function () {
  var setlis = $(this.parentNode.parentNode.parentNode).children();
  var url = setlis[0].innerText, port = setlis[1].innerText, dir = setlis[2].innerText;
  msloading('正在删除中，请稍后...');
  $.post('./ajax.php', { gn: "p_domain_scurl", url: url, port: port, dir: dir }, function (date) {
    var qk = JSON.parse(date).code;
    if (qk == '删除成功') msalert(1, '删除成功！', 2000);
    else msalert(4, qk, 2000);
    urllist();
    msloadingde();
  });
});

$(".custom-control-input").on("change", function () {
  if (this.id == 'urladdms') {
    document.getElementById("ymqhq").style.display = "none";
    document.getElementById("url").placeholder = '请在此输入域名';
  } else {
    document.getElementById("ymqhq").style.display = "block";
    document.getElementById("url").placeholder = '前缀';
  }
});

function zfs() {
  var yer = $("#ymqhq").html();
  var ym = $("#url").val() + '.' + yer;
  var p = /^[0-9a-zA-Z]{1,24}$/;
  if (!p.test($("#url").val())) {
    msalert(3, '只能输入数字和英文！', 2000);
    setTimeout(function () { $('#exampleModal').modal('hide'); }, 500);
    document.getElementById("zfgn").type = 'button';
    return;
  }
  msloading('正在处理中，请稍后...');
  if (yer.indexOf('请选择域名') != '-1' || $("#url").val() == false) {
    msalert(3, '请选择域名和输入前缀！', 2000);
    setTimeout(function () { $('#exampleModal').modal('hide'); }, 500);
    document.getElementById("zfgn").type = 'button';
    msloadingde();
    return;
  }
  document.getElementById("zfgn").type = 'submit';
  document.getElementById("ts").innerHTML = '您正在添加域名：<b>' + ym + '</b>';
  document.getElementById("hf").innerHTML = '购置此二级域名将会花费<b>' + bl_jg + '</b>元';
  document.getElementById("urla").value = yer;
  document.getElementById("urlb").value = $("#url").val();
  document.getElementById("urlzml").value = $(".custom-select").val();
  msloadingde();
}
</script>
