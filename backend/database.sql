-- 成语闯关游戏数据库
-- 创建时间: 2024

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 数据库
CREATE DATABASE IF NOT EXISTS `chengyu_game` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `chengyu_game`;

-- 管理员表
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码（加密）',
  `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
  `role` tinyint(1) NOT NULL DEFAULT 1 COMMENT '角色：1普通管理员 2超级管理员',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT '' COMMENT '最后登录IP',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- 插入默认管理员账号: admin / admin123
-- 注意: 如果你已经导入了数据库，请运行以下命令重置密码:
-- UPDATE admins SET password = '$2y$10$.vGA1O9wmRjrwAVXD98HNOwsOq9GjKx5JfG7q3QvH3eKv5mX7mQ2' WHERE username = 'admin';
INSERT INTO `admins` (`username`, `password`, `nickname`, `role`) VALUES
('admin', '$2y$10$.vGA1O9wmRjrwAVXD98HNOwsOq9GjKx5JfG7q3QvH3eKv5mX7mQ2', '超级管理员', 2);

-- 用户表
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) NOT NULL COMMENT '微信openid',
  `unionid` varchar(100) DEFAULT '' COMMENT '微信unionid',
  `nickname` varchar(100) DEFAULT '' COMMENT '昵称',
  `avatar_url` varchar(500) DEFAULT '' COMMENT '头像URL',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '性别：0未知 1男 2女',
  `current_level` int(11) NOT NULL DEFAULT 1 COMMENT '当前关卡',
  `completed_levels` int(11) NOT NULL DEFAULT 0 COMMENT '已完成关卡数',
  `total_score` int(11) NOT NULL DEFAULT 0 COMMENT '总分数',
  `login_count` int(11) NOT NULL DEFAULT 0 COMMENT '登录次数',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_openid` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 成语表
