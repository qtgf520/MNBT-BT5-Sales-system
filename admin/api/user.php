<?php
if($egn=='list_user') {
    $sorting=strtoupper($_POST['sortOrder']??'')==='DESC'?'DESC':'ASC';
    $paixu=preg_replace('/[^a-zA-Z0-9_]/','',$_POST['sort']??'id')?:'id';
    $pagesize=intval($_POST['limit']?:10);
    $pageu=(intval($_POST['page']??1)-1)*$pagesize;
    $where=$_POST['where']??'';
    $pswhere='';
    $param_arr=[];
    if(!empty($where)){
        $pswhere='AND username LIKE ?';
        $param_arr[]='%'.$where.'%';
    }
    $countdata=$DB->count_prepare("SELECT count(*) from MN_user WHERE 1 $pswhere", $param_arr);
    $data=["total"=>$countdata];
    $data["rows"]=$DB->get_all_prepare("SELECT * FROM MN_user WHERE 1 $pswhere ORDER BY $paixu $sorting LIMIT $pageu,$pagesize", $param_arr);
    exit(json_encode($data));
    return;
}
if($egn=='user_detail') {
    $uid=intval($_POST['uid']);
    $user=$DB->get_row_prepare("SELECT * FROM MN_user WHERE id=?", [$uid]);
    if(!$user) json_exit('用户不存在');
    $user['group_name']=$DB->get_row_prepare("SELECT name FROM MN_user_group WHERE id=?", [$user['group_id']])['name']??'未知';
    exit(json_encode(['code'=>0,'data'=>$user]));
    return;
}
if($egn=='user_group_list') {
    $groups=$DB->get_all_prepare("SELECT * FROM MN_user_group ORDER BY id ASC", []);
    exit(json_encode(['code'=>0,'data'=>$groups?:[]]));
    return;
}
if($egn=='user_add') {
    $username=daddslashes(trim($_POST['username']??''));
    $password=$_POST['password']??'';
    $email=daddslashes(trim($_POST['email']??''));
    $group_id=intval($_POST['group_id']??1);
    if(strlen($username)<3) json_exit('用户名至少3位');
    if(strlen($password)<6) json_exit('密码至少6位');
    $exist=$DB->get_row_prepare("SELECT id FROM MN_user WHERE username=?",[$username]);
    if($exist) json_exit('用户名已存在');
    $salt=substr(md5(uniqid(mt_rand(),true)),0,8);
    $pwd_enc=md5(md5($password).$salt);
    $ret=$DB->query_prepare("INSERT INTO MN_user (username,password,salt,email,group_id,status,reg_date,reg_ip) VALUES(?,?,?,?,?,'true',?,?)",[$username,$pwd_enc,$salt,$email,$group_id,$date,$_SERVER['REMOTE_ADDR']??'']);
    if($ret){json_exit('添加成功');}else{json_exit('添加失败');}
    return;
}
if($egn=='user_delete') {
    $uid=intval($_POST['uid']);
    $user=$DB->get_row_prepare("SELECT * FROM MN_user WHERE id=?", [$uid]);
    if(!$user) json_exit('用户不存在');
    $DB->query_prepare("DELETE FROM MN_user WHERE id=?",[$uid]);
    $DB->query_prepare("DELETE FROM MN_money_log WHERE user_id=?",[$uid]);
    json_exit('删除成功');
    return;
}
if($egn=='user_update') {
    $uid=intval($_POST['uid']);
    $money=daddslashes($_POST['money']??'');
    $score=daddslashes($_POST['score']??'');
    $group_id=intval($_POST['group_id']??0);
    $status=daddslashes($_POST['status']??'true');
    $password=daddslashes($_POST['password']??'');
    $user=$DB->get_row_prepare("SELECT * FROM MN_user WHERE id=?", [$uid]);
    if(!$user) json_exit('用户不存在');
    $updates=[];
    $params=[];
    if($money!==''){
        $diff=$money-$user['money'];
        $updates[]="money=?";
        $params[]=$money;
        if($diff!=0){
            $DB->query_prepare("INSERT INTO MN_money_log (user_id,money,`before`,`after`,memo,`date`) VALUES (?,?,?,?,'管理员调整',?)",[$uid,$diff,$user['money'],$money,$date]);
        }
    }
    if($score!==''){
        $updates[]="score=?";
        $params[]=$score;
    }
    if($group_id>0){
        $updates[]="group_id=?";
        $params[]=$group_id;
    }
    $updates[]="status=?";
    $params[]=$status;
    if(!empty($password)){
        $salt=substr(md5(uniqid(mt_rand(),true)),0,8);
        $updates[]="password=?";
        $params[]=md5(md5($password).$salt);
        $updates[]="salt=?";
        $params[]=$salt;
    }
    if(empty($updates)) json_exit('没有要修改的字段');
    $params[]=$uid;
    $sql="UPDATE MN_user SET ".implode(',',$updates)." WHERE id=?";
    if($DB->query_prepare($sql,$params)){
        mnbt_log($user,'用户管理','修改用户ID'.$uid.'信息','修改成功',$DB);
        json_exit('修改成功');
    }else{
        json_exit('修改失败');
    }
    return;
}
if($egn=='user_money_log') {
    $uid=intval($_POST['uid']);
    $logs=$DB->get_all_prepare("SELECT * FROM MN_money_log WHERE user_id=? ORDER BY id DESC LIMIT 50", [$uid]);
    exit(json_encode(['code'=>0,'data'=>$logs?:[]]));
    return;
}
// 用户-主机关联
if($egn=='user_host_list') {
    $uid=intval($_POST['uid']);
    $hosts=$DB->get_all_prepare("SELECT z.*, uh.id as rid FROM MN_zj z INNER JOIN MN_user_host uh ON z.id=uh.host_id WHERE uh.user_id=? ORDER BY z.id ASC",[$uid]);
    $all_hosts=$DB->get_all_prepare("SELECT id,ssbt,sqldz FROM MN_zj ORDER BY id ASC",[]);
    exit(json_encode(['code'=>0,'hosts'=>$hosts?:[],'all_hosts'=>$all_hosts?:[]]));
    return;
}
if($egn=='user_assign_host') {
    $uid=intval($_POST['uid']);
    $host_id=intval($_POST['host_id']);
    $exist=$DB->get_row_prepare("SELECT id FROM MN_user_host WHERE user_id=? AND host_id=?",[$uid,$host_id]);
    if($exist) json_exit('该主机已分配给此用户');
    $DB->query_prepare("INSERT INTO MN_user_host (user_id,host_id,created_at) VALUES(?,?,?)",[$uid,$host_id,$date]);
    json_exit('分配成功');
    return;
}
if($egn=='user_remove_host') {
    $uid=intval($_POST['uid']);
    $host_id=intval($_POST['host_id']);
    $DB->query_prepare("DELETE FROM MN_user_host WHERE user_id=? AND host_id=?",[$uid,$host_id]);
    json_exit('移除成功');
    return;
}
if($egn=='init_user_host_table') {
    $DB->query_prepare("CREATE TABLE IF NOT EXISTS `MN_user_host` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL COMMENT 'MN_user.id',
        `host_id` int(11) NOT NULL COMMENT 'MN_zj.id',
        `created_at` varchar(50) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_host` (`user_id`,`host_id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",[]);
    exit(json_encode(['code'=>0,'msg'=>'表已创建']));
    return;
}
return;