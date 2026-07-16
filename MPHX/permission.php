<?php
if(!defined('IN_CRONLITE'))exit();

class Permission {
    private $DB;
    private $user_id;

    public function __construct($DB, $user_id = null) {
        $this->DB = $DB;
        $this->user_id = $user_id;
    }

    // 检查是否已初始化（是否有__perm_init__标记）
    private function isInitialized($user_id = null) {
        $uid = $user_id ? $user_id : $this->user_id;
        if (!$uid) return false;
        $row = $this->DB->get_row_prepare("SELECT COUNT(*) as cnt FROM mn_user_permissions WHERE user_id = ? AND permission_code = '__perm_init__'", [$uid]);
        return $row && $row['cnt'] > 0;
    }

    // 获取所有权限列表
    public function getAllPermissions() {
        $result = $this->DB->get_all_prepare("SELECT * FROM mn_permissions ORDER BY sort_order ASC", []);
        return $result ?: [];
    }

    // 获取用户已设置的权限记录（排除标记）
    public function getUserPermissionRecords($user_id = null) {
        $uid = $user_id ? $user_id : $this->user_id;
        if (!$uid) return array();
        $result = $this->DB->get_all_prepare("SELECT permission_code, status FROM mn_user_permissions WHERE user_id = ? AND permission_code != '__perm_init__'", [$uid]);
        $permissions = array();
        if ($result) {
            foreach($result as $row) {
                $permissions[$row['permission_code']] = $row['status'];
            }
        }
        return $permissions;
    }

    // 获取用户的所有权限（含权限名称等完整信息，用于管理界面）
    public function getUserPermissions($user_id = null) {
        $uid = $user_id ? $user_id : $this->user_id;
        if (!$uid) return [];
        $result = $this->DB->get_all_prepare(
            "SELECT p.*, COALESCE(up.status, 0) as has_permission
             FROM mn_permissions p
             LEFT JOIN mn_user_permissions up ON p.code = up.permission_code AND up.user_id = ?
             ORDER BY p.sort_order ASC",
            [$uid]
        );
        return $result ?: [];
    }

    // 检查权限：未初始化→全开 | 已初始化→严格按记录
    public function hasPermission($permission_code) {
        if (!$this->user_id) return false;
        if (!$this->isInitialized()) return true;
        $records = $this->getUserPermissionRecords();
        return isset($records[$permission_code]) && $records[$permission_code] == 1;
    }

    // 检查模块权限
    public function hasModulePermission($module) {
        if (!$this->user_id) return false;
        if (!$this->isInitialized()) return true;
        $records = $this->getUserPermissionRecords();
        $result = $this->DB->get_all_prepare("SELECT code FROM mn_permissions WHERE module = ?", [$module]);
        if ($result) {
            foreach($result as $perm) {
                if (isset($records[$perm['code']]) && $records[$perm['code']] == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    // 保存用户权限（先删除旧记录再插入新记录）
    public function saveUserPermissions($user_id, $permission_codes) {
        $this->DB->query_prepare("DELETE FROM mn_user_permissions WHERE user_id = ?", [$user_id]);
        if (!empty($permission_codes)) {
            foreach ($permission_codes as $code) {
                $this->DB->query_prepare(
                    "INSERT INTO mn_user_permissions (user_id, permission_code, status) VALUES (?, ?, 1)",
                    [$user_id, $code]
                );
            }
        }
        // 写入初始化标记
        $this->DB->query_prepare(
            "INSERT INTO mn_user_permissions (user_id, permission_code, status) VALUES (?, '__perm_init__', 1)",
            [$user_id]
        );
        return true;
    }
}
?>