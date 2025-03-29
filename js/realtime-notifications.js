/**
 * KAGENO - نظام الإشعارات في الوقت الحقيقي
 * 
 * هذا الملف يحتوي على وظائف لإدارة الإشعارات في الوقت الحقيقي
 */

document.addEventListener('DOMContentLoaded', () => {
    // تهيئة نظام الإشعارات
    initializeNotificationSystem();
});

/**
 * تهيئة نظام الإشعارات
 */
function initializeNotificationSystem() {
    // إضافة زر تبديل الصوت
    addSoundToggle();
    
    // إضافة تأثيرات التمرير للإشعارات
    addNotificationScrollEffects();
    
    // إضافة أزرار التحكم في الإشعارات
    addNotificationControls();
    
    // البدء في التحقق من وجود إشعارات جديدة
    checkForNewNotifications();
    
    // فحص دوري للإشعارات الجديدة كل 30 ثانية
    setInterval(checkForNewNotifications, 30000);
}

/**
 * إضافة زر تبديل الصوت في الشريط العلوي
 */
function addSoundToggle() {
    const soundToggle = document.getElementById('sound-icon');
    
    // التحقق من إعدادات الصوت المخزنة
    const soundEnabled = localStorage.getItem('notification_sound_enabled') !== 'false';
    
    // تحديث أيقونة الصوت وفقًا للإعدادات المخزنة
    if (!soundEnabled) {
        soundToggle.classList.remove('fa-volume-up');
        soundToggle.classList.add('fa-volume-mute');
    }
    
    // إضافة مستمع الحدث للنقر على أيقونة الصوت
    soundToggle.parentNode.addEventListener('click', () => {
        const isEnabled = soundToggle.classList.contains('fa-volume-up');
        
        if (isEnabled) {
            soundToggle.classList.remove('fa-volume-up');
            soundToggle.classList.add('fa-volume-mute');
            localStorage.setItem('notification_sound_enabled', 'false');
            showToast('تم إيقاف صوت الإشعارات');
        } else {
            soundToggle.classList.remove('fa-volume-mute');
            soundToggle.classList.add('fa-volume-up');
            localStorage.setItem('notification_sound_enabled', 'true');
            showToast('تم تشغيل صوت الإشعارات');
            
            // تشغيل صوت الإشعار كاختبار
            playNotificationSound();
        }
    });
}

/**
 * إضافة تأثيرات التمرير للإشعارات
 */
function addNotificationScrollEffects() {
    const badge = document.getElementById('notifications-badge');
    const dropdown = document.getElementById('notifications-dropdown');
    
    // إضافة مستمع الحدث لفتح/إغلاق قائمة الإشعارات
    badge.addEventListener('click', (e) => {
        e.stopPropagation();
        
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        } else {
            dropdown.classList.add('show');
            
            // التحقق من وجود إشعارات جديدة عند فتح القائمة
            checkForNewNotifications();
        }
    });
    
    // إغلاق القائمة عند النقر في أي مكان آخر
    document.addEventListener('click', (e) => {
        if (!badge.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
}

/**
 * التحقق من وجود إشعارات جديدة
 */
function checkForNewNotifications() {
    // إرسال طلب AJAX للتحقق من وجود إشعارات جديدة
    fetch('check_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // تحديث شارة عدد الإشعارات
                updateNotificationBadge(data.total_unread);
                
                // إذا كانت هناك إشعارات جديدة
                if (data.new_count > 0) {
                    // تشغيل صوت الإشعار
                    playNotificationSound();
                    
                    // عرض الإشعارات الجديدة
                    const container = document.getElementById('notifications-container');
                    
                    // إزالة رسالة "لا توجد إشعارات"
                    const noNotifications = container.querySelector('.no-notifications');
                    if (noNotifications) {
                        container.removeChild(noNotifications);
                    }
                    
                    // إضافة الإشعارات الجديدة
                    data.notifications.forEach(notification => {
                        addNotification(notification, container);
                    });
                    
                    // عرض إشعار منبثق
                    if (data.new_count === 1) {
                        showToast('لديك إشعار جديد');
                    } else {
                        showToast(`لديك ${data.new_count} إشعارات جديدة`);
                    }
                } else if (data.total_unread === 0) {
                    // إذا لم تكن هناك إشعارات غير مقروءة
                    const container = document.getElementById('notifications-container');
                    
                    // إضافة رسالة "لا توجد إشعارات" إذا كانت القائمة فارغة
                    if (container.children.length === 0) {
                        container.innerHTML = '<div class="no-notifications">لا توجد إشعارات جديدة</div>';
                    }
                }
            } else {
                console.error('خطأ في التحقق من الإشعارات:', data.message);
            }
        })
        .catch(error => {
            console.error('خطأ في طلب التحقق من الإشعارات:', error);
        });
}

