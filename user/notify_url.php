<?php
include("../MPHX/common.php");
require_once(SYSTEM_ROOT."lib/notify.class.php");

function mnbt_pay_log($content, $status = '记录', $orderNo = '') {
    global $DB;
    $suffix = $orderNo ? ' 订单'.$orderNo : '';
    return mnbt_log('支付回调', '支付回调', $content.$suffix, $status, $DB);
}

function mnbt_handle_paid_order($out_trade_no, $trade_status, $money) {
    global $DB, $conf, $date;
    if($trade_status != 'TRADE_SUCCESS') {
        mnbt_pay_log('支付状态非成功：'.$trade_status, '回调失败', $out_trade_no);
        return ['ok'=>false, 'msg'=>'trade_status='.$trade_status];
    }

    $ddxx = $DB->get_row_prepare("SELECT * FROM `MN_dd` WHERE `ddh` = ? limit 1", [$out_trade_no]);
    if(!$ddxx) {
        mnbt_pay_log('订单不存在', '回调异常', $out_trade_no);
        return ['ok'=>false, 'msg'=>'订单不存在'];
    }

    if((string)$ddxx['qk'] === 'true') {
        mnbt_pay_log('订单重复回调，已处理', '重复回调', $out_trade_no);
        return ['ok'=>true, 'msg'=>'该订单已被系统处理'];
    }

    if(isset($ddxx['je']) && (string)$ddxx['je'] !== '' && (string)$money !== '' && (float)$ddxx['je'] != (float)$money) {
        mnbt_pay_log('订单金额不一致，应付'.$ddxx['je'].'实付'.$money, '回调异常', $out_trade_no);
        return ['ok'=>false, 'msg'=>'订单金额不一致'];
    }

    $ddxx_cs=json_decode($ddxx['cs'],true);
    if(!is_array($ddxx_cs)) {
        mnbt_pay_log('订单参数解析失败', '回调异常', $out_trade_no);
        return ['ok'=>false, 'msg'=>'订单参数解析失败'];
    }

    if($ddxx['lx']=='yjbs'){
        $ddxx_xid=$ddxx_cs['gmid'] ?? 0;
        $bscx = $DB->get_row_prepare("SELECT * FROM `MN_bs` WHERE `id` = ? limit 1", [$ddxx_xid]);
        if(!$bscx) {
            mnbt_pay_log('一键部署程序不存在ID'.$ddxx_xid, '处理失败', $out_trade_no);
            return ['ok'=>false, 'msg'=>'程序不存在'];
        }
        $bs_tj=json_decode($bscx['tj'],true);
        if(!is_array($bs_tj)) $bs_tj=[];
        if(!in_array($ddxx_cs['user'], $bs_tj)) array_push($bs_tj,$ddxx_cs['user']);
        $tj_jg=json_encode($bs_tj,256);
        if(!$DB->query_prepare("update `MN_bs` set `tj` =? where `id`=?", [$tj_jg, $ddxx_xid])) {
            mnbt_pay_log('一键部署购买写入失败 用户'.($ddxx_cs['user'] ?? ''), '处理失败', $out_trade_no);
            return ['ok'=>false, 'msg'=>'程序购买写入失败'];
        }
        mnbt_pay_log('一键部署购买处理成功 用户'.($ddxx_cs['user'] ?? ''), '处理成功', $out_trade_no);
    }else{
        $ddxx_url=$ddxx_cs['url_zd'] ?? '';
        $user=$ddxx_cs['user'] ?? '';
        $yhc=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
        if(!$yhc) {
            mnbt_pay_log('域名购买主机不存在 用户'.$user, '处理失败', $out_trade_no);
            return ['ok'=>false, 'msg'=>'主机不存在'];
        }
        $zjid=$yhc['btid'];
        $ssbt=$yhc['ssbt'];
        $bscx = $DB->get_row_prepare("SELECT * FROM `MN_ym` WHERE `url` = ? limit 1", [$ddxx_url]);
        if(!$bscx) {
            mnbt_pay_log('域名商品不存在 '.$ddxx_url, '处理失败', $out_trade_no);
            return ['ok'=>false, 'msg'=>'域名商品不存在'];
        }
        include_once("./class.php");
        $cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
        if(!$cert) {
            mnbt_pay_log('宝塔节点不存在 '.$ssbt, '处理失败', $out_trade_no);
            return ['ok'=>false, 'msg'=>'宝塔节点不存在'];
        }
        $btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
        $btkeye=$cert['btmy'];
        $os_xt=$cert['btos']=='1' ? $conf['hxi'].'/' : $conf['hxo'].'/';
        $ul_url_ym=$ddxx_cs['url_zy'] ?? '';
        $path=$ddxx_cs['path'] ?? '/';
        $apie = new bt_api_set($btipe,$btkeye);
        $api = new bt_api($btipe,$btkeye);
        if(strpos($path,'/') !==false){
            $r_data = $apie->btapi_addym($zjid,$yhc['sqldz'],$ul_url_ym);
        }else{
            $r_data = $api->addzml($zjid,$ul_url_ym,$path,$os_xt.$yhc['sqldz']);
        }
        $are=$r_data['status'] ?? false;
        if($are!='true' && $are!==true){
            $yr_c=true;
            for($yr_a=1;$yr_c && $yr_a<=20;$yr_a++){
                $hskr=mt_rand(4,10);
                $rqsj=md5($date.$user.$yr_a.mt_rand(100,999));
                $wjler=substr($rqsj, $hskr , 5);
                $ul_url_ym=$wjler.'.'.$ddxx_cs['url_zd'];
                if(strpos($path,'/') !==false){
                    $r_data = $apie->btapi_addym($zjid,$yhc['sqldz'],$ul_url_ym);
                }else{
                    $r_data = $api->addzml($zjid,$ul_url_ym,$path,$os_xt.$yhc['sqldz']);
                }
                $yr_c=($r_data['status'] ?? false)=='true' || ($r_data['status'] ?? false)===true ? false : true;
            }
            if($yr_c) {
                mnbt_pay_log('域名绑定失败 '.$ul_url_ym, '处理失败', $out_trade_no);
                return ['ok'=>false, 'msg'=>'域名绑定失败'];
            }
        }

        if(($ddxx_cs['type'] ?? '')=='1'){
            $hhf="\n";
            $apic = new bt_api($btipe,$btkeye);
            $get_host_hq = $apic->GetLogswt($ddxx_cs['hostly']);
            $host_wjnr=($get_host_hq['data'] ?? '').$hhf.$ddxx_cs['yz_ip'].' '.$ul_url_ym;
            $apic->GetLogswh($host_wjnr,$ddxx_cs['hostly']);
            $apic->fxdl_add($ul_url_ym,$yhc['sqldz']);
        }
        $bs_tj=json_decode($bscx['json'],true);
        if(!is_array($bs_tj)) $bs_tj=[];
        if(!in_array($user, $bs_tj)) array_push($bs_tj,$user);
        $tj_jg=json_encode($bs_tj,256);
        if(!$DB->query_prepare("update `MN_ym` set `json` =? where `url`=?", [$tj_jg, $ddxx_url])) {
            mnbt_pay_log('域名购买记录写入失败 用户'.$user, '处理失败', $out_trade_no);
            return ['ok'=>false, 'msg'=>'域名购买记录写入失败'];
        }
        mnbt_pay_log('域名购买处理成功 用户'.$user.' 域名'.$ul_url_ym, '处理成功', $out_trade_no);
    }

    if(!$DB->query_prepare("update `MN_dd` set `qk` =? where `ddh`=?", ['true', $out_trade_no])) {
        mnbt_pay_log('订单状态更新失败', '处理失败', $out_trade_no);
        return ['ok'=>false, 'msg'=>'订单状态更新失败'];
    }
    mnbt_pay_log('订单处理完成 类型'.$ddxx['lx'].' 金额'.$money, '处理成功', $out_trade_no);
    $order_row = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE ddh=? limit 1", [$out_trade_no]);
    if (function_exists('mnbt_do_action')) {
        mnbt_do_action('order.paid', $order_row ?: $ddxx, ['money'=>$money,'source'=>'notify_url']);
    }
    return ['ok'=>true, 'msg'=>'支付成功'];
}

$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {
    $out_trade_no = $_GET['out_trade_no'] ?? '';
    $trade_status = $_GET['trade_status'] ?? '';
    $money = $_GET['money'] ?? '';
    $result = mnbt_handle_paid_order($out_trade_no, $trade_status, $money);
    if(!$result['ok']) echo $result['msg'];
    Header("Location:./");
}else{
    mnbt_pay_log('支付验签失败', '验签失败', $_GET['out_trade_no'] ?? '');
    echo '<!DOCTYPE html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link href="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet"><script src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.js"></script><script src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert-dev.min.js"></script><title>支付失败！</title></head><body></body><script type="text/javascript">swal({title: "支付失败",text: "支付失败", type: "error"},function(){ window.location.href="./";});</script></body></html>';
    Header("Location:./");
}
?>
