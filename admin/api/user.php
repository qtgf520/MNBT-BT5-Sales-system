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
return;