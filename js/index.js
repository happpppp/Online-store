Cookie.set('Admin', false)
var app = new Vue({
    el: '#app',
    data: {
        UserInfo: null,
        Menu: [{ Type: '读取中' }],
        MenuActive: '全部商品',
        ItemList: [],
    },
    mounted: function () {
        // 初始化
        this.getUserInfo()
        this.getMenu()
        this.getProduct()
    },
    methods: {
        // 获取用户信息
        getUserInfo: function () {
            let token = Cookie.get('Token'),
                type = Cookie.get('UserType');
            //console.log(token)

            if (token && type != 'admin') {
                HTTP.get('User_Class', { Mode: 'R' }, true).then(json => {
                    if (json.ErrorCode == 0) {
                        if (!json.Portrait) { json.Portrait = './img/logo.png' }
                        this.UserInfo = json
                    }
                })
            }
        },

        // 获取商品分类
        getMenu: function () {
            HTTP.post('Menu_Class', { Mode: 'L' }).then(json => {
                if (json.ErrorCode == 0) {
                    let menu = [{ Type: '全部商品' }]
                    this.Menu = menu.concat(json.Content)
                }
            })
        },

        // 获取商品列表
        getProduct: function (type) {
            let options = { Mode: 'L' };
            // 如果传入分类，则会显示相应分类的商品
            if (type && type != '全部商品') { options.Type = type }
            // 小标题显示当前正在浏览的内容类型
            if (type) { this.MenuActive = type }

            HTTP.get('Product_Class', options).then(json => {
                if (json.ErrorCode == 0) { this.ItemList = json.Content }
                else { this.ItemList = [] }
            })
        },

        // 搜索商品
        search: function (event) {
            let value = event.target.value;
            if (value == '') { this.getProduct() }
            else {
                HTTP.get('Product_Search', { Text: value }, true).then(json => {
                    if (json.ErrorCode == 0) { this.ItemList = json.Content }
                    else { this.ItemList = [] }
                })
            }
        },

        // 添加到购物车
        setCart: function (event) {

            let id = event.target.dataset.id;
            let options = { Mode: 'C', ID: id, Num: 1 }
            HTTP.post('Cart_Class', options, true).then(json => {
                if (json.ErrorCode == 0) {
                    let check = confirm("添加到购物车成功，是否前往购物车?");
                    if (check) { window.location.href = 'user.html?opt=购物车' }
                }
                if (json.ErrorCode == 1400) {
                    let check = confirm("您还尚未登录，是否前往登录?");
                    if (check) { window.location.href = 'login.html' }
                }
            })
        },

        // 跳转页面
        jump: function (event) {
            // 获取需要跳转的页面
            let page = event.target.dataset.page + '.html';
            // 获取跳转参数
            let option = event.target.dataset.opt;
            if (option) { page += '?opt=' + option }
            window.location.href = page;
        },

        // 退出登录
        logout: function () {
            // 弹出提示框
            let check = confirm("确定退出登录吗?");
            if (check) {
                Cookie.del('Token')
                window.location.href = 'login.html'
            }
        },
    }
});


