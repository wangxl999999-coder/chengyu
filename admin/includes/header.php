<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - 成语闯关</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="top-header">
        <div class="header-left">
            <div class="logo">
                <span class="logo-icon">🎮</span>
                <span class="logo-text">成语闯关</span>
            </div>
        </div>
        <div class="header-right">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_nickname'] ?? $_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="logout-btn">退出登录</a>
            </div>
        </div>
    </header>
