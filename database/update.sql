### --------------- BY jianwei  2018-3-15 ------------------###

# 为 user 表增加 platform 字段,wechat=>微信
ALTER TABLE user add column platform varchar(10) not null default '' comment '平台字段，wechat=>微信';

#  删除 user 表的 login_status 字段
alter table user drop column login_status;

#把 user 表中的登录名称 login 改为 username
ALTER TABLE user change login username varchar(32) NOT NULL DEFAULT '' COMMENT '登录名';

#  为 user 表创建created_at 字段
alter table user add column `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '框架维护字段，创建时间';

#为 user 表创建 updated_at 字段
alter table user add column `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00';

# 为 user 表创建 deleted_at 字段
alter table user add column `deleted_at` timestamp NULL DEFAULT NULL COMMENT '框架维护字段，软删除标记，删除时间';

# 为 user 表增加 openid 字段
alter table user add column `openid` varchar(32) NOT NULL DEFAULT '' COMMENT '微信 openid';

#为 user 表增加 unionid 字段
alter table user add column `unionid` varchar(50) NOT NULL DEFAULT '' COMMENT '微信 unionid';

#为 user 表增加 sex 字段
alter table user add column `sex` tinyint(4) NOT NULL DEFAULT 0 COMMENT '微信性别';

#为 user 表增加 language 字段
alter table user add column `language` varchar(10) NOT NULL DEFAULT '' COMMENT '微信language';

#为 user 表增加 country 字段
alter table user add column `country` varchar(20) NOT NULL DEFAULT '' COMMENT '微信 country';

#为 user 表增加 province 字段
alter table user add column `province` varchar(20) NOT NULL DEFAULT '' COMMENT '微信 province';

#为 user 表增加 city 字段
alter table user add column `city` varchar(20) NOT NULL DEFAULT '' COMMENT '微信 city';

#删除 user 表中的 wxid
alter table user drop column wxid ;


