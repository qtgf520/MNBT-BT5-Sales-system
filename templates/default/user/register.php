<?php mnbt_theme_include('head'); ?>
<div class="container" style="padding-top:10%;">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-info text-white text-center"><h4>用户注册</h4></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>用户名</label>
                        <input type="text" class="form-control" id="reg_username" placeholder="3-20位字母数字下划线">
                    </div>
                    <div class="form-group">
                        <label>邮箱</label>
                        <input type="email" class="form-control" id="reg_email" placeholder="选填">
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input type="password" class="form-control" id="reg_password" placeholder="至少6位">
                    </div>
                    <div class="form-group">
                        <label>确认密码</label>
                        <input type="password" class="form-control" id="reg_password2" placeholder="再次输入密码">
                    </div>
                    <button type="button" class="btn btn-info btn-block" onclick="doRegister()">注册</button>
                    <div class="text-center mt-2"><a href="login.php">已有账号？去登录</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function doRegister() {
    var u=$('#reg_username').val().trim();
    var e=$('#reg_email').val().trim();
    var p=$('#reg_password').val();
    var p2=$('#reg_password2').val();
    if(!u){msalert(3,'请输入用户名',3000);return;}
    if(p.length<6){msalert(3,'密码至少6位',3000);return;}
    if(p!=p2){msalert(3,'两次密码不一致',3000);return;}
    msloading('注册中...');
    $.post('./ajax.php',{gn:'user_register',username:u,email:e,password:p},function(d){
        var r=JSON.parse(d);
        msloadingde();
        if(r.code=='注册成功'){
            msalert(1,'注册成功！',2000);
            setTimeout(function(){window.location.href='login.php';},1500);
        }else{
            msalert(4,r.code,3000);
        }
    });
}
</script>
</body>
</html>