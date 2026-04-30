<?php
/**
 * 反馈管理页面
 */
require_once 'config.php';
check_login();

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // 处理反馈
    if ($action === 'process') {
        $id = intval($_POST['id']);
        $status = intval($_POST['status']);
        $handle_remark = trim($_POST['handle_remark']);
        
        admin_db()->update('reports', [
            'status' => $status,
            'handle_remark' => $handle_remark,
            'handle_admin_id' => $_SESSION['admin_id'],
            'handle_time' => date('Y-m-d H:i:s')
        ], '`id` = ?', [$id]);
        
        header('Location: reports.php');
        exit;
    }
}

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 20;
$offset = ($page - 1) * $pageSize;

// 筛选
$status = isset($_GET['status']) ? intval($_GET['status']) : -1;

// 构建查询条件
$where = '1=1';
$params = [];

if ($status >= 0) {
    $where .= " AND r.`status` = ?";
    $params[] = $status;
}

// 获取总数
$total = admin_db()->count('reports r', $where, $params);
$totalPages = ceil($total / $pageSize);

// 获取反馈列表
$reports = admin_db()->select(
    "SELECT r.*, u.nickname, u.avatar_url 
     FROM `reports` r 
     LEFT JOIN `users` u ON r.user_id = u.id 
     WHERE {$where} 
     ORDER BY r.`created_at` DESC 
     LIMIT {$pageSize} OFFSET {$offset}",
    $params
);

// 统计
$pendingCount = admin_db()->count('reports', '`status` = 0');
$resolvedCount = admin_db()->count('reports', '`status` = 1');
$ignoredCount = admin_db()->count('reports', '`status` = 2');