/**
 * إضافة إشعار جديد
 */
function addNotification(notification, container) {
    // إنشاء عنصر الإشعار
    const notificationEl = document.createElement('div');
    notificationEl.className = 'notification animated unread';
    notificationEl.dataset.id = notification.id;
    
    // تحديد أيقونة الإشعار بناءً على الأولوية
    let priorityIcon = 'fa-bell';
    let priorityClass = '';
    
    if (notification.priority === 'high') {
        priorityIcon = 'fa-exclamation-circle';
        priorityClass = 'high-priority';
    } else if (notification.priority === 'low') {
        priorityIcon = 'fa-info-circle';
        priorityClass = 'low-priority';
    }
    
    // إضافة محتوى الإشعار
    notificationEl.innerHTML = `
        <div class="notification-icon ${priorityClass}">
            <i class="fas ${priorityIcon}"></i>
        </div>
        <div class="notification-message">
            ${notification.message}
            <br>
            <small>${notification.detail}</small>
            <span class="notification-time">${notification.time}</span>
        </div>
        <div class="notification-actions">
            <button class="mark-read-btn" title="تأشير كمقروءة">
                <i class="fas fa-check"></i>
            </button>
        </div>
    `;
    
    // إضافة الإشعار إلى الحاوية
    if (container.firstChild) {
        container.insertBefore(notificationEl, container.firstChild);
    } else {
        container.appendChild(notificationEl);
    }
    
    // إضافة تأثير ظهور الإشعار
    setTimeout(() => {
        notificationEl.classList.add('visible');
    }, 10);
    
    // إضافة مستمع الحدث للنقر على الإشعار
    notificationEl.addEventListener('click', () => {
        handleNotificationAction(notification);
    });
    
    // إضافة مستمع الحدث لزر "تأشير كمقروءة"
    const markReadBtn = notificationEl.querySelector('.mark-read-btn');
    markReadBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        markNotificationAsRead(notification.id, markReadBtn);
    });
}

/**
 * تحديث شارة عدد الإشعارات
 */
function updateNotificationBadge(count) {
    const badge = document.getElementById('badge-count');
    badge.textContent = count;
    
    if (count > 0) {
        badge.classList.add('has-notifications');
        
        // إضافة تأثير النبض إذا كانت هذه إشعارات جديدة
        if (!badge.classList.contains('pulse')) {
            badge.classList.add('pulse');
            setTimeout(() => {
                badge.classList.remove('pulse');
            }, 1000);
        }
    } else {
        badge.classList.remove('has-notifications');
    }
}

/**
 * تأشير إشعار كمقروءة
 */
function markNotificationAsRead(id, button) {
    // إرسال طلب AJAX لتأشير الإشعار كمقروءة
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('mark_notification_read.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // العثور على عنصر الإشعار
            const notification = button.closest('.notification');
            
            // إضافة تأثير إخفاء الإشعار
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(20px)';
            
            // حذف الإشعار بعد انتهاء التأثير
            setTimeout(() => {
                notification.parentNode.removeChild(notification);
                
                // التحقق من وجود إشعارات متبقية
                const container = document.getElementById('notifications-container');
                if (container.children.length === 0) {
                    container.innerHTML = '<div class="no-notifications">لا توجد إشعارات جديدة</div>';
                }
            }, 300);
            
            // تحديث شارة عدد الإشعارات
            updateNotificationBadge(data.total_unread);
            
            // عرض إشعار منبثق
            showToast('تم تأشير الإشعار كمقروءة');
        } else {
            console.error('خطأ في تأشير الإشعار كمقروءة:', data.message);
            showToast('حدث خطأ أثناء تأشير الإشعار كمقروءة', 3000);
        }
    })
    .catch(error => {
        console.error('خطأ في طلب تأشير الإشعار كمقروءة:', error);
        showToast('حدث خطأ في الاتصال بالخادم', 3000);
    });
}

