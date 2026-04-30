<?php
/**
 * 关卡管理页面
 */
require_once 'config.php';
check_login();

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // 添加关卡
    if ($action === 'add') {
        $level_num = intval($_POST['level_num']);
        $title = trim($_POST['title']);
        $idiom_count = intval($_POST['idiom_count']);
        $difficulty = intval($_POST['difficulty']);
        $status = intval($_POST['status']);
        
        if ($level_num <= 0) {
            $error = '关卡号不能为空';
        } else {
            // 检查关卡号是否已存在
            $exists = admin_db()->selectOne("SELECT `id` FROM `levels` WHERE `level_num` = ?", [$level_num]);
            if ($exists) {
                $error = '该关卡号已存在';
            } else {
                admin_db()->insert('levels', [
                    'level_num' => $level_num,
                    'title' => $title,
                    'idiom_count' => $idiom_count,
                    'difficulty' => $difficulty,
                    'status' => $status
                ]);
                
                header('Location: levels.php');
                exit;
            }
        }
    }
    
    // 编辑关卡
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $level_num = intval($_POST['level_num']);
        $title = trim($_POST['title']);
        $idiom_count = intval($_POST['idiom_count']);
        $difficulty = intval($_POST['difficulty']);
        $status = intval($_POST['status']);
        
        admin_db()->update('levels', [
            'level_num' => $level_num,
            'title' => $title,
            'idiom_count' => $idiom_count,
            'difficulty' => $difficulty,
            'status' => $status
        ], '`id` = ?', [$id]);
        
        header('Location: levels.php');
        exit;
    }
    
    // 删除关卡
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        admin_db()->delete('levels', '`id` = ?', [$id]);
        
        header('Location: levels.php');
        exit;
    }
}

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 20;
$offset = ($page - 1) * $pageSize;

// 获取总数
$total = admin_db()->count('levels');
$totalPages = ceil($total / $pageSize);

// 获取关卡列表
$levels = admin_db()->select(
    "SELECT l.*, 
     (SELECT COUNT(*) FROM `idioms` WHERE `level_id` = l.id AND `status` = 1) as idiom_count_actual
     FROM `levels` l 
     ORDER BY l.`level_num` ASC LIMIT {$pageSize} OFFSET {$offset}"
);

// 编辑模式
$editLevel = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editLevel = admin_db()->selectOne("SELECT * FROM `levels` WHERE `id` = ?", [$editId]);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关卡管理 - 成语闯关管理后台</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h2>关卡管理</h2>
                <p>共 <?php echo $total; ?> 个关卡</p>
            </div>
            
            <!-- 工具栏 -->
            <div class="toolbar">
                <div></div>
                <a href="?add=1" class="add-btn">+ 添加关卡</a>
            </div>
            
            <!-- 添加/编辑表单 -->
            <?php if (isset($_GET['add']) || $editLevel): ?>
            <div class="content-card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3><?php echo $editLevel ? '编辑关卡' : '添加关卡'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="<?php echo $editLevel ? 'edit' : 'add'; ?>">
                        <?php if ($editLevel): ?>
                            <input type="hidden" name="id" value="<?php echo $editLevel['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>关卡号 *</label>
                                <input type="number" name="level_num" value="<?php echo $editLevel ? $editLevel['level_num'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>关卡标题</label>
                                <input type="text" name="title" value="<?php echo $editLevel ? htmlspecialchars($editLevel['title']) : ''; ?>" placeholder="如：入门篇">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>成语数量</label>
                                <select name="idiom_count">
                                    <option value="1" <?php echo ($editLevel && $editLevel['idiom_count'] == 1) ? 'selected' : ''; ?>>1个</option>
                                    <option value="2" <?php echo (!$editLevel || $editLevel['idiom_count'] == 2) ? 'selected' : ''; ?>>2个</option>
                                    <option value="3" <?php echo ($editLevel && $editLevel['idiom_count'] == 3) ? 'selected' : ''; ?>>3个</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>难度等级</label>
                                <select name="difficulty">
                                    <option value="1" <?php echo (!$editLevel || $editLevel['difficulty'] == 1) ? 'selected' : ''; ?>>简单</option>
                                    <option value="2" <?php echo ($editLevel && $editLevel['difficulty'] == 2) ? 'selected' : ''; ?>>中等</option>
                                    <option value="3" <?php echo ($editLevel && $editLevel['difficulty'] == 3) ? 'selected' : ''; ?>>困难</option>
                                    <option value="4" <?php echo ($editLevel && $editLevel['difficulty'] == 4) ? 'selected' : ''; ?>>专家</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>状态</label>
                                <select name="status">
                                    <option value="1" <?php echo (!$editLevel || $editLevel['status'] == 1) ? 'selected' : ''; ?>>启用</option>
                                    <option value="0" <?php echo ($editLevel && $editLevel['status'] == 0) ? 'selected' : ''; ?>>禁用</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="levels.php" class="btn-cancel">取消</a>
                            <button type="submit" class="btn-save">保存</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 关卡列表 -->
            <div class="content-card">
                <div class="card-body">
                    <?php if (empty($levels)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🏆</div>
                        <div class="empty-text">暂无关卡数据</div>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>关卡号</th>
                                <th>标题</th>
                                <th>成语数量</th>
                                <th>难度</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($levels as $level): ?>
                            <tr>
                                <td><?php echo $level['id']; ?></td>
                                <td>
                                    <strong>第 <?php echo $level['level_num']; ?> 关</strong>
                                </td>
                                <td><?php echo htmlspecialchars($level['title'] ?: '-'); ?></td>
                                <td>
                                    <?php echo $level['idiom_count_actual']; ?> / <?php echo $level['idiom_count']; ?> 个
                                    <?php if ($level['idiom_count_actual'] < $level['idiom_count']): ?>
                                    <br><span style="color: #ff9800; font-size: 12px;">成语不足</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $diffMap = [1 => '简单', 2 => '中等', 3 => '困难', 4 => '专家'];
                                    $diffClass = [1 => 'status-resolved', 2 => 'status-pending', 3 => 'status-ignored', 4 => ''];
                                    ?>
                                    <span class="status-badge <?php echo $diffClass[$level['difficulty']]; ?>">
                                        <?php echo $diffMap[$level['difficulty']] ?? '未知'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $level['status'] ? 'status-resolved' : 'status-ignored'; ?>">
                                        <?php echo $level['status'] ? '启用' : '禁用'; ?>
                                    </span>
                                </td>
                                <td><?php echo $level['created_at']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $level['id']; ?>" class="action-btn btn-edit">编辑</a>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('确定要删除这个关卡吗？');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $level['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete">删除</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- 分页 -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>">上一页</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>">下一页</a>
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
