<?php
/**
 * 重置管理员密码脚本
 * 使用方法：
 * 1. 将此文件上传到服务器
 * 2. 在浏览器中访问此文件
 * 3. 或者在命令行运行: php reset_password.php
 */

require_once 'config.php';

// 要设置的密码
$newPassword = 'admin123';

// 生成密码哈希
$passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

echo "================================\n";
echo "管理员密码重置工具\n";
echo "================================\n\n";

echo "新密码: {$newPassword}\n";
echo "新哈希: {$passwordHash}\n\n";

// 验证哈希是否正确
if (password_verify($newPassword, $passwordHash)) {
    echo "✓ 密码哈希验证通过\n\n";
} else {
    echo "✗ 密码哈希验证失败\n\n";
    exit(1);
}

// 尝试更新数据库
try {
    $db = admin_db();
    
    // 检查admin用户是否存在
    $admin = $db->selectOne("SELECT * FROM `admins` WHERE `username` = ?", ['admin']);
    
    if ($admin) {
        // 更新密码
        $result = $db->update('admins', [
            'password' => $passwordHash
        ], '`username` = ?', ['admin']);
        
        if ($result) {
            echo "✓ 数据库密码更新成功！\n";
            echo "================================\n";
            echo "登录信息:\n";
            echo "  用户名: admin\n";
            echo "  密码: {$newPassword}\n";
            echo "================================\n";
        } else {
            echo "⚠ 数据库更新可能未生效（密码可能已相同）\n";
        }
    } else {
        echo "✗ 数据库中不存在 admin 用户\n";
        echo "请先运行 database.sql 初始化数据库\n";
    }
    
} catch (Exception $e) {
    echo "✗ 数据库错误: " . $e->getMessage() . "\n\n";
    echo "请手动执行以下SQL来更新密码:\n";
    echo "UPDATE admins SET password = '{$passwordHash}' WHERE username = 'admin';\n";
}

echo "\n";
