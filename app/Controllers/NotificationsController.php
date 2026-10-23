<?php
namespace App\Controllers;

require_once __DIR__ . '/NotificationController.php';

/**
 * Alias — makes /notifications/* routes work.
 * Delegates everything to NotificationController (singular).
 */
class NotificationsController extends NotificationController {
    // Inherits all methods: index, feed, markRead, markAllRead
}