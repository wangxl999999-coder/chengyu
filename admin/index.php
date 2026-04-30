<?php
/**
 * 管理后台首页
 */
require_once 'config.php';
check_login();

// 获取统计数据
$userCount = admin_db()->count('users');
$idiomCount = admin_db()->count('idioms', '`status` = 1');
$levelCount = admin_db()->count('levels', '`status` = 1');
$pendingReports = admin_db()->count('reports', '`status` = 0');

// 获取最近注册的用户
$recentUsers = admin_db()->select(
    "SELECT * FROM `users` ORDER BY `created_at` DESC LIMIT 5"
);

// 获取最近的反馈
$recentReports = admin_db()->select(
    "SELECT r.*, u.nickname FROM `reports` r 
     LEFT JOIN `users` u ON r.user_id = u.id 
     ORDER BY r.created_at DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>仪表盘 - 成语闯关管理后台</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h2>仪表盘</h2>
                <p>欢迎回来，<?php echo htmlspecialchars($_SESSION['admin_nickname'] ?? $_SESSION['admin_username']); ?></p>
            </div>
            
            <!-- 统计卡片 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        👥
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $userCount; ?></div>
                        <div class="stat-label">用户总数</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        📚
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $idiomCount; ?></div>
                        <div class="stat-label">成语总数</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        🏆
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $levelCount; ?></div>
                        <div class="stat-label">关卡总数</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        ⚠️
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $pendingReports; ?></div>
                        <div class="stat-label">待处理反馈</div>
                    </div>
                </div>
            </div>
            
            <!-- 最近数据 -->
            <div class="content-row">
                <div class="content-card">
                    <div class="card-header">
                        <h3>最近注册用户</h3>
                        <a href="users.php" class="view-all">查看全部</a>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>昵称</th>
                                    <th>当前关卡</th>
                                    <th>已通关</th>
                                    <th>注册时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
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
                                    <td>第 <?php echo $user['current_level']; ?> 关</td>
                                    <td><?php echo $user['completed_levels']; ?> 关</td>
                                    <td><?php echo $user['created_at']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="content-card">
                    <div class="card-header">
                        <h3>最近反馈</h3>
                        <a href="reports.php" class="view-all">查看全部</a>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>用户</th>
                                    <th>成语</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentReports as $report): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($report['nickname'] ?: '未知'); ?></td>
                                    <td><?php echo htmlspecialchars($report['idiom'] ?: '-'); ?></td>
                                    <td>
                                        <?php
                                        $statusMap = [
                                            0 => ['text' => '待处理', 'class' => 'status-pending'],
                                            1 => ['text' => '已处理', 'class' => 'status-resolved'],
                                            2 => ['text' => '已忽略', 'class' => 'status-ignored']
                                        ];
                                        $status = $statusMap[$report['status']] ?? $statusMap[0];
                                        ?>
                                        <span class="status-badge <?php echo $status['class']; ?>">
                                            <?php echo $status['text']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $report['created_at']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
