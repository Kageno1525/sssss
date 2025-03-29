<?php
include 'db.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول
if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'غير مصرح به',
        'notifications' => []
    ]);
    exit;
}

// استخدام الاتصال الموجود من ملف config.php
$last_check_time = $_SESSION['last_notification_check'] ?? '1970-01-01 00:00:00';
$_SESSION['last_notification_check'] = date('Y-m-d H:i:s');

// تحسين استعلام جلب الإشعارات
$query = "
    SELECT id, name, phone, gender, submission_date, priority
    FROM submissions 
    WHERE is_read = FALSE AND submission_date > $1
    ORDER BY 
        CASE priority
            WHEN 'high' THEN 1
            WHEN 'normal' THEN 2
            WHEN 'low' THEN 3
            ELSE 4
        END,
        submission_date DESC
    LIMIT 10
";

$result = pg_query_params($conn, $query, array($last_check_time));

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء استرداد الإشعارات',
        'notifications' => []
    ]);
    exit;
}

$notifications = [];
$count = 0;

while ($row = pg_fetch_assoc($result)) {
    $count++;
    $time_ago = time_elapsed_string($row['submission_date']);
    
    $priority_texts = [
        'high' => 'مهم',
        'normal' => 'عادي',
        'low' => 'غير مستعجل'
    ];
    
    $priority_text = $priority_texts[$row['priority']] ?? 'عادي';
    $gender_text = ($row['gender'] == 'male') ? 'ذكر' : 'أنثى';
    
    $notifications[] = [
        'id' => $row['id'],
        'message' => "طلب جديد من {$row['name']} ({$gender_text}) - {$priority_text}",
        'detail' => "رقم الهاتف: {$row['phone']}",
        'time' => $time_ago,
        'timestamp' => strtotime($row['submission_date']),
        'priority' => $row['priority'] ?? 'normal'
    ];
}

// تحسين استعلام عدد الطلبات غير المقروءة
$unread_query = "SELECT COUNT(*) AS count FROM submissions WHERE is_read = FALSE";
$unread_result = pg_query($conn, $unread_query);
$unread_count = pg_fetch_assoc($unread_result)['count'] ?? 0;

// لا نغلق الاتصال لأنه قد يُستخدم في مكان آخر
echo json_encode([
    'success' => true,
    'new_count' => $count,
    'total_unread' => (int)$unread_count,
    'notifications' => $notifications
]);

// تحسين دالة حساب الوقت المنقضي
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [
        'y' => 'سنة',
        'm' => 'شهر',
        'w' => 'أسبوع',
        'd' => 'يوم',
        'h' => 'ساعة',
        'i' => 'دقيقة',
        's' => 'ثانية',
    ];

    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'منذ ' . implode(', ', $string) : 'الآن';
}
?>