/**
 * التعامل مع إجراء الإشعار
 */
function handleNotificationAction(notification) {
    // فتح صفحة عرض الطلب للاطلاع على التفاصيل
    window.location.href = `view_submission.php?id=${notification.id}`;
}

/**
 * تشغيل صوت الإشعار
 */
function playNotificationSound() {
    // التحقق من إعدادات الصوت المخزنة
    const soundEnabled = localStorage.getItem('notification_sound_enabled') !== 'false';
    
    if (soundEnabled) {
        // إنشاء عنصر الصوت
        const audio = new Audio('../sounds/notification.mp3');
        audio.volume = 0.5;
        
        // تشغيل الصوت
        audio.play().catch(error => {
            console.error('خطأ في تشغيل صوت الإشعار:', error);
        });
    }
}

/**
 * عرض إشعار منبثق (toast)
 */
function showToast(message, duration = 3000) {
    const toast = document.getElementById('toast-notification');
    toast.textContent = message;
    toast.classList.add('show');
    
    // إخفاء الإشعار المنبثق بعد المدة المحددة
    setTimeout(() => {
        toast.classList.remove('show');
    }, duration);
}

/**
 * تنسيق الوقت بصيغة "منذ..."
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    // تحويل الثواني إلى وحدات زمنية مناسبة
    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1) {
        return `منذ ${interval} ${interval === 1 ? 'سنة' : 'سنوات'}`;
    }
    
    interval = Math.floor(seconds / 2592000);
    if (interval >= 1) {
        return `منذ ${interval} ${interval === 1 ? 'شهر' : 'أشهر'}`;
    }
    
    interval = Math.floor(seconds / 86400);
    if (interval >= 1) {
        return `منذ ${interval} ${interval === 1 ? 'يوم' : 'أيام'}`;
    }
    
    interval = Math.floor(seconds / 3600);
    if (interval >= 1) {
        return `منذ ${interval} ${interval === 1 ? 'ساعة' : 'ساعات'}`;
    }
    
    interval = Math.floor(seconds / 60);
    if (interval >= 1) {
        return `منذ ${interval} ${interval === 1 ? 'دقيقة' : 'دقائق'}`;
    }
    
    return 'منذ لحظات';
}

/**
 * إضافة أزرار التحكم في الإشعارات
 */
function addNotificationControls() {
    // إضافة مستمع الحدث لزر "تأشير الكل كمقروءة"
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    markAllReadBtn.addEventListener('click', markAllNotificationsAsRead);
}

/**
 * تأشير جميع الإشعارات كمقروءة
 */
function markAllNotificationsAsRead() {
    // إرسال طلب AJAX لتأشير جميع الإشعارات كمقروءة
    fetch('mark_all_notifications_read.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // إزالة جميع الإشعارات من القائمة
                const container = document.getElementById('notifications-container');
                container.innerHTML = '<div class="no-notifications">لا توجد إشعارات جديدة</div>';
                
                // تحديث شارة عدد الإشعارات
                updateNotificationBadge(0);
                
                // عرض إشعار منبثق
                showToast('تم تأشير جميع الإشعارات كمقروءة');
            } else {
                console.error('خطأ في تأشير جميع الإشعارات كمقروءة:', data.message);
                showToast('حدث خطأ أثناء تأشير جميع الإشعارات كمقروءة', 3000);
            }
        })
        .catch(error => {
            console.error('خطأ في طلب تأشير جميع الإشعارات كمقروءة:', error);
            showToast('حدث خطأ في الاتصال بالخادم', 3000);
        });
}