DROP TABLE IF EXISTS `idioms`;
CREATE TABLE `idioms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idiom` varchar(50) NOT NULL COMMENT '成语',
  `pinyin` varchar(200) DEFAULT '' COMMENT '拼音',
  `explanation` text COMMENT '释义',
  `source` text COMMENT '出处',
  `example` text COMMENT '示例',
  `difficulty` tinyint(1) NOT NULL DEFAULT 1 COMMENT '难度：1简单 2中等 3困难 4专家',
  `level_id` int(11) unsigned DEFAULT NULL COMMENT '所属关卡ID',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_level_id` (`level_id`),
  KEY `idx_difficulty` (`difficulty`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='成语表';

-- 关卡表
DROP TABLE IF EXISTS `levels`;
CREATE TABLE `levels` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `level_num` int(11) NOT NULL COMMENT '关卡号',
  `title` varchar(100) DEFAULT '' COMMENT '关卡标题',
  `idiom_count` tinyint(1) NOT NULL DEFAULT 2 COMMENT '本关成语数量（1-3）',
  `difficulty` tinyint(1) NOT NULL DEFAULT 1 COMMENT '关卡难度',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_level_num` (`level_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='关卡表';

-- 生词本表
DROP TABLE IF EXISTS `notebooks`;
CREATE TABLE `notebooks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `idiom_id` int(11) unsigned NOT NULL COMMENT '成语ID',
  `idiom` varchar(50) NOT NULL COMMENT '成语',
  `pinyin` varchar(200) DEFAULT '' COMMENT '拼音',
  `explanation` text COMMENT '释义',
  `source` text COMMENT '出处',
  `example` text COMMENT '示例',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_idiom` (`user_id`, `idiom_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='生词本表';

-- 报错反馈表
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `idiom_id` int(11) unsigned DEFAULT NULL COMMENT '成语ID',
  `idiom` varchar(50) DEFAULT '' COMMENT '成语',
  `content` text NOT NULL COMMENT '反馈内容',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '处理状态：0待处理 1已处理 2已忽略',
  `handle_remark` text COMMENT '处理备注',
  `handle_admin_id` int(11) unsigned DEFAULT NULL COMMENT '处理管理员ID',
  `handle_time` datetime DEFAULT NULL COMMENT '处理时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_idiom_id` (`idiom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='报错反馈表';

-- 用户关卡记录表
DROP TABLE IF EXISTS `user_levels`;
CREATE TABLE `user_levels` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `level_id` int(11) unsigned NOT NULL COMMENT '关卡ID',
  `level_num` int(11) NOT NULL COMMENT '关卡号',
  `is_complete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否完成',
  `complete_time` datetime DEFAULT NULL COMMENT '完成时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_level` (`user_id`, `level_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户关卡记录表';

-- 初始化一些成语数据（示例数据）
INSERT INTO `levels` (`level_num`, `title`, `idiom_count`, `difficulty`) VALUES
(1, '入门篇', 2, 1),
(2, '初学篇', 2, 1),
(3, '进阶篇', 3, 1),
(4, '提升篇', 3, 2),
(5, '熟练篇', 3, 2);

INSERT INTO `idioms` (`idiom`, `pinyin`, `explanation`, `source`, `difficulty`, `level_id`, `sort`) VALUES
('一心一意', 'yī xīn yī yì', '形容专心一意，一门心思只做一件事。', '《三国志·魏志·杜恕传》：\"免为庶人，徙章武郡，是岁嘉平元年。\"裴松之注引《杜氏新书》：\"故推一心，任一意，直而行之耳。\"', 1, 1, 1),
('三心二意', 'sān xīn èr yì', '形容犹豫不决，意志不坚定或用心不专一。', '元·关汉卿《救风尘》第一折：\"争奈是匪妓，都三心二意。\"', 1, 1, 2),
('四面八方', 'sì miàn bā fāng', '指各个方面或各个地方。', '宋·释道原《景德传灯录》卷二十：\"忽遇四面八方怎么生？\"', 1, 2, 1),
('五颜六色', 'wǔ yán liù sè', '形容色彩复杂或花样繁多。引申为各色各样。', '清·李汝珍《镜花缘》第十四回：\"惟各人所登之云，五颜六色，其形不一。\"', 1, 2, 2),
('七上八下', 'qī shàng bā xià', '形容心情（担心、顾虑等）起伏不定，心神不安。', '明·施耐庵《水浒全传》第二十六回：\"那胡正卿心头十五个吊桶打水，七上八下。\"', 1, 3, 1),
('九牛一毛', 'jiǔ niú yī máo', '九条牛身上的一根毛。比喻极大数量中极微小的数量，微不足道。', '汉·司马迁《报任少卿书》：\"假令仆伏法受诛，若九牛亡一毛，与蝼蚁何以异？\"', 1, 3, 2),
('十全十美', 'shí quán shí měi', '十分完美，毫无欠缺。', '《周礼·天官冢宰下·医师》：\"岁终，则稽其医事，以制其事，十全为上，十失一次之。\"', 1, 3, 3),
('百发百中', 'bǎi fā bǎi zhòng', '形容射箭或打枪准确，每次都命中目标。也比喻做事有充分把握。', '《战国策·西周策》：\"楚有养由基者，善射，去柳叶百步而射之，百发百中。\"', 2, 4, 1),
('千方百计', 'qiān fāng bǎi jì', '想尽或用尽一切办法。', '《朱子语类·论语十七》：\"譬如捉贼相似，须是着起气力精神，千方百计去赶他。\"', 2, 4, 2),
('万紫千红', 'wàn zǐ qiān hóng', '形容百花齐放，色彩艳丽。也比喻事物丰富多彩。', '宋·朱熹《春日》诗：\"等闲识得东风面，万紫千红总是春。\"', 2, 4, 3),
('画龙点睛', 'huà lóng diǎn jīng', '原形容梁代画家张僧繇作画的神妙。后多比喻写文章或讲话时，在关键处用几句话点明实质，使内容更加生动有力。', '唐·张彦远《历代名画记·张僧繇》：\"金陵安乐寺四白龙不点眼睛，每云：\"点睛即飞去。\"人以为妄诞，固请点之。须臾，雷电破壁，两龙乘云腾去上天，二龙未点眼者见在。\"', 2, 5, 1),
('守株待兔', 'shǒu zhū dài tù', '原比喻希图不经过努力而得到成功的侥幸心理。现也比喻死守狭隘经验，不知变通。', '《韩非子·五蠹》记载：战国时宋国有一个农民，看见一只兔子撞在树根上死了，便放下锄头在树根旁等待，希望再得到撞死的兔子。', 2, 5, 2),
('刻舟求剑', 'kè zhōu qiú jiàn', '比喻不懂事物已发展变化而仍静止地看问题。', '《吕氏春秋·察今》》：\"楚人有涉江者，其剑自舟中坠于水，遽契其舟曰：\"是吾剑之所从坠。\"舟止，从其所契者入水求之。舟已行矣，而剑不行，求剑若此，不亦惑乎？\"', 2, 5, 3);

SET FOREIGN_KEY_CHECKS = 1;
