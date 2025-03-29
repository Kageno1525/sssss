<?php
include 'db.php';

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('login.php');
}

// Get submission ID
if (!isset($_GET['id'])) {
    // Redirect to dashboard if no ID provided
    redirect('dashboard.php');
}

$submission_id = sanitize_input($_GET['id']);
$conn = pg_connect($_ENV['DATABASE_URL']);

// Get submission details
$submission = get_submission_by_id($conn, $submission_id);

// If submission doesn't exist, redirect to dashboard
if (!$submission) {
    redirect('dashboard.php');
}

// Handle marking as read
if (isset($_GET['action']) && $_GET['action'] == 'mark_read' && $submission['is_read'] == 'f') {
    mark_submission_as_read($conn, $submission_id, $_SESSION['username']);
    // Refresh page to update data
    redirect("view_submission.php?id={$submission_id}");
}

// Handle adding notes
$note_success = '';
$note_error = '';
$priority_success = '';
$priority_error = '';

// Handle updating priority
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_priority'])) {
    $priority = sanitize_input($_POST['priority']);
    
    if (update_submission_priority($conn, $submission_id, $priority)) {
        $priority_success = "تم تحديث أولوية الطلب بنجاح";
        
        // Log the activity
        $priority_text = '';
        if ($priority == 'high') $priority_text = 'مهم';
        elseif ($priority == 'normal') $priority_text = 'عادي';
        else $priority_text = 'غير مستعجل';
        
        log_activity($conn, $_SESSION['username'], 'تعديل أولوية طلب', 
                    'تم تغيير أولوية الطلب رقم ' . $submission_id . ' إلى "' . $priority_text . '"');
        
        // Refresh submission data
        $submission = get_submission_by_id($conn, $submission_id);
    } else {
        $priority_error = "حدث خطأ أثناء تحديث أولوية الطلب";
        error_log("Error updating priority: " . pg_last_error($conn));
    }
}

// Handle adding notes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_note'])) {
    $note = sanitize_input($_POST['note']);
    
    if (!empty($note)) {
        $username = $_SESSION['username'];
        
        // Add note to the submission_notes table
        $sql = "INSERT INTO submission_notes (submission_id, note_text, added_by) VALUES ($1, $2, $3)";
        $result = pg_query_params($conn, $sql, array(
            $submission_id,
            $note,
            $username
        ));
        
        if ($result) {
            $note_success = "تمت إضافة الملاحظة بنجاح";
            // Log activity
            log_activity($conn, $_SESSION['username'], 'إضافة ملاحظة', 'تمت إضافة ملاحظة جديدة للطلب رقم ' . $submission_id);
            // Refresh submission data
            $submission = get_submission_by_id($conn, $submission_id);
        } else {
            $note_error = "حدث خطأ أثناء إضافة الملاحظة";
            error_log("Error adding note: " . pg_last_error($conn));
        }
    } else {
        $note_error = "الرجاء إدخال ملاحظة قبل الإرسال";
    }
}

