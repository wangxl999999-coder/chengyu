<?php
/**
 * 微信登录接口
 */
require_once 'config.php';

try {
    // 获取微信登录code
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    
    if (empty($code)) {
        db()->jsonResponse(false, '缺少必要参数');
    }
    
    // 调用微信API获取openid
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . WX_APPID . 
           "&secret=" . WX_SECRET . 
           "&js_code=" . $code . 
           "&grant_type=authorization_code";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $wxData = json_decode($result, true);
    
    if (isset($wxData['errcode']) && $wxData['errcode'] != 0) {
        db()->jsonResponse(false, '微信登录失败: ' . ($wxData['errmsg'] ?? '未知错误'));
    }
    
    $openid = $wxData['openid'] ?? '';
    $session_key = $wxData['session_key'] ?? '';
    $unionid = $wxData['unionid'] ?? '';
    
    if (empty($openid)) {
        db()->jsonResponse(false, '获取openid失败');
    }
    
    // 检查用户是否存在
    $user = db()->selectOne("SELECT * FROM `users` WHERE `openid` = ?", [$openid]);
    
    if (!$user) {
        // 新用户，创建记录
        $userId = db()->insert('users', [
            'openid' => $openid,
            'unionid' => $unionid,
            'nickname' => '',
            'avatar_url' => '',
            'gender' => 0,
            'current_level' => 1,
            'completed_levels' => 0,
            'total_score' => 0,
            'login_count' => 1,
            'last_login_time' => date('Y-m-d H:i:s')
        ]);
        
        $user = db()->selectOne("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
    } else {
        // 老用户，更新登录信息
        db()->update('users', [
            'login_count' => $user['login_count'] + 1,
            'last_login_time' => date('Y-m-d H:i:s')
        ], '`id` = ?', [$user['id']]);
    }
    
    // 返回数据
    db()->jsonResponse(true, '登录成功', [
        'openid' => $openid,
        'session_key' => $session_key,
        'user' => [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
            'current_level' => $user['current_level'],
            'completed_levels' => $user['completed_levels']
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    db()->jsonResponse(false, '服务器错误');
}
