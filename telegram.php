<?php
function telegram_send($message) {
    $token = '6700328598:AAEi7xlZoZyiYtkdbXXSYFLQmo2M6JINRvA';
    $chat_id = '-5543804825';
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = array(
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true
    );
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        )
    );
    @file_get_contents($url, false, stream_context_create($options));
}
?>