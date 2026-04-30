<?php
/**
 * 成语管理页面
 */
require_once 'config.php';
check_login();

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // 添加成语
    if ($action === 'add') {
        $idiom = trim($_POST['idiom']);
        $pinyin = trim($_POST['pinyin']);
        $explanation = trim($_POST['explanation']);
        $source = trim($_POST['source']);
        $example = trim($_POST['example']);
        $difficulty = intval($_POST['difficulty']);
        $level_id = intval($_POST['level_id']) ?: null;
        $sort = intval($_POST['sort']);
        $status = intval($_POST['status']);
        
        if (empty($idiom)) {
            $error = '成语不能为空';
        } else {
            $id = admin_db()->insert('idioms', [
                'idiom' => $idiom,
                'pinyin' => $pinyin,
                'explanation' => $explanation,
                'source' => $source,
                'example' => $example,
                'difficulty' => $difficulty,
                'level_id' => $level_id,
                'sort' => $sort,
                'status' => $status
            ]);
            
            header('Location: idioms.php');
            exit;
        }
    }
    
    // 编辑成语
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $idiom = trim($_POST['idiom']);
        $pinyin = trim($_POST['pinyin']);
        $explanation = trim($_POST['explanation']);
        $source = trim($_POST['source']);
        $example = trim($_POST['example']);
        $difficulty = intval($_POST['difficulty']);
        $level_id = intval($_POST['level_id']) ?: null;
        $sort = intval($_POST['sort']);
        $status = intval($_POST['status']);
        
        admin_db()->update('idioms', [
            'idiom' => $idiom,
            'pinyin' => $pinyin,
            'explanation' => $explanation,
            'source' => $source,
            'example' => $example,
            'difficulty' => $difficulty,
            'level_id' => $level_id,
            'sort' => $sort,
            'status' => $status
        ], '`id` = ?', [$id]);
        
        header('Location: idioms.php');
        exit;
    }
    
    // 删除成语
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        admin_db()->delete('idioms', '`id` = ?', [$id]);
        
        header('Location: idioms.php');
        exit;
    }
}

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 20;
$offset = ($page - 1) * $pageSize;

// 搜索
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$difficulty = isset($_GET['difficulty']) ? intval($_GET['difficulty']) : 0;

// 构建查询条件
$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND `idiom` LIKE ?";
    $params[] = "%{$search}%";
}

if ($difficulty > 0) {
    $where .= " AND `difficulty` = ?";
    $params[] = $difficulty;
}

// 获取总数
$total = admin_db()->count('idioms', $where, $params);
$totalPages = ceil($total / $pageSize);

// 获取成语列表
$idioms = admin_db()->select(
    "SELECT i.*, l.level_num FROM `idioms` i 
     LEFT JOIN `levels` l ON i.level_id = l.id 
     WHERE {$where} ORDER BY i.`level_id` ASC, i.`sort` ASC, i.`id` ASC LIMIT {$pageSize} OFFSET {$offset}",
    $params
);

// 获取所有关卡
$levels = admin_db()->select("SELECT * FROM `levels` ORDER BY `level_num` ASC");

