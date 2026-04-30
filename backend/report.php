<?php
/**
 * 报错反馈接口
 */
require_once 'config.php';

try {
    $openid = isset($_REQUEST['openid']) ? trim($_REQUEST['openid']) : '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    if (empty($openid)) {
        db()->jsonResponse(false, '请先登录');
    }
    
    // 获取用户
    $user = db()->selectOne("SELECT * FROM `users` WHERE `openid` = ?", [$openid]);
    
    if (!$user) {
        db()->jsonResponse(false, '用户不存在');
    }
    
    // 提交反馈
    if ($method === 'POST') {
        $idiomId = isset($_POST['idiom_id']) ? intval($_POST['idiom_id']) : 0;
        $idiom = isset($_POST['idiom']) ? trim($_POST['idiom']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        
        if (empty($content)) {
            db()->jsonResponse(false, '请输入错误描述');
        }
        
        db()->insert('reports', [
            'user_id' => $user['id'],
            'idiom_id' => $idiomId,
            'idiom' => $idiom,
            'content' => $content,
            'status' => 0
        ]);
        
        db()->jsonResponse(true, '提交成功');
    }
    
    // 获取反馈列表（需要管理员权限，这里暂时只返回用户自己的反馈）
    if ($method === 'GET') {
        $reports = db()->select(
            "SELECT * FROM `reports` WHERE `user_id` = ? ORDER BY `created_at` DESC",
            [$user['id']]
        );
        
        db()->jsonResponse(true, '获取成功', $reports);
    }
    
    db()->jsonResponse(false, '不支持的请求方法');
    
} catch (Exception $e) {
    error_log('Report error: ' . $e->getMessage());
    db()->jsonResponse(false, '服务器错误');
}
