<?php
if(!defined('IN_CRONLITE'))exit();

class UserAuth {
    private $DB;

    public function __construct($DB) {
        $this->DB = $DB;
    }

    // 用户注册
    public function register($username, $password, $email = '') {
        global $date;
        $username = trim($username);
        if (strlen($username) < 3 || strlen($username) > 20) {
            return array('code' => -1, 'msg' => '用户名长度必须在3-20位之间');
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return array('code' => -1, 'msg' => '用户名只能包含字母、数字和下划线');
        }
        if (strlen($password) < 6) {
            return array('code' => -1, 'msg' => '密码长度不能少于6位');
        }
        // 检查用户名是否已存在
        $exists = $this->DB->get_row_prepare("SELECT id FROM MN_user WHERE username=?", [$username]);
        if ($exists) {
            return array('code' => -1, 'msg' => '用户名已存在');
        }
        // 生成密码盐和加密密码
        $salt = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $password_enc = md5(md5($password) . $salt);
        $clientip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        // 插入用户
        $ret = $this->DB->query_prepare(
            "INSERT INTO MN_user (username, password, salt, email, group_id, status, reg_date, reg_ip) VALUES (?, ?, ?, ?, 1, 'true', ?, ?)",
            [$username, $password_enc, $salt, $email, $date, $clientip]
        );
        if ($ret) {
            $uid = $this->DB->insert_id ?? $this->DB->query("SELECT LAST_INSERT_ID() as id");
            return array('code' => 0, 'msg' => '注册成功', 'uid' => $uid);
        }
        return array('code' => -1, 'msg' => '注册失败');
    }

    // 用户登录
    public function login($username, $password) {
        $user = $this->DB->get_row_prepare("SELECT * FROM MN_user WHERE username=? limit 1", [$username]);
        if (!$user) {
            return array('code' => -1, 'msg' => '用户不存在');
        }
        if ($user['status'] != 'true') {
            return array('code' => -1, 'msg' => '账号已被禁用');
        }
        // 验证密码
        $password_enc = md5(md5($password) . $user['salt']);
        if ($password_enc != $user['password']) {
            return array('code' => -1, 'msg' => '密码错误');
        }
        // 更新登录信息
        $clientip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        global $date;
        $this->DB->query_prepare(
            "UPDATE MN_user SET login_date=?, login_ip=? WHERE id=?",
            [$date, $clientip, $user['id']]
        );
        // 生成Token
        $session = md5($user['username'] . $password . SYS_KEY);
        $token = authcode("{$user['id']}\t{$user['username']}\t{$session}", 'ENCODE', SYS_KEY);
        return array('code' => 0, 'msg' => '登录成功', 'token' => $token, 'user' => $user);
    }

    // Token验证
    public function validateToken($token) {
        $decoded = authcode(daddslashes($token), 'DECODE', SYS_KEY);
        if (!$decoded) return false;
        list($uid, $username, $session) = explode("\t", $decoded);
        $user = $this->DB->get_row_prepare("SELECT * FROM MN_user WHERE id=? AND username=? limit 1", [$uid, $username]);
        if (!$user || $user['status'] != 'true') return false;
        $check_session = md5($user['username'] . $user['password'] . SYS_KEY);
        if ($session != $check_session) return false;
        return $user;
    }

    // 获取用户信息
    public function getUser($uid) {
        return $this->DB->get_row_prepare("SELECT * FROM MN_user WHERE id=? limit 1", [$uid]);
    }

    // 更新余额
    public function updateMoney($uid, $amount, $memo = '') {
        global $date;
        $user = $this->getUser($uid);
        if (!$user) return false;
        $before = $user['money'];
        $after = $before + $amount;
        if ($after < 0) return false;
        $this->DB->query_prepare("UPDATE MN_user SET money=? WHERE id=?", [$after, $uid]);
        $this->DB->query_prepare(
            "INSERT INTO MN_money_log (user_id, money, `before`, `after`, memo, `date`) VALUES (?, ?, ?, ?, ?, ?)",
            [$uid, $amount, $before, $after, $memo, $date]
        );
        return true;
    }
}
?>