// Get the filter value to return to the same filter view
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب - KAGENO</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>KAGENO</h1>
            <p>تفاصيل الطلب</p>
        </header>
        
        <main>
            <div class="back-nav">
                <a href="dashboard.php?filter=<?php echo $filter; ?>" class="back-link">« العودة إلى لوحة التحكم</a>
            </div>
            
            <div class="submission-details-card">
                <div class="submission-header">
                    <h2>معلومات الطلب #<?php echo $submission['id']; ?></h2>
                    <div class="submission-status">
                        <?php if ($submission['is_read'] == 't'): ?>
                            <span class="status-badge read">مقروء</span>
                            <?php if (!empty($submission['read_by'])): ?>
                                <span class="read-by">بواسطة: <?php echo htmlspecialchars($submission['read_by']); ?></span>
                                <span class="read-at">في: <?php echo date('Y-m-d H:i', strtotime($submission['read_at'])); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="status-badge unread">غير مقروء</span>
                            <a href="?id=<?php echo $submission['id']; ?>&action=mark_read&filter=<?php echo $filter; ?>" class="action-btn mark-read-btn">تأشير كمقروءة</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="submission-info">
                    <div class="info-group">
                        <label>الاسم:</label>
                        <div class="info-value"><?php echo htmlspecialchars($submission['name']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <label>رقم الهاتف:</label>
                        <div class="info-value"><?php echo htmlspecialchars($submission['phone']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <label>الجنس:</label>
                        <div class="info-value"><?php echo $submission['gender'] == 'male' ? 'ذكر' : 'أنثى'; ?></div>
                    </div>
                    
                    <div class="info-group">
                        <label>المهنة:</label>
                        <div class="info-value"><?php echo htmlspecialchars($submission['occupation']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <label>تاريخ التسجيل:</label>
                        <div class="info-value"><?php echo date('Y-m-d H:i:s', strtotime($submission['submission_date'])); ?></div>
                    </div>
                    
                    <div class="info-group priority-group">
                        <label>أولوية الطلب:</label>
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
                        <div class="info-value">
                            <span class="priority-badge <?php echo $priorityClass; ?>"><?php echo $priorityText; ?></span>
                        </div>
                        
                        <?php if (!empty($priority_success)): ?>
                            <div class="success-message small-message"><?php echo $priority_success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($priority_error)): ?>
                            <div class="error-message small-message"><?php echo $priority_error; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $submission['id']; ?>&filter=<?php echo $filter; ?>" class="priority-form">
                            <div class="priority-selector">
                                <select name="priority" required>
                                    <option value="high" <?php echo (isset($submission['priority']) && $submission['priority'] == 'high') ? 'selected' : ''; ?>>مهم</option>
                                    <option value="normal" <?php echo (!isset($submission['priority']) || $submission['priority'] == 'normal') ? 'selected' : ''; ?>>عادي</option>
                                    <option value="low" <?php echo (isset($submission['priority']) && $submission['priority'] == 'low') ? 'selected' : ''; ?>>غير مستعجل</option>
                                </select>
                                <button type="submit" name="update_priority" class="priority-btn">تحديث الأولوية</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="submission-notes">
                    <h3>الملاحظات</h3>
                    
                    <?php if (!empty($note_success)): ?>
                    <div class="success-message"><?php echo $note_success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($note_error)): ?>
                    <div class="error-message"><?php echo $note_error; ?></div>
                    <?php endif; ?>
                    
                    <div class="notes-content">
                        <?php 
                        // Get notes for this submission from the new table
                        $notes = get_submission_notes($conn, $submission_id);
                        
                        if (!empty($notes)): ?>
                            <div class="notes-list">
                                <?php foreach ($notes as $note): ?>
                                <div class="note-item">
                                    <div class="note-header">
                                        <span class="note-user"><?php echo htmlspecialchars($note['added_by']); ?></span>
                                        <span class="note-date"><?php echo date('Y-m-d H:i', strtotime($note['created_at'])); ?></span>
                                    </div>
                                    <div class="note-content"><?php echo nl2br(htmlspecialchars($note['note_text'])); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (!empty($submission['notes'])): ?>
                                <div class="legacy-notes">
                                    <h4>ملاحظات سابقة</h4>
                                    <pre class="note-text"><?php echo htmlspecialchars($submission['notes']); ?></pre>
                                </div>
                            <?php endif; ?>

                        <?php elseif (!empty($submission['notes'])): ?>
                            <pre class="note-text"><?php echo htmlspecialchars($submission['notes']); ?></pre>
                        <?php else: ?>
                            <p class="no-notes">لا توجد ملاحظات لهذا الطلب.</p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $submission['id']; ?>&filter=<?php echo $filter; ?>" class="add-note-form">
                        <textarea name="note" placeholder="أضف ملاحظة جديدة..." rows="4" required></textarea>
                        <button type="submit" name="add_note" class="submit-btn">إضافة ملاحظة</button>
                    </form>
                </div>
                
                <div class="submission-actions">
                    <a href="dashboard.php?filter=<?php echo $filter; ?>" class="back-link">العودة إلى القائمة</a>
                    <a href="print_submission.php?id=<?php echo $submission['id']; ?>&filter=<?php echo $filter; ?>" class="action-btn print-btn" target="_blank">طباعة الطلب</a>
                    <a href="dashboard.php?action=delete&id=<?php echo $submission['id']; ?>&filter=<?php echo $filter; ?>" class="action-btn delete-btn" onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">حذف الطلب</a>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> KAGENO - جميع الحقوق محفوظة</p>
        </footer>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>