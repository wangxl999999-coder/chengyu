<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">仪表盘</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <a href="users.php">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">用户管理</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'idioms.php' ? 'active' : ''; ?>">
                <a href="idioms.php">
                    <span class="nav-icon">📚</span>
                    <span class="nav-text">成语管理</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'levels.php' ? 'active' : ''; ?>">
                <a href="levels.php">
                    <span class="nav-icon">🏆</span>
                    <span class="nav-text">关卡管理</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php">
                    <span class="nav-icon">⚠️</span>
                    <span class="nav-text">反馈管理</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
