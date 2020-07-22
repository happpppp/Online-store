Cookie.set('Admin', true)
var app = new Vue({
    el: '#app',
    data: {
        Menu: [{ Type: '读取中' }],
        Menu_New: '',
        ItemList: [],
        NewProduct: { Name: '', Introduction: '', Price: 0, Image: null, },
        NewProduct_Select_Image: null,
    },
    mounted: function () {
        // 初始化
        let App = this;
        // 自动登陆验证，检测Token是否存在，如果存在，则使用该Token去登录
        let cookie_Account = Cookie.get('Token_Admin');
        if (cookie_Account) { loginConfirm() } else {window.location.href = 'login_admin.html' }

        function loginConfirm() {
            HTTP.post('Admin_Login', { 'Mode': 'Confirm' }).then(json => {
                // 如果登录失败，那么回到登录页面
                if (json.ErrorCode != 0) {
                    Cookie.del('Token')
                    window.location.href = 'login_admin.html'
                }
                else {
                    App.getMenu()
                    App.getProduct()
                }
            })
        }

    },
    methods: {
        // 获取商品分类
        getMenu: function () {
            HTTP.post('Menu_Class', { Mode: 'L' }).then(json => {
                if (json.ErrorCode == 0) {
                    this.Menu = json.Content
                }
            })
        },

        // 新商品分类
        newMenu: function () {
            // 输入框内容不为空时，才调用接口
            if (this.Menu_New != '') {
                HTTP.post('Menu_Class', { Mode: 'C', Type: this.Menu_New }).then(json => {
                    // 添加成功后，刷新商品分类
                    if (json.ErrorCode == 0) {
                        this.Menu_New = ''
                        this.getMenu()
                    }
                })
            }
        },

        // 删除商品分类
        delMenu: function (event) {
            let id = event.target.dataset.id;
            // 弹出提示框
            let check = confirm("确定删除该分类吗?");
            // 如果用户点了是，那么删除分类
            if (check) {
                HTTP.post('Menu_Class', { Mode: 'D', ID: id }).then(json => {
                    // 删除成功后，刷新商品分类
                    if (json.ErrorCode == 0) { this.getMenu() }
                    if (json.ErrorCode == 10020) { alert('该分类存在商品，无法删除') }
                })
            }
        },

        // 获取商品列表
        getProduct: function () {
            HTTP.get('Product_Class', { Mode: 'L' }).then(json => {
                if (json.ErrorCode == 0) {
                    this.ItemList = json.Content
                }
            })
        },

        changeImg: function () {
            let App = this;
            let files = document.getElementById('imgFile').files;
            if (files.length > 0) {
                let file = files[0];
                App.NewProduct.Image = file;

                //创建文件读取对象
                let reader = new FileReader();
                // 读取文件
                reader.readAsDataURL(file);
                // 显示选择的图片
                reader.onloadend = function () {
                    App.NewProduct_Select_Image = this.result;
                }
            }
        },

        addProduct: function () {
            // 获取所选分类
            let selector = document.getElementById('ProductTypeSelector'),
                typeIndex = selector.selectedIndex,
                type = selector.options[typeIndex].value;

            let App = this,
                info = App.NewProduct,
                check = true;
            // 循环新商品信息json，如果有空值存在，说明还有信息没有填写
            for (const key in info) {
                if (info[key] == '' || info[key] == null) { check = false }
            }
            if (!check) { alert('请输入完整商品信息') }
            else {
                let options = {
                    Mode: 'C',
                    Name: info.Name,
                    Introduction: info.Introduction,
                    Price: info.Price,
                    Type: type
                }

                HTTP.get('Product_Class', options, false, [{ Key: "Image", Value: info.Image }]).then(json => {
                    // 添加成功后，刷新商品列表
                    console.log(json)
                    if (json.ErrorCode == 0) {
                        App.getProduct()
                        App.NewProduct = { Name: '', Introduction: '', Price: 0, Image: null, }
                        App.NewProduct_Select_Image = null
                    }
                })

            }

        },

        // 删除商品
        delProduct: function (event) {
            let id = event.target.dataset.id;
            // 弹出提示框
            let check = confirm("确定删除该商品吗?");
            // 如果用户点了是，那么删除该商品
            if (check) {
                HTTP.get('Product_Class', { Mode: 'D', ID: id }).then(json => {
                    // 删除成功后，刷新商品列表
                    if (json.ErrorCode == 0) { this.getProduct() }
                })
            }
        },

        jump: function (event) {
            let page = event.target.dataset.page + '.html';
            window.location.href = page;
        },

        // 退出登录
        logout: function () {
            // 弹出提示框
            let check = confirm("确定退出登录吗?");
            if (check) {
                Cookie.del('Token_Admin')
                window.location.href = 'login_admin.html'
            }
        },
    }
});


