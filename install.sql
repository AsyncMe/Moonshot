CREATE TABLE `ng_sys_plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `properties` VARCHAR (100) NOT NULL DEFAULT 'build in' COMMENT '属性',
  `type` VARCHAR (100) NOT NULL DEFAULT 'plugin' COMMENT '类型',
  `category`  VARCHAR (100) NOT NULL DEFAULT '默认' COMMENT '插件类别名称',
  `sub_cate` VARCHAR (100) NULL DEFAULT '默认' COMMENT '插件子类别名称',
  `title` VARCHAR (150) NOT NULL COMMENT '插件名称',
  `class_name` VARCHAR (150) NOT NULL COMMENT '插件类名称',
  `desc` VARCHAR (255) NULL COMMENT '插件描述',
  `author` VARCHAR (255) NOT NULL COMMENT '插件作者',
  `icon` VARCHAR (255) NOT NULL COMMENT 'icon',
  `plugin_root` VARCHAR (255) NOT NULL COMMENT '所在路径',
  `version` VARCHAR (150) NOT NULL COMMENT '版本',
  `pub_time` VARCHAR (150) NOT NULL COMMENT '版本发布时间',
  `operation` varchar(200) NOT NULL COMMENT '操作人',
  `status` tinyint(1) unsigned DEFAULT 1 NULL COMMENT '状态 0:关闭;1:打开',
  `is_recycle` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '回收站 0:否;1:是',
  `is_lock` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '锁住 0:否;1:是',
  `plugin_process` text null COMMENT '插件的安装记录json格式',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_cate` (`category`),
  KEY `idx_class_name` (`class_name`),
  KEY `idx_title` (`title`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='插件管理表';

CREATE TABLE `ng_sys_plugins_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `parentid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `app` varchar(100) NOT NULL DEFAULT '' COMMENT '应用名称app/插件名称',
  `model` varchar(30)  NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(50)  NULL DEFAULT '' COMMENT '操作名称',
  `data` varchar(250)  NULL DEFAULT '' COMMENT '额外参数',
  `category` varchar(250)  NULL DEFAULT '' COMMENT '分类组合',
  `placehold` varchar(50) null COMMENT '替换符合，通常用于bid',
  `use_priv` tinyint(1) NOT NULL DEFAULT '1' COMMENT ' 1：权限认证,0:不使用权限',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '菜单类型  0：作为分组; 1：只作为菜单；2:外链',
  `link` varchar(255) NULL  COMMENT '外链URL，仅在type为2时生效',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态，1显示，0不显示',
  `nav_show_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '导航栏状态，1显示，0不显示',
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `icon` varchar(50) DEFAULT NULL COMMENT '菜单图标',
  `remark` varchar(255)  NULL DEFAULT '' COMMENT '备注',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_plugin_id` (`plugin_id`),
  KEY `idx_name` (`name`),
  KEY `idx_app` (`app`),
  KEY `idx_listorder` (`listorder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='插件管理菜单表';


CREATE TABLE `ng_sys_plugins_rel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bussine_id` int(10) unsigned NOT NULL COMMENT '大Bid',
  `plugin_id` int(10) unsigned NOT NULL COMMENT '插件id',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_bp_id` (`bussine_id`,`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='插件引用关系表';


CREATE TABLE `ng_sys_admin_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `account` varchar(255) NOT NULL COMMENT '账号',
  `avatar` varchar(255) NOT NULL COMMENT '头像',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `slat` varchar(10) NULL COMMENT '随机密码',
  `nickname` varchar(150) NOT NULL COMMENT '名称',
  `expire_time` int(11) NOT NULL COMMENT '有效时间',
  `status` tinyint(1) unsigned DEFAULT 1 NULL COMMENT '状态 0:关闭;1:打开',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_account` (`account`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_nickname` (`nickname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统管理员表';

insert into `ng_sys_admin_account` (`company_id`,`account`,`avatar`,`password`,`slat`,`nickname`,`expire_time`,`status`,`ctime`,`mtime`)
VALUES (123,'admin','default','57395bc5b73f0e880830285482f716f5','tq8smr','管理员',0,1,1533183790,1533183790);

CREATE TABLE `ng_sys_admin_account_faillog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_uid` int(10) unsigned NOT NULL COMMENT '管理用户id',
  `try_count` int(10) unsigned NOT NULL COMMENT '尝试次数',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_admin_uid` (`admin_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统管理员登陆错误表';

CREATE TABLE `ng_sys_privilege_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `admin_uid` int(10) unsigned NOT NULL COMMENT '管理用户id',
  `priv_path` VARCHAR(255) NULL COMMENT '权限路径',
  `priv_custom_data` text NULL COMMENT '自定义权限池,json格式',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_admin_uid` (`admin_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统权限控制表';

CREATE TABLE `ng_sys_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `type` varchar(255) NOT NULL COMMENT '类型',
  `info` text NULL COMMENT '详细',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统日志表';

CREATE TABLE `ng_sys_company_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(255) NOT NULL COMMENT '账号',
  `group_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `group_type` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '0:主账号,1:子账号',
  `avatar` varchar(255) NOT NULL COMMENT '账号',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `nickname` varchar(150) NOT NULL COMMENT '名称',
  `alias` varchar(150) NOT NULL COMMENT '别名',
  `hash_val` varchar(16) NOT NULL COMMENT '哈希值',
  `version` varchar(16) NOT NULL DEFAULT '1.0' COMMENT '版本号',
  `slat` varchar(10) NULL COMMENT '随机密码',
  `contact_user` varchar(150) NOT NULL COMMENT '联系人',
  `contact_phone` varchar(150) NOT NULL COMMENT '联系电话',
  `desc` varchar(255) COMMENT '简介',
  `logo_url` varchar(200) DEFAULT NULL COMMENT 'logo 地址',
  `operation` varchar(200) NOT NULL COMMENT '操作人',
  `expire_time` int(11) NOT NULL COMMENT '有效时间',
  `status` tinyint(1) unsigned DEFAULT 1 NULL COMMENT '状态 0:关闭;1:打开',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_account` (`account`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_hash_val` (`hash_val`),
  KEY `idx_nickname` (`nickname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企业账号信息表';

#管理功能权限表
CREATE TABLE `ng_sys_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `app` varchar(100) NOT NULL DEFAULT '' COMMENT '应用名称app/插件名称',
  `model` varchar(30)  NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(50)  NULL DEFAULT '' COMMENT '操作名称',
  `data` varchar(250)  NULL DEFAULT '' COMMENT '额外参数',
  `category` varchar(250)  NULL DEFAULT '' COMMENT '分类组合',
  `placehold` varchar(50) null COMMENT '替换符合，通常用于bid',
  `use_priv` tinyint(1) NOT NULL DEFAULT '1' COMMENT ' 1：权限认证,0:不使用权限',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '菜单类型  0：作为分组; 1：只作为菜单；2:外链',
  `link` varchar(255) NULL  COMMENT '外链URL，仅在type为2时生效',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态，1显示，0不显示',
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `icon` varchar(50) DEFAULT NULL COMMENT '菜单图标',
  `remark` varchar(255)  NULL DEFAULT '' COMMENT '备注',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_app` (`app`),
  KEY `idx_listorder` (`listorder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='后台管理菜单表';

#insert datas

INSERT INTO `ng_sys_menu` VALUES
(1,0,'admin','index','index','','top','',1,1,'',1,'首页','th','',30,1532693502,1535022134),
(2,0,'admin','site','index','','top','',1,1,'',1,'网站','th','',21,1532693502,1535022134),
(3,0,'admin','mini','index','','top','',1,1,'',1,'小程序','th','',25,1532693502,1535022134),
(4,0,'admin','user','index','','top','',1,1,'',1,'用户','th','',13,1532693502,1535022134),
(5,0,'admin','setting','index','','top','',1,1,'',1,'设置','th','',15,1532693502,1535022134),
(6,0,'admin','plugins','index','','top','',1,1,'',1,'插件','th','',10,1532693502,1535022134),
(10,1,'admin','index','info','','综合','',1,1,'',1,'信息','th','',30,1532693502,1535022134),
(11,1,'admin','index','dashboard','','综合','',1,1,'',1,'仪表盘','th','',30,1532693502,1535022134),
(12,4,'admin','user','admin','','用户','',1,1,'',1,'管理者','th','',30,1532693502,1535442995),
(13,4,'admin','user','company','','用户','',1,1,'',1,'运营者','th','',30,1532693502,1535022134),
(14,5,'admin','setting','lists','','',NULL,1,1,NULL,1,'配置',NULL,'',0,1535443949,1535443949),
(15,5,'admin','setting','menu','','',NULL,1,1,NULL,1,'管理菜单',NULL,'',0,1535444004,1535444004),
(16,5,'admin','setting','manage_menu','','',NULL,1,1,NULL,1,'运营菜单',NULL,'',0,1535445793,1535445793);



#经营功能菜单表
CREATE TABLE `ng_manage_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `app` varchar(100) NOT NULL DEFAULT '' COMMENT '应用名称app/插件名称',
  `model` varchar(30)  NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(50)  NULL DEFAULT '' COMMENT '操作名称',
  `data` varchar(250)  NULL DEFAULT '' COMMENT '额外参数',
  `category` varchar(250)  NULL DEFAULT '' COMMENT '分类组合',
  `placehold` varchar(50) null COMMENT '替换符合，通常用于bid',
  `use_priv` tinyint(1) NOT NULL DEFAULT '1' COMMENT ' 1：权限认证,0:不使用权限',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '菜单类型  0：作为分组; 1：只作为菜单；2:外链',
  `link` varchar(255) NULL  COMMENT '外链URL，仅在type为2时生效',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态，1显示，0不显示',
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `icon` varchar(50) DEFAULT NULL COMMENT '菜单图标',
  `remark` varchar(255)  NULL DEFAULT '' COMMENT '备注',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_app` (`app`),
  KEY `idx_listorder` (`listorder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='运营管理菜单表';

#经营者功能权限表
CREATE TABLE `ng_manage_func_privs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '运营者账号id',
  `privs` text NULL COMMENT '权限池',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_company_account` (`company_id`,`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='经营者功能权限表';

#经营者数量限制表
CREATE TABLE `ng_manage_num_limit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '运营者账号id',
  `config` text NULL COMMENT '键值对,json格式',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_account` (`company_id`,`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='经营者数量限制表';


CREATE TABLE `ng_sys_config` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '名称',
  `config` text NULL COMMENT '键值对,json格式',
  `desc` varchar(150)  NULL COMMENT '描述',
  `config_desc` text NULL COMMENT '键值对描述,json格式',
  `lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '锁定',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='后台管理配置表';

insert into `ng_sys_config` (`id`,`name`,`config`,`lock`,`ctime`,`mtime`) VALUES
(1,'sys_global','{\"site_title\":\"插件管理平台\",\"site_desc\":\"插件,管理,平台,微信,小程序\",\"site_style\":\"bluesky\",\"root\":\"xxx\"}',1,1532693502,1532693502);


CREATE TABLE `ng_frontend_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sys_uid` varchar(16)  NOT NULL COMMENT '系统用户uid',
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `work_id` varchar(16)  NOT NULL COMMENT '业务id:一般为8位',
  `username` varchar(255) NOT NULL COMMENT '用户名称',
  `password` varchar(255)  NULL COMMENT '密码',
  `nickname` varchar(255)  NULL COMMENT '用户昵称',
  `openid` varchar(255)  NULL COMMENT '各个平台用户openid',
  `unionid` varchar(255)  NULL COMMENT '各个平台用户unionid',
  `sex` SMALLINT(1) NOT NULL DEFAULT 0 COMMENT '0:保密,1:男,2:女',
  `avatar` varchar(255)  NULL COMMENT '头像url',
  `comeform` VARCHAR(255) NOT NULL DEFAULT 'unkonw' comment '来自什么平台',
  `config` text NULL COMMENT '键值对,json格式',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:正常,2:不正常',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  UNIQUE KEY `uni_sys_uid` (`sys_uid`),
  UNIQUE KEY `uni_company_work` (`company_id`,`work_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='前端用户表';

CREATE TABLE `ng_frontend_user_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sys_uid` varchar(16)  NOT NULL COMMENT '系统用户uid',
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `work_id` varchar(16)  NOT NULL COMMENT '业务id:一般为8位',
  `username` varchar(255) NOT NULL COMMENT '用户名称',
  `detail` text NULL COMMENT '键值对,json格式',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_sys_uid` (`sys_uid`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_work_id` (`work_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='前端用户详细资料表';



CREATE TABLE `ng_works` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_id` varchar(10) NOT NULL DEFAULT 1 COMMENT '业务id',
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `type_id` int(10) unsigned NOT NULL DEFAULT 1 COMMENT '类型',
  `name` varchar(50) NOT NULL COMMENT '业务名称',
  `config` text NULL COMMENT '键值对,json格式',
  `lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '锁定',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:正常,2:不正常',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_type` (`company_id`,`type_id`),
  UNIQUE KEY `uni_work_id` (`work_id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='业务表';

CREATE TABLE `ng_works_admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `work_id` varchar(10)  NOT NULL DEFAULT 1 COMMENT '业务id',
  `account_id` varchar(50) NOT NULL COMMENT '用户id',
  `account_nickname` varchar(50) NOT NULL COMMENT '用户名称',
  `privs` text NULL COMMENT '权限设置,json格式',
  `expire_time` int(11) NOT NULL COMMENT '有效时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:正常,2:不正常',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_type` (`company_id`,`type_id`),
  KEY `idx_work_id` (`work_id`),
  KEY `idx_account_id` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='业务管理人员表';

CREATE TABLE `ng_works_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_id` varchar(10) unsigned NOT NULL DEFAULT 1 COMMENT '业务id',
  `name` varchar(50) NOT NULL COMMENT '业务名称',
  `config` text NULL COMMENT '键值对,json格式',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:正常,2:不正常',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_work_id` (`work_id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='业务配置表';

CREATE TABLE `ng_works_plugin_ref` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_id` varchar(10) unsigned NOT NULL DEFAULT 1 COMMENT '业务id',
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `plugin_id` int(10) unsigned NOT NULL DEFAULT 1 COMMENT '插件id',
  `name` varchar(50) NOT NULL COMMENT '业务名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:正常,2:不正常',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_work` (`company_id`,`work_id`),
  KEY `idx_plugin_id` (`plugin_id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='业务插件关联表';


CREATE TABLE `ng_sys_keywoks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL COMMENT '名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:正常,2:不正常',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='关键字表';

CREATE TABLE `ng_sys_keywoks_ref` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(150) NOT NULL COMMENT '数据表类型',
  `keywork_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '关键词id,',
  `ref_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '关键词id,',
  `ref_type` varchar(150) NOT NULL COMMENT '资源类型',
  PRIMARY KEY (`id`),
  KEY `idx_model_type` (`model_type`),
  KEY `idx_ref_id` (`ref_id`),
  KEY `idx_keywork_id` (`keywork_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='关键字引用关系表';


CREATE TABLE `ng_manage_assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` char(16) NOT NULL COMMENT '唯一id',
  `company_id` int(10) unsigned NOT NULL COMMENT '商业id',
  `account_id` bigint(20) unsigned NOT NULL COMMENT '运营者账号id',
  `cate_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分类id,默认分类',
  `name` varchar(150) NOT NULL COMMENT '名称',
  `desc` varchar(255)  NULL COMMENT '描述',
  `thumb` varchar(255)  NULL COMMENT '缩略图',
  `file` varchar(255) NOT NULL COMMENT '文件路径',
  `filesize` int(10) unsigned NOT NULL COMMENT '文件大小,字节',
  `filetype` varchar(150) NOT NULL COMMENT '文件类型',
  `hash` CHAR(32)  NOT NULL COMMENT '文件唯一校样',
  `smeta` text  NULL COMMENT '其他属性，比如经纬度,json格式',
  `keyworks` text  NULL COMMENT '关键词,json格式',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:正常,2:不正常',
  `is_review` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '审核 0:否;1:是',
  `is_hot` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '热销 0:否;1:是',
  `is_hot_exipre` int(10) unsigned DEFAULT 0 NULL COMMENT '热销过期时间',
  `is_favor` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '推荐 0:否;1:是',
  `is_top` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '置顶 0:否;1:是',
  `is_recycle` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '回收站 0:否;1:是',
  `is_recycle_exipre` int(10) unsigned DEFAULT 0 NULL COMMENT '回收站删除时间',
  `is_lock` tinyint(1) unsigned DEFAULT 0 NULL COMMENT '锁住 0:否;1:是',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_account` (`company_id`,`account_id`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_cate_id` (`cate_id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='经营资源表';

