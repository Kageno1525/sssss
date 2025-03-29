<?php
include 'db.php';

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Get submission ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Get submission details
$submission = get_submission_by_id($conn, $id);

// Redirect if submission doesn't exist
if (!$submission) {
    redirect('dashboard.php');
}

// Log activity
log_activity($conn, $_SESSION['username'], 'طباعة الطلب', "تمت طباعة الطلب #$id");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الطلب #<?php echo $id; ?> - KAGENO</title>
    <link rel="stylesheet" href="../css/print-style.css">
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1>KAGENO</h1>
            <h2>تفاصيل الطلب #<?php echo $id; ?></h2>
            <div class="print-date">تاريخ الطباعة: <?php echo date('Y-m-d H:i'); ?></div>
            <div class="print-user">تمت الطباعة بواسطة: <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        </div>
        
        <div class="print-content">
            <div class="info-section">
                <h3>معلومات المتقدم</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">الاسم الكامل:</div>
                        <div class="info-value"><?php echo htmlspecialchars($submission['name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">رقم الهاتف:</div>
                        <div class="info-value"><?php echo htmlspecialchars($submission['phone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">الجنس:</div>
                        <div class="info-value"><?php echo $submission['gender'] == 'male' ? 'ذكر' : 'أنثى'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">المهنة:</div>
                        <div class="info-value"><?php echo htmlspecialchars($submission['occupation']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h3>تفاصيل الطلب</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">تاريخ التقديم:</div>
                        <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($submission['submission_date'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">حالة القراءة:</div>
                        <div class="info-value">
                            <?php echo $submission['is_read'] == 't' ? 'مقروء' : 'غير مقروء'; ?>
                            <?php if ($submission['is_read'] == 't' && !empty($submission['read_by'])): ?>
                            <span>(بواسطة: <?php echo htmlspecialchars($submission['read_by']); ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">الأولوية:</div>
                        <div class="info-value">
                            <?php 
                                $priorityText = 'عادي';
                                $priorityClass = 'normal-priority';
                                
                                if (isset($submission['priority'])) {
                                    if ($submission['priority'] == 'high') {
                                        $priorityText = 'مهم';
                                        $priorityClass = 'high-priority';
                                    } elseif ($submission['priority'] == 'low') {
                                        $priorityText = 'غير مستعجل';
                                        $priorityClass = 'low-priority';
                                    }
                                }
                            ?>
                            <span class="priority-print <?php echo $priorityClass; ?>"><?php echo $priorityText; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
            // Get notes for this submission
            $notes = get_submission_notes($conn, $id);
            if (!empty($notes)): 
            ?>
            <div class="info-section">
                <h3>الملاحظات</h3>
                <div class="notes-list">
                    <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <div class="note-header">
                            <div class="note-user"><?php echo htmlspecialchars($note['added_by']); ?></div>
                            <div class="note-date"><?php echo date('Y-m-d H:i', strtotime($note['created_at'])); ?></div>
                        </div>
                        <div class="note-content"><?php echo nl2br(htmlspecialchars($note['note_text'])); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="print-footer">
            <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> KAGENO</p>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>