Cookie.set('Admin', false)
var app = new Vue({
    el: '#app',
    data: {
        UserInfo: null,
        ItemID: null,
        ItemNum: 1,
        ItemInfo: {},
        FollowList: [],
        Star: false,
    },
    mounted: function () {
        // 初始化
        this.getUserInfo()

        // 从url获取商品id
        let ItemID = this.getQuery('opt');
        this.ItemID = ItemID;

        // 查询该商品信息
        this.getProduct(ItemID)

        this.getFollowList()

    },
    methods: {
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


        // 获取用户信息
        getUserInfo: function () {
            let token = Cookie.get('Token'),
                type = Cookie.get('UserType');

            if (token && type != 'admin') {
                HTTP.get('User_Class', { Mode: 'R' }).then(json => {
                    if (json.ErrorCode == 0) {
                        if (!json.Portrait) { json.Portrait = './img/logo.png' }
                        this.UserInfo = json
                    }
                })
            }
        },

        // 获取商品信息
        getProduct: function (id) {
            HTTP.get('Product_Class', { Mode: 'R', ID: id }, true).then(json => {
                if (json.ErrorCode == 0) { this.ItemInfo = json }
            })
        },


        // 获取收藏商品列表，用该列表来判断商品是否已经被收藏
        getFollowList: function () {
            HTTP.post('Follow_Class', { Mode: 'L' }).then(json => {
                let star = false;
                if (json.ErrorCode == 0) {
                    this.FollowList = json.Content
                    // 循环收藏列表，如果存在该商品，那么该商品就是已经被收藏过了
                    for (const item of json.Content) {
                        if (item.ID == this.ItemID) { star = true }
                    }
                }
                this.Star = star
            })
        },

        // 添加收藏
        setFollow: function () {
            let options = { ID: this.ItemID }
            // 如果商品已经收藏，那么取消收藏
            if (this.Star) { options.Mode = 'D' }
            // 否则添加收藏
            else { options.Mode = 'C' }

            HTTP.post('Follow_Class', options, true).then(json => {
                console.log(json)
                if (json.ErrorCode == 0) { this.getFollowList() }
                if (json.ErrorCode == 1400) {
                    let check = confirm("您还尚未登录，是否前往登录?");
                    if (check) { window.location.href = 'login.html' }
                }
            })
        },

        // 添加到购物车
        setCart: function () {
            let options = { Mode: 'C', ID: this.ItemID, Num: this.ItemNum }
            HTTP.post('Cart_Class', options).then(json => {
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

        // 数量加
        num_add: function () {
            this.ItemNum++
        },
        // 数量减
        num_remove: function () {
            if (this.ItemNum > 1) { this.ItemNum-- }
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

// 图片放大镜
// 获取HTML元素
let Move = document.getElementById('Move');
let Booth = document.getElementById('Booth');
let Magnifier = document.getElementById('Magnifier');
let Magnifier_Img = Magnifier.getElementsByTagName('img')[0];
let Info = document.getElementById('Info');

// 鼠标移动时
Booth.onmousemove = function (event) {
    event = event || window.event;
    this.style.cursor = 'Move';
    // 计算move移动块的left值
    let move_left = event.clientX - this.offsetLeft - Move.offsetWidth / 2;
    // 计算move移动块的top值
    let move_top = event.clientY - this.offsetTop - Move.offsetHeight / 2;
    // 超出左边界赋值为0
    move_left = move_left < 0 ? 0 : move_left;
    // 超出右边界赋值为盒子宽度-移动块的宽度
    move_left = move_left > this.offsetWidth - Move.offsetWidth ? this.offsetWidth - Move.offsetWidth : move_left;
    // 超出上边界赋值为0
    move_top = move_top < 0 ? 0 : move_top;
    // 超出下边界赋值为盒子高度-移动块高度
    move_top = move_top > this.offsetHeight - Move.offsetHeight ? this.offsetHeight - Move.offsetHeight : move_top;
    // 修改移动块left、top值
    Move.style.left = move_left + 'px';
    Move.style.top = move_top + 'px';

    // 计算图片需要移动的坐标
    let big_x = move_left / (Booth.offsetWidth - Move.offsetWidth) * (Magnifier_Img.offsetWidth - Magnifier.offsetWidth);
    let big_y = move_top / (Booth.offsetHeight - Move.offsetHeight) * (Magnifier_Img.offsetHeight - Magnifier.offsetHeight);

    // 修改图片定位
    Magnifier_Img.style.left = -big_x + 'px';
    Magnifier_Img.style.top = -big_y + 'px';
}

// 鼠标指针移动到指定的对象上时
Booth.onmouseover = function () {
    Move.style.display = 'block';
    Magnifier.style.display = 'block';
    Info.style.display = 'none';
}

// 鼠标指针移除指定对象外时
Booth.onmouseout = function () {
    Move.style.display = 'none';
    Magnifier.style.display = 'none';
    Info.style.display = 'block';
}