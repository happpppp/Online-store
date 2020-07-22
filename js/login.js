// 自动登陆验证，检测Token是否存在，如果存在，则使用该Token去登录
Cookie.set('Admin', false)
let token = Cookie.get('Token');
if (token) {
    HTTP.post('User_Login', { 'Mode': 'Confirm' }).then(json => {
        if (json.ErrorCode == 0) { loginDone('index') }
    })
}

// 回车键登陆
function loginEnter(e) {
    let event = e || window.event;
    if (event.keyCode == 13) { login() }
}

// 登陆
function login() {
    // 获取输入的账号密码
    let Account = document.getElementById('Account').value,
        Password = document.getElementById('Password').value;

    // 获取记住账号是否勾选
    let Remember = document.getElementById('remember').checked;

    // 账号密码不为空的情况下才允许登录
    if (Account != '' && Password != '') {
        let options = { 'Mode': 'Login', 'UserID': Account, 'Password': Password };
        HTTP.post('User_Login', options).then(json => {
            if (json.ErrorCode == 0) {
                // 保存Token，以方便日后自动登录
                Cookie.set('Token', json.Token);
                // 如果勾选记住账号，那么保存账号，否则删除保存的账号
                if (Remember) { Cookie.set('UserID', Account) }
                else { Cookie.del('UserID') }
                // 登录完成
                loginDone('index');
            }
            else { alert('登录失败，请检查用户名和密码是否正确') }
        })
    }
}


// 登录完成后，跳转到首页
function loginDone(page) {
    window.location.href = page + ".html"
}

// 设置记住账号的勾选框是否勾选，以及用户名输入框是否显示记住的账号
function setRemember() {
    let Account = Cookie.get('UserID'),
        Remember = document.getElementById('remember'),
        Account_Input = document.getElementById('Account');

    // 如果存在记录的账号，那么显示账号，并且勾选记住账号的选框
    if (Account) {
        Remember.checked = true;
        Account_Input.value = Account;
    }
    // 否则就取消记住账号的勾选
    else { Remember.checked = false; }
}

// 注册用户
function signup() {
    let Account = document.getElementById('Account').value,
        Password = document.getElementById('Password').value,
        PasswordCheck = document.getElementById('PasswordCheck').value,
        Phone = document.getElementById('Phone').value,
        EMail = document.getElementById('EMail').value;
    // 检查内容是否填写完整
    if (Account == '' || Password == '' || PasswordCheck == '' || Phone == '' || EMail == '') {
        alert('您还有内容尚未填写，请填写完整注册信息')
    }

    // 检查两次输入的密码是否相同
    else if (Password != PasswordCheck) { alert('两次输入的密码不一致') }

    // 检查手机号码是否正确
    else if (!checkPhone(Phone)) { alert('请输入正确的手机号码') }

    else if (!checkEMail(EMail)) { alert('请输入正确的邮箱地址') }

    // 通过以上的验证后，开始调用注册接口
    else {
        let options = { 'UserID': Account, 'Password': Password, 'Phone': Phone, 'EMail': EMail };
        HTTP.get('User_Register', options, true).then(json => {
            if (json.ErrorCode == 0) {
                alert('注册成功!')
                window.location.href = "login.html"
            }
            if(json.ErrorCode == 10001) {
                alert('用户已注册！')
            }
        })
    }
}

// 发送验证码（假的）
function SendCode() {
    let Phone = document.getElementById('Phone').value;
    if (!checkPhone(Phone)) { alert('请输入正确的手机号码') }
    else { alert('发送成功') }
}

// 忘记密码，修改新密码
function forget() {
    let Account = document.getElementById('Account').value,
        Phone = document.getElementById('Phone').value,
        Code = document.getElementById('Code').value,
        Password = document.getElementById('Password').value;

    // 检查内容是否填写完整
    if (Account == '' || Password == '' || Phone == '' || Code == '') {
        alert('您还有内容尚未填写，请填写完整信息')
    } else {
        let options = { 'UserID': Account, 'NewPassword': Password, 'Phone': Phone };
        console.log(options)
        HTTP.post('User_Forget', options).then(json => {
            console.log(json)
            if (json.ErrorCode != 0) { alert('用户名与手机号码不匹配，无法重置密码') }
            else {
                alert('重置密码成功')
                window.location.href = 'login.html'
            }
        })
    }
}

// 使用正则表达式，检查手机号码是否正确
function checkPhone(phone) {
    let pattern = /^1[3456789]\d{9}$/;
    if (!pattern.test(phone)) { return false; }
    else { return true; }
}

// 使用正则表达式，检查邮箱是否正确
function checkEMail(email) {
    let pattern = /^([A-Za-z0-9_\-\.\u4e00-\u9fa5])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,8})$/;
    if (pattern.test(email)) { return true; }
    else { return false; }
}