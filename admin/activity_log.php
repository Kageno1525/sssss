<?php
include 'db.php';

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Make sure the current user is an admin
if (!is_admin($conn, $_SESSION['username'])) {
    $error = "ليس لديك صلاحية للوصول إلى هذه الصفحة";
    // Log unauthorized access attempt
    log_activity($conn, $_SESSION['username'], 'محاولة وصول غير مصرح', 'محاولة الوصول لصفحة سجل الأنشطة');
    header("Location: dashboard.php");
    exit;
}

// Get user filter if specified
$user_filter = '';
if (isset($_GET['user']) && !empty($_GET['user'])) {
    $user_filter = sanitize_input($_GET['user']);
    $logs = get_user_activity_logs($conn, $user_filter, 100);
} else {
    $logs = get_activity_logs($conn, 100);
}

// Get all users for filter dropdown
$users = array();
$sql = "SELECT DISTINCT username FROM activity_log ORDER BY username";
$result = pg_query($conn, $sql);
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $users[] = $row['username'];
    }
}

// Log the view activity
log_activity($conn, $_SESSION['username'], 'عرض سجل النشاط', ($user_filter ? 'تصفية بواسطة المستخدم: ' . $user_filter : 'عرض كل الأنشطة'));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAGENO - سجل الأنشطة</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>KAGENO</h1>
            <p>سجل الأنشطة</p>
        </header>
        
        <main>
            <div class="dashboard-card">
                <div class="dashboard-header">
                    <h2>سجل أنشطة النظام</h2>
                    
                    <div class="dashboard-controls">
                        <a href="dashboard.php" class="back-link">العودة إلى لوحة التحكم</a>
                    </div>
                </div>
                
                <!-- User Filter -->
                <div class="user-filter">
                    <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="filter-select-group">
                            <label for="user">تصفية حسب المستخدم:</label>
                            <select name="user" id="user" onchange="this.form.submit()">
                                <option value="">جميع المستخدمين</option>
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user); ?>" <?php echo $user_filter === $user ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                
                <!-- Activity Log Table -->
                <?php if (empty($logs)): ?>
                <div class="error-message">لا توجد سجلات أنشطة للعرض</div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ والوقت</th>
                            <th>المستخدم</th>
                            <th>النشاط</th>
                            <th>التفاصيل</th>
                            <th>عنوان IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['action_details'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> KAGENO - جميع الحقوق محفوظة</p>
        </footer>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>