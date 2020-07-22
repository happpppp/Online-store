Cookie.set('Admin', false)
var app = new Vue({
    el: '#app',
    data: {
        UserInfo: {},
        Menu: [
            { Title: '个人信息', Icon: 'ri-user-line' },
            { Title: '订单列表', Icon: 'ri-file-list-3-line' },
            { Title: '地址管理', Icon: 'ri-building-line' },
            { Title: '我的收藏', Icon: 'ri-star-line' },
            { Title: '购物车', Icon: 'ri-shopping-cart-line' },
        ],
        MenuActive: '个人信息',
        FollowList: [],
        OrderList: [],
        OrderList_Search: [],
        OrderList_Search_Enable: false,
        CartList: [],
        AddressList: [],
        NewUserInfo: { EMail: '', Phone: '', Password: '' },
        NewUserAvatar: { File: null, Img: null },
        NewContact: { Name: '', Phone: '', Address: '' }

    },
    mounted: function () {

        // 初始化
        this.getUserInfo();

        // 如果是点击购物车进来的个人呢页面，那么跳转至购物车显示
        let page = decodeURI(this.getQuery('opt'))
        if (page == '个人信息' || page == '购物车' || page == '订单列表') { this.switchMenu(null, page) }
    },
    methods: {
        // 获取用户信息
        getUserInfo: function () {
            let token = Cookie.get('Token'),
                type = Cookie.get('UserType');

            if (token && type != 'admin') {
                HTTP.get('User_Class', { Mode: 'R' }).then(json => {
                    if (json.ErrorCode == 0) {
                        if (!json.Portrait) { json.Portrait = './img/logo.png' }
                        this.UserInfo = json
                        this.NewUserInfo = { EMail: json.EMail, Phone: json.Phone, Password: '' }
                        this.NewUserAvatar = { File: null, Img: null }
                    }
                })
            } else { window.location.href = 'login.html' }
        },

        // 修改用户信息
        setUserInfo: function () {
            if (this.NewUserInfo.EMail == '' || this.NewUserInfo.Phone == '') {
                alert('请填写邮箱地址和联系电话')
            }

            else if (!this.checkPhone(this.NewUserInfo.Phone)) { alert('请输入正确的手机号码') }
            else if (!this.checkEMail(this.NewUserInfo.EMail)) { alert('请输入正确的邮箱地址') }
            else {
                let options = {
                    Mode: 'U',
                    EMail: this.NewUserInfo.EMail,
                    Phone: this.NewUserInfo.Phone,
                }

                // 如果用户设置了新密码，那么添加到options中
                if (this.NewUserInfo.Password != '') {
                    options.Password = this.NewUserInfo.Password
                }

                HTTP.get('User_Class', options, true, [{ Key: 'Portrait', Value: this.NewUserAvatar.File }]).then(json => {
                    if (json.ErrorCode == 0) {
                        alert('修改信息成功')
                        window.location.href = 'user.html?opt=个人信息'
                    }
                })
            }
        },

        // 选择头像
        changeImg: function () {
            let App = this;
            let files = document.getElementById('imgFile').files;
            if (files.length > 0) {
                let file = files[0];
                App.NewUserAvatar.File = file;

                //创建文件读取对象
                let reader = new FileReader();
                // 读取文件
                reader.readAsDataURL(file);
                // 显示选择的图片
                reader.onloadend = function () {
                    App.NewUserAvatar.Img = this.result;
                }
            }
        },

        // 获取收藏商品列表
        getFollowList: function () {
            this.FollowList = []
            HTTP.post('Follow_Class', { Mode: 'L' }).then(json => {
                if (json.ErrorCode == 0) {
                    this.FollowList = json.Content
                }
            })
        },

        // 取消收藏
        delFollow: function (event) {
            let id = event.target.dataset.id;
            // 弹出提示框
            let check = confirm("确定取消该收藏吗?");
            // 如果用户点了是，那么取消该收藏
            if (check) {
                HTTP.post('Follow_Class', { Mode: 'D', ID: id }).then(json => {
                    // 取消成功后，刷新收藏列表
                    if (json.ErrorCode == 0) { this.getFollowList() }
                })
            }
        },

        // 获取我的订单列表
        getOrderList: function () {
            HTTP.post('Order_Class', { Mode: 'L' }).then(json => {
                console.log(json)
                if (json.ErrorCode == 0) { this.OrderList = json.Content }
            })
        },

        // 获取购物车列表
        getCartList: function () {
            HTTP.post('Cart_Class', { Mode: 'L' }).then(json => {
                console.log(json)
                if (json.ErrorCode == 0) { this.CartList = json.Content }
            })
        },

        // 删除购物车中的商品
        delCart: function (event) {
            let id = event.target.dataset.id;
            // 弹出提示框
            let check = confirm("确定从购物车中删除该商品吗?");
            // 如果用户点了是，那么删除
            if (check) {
                HTTP.post('Cart_Class', { Mode: 'D', ID: id }, true).then(json => {
                    // 删除成功后，刷新购物车列表
                    if (json.ErrorCode == 0) { this.getCartList() }
                })
            }
        },

        // 数量加
        num_add: function (event) {
            let index = event.target.dataset.index;
            this.CartList[index].Num++
        },
        // 数量减
        num_remove: function (event) {
            let index = event.target.dataset.index;
            if (this.CartList[index].Num > 1) { this.CartList[index].Num-- }
        },

        // 下单购物车中的商品
        newOrder: function () {
            let options = { Mode: 'C', ProductList: [] }
            // 获取需要下单的商品
            for (const item of this.CartList) {
                options.ProductList.push({ ID: item.ID, Num: item.Num })
            }
            options.ProductList = JSON.stringify(options.ProductList)
            // 获取地址信息
            let selector = document.getElementById('AddressSelector'),
                addressIndex = selector.selectedIndex,
                addressID = selector.options[addressIndex].dataset.id;
            options.Contact_ID = addressID;

            // 开始下单
            HTTP.post('Order_Class', options, true).then(json => {
                console.log(json)
                if (json.ErrorCode == 0) {
                    alert('下单成功!')
                    window.location.href = 'user.html?opt=订单列表'
                }
            })
        },

        // 搜索订单
        searchOrder: function (event) {
            let value = event.target.value;
            // 如果输入框内容为空，那么显示全部订单
            if (value == '') {
                this.OrderList_Search = [];
                this.OrderList_Search_Enable = false;
            }
            else {
                let search = [];
                // 循环订单列表
                for (let i = 0; i < this.OrderList.length; i++) {
                    for (const item of this.OrderList[i].ProductList) {
                        // 判断该订单的商品是否与输入的内容匹配
                        if (item.Name.indexOf(value) != -1) {
                            search.push(this.OrderList[i])
                        }
                    }
                }
                // 设置订单显示模式为搜索模式
                this.OrderList_Search = search;
                this.OrderList_Search_Enable = true;
            }
        },

        // 获取地址列表
        getAddressList: function () {
            HTTP.post('Address_Class', { Mode: 'L' }).then(json => {
                console.log(json)
                if (json.ErrorCode == 0) { this.AddressList = json.Content }
            })
        },

        // 新增地址
        newAddress: function () {
            let info = this.NewContact;
            if (info.Name == '' || info.Phone == '' || info.Address == '') {
                alert('请填写完整地址信息')
            }
            else if (!this.checkPhone(info.Phone)) { alert('请输入正确的手机号码') }
            else {
                let options = {
                    Mode: 'C',
                    Name: info.Name,
                    Phone: info.Phone,
                    Address: info.Address,
                }
                HTTP.post('Address_Class', options).then(json => {
                    if (json.ErrorCode == 0) {
                        alert('添加成功')
                        this.NewContact = { Name: '', Phone: '', Address: '' }
                        this.getAddressList()
                    }
                })
            }
        },

        // 删除地址
        delAddress: function (event) {
            let id = event.target.dataset.id;
            // 弹出提示框
            let check = confirm("确定删除该地址吗?");
            // 如果用户点了是，那么删除分类
            if (check) {
                HTTP.post('Address_Class', { Mode: 'D', ID: id }).then(json => {
                    // 删除成功后，刷新地址列表
                    if (json.ErrorCode == 0) { this.getAddressList() }
                })
            }
        },

        // 点击菜单时，切换页面内容
        switchMenu: function (event, page) {
            let menu;
            if (page) { menu = page }
            else { menu = event.target.dataset.menu }
            this.MenuActive = menu;
            switch (menu) {
                case '订单列表':
                    this.getOrderList()
                    break;
                case '地址管理':
                    this.getAddressList()
                    break;
                case '我的收藏':
                    this.getFollowList()
                    break;
                case '购物车':
                    this.getCartList()
                    this.getAddressList()
                    break;
            }
        },

        // 使用正则表达式，检查手机号码是否正确
        checkPhone: function (phone) {
            let pattern = /^1[3456789]\d{9}$/;
            if (!pattern.test(phone)) { return false; }
            else { return true; }
        },

        // 使用正则表达式，检查邮箱是否正确
        checkEMail: function (email) {
            let pattern = /^([A-Za-z0-9_\-\.\u4e00-\u9fa5])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,8})$/;
            if (pattern.test(email)) { return true; }
            else { return false; }
        },

        // 跳转页面
        jump: function (event) {
            let page = event.target.dataset.page + '.html';
            window.location.href = page;
        },

        // 从url获取参数
        getQuery: function (value) {
            let query = window.location.search.substring(1);
            let vars = query.split("&");
            for (const i of vars) {
                let pair = i.split("=");
                if (pair[0] == value) { return pair[1]; }
            }
            return false;
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


