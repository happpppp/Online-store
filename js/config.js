// 服务器地址
const Server = 'http://localhost/php/';

// 接口请求函数
const HTTP = {
    // POST请求
    post: function (PHPFile, PostOptions, PrintInfo) {
        var url = Server + PHPFile + ".php?",
            Token,
            sourceData,
            print = { 'URL': url, 'Options': PostOptions };

        if (Cookie.get('Admin') == 'true') { Token = Cookie.get('Token_Admin') }
        else { Token = Cookie.get('Token') }

        return new Promise(function (resolve, reject) {
            fetch(url, {
                method: "post",
                body: JSON.stringify(PostOptions),
                headers: {
                    'content-Type': 'application/json',
                    'Authorization': Token
                }
            })
                .then(response => {
                    sourceData = response;
                    return response.text();
                })
                .then(res => {
                    if (PrintInfo) {
                        if (HTTP.check(res)) { print.response = JSON.parse(res) } else { print.response = res }
                        console.log(print)
                    }
                    if (sourceData.ok) { resolve(JSON.parse(res)) } else { reject(http) }
                })
        });
    },

    // GET请求
    get: function (PHPFile, Options, PrintInfo, FileList) {
        var url = Server + PHPFile + ".php?",
            Token,
            sourceData,
            formData = HTTP.makeBody(FileList),
            print = { 'URL': url, 'Options': Options };

        if (Cookie.get('Admin') == 'true') { Token = Cookie.get('Token_Admin') }
        else { Token = Cookie.get('Token') }
        
        if (Options != null) {
            for (const key in Options) { url += '&' + key + '=' + Options[key] }
        }

        return new Promise(function (resolve, reject) {
            fetch(url, {
                method: "post",
                body: formData,
                headers: { 'Authorization': Token }
            })
                .then(response => {
                    sourceData = response;
                    return response.text();
                })
                .then(res => {
                    if (PrintInfo) {
                        if (HTTP.check(res)) { print.response = JSON.parse(res) } else { print.response = res }
                        //console.log(print)
                    }
                    if (sourceData.ok) { resolve(JSON.parse(res)) } else { reject(http) }
                })
                .catch(error => console.error('Error:', error));
        });
    },

    // 检查json正确性
    check: function (string) {
        try { if (typeof JSON.parse(string) == "object") { return true; } } catch (e) { }
        return false;
    },

    // 组合Post请求的FormData
    makeBody: function (options) {
        var form = new FormData();
        if (options) {
            for (let i = 0; i < options.length; i++) {
                var key = options[i].Key,
                    val = options[i].Value;
                form.append(key, val)
            }
        }
        return form;
    },
}


// 浏览器Cookie操作
const Cookie = {
    // 设置Cookie
    set: function (name, value) {
        var Days = 3;
        var exp = new Date();
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
        document.cookie = name + "=" + value + ";expires=" + exp.toGMTString();
    },

    //读取cookies
    get: function (name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return arr[2];
        else
            return null;
    },

    //删除cookies
    del: function (name) {
        var exp = new Date();
        exp.setTime(exp.getTime() - 1);
        var cval = Cookie.get(name);
        if (cval != null)
            document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
    }
}