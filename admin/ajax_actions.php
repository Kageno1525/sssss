<?php
include 'db.php';

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Prepare response
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

// Handle different AJAX actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Mark a submission as read
    if (isset($_POST['action']) && $_POST['action'] == 'mark_read') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            
            try {
                $result = mark_submission_as_read($conn, $id, $_SESSION['username']);
                
                if ($result) {
                    // Log the action
                    log_activity($conn, $_SESSION['username'], 'تأشير كمقروءة', 
                        'تم تأشير الطلب رقم ' . $id . ' كمقروءة');
                    
                    $response = [
                        'success' => true,
                        'message' => 'تم تأشير الطلب كمقروءة بنجاح',
                        'id' => $id,
                        'read_by' => $_SESSION['username'],
                        'read_at' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'حدث خطأ أثناء تأشير الطلب كمقروءة'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'معرف الطلب غير صالح'
            ];
        }
    }
    
    // Delete a submission
    else if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            
            try {
                // Get submission details before deletion for logging
                $query = "SELECT name, gender FROM submissions WHERE id = $1";
                $result = pg_query_params($conn, $query, [$id]);
                
                if ($row = pg_fetch_assoc($result)) {
                    $submissionName = $row['name'];
                    $submissionGender = $row['gender'] == 'male' ? 'ذكر' : 'أنثى';
                    
                    // Delete the submission
                    $query = "DELETE FROM submissions WHERE id = $1";
                    $result = pg_query_params($conn, $query, [$id]);
                    
                    if ($result) {
                        // Log the deletion
                        log_activity($conn, $_SESSION['username'], 'حذف طلب', 
                            'تم حذف الطلب: ' . $submissionName . ' (' . $submissionGender . ')');
                        
                        $response = [
                            'success' => true,
                            'message' => 'تم حذف الطلب بنجاح',
                            'id' => $id
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'حدث خطأ أثناء حذف الطلب'
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'الطلب غير موجود'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'معرف الطلب غير صالح'
            ];
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Prepare response
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

// Handle different AJAX actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Mark a submission as read
    if (isset($_POST['action']) && $_POST['action'] == 'mark_read') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            
            try {
                $result = mark_submission_as_read($conn, $id, $_SESSION['username']);
                
                if ($result) {
                    // Log the action
                    log_activity($conn, $_SESSION['username'], 'تأشير كمقروءة', 
                        'تم تأشير الطلب رقم ' . $id . ' كمقروءة');
                    
                    $response = [
                        'success' => true,
                        'message' => 'تم تأشير الطلب كمقروءة بنجاح',
                        'id' => $id,
                        'read_by' => $_SESSION['username'],
                        'read_at' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'حدث خطأ أثناء تأشير الطلب كمقروءة'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'معرف الطلب غير صالح'
            ];
        }
    }
    
    // Delete a submission
    else if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            
            try {
                // Get submission details before deletion for logging
                $query = "SELECT name, gender FROM submissions WHERE id = $1";
                $result = pg_query_params($conn, $query, [$id]);
                
                if ($row = pg_fetch_assoc($result)) {
                    $submissionName = $row['name'];
                    $submissionGender = $row['gender'] == 'male' ? 'ذكر' : 'أنثى';
                    
                    // Delete the submission
                    $query = "DELETE FROM submissions WHERE id = $1";
                    $result = pg_query_params($conn, $query, [$id]);
                    
                    if ($result) {
                        // Log the deletion
                        log_activity($conn, $_SESSION['username'], 'حذف طلب', 
                            'تم حذف الطلب: ' . $submissionName . ' (' . $submissionGender . ')');
                        
                        $response = [
                            'success' => true,
                            'message' => 'تم حذف الطلب بنجاح',
                            'id' => $id
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'حدث خطأ أثناء حذف الطلب'
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'الطلب غير موجود'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'معرف الطلب غير صالح'
            ];
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Prepare response
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

// Handle different AJAX actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Mark a submission as read
    if (isset($_POST['action']) && $_POST['action'] == 'mark_read') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            
            try {
                $result = mark_submission_as_read($conn, $id, $_SESSION['username']);
                
                if ($result) {
                    // Log the action
                    log_activity($conn, $_SESSION['username'], 'تأشير كمقروءة', 
                        'تم تأشير الطلب رقم ' . $id . ' كمقروءة');
                    
                    $response = [
                        'success' => true,
                        'message' => 'تم تأشير الطلب كمقروءة بنجاح',
                        'id' => $id,
                        'read_by' => $_SESSION['username'],
                        'read_at' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'حدث خطأ أثناء تأشير الطلب كمقروءة'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'معرف الطلب غير صالح'
            ];
        }
    }
    
    // Delete a submission
    else if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            
            try {
                // Get submission details before deletion for logging
                $query = "SELECT name, gender FROM submissions WHERE id = $1";
                $result = pg_query_params($conn, $query, [$id]);
                
                if ($row = pg_fetch_assoc($result)) {
                    $submissionName = $row['name'];
                    $submissionGender = $row['gender'] == 'male' ? 'ذكر' : 'أنثى';
                    
                    // Delete the submission
                    $query = "DELETE FROM submissions WHERE id = $1";
                    $result = pg_query_params($conn, $query, [$id]);
                    
                    if ($result) {
                        // Log the deletion
                        log_activity($conn, $_SESSION['username'], 'حذف طلب', 
                            'تم حذف الطلب: ' . $submissionName . ' (' . $submissionGender . ')');
                        
                        $response = [
                            'success' => true,
                            'message' => 'تم حذف الطلب بنجاح',
                            'id' => $id
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'حدث خطأ أثناء حذف الطلب'
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'الطلب غير موجود'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'معرف الطلب غير صالح'
            ];
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);