// 编辑模式
$editIdiom = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editIdiom = admin_db()->selectOne("SELECT * FROM `idioms` WHERE `id` = ?", [$editId]);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>成语管理 - 成语闯关管理后台</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h2>成语管理</h2>
                <p>共 <?php echo $total; ?> 个成语</p>
            </div>
            
            <!-- 工具栏 -->
            <div class="toolbar">
                <form method="GET" action="" class="search-box" style="gap: 10px;">
                    <input type="text" name="search" placeholder="搜索成语..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="difficulty" style="padding: 10px 16px; border: 1px solid #ddd; border-radius: 6px;">
                        <option value="0">全部难度</option>
                        <option value="1" <?php echo $difficulty == 1 ? 'selected' : ''; ?>>简单</option>
                        <option value="2" <?php echo $difficulty == 2 ? 'selected' : ''; ?>>中等</option>
                        <option value="3" <?php echo $difficulty == 3 ? 'selected' : ''; ?>>困难</option>
                        <option value="4" <?php echo $difficulty == 4 ? 'selected' : ''; ?>>专家</option>
                    </select>
                    <button type="submit" class="search-btn">搜索</button>
                </form>
                <a href="?add=1" class="add-btn">+ 添加成语</a>
            </div>
            
            <!-- 添加/编辑表单 -->
            <?php if (isset($_GET['add']) || $editIdiom): ?>
            <div class="content-card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3><?php echo $editIdiom ? '编辑成语' : '添加成语'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="<?php echo $editIdiom ? 'edit' : 'add'; ?>">
                        <?php if ($editIdiom): ?>
                            <input type="hidden" name="id" value="<?php echo $editIdiom['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>成语 *</label>
                                <input type="text" name="idiom" value="<?php echo $editIdiom ? htmlspecialchars($editIdiom['idiom']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>拼音</label>
                                <input type="text" name="pinyin" value="<?php echo $editIdiom ? htmlspecialchars($editIdiom['pinyin']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>释义</label>
                            <textarea name="explanation" rows="3"><?php echo $editIdiom ? htmlspecialchars($editIdiom['explanation']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>出处</label>
                            <textarea name="source" rows="2"><?php echo $editIdiom ? htmlspecialchars($editIdiom['source']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>示例</label>
                            <textarea name="example" rows="2"><?php echo $editIdiom ? htmlspecialchars($editIdiom['example']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>难度</label>
                                <select name="difficulty">
                                    <option value="1" <?php echo ($editIdiom && $editIdiom['difficulty'] == 1) ? 'selected' : ''; ?>>简单</option>
                                    <option value="2" <?php echo ($editIdiom && $editIdiom['difficulty'] == 2) ? 'selected' : ''; ?>>中等</option>
                                    <option value="3" <?php echo ($editIdiom && $editIdiom['difficulty'] == 3) ? 'selected' : ''; ?>>困难</option>
                                    <option value="4" <?php echo ($editIdiom && $editIdiom['difficulty'] == 4) ? 'selected' : ''; ?>>专家</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>所属关卡</label>
                                <select name="level_id">
                                    <option value="">不指定</option>
                                    <?php foreach ($levels as $level): ?>
                                    <option value="<?php echo $level['id']; ?>" <?php echo ($editIdiom && $editIdiom['level_id'] == $level['id']) ? 'selected' : ''; ?>>
                                        第 <?php echo $level['level_num']; ?> 关 - <?php echo htmlspecialchars($level['title']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>排序</label>
                                <input type="number" name="sort" value="<?php echo $editIdiom ? $editIdiom['sort'] : 0; ?>">
                            </div>
                            <div class="form-group">
                                <label>状态</label>
                                <select name="status">
                                    <option value="1" <?php echo ($editIdiom && $editIdiom['status'] == 1) ? 'selected' : ''; ?>>启用</option>
                                    <option value="0" <?php echo ($editIdiom && $editIdiom['status'] == 0) ? 'selected' : ''; ?>>禁用</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="idioms.php" class="btn-cancel">取消</a>
                            <button type="submit" class="btn-save">保存</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 成语列表 -->
            <div class="content-card">
                <div class="card-body">
                    <?php if (empty($idioms)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <div class="empty-text">暂无成语数据</div>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>成语</th>
                                <th>拼音</th>
                                <th>难度</th>
                                <th>关卡</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($idioms as $idiom): ?>
                            <tr>
                                <td><?php echo $idiom['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($idiom['idiom']); ?></strong>
                                    <?php if ($idiom['explanation']): ?>
                                    <br>
                                    <span style="color: #999; font-size: 12px;"><?php echo mb_substr(htmlspecialchars($idiom['explanation']), 0, 30); ?>...</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($idiom['pinyin'] ?: '-'); ?></td>
                                <td>
                                    <?php
                                    $diffMap = [1 => '简单', 2 => '中等', 3 => '困难', 4 => '专家'];
                                    $diffClass = [1 => 'status-resolved', 2 => 'status-pending', 3 => 'status-ignored', 4 => ''];
                                    ?>
                                    <span class="status-badge <?php echo $diffClass[$idiom['difficulty']]; ?>">
                                        <?php echo $diffMap[$idiom['difficulty']] ?? '未知'; ?>
                                    </span>
                                </td>
                                <td><?php echo $idiom['level_num'] ? '第' . $idiom['level_num'] . '关' : '-'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $idiom['status'] ? 'status-resolved' : 'status-ignored'; ?>">
                                        <?php echo $idiom['status'] ? '启用' : '禁用'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $idiom['id']; ?>" class="action-btn btn-edit">编辑</a>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('确定要删除这个成语吗？');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $idiom['id']; ?>">
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
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&difficulty=<?php echo $difficulty; ?>">上一页</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&difficulty=<?php echo $difficulty; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&difficulty=<?php echo $difficulty; ?>">下一页</a>
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
