<?php
/**
 * 关卡接口
 */
require_once 'config.php';

try {
    $openid = isset($_REQUEST['openid']) ? trim($_REQUEST['openid']) : '';
    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
    
    // 获取关卡信息
    if ($method === 'GET') {
        $level = isset($_GET['level']) ? intval($_GET['level']) : 1;
        
        // 获取关卡信息
        $levelInfo = db()->selectOne(
            "SELECT * FROM `levels` WHERE `level_num` = ? AND `status` = 1", 
            [$level]
        );
        
        if (!$levelInfo) {
            // 如果关卡不存在，尝试创建默认关卡或返回第一个关卡
            $levelInfo = db()->selectOne(
                "SELECT * FROM `levels` WHERE `status` = 1 ORDER BY `level_num` ASC LIMIT 1"
            );
            
            if (!$levelInfo) {
                db()->jsonResponse(false, '没有可用关卡');
            }
        }
        
        // 获取该关卡的成语
        $idioms = db()->select(
            "SELECT * FROM `idioms` WHERE `level_id` = ? AND `status` = 1 ORDER BY `sort` ASC",
            [$levelInfo['id']]
        );
        
        // 如果该关卡没有成语，尝试随机获取
        if (empty($idioms)) {
            $idiomCount = $levelInfo['idiom_count'] ?? 2;
            $difficulty = $levelInfo['difficulty'] ?? 1;
            
            $idioms = db()->select(
                "SELECT * FROM `idioms` WHERE `status` = 1 AND `difficulty` <= ? ORDER BY RAND() LIMIT ?",
                [$difficulty, $idiomCount]
            );
        }
        
        db()->jsonResponse(true, '获取成功', [
            'level_id' => $levelInfo['id'],
            'level_num' => $levelInfo['level_num'],
            'title' => $levelInfo['title'],
            'idiom_count' => $levelInfo['idiom_count'],
            'difficulty' => $levelInfo['difficulty'],
            'idioms' => $idioms
        ]);
    }
    
    // 完成关卡
    if ($method === 'POST' && $action === 'complete') {
        if (empty($openid)) {
            db()->jsonResponse(false, '请先登录');
        }
        
        $levelNum = isset($_POST['level']) ? intval($_POST['level']) : 1;
        
        // 获取用户
        $user = db()->selectOne("SELECT * FROM `users` WHERE `openid` = ?", [$openid]);
        
        if (!$user) {
            db()->jsonResponse(false, '用户不存在');
        }
        
        // 获取关卡信息
        $level = db()->selectOne(
            "SELECT * FROM `levels` WHERE `level_num` = ?",
            [$levelNum]
        );
        
        if ($level) {
            // 检查用户关卡记录
            $userLevel = db()->selectOne(
                "SELECT * FROM `user_levels` WHERE `user_id` = ? AND `level_id` = ?",
                [$user['id'], $level['id']]
            );
            
            if ($userLevel) {
                if (!$userLevel['is_complete']) {
                    db()->update('user_levels', [
                        'is_complete' => 1,
                        'complete_time' => date('Y-m-d H:i:s')
                    ], '`id` = ?', [$userLevel['id']]);
                }
            } else {
                db()->insert('user_levels', [
                    'user_id' => $user['id'],
                    'level_id' => $level['id'],
                    'level_num' => $levelNum,
                    'is_complete' => 1,
                    'complete_time' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // 更新用户信息
        $newCurrentLevel = $levelNum + 1;
        $newCompletedLevels = $user['completed_levels'];
        
        if ($levelNum >= $user['completed_levels']) {
            $newCompletedLevels = $levelNum;
        }
        
        db()->update('users', [
            'current_level' => $newCurrentLevel,
            'completed_levels' => $newCompletedLevels
        ], '`id` = ?', [$user['id']]);
        
        // 返回更新后的用户信息
        $updatedUser = db()->selectOne("SELECT * FROM `users` WHERE `id` = ?", [$user['id']]);
        
        db()->jsonResponse(true, '关卡完成', [
            'id' => $updatedUser['id'],
            'nickname' => $updatedUser['nickname'],
            'avatar_url' => $updatedUser['avatar_url'],
            'current_level' => $updatedUser['current_level'],
            'completed_levels' => $updatedUser['completed_levels']
        ]);
    }
    
    db()->jsonResponse(false, '不支持的请求方法');
    
} catch (Exception $e) {
    error_log('Level error: ' . $e->getMessage());
    db()->jsonResponse(false, '服务器错误');
}
