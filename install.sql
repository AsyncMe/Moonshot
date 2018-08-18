CREATE TABLE `ng_sys_plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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

#功能权限表
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
insert into  `ng_sys_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`,`category`, `placehold`, `use_priv`,`type`,`link`, `status`, `name`, `icon`, `remark`, `listorder` ,`ctime`,`mtime`) VALUES
(1,0,'admin','index','index','','top','',1,1,'',1,'首页','th','',30,1532693502,1532693502),
(2,0,'admin','site','index','','top','',1,1,'',1,'网站','th','',30,1532693502,1532693502),
(3,0,'admin','mini','index','','top','',1,1,'',1,'小程序','th','',25,1532693502,1532693502),
(4,0,'admin','user','index','','top','',1,1,'',1,'用户','th','',20,1532693502,1532693502),
(5,0,'admin','setting','index','','top','',1,1,'',1,'设置','th','',15,1532693502,1532693502),
(6,0,'admin','plugins','index','','top','',1,1,'',1,'插件','th','',10,1532693502,1532693502);

insert into  `ng_sys_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`,`category`, `placehold`, `use_priv`,`type`,`link`, `status`, `name`, `icon`, `remark`, `listorder` ,`ctime`,`mtime`) VALUES
(10,1,'admin','index','info','','综合','',1,1,'',1,'信息','th','',30,1532693502,1532693502),
(11,1,'admin','index','dashboard','','综合','',1,1,'',1,'仪表盘','th','',30,1532693502,1532693502);

insert into  `ng_sys_menu` ( `parentid`, `app`, `model`, `action`, `data`,`category`, `placehold`, `use_priv`,`type`,`link`, `status`, `name`, `icon`, `remark`, `listorder` ,`ctime`,`mtime`) VALUES
(4,'admin','user','admin','','用户','',1,1,'',1,'管理者','th','',30,1532693502,1532693502),
(4,'admin','user','company','','用户','',1,1,'',1,'运营者','th','',30,1532693502,1532693502);

CREATE TABLE `ng_sys_config` (
  `id` smallint(6) unsigned NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '名称',
  `config` text NULL COMMENT '键值对,json格式',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='后台管理配置表';

insert into `ng_sys_config` (`id`,`name`,`config`,`ctime`,`mtime`) VALUES
(1,'sys_global','{"site_title":"插件管理平台","site_desc":"插件,管理,平台,微信,小程序","site_style":"bluesky"}',1532693502,1532693502);