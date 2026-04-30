<?php
/**
 * 用户管理页面
 */
require_once 'config.php';
check_login();

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 20;
$offset = ($page - 1) * $pageSize;

// 搜索
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 构建查询条件
$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (`nickname` LIKE ? OR `openid` LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

// 获取总数
$total = admin_db()->count('users', $where, $params);
$totalPages = ceil($total / $pageSize);

// 获取用户列表
$users = admin_db()->select(
    "SELECT * FROM `users` WHERE {$where} ORDER BY `created_at` DESC LIMIT {$pageSize} OFFSET {$offset}",
    $params
);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - 成语闯关管理后台</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h2>用户管理</h2>
                <p>共 <?php echo $total; ?> 个用户</p>
            </div>
            
            <!-- 工具栏 -->
            <div class="toolbar">
                <form method="GET" action="" class="search-box">
                    <input type="text" name="search" placeholder="搜索昵称或openid..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">搜索</button>
                </form>
            </div>
            
            <!-- 用户列表 -->
            <div class="content-card">
                <div class="card-body">
                    <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <div class="empty-text">暂无用户数据</div>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>用户</th>
                                <th>openid</th>
                                <th>当前关卡</th>
                                <th>已通关</th>
                                <th>登录次数</th>
                                <th>最后登录</th>
                                <th>注册时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <?php if ($user['avatar_url']): ?>
                                            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="">
                                        <?php else: ?>
                                            <span class="default-avatar">👤</span>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($user['nickname'] ?: '游客'); ?></span>
                                    </div>
                                </td>
                                <td style="font-size: 12px; color: #999;"><?php echo htmlspecialchars($user['openid']); ?></td>
                                <td>第 <?php echo $user['current_level']; ?> 关</td>
                                <td><?php echo $user['completed_levels']; ?> 关</td>
                                <td><?php echo $user['login_count']; ?> 次</td>
                                <td><?php echo $user['last_login_time'] ?: '-'; ?></td>
                                <td><?php echo $user['created_at']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- 分页 -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">上一页</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">下一页</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
