-- Adminer 4.7.6 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `Address`;
CREATE TABLE `Address` (
  `UserID` varchar(300) DEFAULT NULL COMMENT '所属用户ID',
  `Name` varchar(300) DEFAULT NULL COMMENT '收货人',
  `Phone` varchar(300) DEFAULT NULL COMMENT '收货电话',
  `Address` varchar(300) DEFAULT NULL COMMENT '收货地址',
  `id` int(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='收货地址表';

INSERT INTO `Address` (`UserID`, `Name`, `Phone`, `Address`, `id`) VALUES
('as5081523',	'泥煤',	'111',	'柳来路',	1),
('ant',	'蚂蚁',	'18058385766',	'财富168',	2),
('ant',	'老王',	'13838383838',	'你家隔壁',	4),
('123',	'123',	'15245678912',	'123',	5),
('123',	'456',	'13894561245',	'987',	6);

DROP TABLE IF EXISTS `Admin`;
CREATE TABLE `Admin` (
  `UserID` varchar(100) DEFAULT NULL COMMENT '用户名',
  `Password` varchar(100) DEFAULT NULL COMMENT '密码',
  `LastTime` datetime DEFAULT NULL COMMENT '最后一次操作时间',
  `id` int(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员表';

INSERT INTO `Admin` (`UserID`, `Password`, `LastTime`, `id`) VALUES
('admin',	'123456',	'2020-03-30 18:02:10',	1);

DROP TABLE IF EXISTS `Menu`;
CREATE TABLE `Menu` (
  `Type` varchar(100) DEFAULT NULL COMMENT '类型',
  `id` int(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='分类表';

INSERT INTO `Menu` (`Type`, `id`) VALUES
('新增分类1',	8),
('新增分类2',	10),
('新增分类3',	11),
('4',	12);

DROP TABLE IF EXISTS `Order`;
CREATE TABLE `Order` (
  `OrderID` varchar(300) DEFAULT NULL COMMENT '订单号',
  `UserID` varchar(300) DEFAULT NULL COMMENT '下单者ID',
  `Contact_Address` varchar(300) DEFAULT NULL COMMENT '收货地址',
  `Contact_Name` varchar(300) DEFAULT NULL COMMENT '收货人',
  `Contact_Phone` varchar(300) DEFAULT NULL COMMENT '收货电话',
  `ProductList` text COMMENT '商品ID与数量',
  `Total` double DEFAULT NULL COMMENT '总价',
  `Time` datetime DEFAULT NULL COMMENT '下单时间',
  `id` int(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='订单表';

INSERT INTO `Order` (`OrderID`, `UserID`, `Contact_Address`, `Contact_Name`, `Contact_Phone`, `ProductList`, `Total`, `Time`, `id`) VALUES
('020as50815232003271241031228',	'as5081523',	'柳来路',	'泥煤',	'111',	'{\"ID\":2,\"Num\":2}, {\"ID\":3,\"Num\":2}',	34,	'2020-03-27 00:41:03',	6),
('020ant2003270344183386',	'ant',	'你家隔壁',	'老王',	'13838383838',	'[{\"ID\":\"8\",\"Num\":1}]',	780,	'2020-03-27 15:44:18',	8),
('020ant200327034605204',	'ant',	'财富168',	'蚂蚁',	'18058385766',	'[{\"ID\":\"9\",\"Num\":5},{\"ID\":\"8\",\"Num\":2}]',	1340,	'2020-03-27 15:46:05',	9),
('020ant200327034826225',	'ant',	'你家隔壁',	'老王',	'13838383838',	'[{\"ID\":\"9\",\"Num\":1},{\"ID\":\"8\",\"Num\":1}]',	340,	'2020-03-27 15:48:26',	10),
('0202003270618191940',	'ant',	'财富168',	'蚂蚁',	'18058385766',	'[{\"ID\":\"2\",\"Num\":1}]',	2,	'2020-03-27 18:18:19',	11),
('0202003300333151696',	'as5081523',	'柳来路',	'泥煤',	'111',	'[{\"ID\":\"13\",\"Num\":1}]',	456,	'2020-03-30 15:33:15',	12),
('0202003300333263282',	'ant',	'你家隔壁',	'老王',	'13838383838',	'[{\"ID\":\"13\",\"Num\":1}]',	456,	'2020-03-30 15:33:26',	13),
('020200330033608451',	'as5081523',	'柳来路',	'泥煤',	'111',	'[{\"ID\":\"12\",\"Num\":1},{\"ID\":\"9\",\"Num\":1},{\"ID\":\"10\",\"Num\":1}]',	563,	'2020-03-30 15:36:08',	14),
('020200330033715324',	'ant',	'你家隔壁',	'老王',	'13838383838',	'[{\"ID\":\"9\",\"Num\":1},{\"ID\":\"10\",\"Num\":3},{\"ID\":\"13\",\"Num\":2}]',	1792,	'2020-03-30 15:37:15',	15),
('0202003300337382395',	'ant',	'你家隔壁',	'老王',	'13838383838',	'[{\"ID\":\"13\",\"Num\":2},{\"ID\":\"12\",\"Num\":4},{\"ID\":\"11\",\"Num\":1},{\"ID\":\"10\",\"Num\":2},{\"ID\":\"2\",\"Num\":1}]',	2046,	'2020-03-30 15:37:38',	16),
('0202003300500021048',	'123',	'987',	'456',	'13894561245',	'[{\"ID\":\"9\",\"Num\":1},{\"ID\":\"2\",\"Num\":5},{\"ID\":\"12\",\"Num\":1}]',	353,	'2020-03-30 17:00:02',	17);

DROP TABLE IF EXISTS `Product`;
CREATE TABLE `Product` (
  `Type` varchar(100) DEFAULT NULL COMMENT '商品类型',
  `Price` double DEFAULT NULL COMMENT '单价',
  `Name` varchar(100) DEFAULT NULL COMMENT '商品名称',
  `Introduction` text COMMENT '简介',
  `Image` varchar(300) DEFAULT NULL COMMENT '图片',
  `Admin` varchar(100) DEFAULT NULL COMMENT '上传者',
  `AddTime` datetime DEFAULT NULL COMMENT '上传时间',
  `Del` varchar(5) DEFAULT 'false' COMMENT '商品是否已删除',
  `id` int(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='商品表';

INSERT INTO `Product` (`Type`, `Price`, `Name`, `Introduction`, `Image`, `Admin`, `AddTime`, `Del`, `id`) VALUES
('新增分类2',	2,	'测试服装',	'好看的服装',	'http://img03.taobaocdn.com/imgextra/i3/111942793/T2Hik8XlBXXXXXXXXX_!!111942793.jpg',	NULL,	NULL,	'false',	2),
('新增分类1',	15,	'老蚂蚁商品',	'装逼必须品',	'http://img13.360buyimg.com/n0/jfs/t2692/6/203403884/318070/22596ff/5707e2bfN262fe074.jpg',	'as5081523',	'2020-03-26 22:43:57',	'true',	3),
('新增分类1',	120,	'戏剧服A款',	'白色服装',	'http://test.zwhq.mobi/Img/Product/Product20200327143406_ant.jpeg',	'ant',	'2020-03-27 14:34:06',	'false',	8),
('新增分类1',	220,	'戏剧服装B款',	'你最喜欢的绿色',	'http://test.zwhq.mobi/Img/Product/Product20200327143635.jpeg',	'ant',	'2020-03-27 14:36:35',	'true',	9),
('4',	220,	'戏剧2',	'红',	'http://test.zwhq.mobi/Img/Product/Product20200329212542.jpg',	'admin',	'2020-03-29 21:25:42',	'false',	10),
('4',	200,	'戏剧京剧服装 越剧 头饰帽子 三凤冠 凤冠帽子 娘娘凤冠 婚礼影',	'全部是电镀银色 塑料材料 三凤冠  成人都可以使用 可以调节',	'http://test.zwhq.mobi/Img/Product/Product20200329213201.jpg',	'admin',	'2020-03-29 21:32:01',	'false',	11),
('4',	123,	'123',	'123',	'http://test.zwhq.mobi/Img/Product/Product20200329213559.jpg',	'admin',	'2020-03-29 21:35:59',	'false',	12),
('新增分类2',	456,	'456',	'456',	'http://test.zwhq.mobi/Img/Product/Product20200329213621.jpg',	'admin',	'2020-03-29 21:36:21',	'false',	13),
('新增分类1',	20,	'777',	'777',	'http://test.zwhq.mobi/Img/Product/Product20200330170407.jpg',	'admin',	'2020-03-30 17:04:07',	'false',	14),
('新增分类2',	55,	'555',	'555',	'http://test.zwhq.mobi/Img/Product/Product20200330175106.jpg',	'123',	'2020-03-30 17:51:06',	'false',	15);

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `UserID` varchar(100) DEFAULT NULL COMMENT '用户名',
  `Password` varchar(100) DEFAULT NULL COMMENT '密码',
  `Phone` varchar(100) DEFAULT NULL COMMENT '手机',
  `EMail` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `Portrait` varchar(300) DEFAULT NULL COMMENT '头像',
  `Follow` text COMMENT '收藏夹',
  `Cart` text COMMENT '购物车',
  `LastTime` datetime DEFAULT NULL COMMENT '最后一次操作时间',
  `Time` datetime DEFAULT NULL COMMENT '注册时间',
  `id` int(8) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='用户表';

INSERT INTO `User` (`UserID`, `Password`, `Phone`, `EMail`, `Portrait`, `Follow`, `Cart`, `LastTime`, `Time`, `id`) VALUES
('as5081523',	'11111',	'7777',	'',	'http://test.zwhq.mobi/Img/User/Portrait_.jpg',	'[{\"ID\":13}]',	'',	'2020-03-30 15:36:14',	'2020-03-26 10:44:50',	2),
('ant',	'll028288',	'18058385765',	'lichenyu9024@gmail.com',	'http://test.zwhq.mobi/Img/User/Portrait_ant.png',	'[{\"ID\":2},{\"ID\":12}]',	'[{\"ID\":10,\"Num\":1},{\"ID\":9,\"Num\":1}]',	'2020-03-30 18:02:10',	'2020-03-26 19:26:41',	13),
('123',	'123456',	'15236987125',	'123@163.com',	'',	'[{\"ID\":9},{\"ID\":2}]',	'[{\"ID\":15,\"Num\":1}]',	'2020-03-30 17:51:12',	'2020-03-29 21:11:30',	14);

-- 2020-03-30 22:13:38
