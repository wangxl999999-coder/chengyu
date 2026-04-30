<?php
/**
 * 生词本接口
 */
require_once 'config.php';

try {
    $openid = isset($_REQUEST['openid']) ? trim($_REQUEST['openid']) : '';
    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
    
    if (empty($openid)) {
        db()->jsonResponse(false, '请先登录');
    }
    
    // 获取用户
    $user = db()->selectOne("SELECT * FROM `users` WHERE `openid` = ?", [$openid]);
    
    if (!$user) {
        db()->jsonResponse(false, '用户不存在');
    }
    
    // 获取生词本列表
    if ($method === 'GET' && $action === 'list') {
        $notebooks = db()->select(
            "SELECT * FROM `notebooks` WHERE `user_id` = ? ORDER BY `created_at` DESC",
            [$user['id']]
        );
        
        db()->jsonResponse(true, '获取成功', $notebooks);
    }
    
    // 获取生词本数量
    if ($method === 'GET' && $action === 'count') {
        $count = db()->selectOne(
            "SELECT COUNT(*) as `count` FROM `notebooks` WHERE `user_id` = ?",
            [$user['id']]
        );
        
        db()->jsonResponse(true, '获取成功', $count);
    }
    
    // 检查是否已加入生词本
    if ($method === 'GET' && $action === 'check') {
        $idiomId = isset($_GET['idiom_id']) ? intval($_GET['idiom_id']) : 0;
        
        if ($idiomId <= 0) {
            db()->jsonResponse(false, '参数错误');
        }
        
        $exists = db()->selectOne(
            "SELECT `id` FROM `notebooks` WHERE `user_id` = ? AND `idiom_id` = ?",
            [$user['id'], $idiomId]
        );
        
        db()->jsonResponse(true, '获取成功', [
            'exists' => $exists ? true : false
        ]);
    }
    
    // 添加到生词本
    if ($method === 'POST' && $action === 'add') {
        $idiomId = isset($_POST['idiom_id']) ? intval($_POST['idiom_id']) : 0;
        $idiom = isset($_POST['idiom']) ? trim($_POST['idiom']) : '';
        $pinyin = isset($_POST['pinyin']) ? trim($_POST['pinyin']) : '';
        $explanation = isset($_POST['explanation']) ? trim($_POST['explanation']) : '';
        $source = isset($_POST['source']) ? trim($_POST['source']) : '';
        $example = isset($_POST['example']) ? trim($_POST['example']) : '';
        
        if ($idiomId <= 0 || empty($idiom)) {
            db()->jsonResponse(false, '参数错误');
        }
        
        // 检查是否已存在
        $exists = db()->selectOne(
            "SELECT `id` FROM `notebooks` WHERE `user_id` = ? AND `idiom_id` = ?",
            [$user['id'], $idiomId]
        );
        
        if ($exists) {
            db()->jsonResponse(true, '已在生词本中');
        }
        
        db()->insert('notebooks', [
            'user_id' => $user['id'],
            'idiom_id' => $idiomId,
            'idiom' => $idiom,
            'pinyin' => $pinyin,
            'explanation' => $explanation,
            'source' => $source,
            'example' => $example
        ]);
        
        db()->jsonResponse(true, '添加成功');
    }
    
    // 从生词本移除
    if ($method === 'POST' && $action === 'remove') {
        $idiomId = isset($_POST['idiom_id']) ? intval($_POST['idiom_id']) : 0;
        
        if ($idiomId <= 0) {
            db()->jsonResponse(false, '参数错误');
        }
        
        db()->delete('notebooks', '`user_id` = ? AND `idiom_id` = ?', [
            $user['id'],
            $idiomId
        ]);
        
        db()->jsonResponse(true, '移除成功');
    }
    
    db()->jsonResponse(false, '不支持的请求方法');
    
} catch (Exception $e) {
    error_log('Notebook error: ' . $e->getMessage());
    db()->jsonResponse(false, '服务器错误');
}
