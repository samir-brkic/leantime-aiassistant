<?php

/**
 * AI Assistant Plugin Registration
 * 
 * Registers menu items, event listeners, and middleware for the AI Assistant plugin
 */

use Leantime\Domain\Plugins\Services\Registration;
use Leantime\Core\Events\EventDispatcher;

// Create Registration instance
$registration = new Registration('AIAssistant');

// Register language files
$registration->registerLanguageFiles(['de-DE', 'en-US']);

// Add Quick Capture menu item to "default" menu
$registration->addMenuItem(
    [
        'title' => 'menu.quick_capture',
        'icon' => 'fa fa-fw fa-bolt',
        'tooltip' => 'Quick Capture - AI Task Generator',
        'href' => '/AIAssistant/quickCapture',
        'active' => ['quickCapture']
    ],
    'default',  // Menu section
    [10, 45]    // Location: [submenu 10, position 45]
);

// Add AI Settings to company menu (admin only)
$registration->addMenuItem(
    [
        'title' => 'menu.ai_settings',
        'icon' => 'fa fa-fw fa-robot',
        'tooltip' => 'AI Assistant Settings',
        'href' => '/AIAssistant/settings',
        'active' => ['settings'],
        'role' => 'admin'
    ],
    'company',  // Menu section
    [15, 20]    // Location: [submenu 15 (Administration), position 20]
);

error_log("AIAssistant: Menu items registered via Registration service!");

