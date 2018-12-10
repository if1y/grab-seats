
CREATE DATABASE IF NOT EXISTS tx_exam default charset utf8 COLLATE utf8_general_ci;



create table `tx_exam`.`tx_users` (
    id char(36) not null primary key,
    account varchar(80) not null comment "帐号",
    password char(32) not null comment "密码",
    is_admin tinyint not null default 0 comment "是否是管理员",
    salt char(16) not null ,
    created int not null default 0,
    UNIQUE KEY  account(account)
) ENGINE = InnoDB comment = "用户表";


create table `tx_exam`.`tx_stage` (
    id char(36) not null primary key ,
    name varchar(128) not null comment "舞台名称",
    status tinyint not null default 1 comment "1显示，2不显示",
    opt  varchar(2048) not null default "{}" comment "扩展字段",
    created int not null default 0
) ENGINE = InnoDB comment = "舞台表";


-- 设计考虑，每行座位数不一定规律
create table `tx_exam`.`tx_stage_seats`(
    id char(36) not null primary key ,
    stage_id char(36) not null comment "舞台id",
    group_tag varchar(10) not null default 'default' comment "作为属于的组",
    row_idx int not null comment "行号",
    col_numbs int not null comment "座位数",
    opt varchar(2048) not null default "" comment "扩展字段",
    created int not null comment "创建时间",
    key (stage_id)
) ENGINE = InnoDB comment = "舞台座位表";


create table `tx_exam`.`tx_events` (
    id char(36) not null primary key,
    stage_id char(36) not null comment "舞台id",
    -- parent_id varchar(64) not null default "-1" comment "父活动id",
    name varchar(128) not null comment "活动名称",
    status tinyint not null default 1 comment "状态",
    seats_per_person int not null default 1 comment "每个用户最多选择多少位置",
    opt varchar(2048) not null default "{}" comment "扩展字段",
    start_time int not null comment "开始时间",
    end_time int not null comment "结束时间",
    created int not null default 0 comment "创建时间",
    key (stage_id)
) ENGINE = InnoDB comment = "活动信息表";



-- 用户座位表，event_id  seat_info 确定唯一的座次
-- seat_info :  group_tag:row:col  
create table `tx_exam`.`tx_user_seats` (
   id char(36) not null primary key,
   account varchar(64) not null comment "用户帐号",
   event_id char(64) not null comment "活动编号",
   seat_info char(64) not null comment "座位编号 ",
   created int not null comment "创建时间",
   key event_account(event_id, account),
   UNIQUE KEY  event_seat(event_id, seat_info)
)ENGINE = InnoDB comment = "用户的座位";