<?php
/**
 * 密码生成工具
 */

$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "密码: {$password}\n";
echo "哈希值: {$hash}\n\n";

// 验证
if (password_verify($password, $hash)) {
    echo "验证通过 ✓\n";
} else {
    echo "验证失败 ✗\n";
}

// 旧的错误哈希
$oldHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "\n--- 旧哈希验证 ---\n";
if (password_verify('password', $oldHash)) {
    echo "旧哈希对应密码: password\n";
}
if (password_verify('admin123', $oldHash)) {
    echo "旧哈希对应密码: admin123\n";
}
