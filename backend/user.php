<?php
/**
 * 用户信息接口
 */
require_once 'config.php';

try {
    $openid = isset($_REQUEST['openid']) ? trim($_REQUEST['openid']) : '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    if (empty($openid)) {
        db()->jsonResponse(false, '请先登录');
    }
    
    // 获取用户信息
    if ($method === 'GET') {
        $user = db()->selectOne("SELECT * FROM `users` WHERE `openid` = ?", [$openid]);
        
        if (!$user) {
            db()->jsonResponse(false, '用户不存在');
        }
        
        db()->jsonResponse(true, '获取成功', [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
            'gender' => $user['gender'],
            'current_level' => $user['current_level'],
            'completed_levels' => $user['completed_levels'],
            'total_score' => $user['total_score']
        ]);
    }
    
    // 更新用户信息
    if ($method === 'POST') {
        $user = db()->selectOne("SELECT * FROM `users` WHERE `openid` = ?", [$openid]);
        
        if (!$user) {
            db()->jsonResponse(false, '用户不存在');
        }
        
        $updateData = [];
        
        if (isset($_POST['nickname'])) {
            $updateData['nickname'] = trim($_POST['nickname']);
        }
        if (isset($_POST['avatar_url'])) {
            $updateData['avatar_url'] = trim($_POST['avatar_url']);
        }
        if (isset($_POST['gender'])) {
            $updateData['gender'] = intval($_POST['gender']);
        }
        
        if (!empty($updateData)) {
            db()->update('users', $updateData, '`id` = ?', [$user['id']]);
        }
        
        db()->jsonResponse(true, '更新成功');
    }
    
    db()->jsonResponse(false, '不支持的请求方法');
    
} catch (Exception $e) {
    error_log('User error: ' . $e->getMessage());
    db()->jsonResponse(false, '服务器错误');
}
