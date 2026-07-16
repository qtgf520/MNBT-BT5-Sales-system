<?php
if($egn=='get_all_permissions') {
    $uid = intval($_POST['uid']);
    if(!$uid) json_exit('参数错误');
    $Permission = new Permission($DB, $uid);
    $perms = $Permission->getUserPermissions($uid);
    exit(json_encode(['code'=>0,'data'=>$perms]));
    return;
}
if($egn=='update_user_permissions') {
    $uid = intval($_POST['uid']);
    $perms_str = daddslashes($_POST['permissions']);
    if(!$uid) json_exit('参数错误');
    $perms_arr = $perms_str ? explode(',', $perms_str) : [];
    $Permission = new Permission($DB, $uid);
    $Permission->saveUserPermissions($uid, $perms_arr);
    mnbt_log($user,'权限管理','设置用户ID'.$uid.'的权限','修改成功',$DB);
    json_exit('权限保存成功');
    return;
}
return;