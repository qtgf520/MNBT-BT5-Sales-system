<?php
// urllist/erurl/tjurl/scurl/seturl 已迁移至 domain_shop 插件（user/api/bind.php）
// 由 mnbt_plugin_dispatch_ajax 优先分发，本文件仅保留 hqzmlls 与 listurl
if($egn=='hqzmlls') {
	//获取当前运行目录(非/)下的子目录列表
	$arr=[];
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$yxml = ($api->yxmlrhq($zjid,$os_xt.$yhc['sqldz']) ?: [])['runPath']['runPath'] ?? '/';
	//获取运行目录
	if($yxml!='/') {
		$listz = $api->urlzmlls($zjid) ?: [];
		//子目录域名
		foreach (($listz['binding'] ?? []) as $val) {
			//子目录
			if(substr($val['path'],0,3)!='../') {
				$arr[]=$val['domain'];
			}
		}
	}
	if(empty($arr)) {
		exit('false');
	} else {
		exit(json_encode($arr,256));
	}
	return;
}
if($egn=='listurl') {
	//获取域名列表(包含子目录)
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$data=$api->get_site_domains($yhc['btid']);
	$arr=[];
	foreach (($data['domains'] ?? []) as $val) {
		if($val['name']!=$yhc['sqldz']) {
			$arr['domains'][]=["name"=>$val['name']];
		}
	}
	exit(json_encode($arr,256));
	return;
}
