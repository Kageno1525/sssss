<?php
include 'db.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = $error = '';

// Process form submission - with improved error logging
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? sanitize_input($_POST["name"]) : '';
    $phone = isset($_POST["phone"]) ? sanitize_input($_POST["phone"]) : '';
    $gender = isset($_POST["gender"]) ? sanitize_input($_POST["gender"]) : '';
    $occupation = isset($_POST["occupation"]) ? sanitize_input($_POST["occupation"]) : '';
    
    // Validate form data
    if (empty($name)) {
        $error = "الرجاء إدخال الاسم الكامل";
    } elseif (empty($phone)) {
        $error = "الرجاء إدخال رقم الهاتف";
    } elseif (!validate_phone($phone)) {
        $error = "صيغة رقم الهاتف غير صحيحة";
    } elseif (empty($gender)) {
        $error = "الرجاء اختيار الجنس";
    } elseif (empty($occupation)) {
        $error = "الرجاء إدخال المهنة";
    } else {
        // Try to submit the form data to database
        try {
            if (submit_form_data($conn, $name, $phone, $gender, $occupation)) {
                // Log the new submission
                log_activity($conn, 'نظام', 'طلب جديد', 
                    'تم استلام طلب جديد من: ' . $name . ' (' . ($gender == 'male' ? 'ذكر' : 'أنثى') . ')');
                
                $success = "تم تسجيل بياناتك بنجاح";
                // Clear form data
                $name = $phone = $gender = $occupation = '';
            } else {
                $error = "حدث خطأ أثناء تسجيل البيانات، يرجى المحاولة مرة أخرى";
                error_log("Database error: " . mysqli_error($conn)); // تم التعديل لاستخدام MySQL
            }
        } catch (Exception $e) {
            $error = "حدث خطأ أثناء تسجيل البيانات، يرجى المحاولة مرة أخرى";
            error_log("Exception: " . $e->getMessage());
        }
    }
}
?>
