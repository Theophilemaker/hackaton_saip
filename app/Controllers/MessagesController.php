<?php
namespace App\Controllers;

require_once __DIR__ . '/ChatController.php';

if (!class_exists('App\\Controllers\\MessagesController')) {
    /**
     * InfinityFree blocks lowercase "chat" in URLs via its WAF.
     * This wrapper exposes the same features at /Messages.
     */
    class MessagesController extends ChatController {}
}