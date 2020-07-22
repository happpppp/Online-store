// 自动登陆验证，检测Token是否存在，如果存在，则使用该Token去登录
Cookie.set('Admin', true)
let token = Cookie.get('Token_Admin');
if (token) {
    HTTP.post('Admin_Login', { 'Mode': 'Confirm' }).then(json => {
        if (json.ErrorCode == 0) { loginDone('admin') }
    })
}

// 回车键登陆
function loginEnter(e) {
    let event = e || window.event;
    if (event.keyCode == 13) { login() }
}

// 管理员登录
function login() {
    // 获取输入的账号密码
    let Account = document.getElementById('Account').value,
        Password = document.getElementById('Password').value;

    // 获取记住账号是否勾选
    let Remember = document.getElementById('remember').checked;

    // 账号密码不为空的情况下才允许登录
    if (Account != '' && Password != '') {
        let options = { 'Mode': 'Login', 'UserID': Account, 'Password': Password };

        // 管理员登录
        HTTP.post('Admin_Login', options, true).then(json => {
            if (json.ErrorCode == 0) {
                // 保存Token，以方便日后自动登录
                Cookie.set('Token_Admin', json.Token);
                // 如果勾选记住账号，那么保存账号，否则删除保存的账号
                if (Remember) { Cookie.set('UserID', Account) }
                else { Cookie.del('UserID') }
                // 登录完成
                loginDone('admin')
            }
            else { alert('登录失败，请检查用户名和密码是否正确') }
        })

    }
}


// 登录完成后，跳转到后台
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