// 查看详情
$viewReport = null;
if (isset($_GET['view'])) {
    $viewId = intval($_GET['view']);
    $viewReport = admin_db()->selectOne(
        "SELECT r.*, u.nickname, u.avatar_url 
         FROM `reports` r 
         LEFT JOIN `users` u ON r.user_id = u.id 
         WHERE r.`id` = ?",
        [$viewId]
    );
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>反馈管理 - 成语闯关管理后台</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h2>反馈管理</h2>
                <p>共 <?php echo $total; ?> 条反馈</p>
            </div>
            
            <!-- 统计卡片 -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 20px;">
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $pendingCount; ?></div>
                        <div class="stat-label">待处理</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $resolvedCount; ?></div>
                        <div class="stat-label">已处理</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $ignoredCount; ?></div>
                        <div class="stat-label">已忽略</div>
                    </div>
                </div>
            </div>
            
            <!-- 工具栏 -->
            <div class="toolbar">
                <form method="GET" action="" class="search-box">
                    <select name="status" style="padding: 10px 16px; border: 1px solid #ddd; border-radius: 6px;">
                        <option value="-1" <?php echo $status == -1 ? 'selected' : ''; ?>>全部状态</option>
                        <option value="0" <?php echo $status === 0 ? 'selected' : ''; ?>>待处理</option>
                        <option value="1" <?php echo $status == 1 ? 'selected' : ''; ?>>已处理</option>
                        <option value="2" <?php echo $status == 2 ? 'selected' : ''; ?>>已忽略</option>
                    </select>
                    <button type="submit" class="search-btn">筛选</button>
                </form>
            </div>
            
            <!-- 查看详情弹窗 -->
            <?php if ($viewReport): ?>
            <div class="modal-overlay" onclick="if(event.target === this) location.href='reports.php?status=<?php echo $status; ?>';">
                <div class="modal-content" style="max-width: 600px;">
                    <div class="modal-header">
                        <h3>反馈详情</h3>
                        <span class="modal-close" onclick="location.href='reports.php?status=<?php echo $status; ?>';">×</span>
                    </div>
                    <div class="modal-body">
                        <div class="detail-row">
                            <div class="detail-label">提交用户</div>
                            <div class="detail-value">
                                <div class="user-cell">
                                    <?php if ($viewReport['avatar_url']): ?>
                                        <img src="<?php echo htmlspecialchars($viewReport['avatar_url']); ?>" alt="">
                                    <?php else: ?>
                                        <span class="default-avatar">👤</span>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($viewReport['nickname'] ?: '未知用户'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">相关成语</div>
                            <div class="detail-value"><?php echo htmlspecialchars($viewReport['idiom'] ?: '-'); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">反馈内容</div>
                            <div class="detail-value" style="white-space: pre-wrap; line-height: 1.8;"><?php echo htmlspecialchars($viewReport['content']); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">当前状态</div>
                            <div class="detail-value">
                                <?php
                                $statusMap = [
                                    0 => ['text' => '待处理', 'class' => 'status-pending'],
                                    1 => ['text' => '已处理', 'class' => 'status-resolved'],
                                    2 => ['text' => '已忽略', 'class' => 'status-ignored']
                                ];
                                $s = $statusMap[$viewReport['status']] ?? $statusMap[0];
                                ?>
                                <span class="status-badge <?php echo $s['class']; ?>"><?php echo $s['text']; ?></span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">提交时间</div>
                            <div class="detail-value"><?php echo $viewReport['created_at']; ?></div>
                        </div>
                        <?php if ($viewReport['handle_remark']): ?>
                        <div class="detail-row">
                            <div class="detail-label">处理备注</div>
                            <div class="detail-value" style="white-space: pre-wrap;"><?php echo htmlspecialchars($viewReport['handle_remark']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($viewReport['status'] == 0): ?>
                        <form method="POST" action="" style="margin-top: 20px;">
                            <input type="hidden" name="action" value="process">
                            <input type="hidden" name="id" value="<?php echo $viewReport['id']; ?>">
                            
                            <div class="form-group">
                                <label>处理状态</label>
                                <select name="status">
                                    <option value="1">标记为已处理</option>
                                    <option value="2">标记为已忽略</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>处理备注</label>
                                <textarea name="handle_remark" rows="3" placeholder="可选，填写处理说明..."></textarea>
                            </div>
                            <div class="form-actions" style="border-top: none; padding: 0; margin: 0;">
                                <button type="button" class="btn-cancel" onclick="location.href='reports.php?status=<?php echo $status; ?>';">取消</button>
                                <button type="submit" class="btn-save">确认处理</button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 反馈列表 -->
            <div class="content-card">
                <div class="card-body">
                    <?php if (empty($reports)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">⚠️</div>
                        <div class="empty-text">暂无反馈数据</div>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户</th>
                                <th>成语</th>
                                <th>反馈内容</th>
                                <th>状态</th>
                                <th>提交时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo $report['id']; ?></td>
                                <td>
                                    <div class="user-cell">
                                        <?php if ($report['avatar_url']): ?>
                                            <img src="<?php echo htmlspecialchars($report['avatar_url']); ?>" alt="">
                                        <?php else: ?>
                                            <span class="default-avatar">👤</span>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($report['nickname'] ?: '未知'); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($report['idiom'] ?: '-'); ?></td>
                                <td style="max-width: 250px;">
                                    <span style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($report['content']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        0 => ['text' => '待处理', 'class' => 'status-pending'],
                                        1 => ['text' => '已处理', 'class' => 'status-resolved'],
                                        2 => ['text' => '已忽略', 'class' => 'status-ignored']
                                    ];
                                    $s = $statusMap[$report['status']] ?? $statusMap[0];
                                    ?>
                                    <span class="status-badge <?php echo $s['class']; ?>"><?php echo $s['text']; ?></span>
                                </td>
                                <td><?php echo $report['created_at']; ?></td>
                                <td>
                                    <a href="?view=<?php echo $report['id']; ?>&status=<?php echo $status; ?>" class="action-btn btn-process">查看</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- 分页 -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>">上一页</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>">下一页</a>
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
