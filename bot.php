<?php

$token = "8925750287:AAHdk9k_vwTkLbNjhX_c21suZX1Bu9B2LiY";
$api = "https://api.telegram.org/bot$token/";

$update = json_decode(file_get_contents("php://input"), true);

function bot($method, $data = []) {
    global $api;

    $url = $api . $method;

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

if(isset($update['message'])) {

    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';

    if($text == '/start') {

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '👤 User', 'callback_data' => 'user'],
                    ['text' => '💎 Premium', 'callback_data' => 'premium'],
                    ['text' => '🤖 Bot', 'callback_data' => 'bot']
                ],
                [
                    ['text' => '👥 Group', 'callback_data' => 'group'],
                    ['text' => '📢 Channel', 'callback_data' => 'channel'],
                    ['text' => '💬 Forum', 'callback_data' => 'forum']
                ]
            ]
        ];

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "🔥 Advanced Info Bot\n\nSend or forward anything to get information.",
            'reply_markup' => json_encode($keyboard)
        ]);

        exit;
    }

    # USER INFO
    if(isset($message['forward_from'])) {

        $u = $message['forward_from'];

        $id = $u['id'] ?? 'N/A';
        $first = $u['first_name'] ?? 'N/A';
        $last = $u['last_name'] ?? 'N/A';
        $username = isset($u['username']) ? '@'.$u['username'] : 'No Username';
        $lang = $u['language_code'] ?? 'Unknown';

        $premium = isset($u['is_premium']) ? 'Yes ✅' : 'No ❌';
        $isbot = isset($u['is_bot']) && $u['is_bot'] ? 'Yes 🤖' : 'No 👤';

        $msg = "🔥 USER INFORMATION\n\n";
        $msg .= "🆔 ID : <code>$id</code>\n";
        $msg .= "👤 First Name : $first\n";
        $msg .= "👤 Last Name : $last\n";
        $msg .= "📛 Username : $username\n";
        $msg .= "🌐 Language : $lang\n";
        $msg .= "💎 Premium : $premium\n";
        $msg .= "🤖 Bot : $isbot\n";

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);

        exit;
    }

    # GROUP INFO
    if($message['chat']['type'] == 'group' || $message['chat']['type'] == 'supergroup') {

        $group_id = $message['chat']['id'];
        $title = $message['chat']['title'] ?? 'Unknown';
        $type = $message['chat']['type'];

        $msg = "👥 GROUP INFORMATION\n\n";
        $msg .= "🆔 Group ID : <code>$group_id</code>\n";
        $msg .= "📛 Group Name : $title\n";
        $msg .= "📂 Type : $type\n";

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);
    }
}

# BUTTON CLICK
if(isset($update['callback_query'])) {

    $call = $update['callback_query'];
    $data = $call['data'];
    $chat_id = $call['message']['chat']['id'];

    if($data == 'user') {
        $msg = "👤 Send or forward user message to get user information.";
    }

    elseif($data == 'premium') {
        $msg = "💎 Premium users show is_premium = true.";
    }

    elseif($data == 'bot') {
        $msg = "🤖 Forward any bot message to detect bot info.";
    }

    elseif($data == 'group') {
        $msg = "👥 Add bot in group to get group information.";
    }

    elseif($data == 'channel') {
        $msg = "📢 Add bot as admin in channel to get channel information.";
    }

    elseif($data == 'forum') {
        $msg = "💬 Forums are Telegram topic groups.";
    }

    bot('answerCallbackQuery', [
        'callback_query_id' => $call['id'],
        'text' => 'Opened'
    ]);

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $msg
    ]);
}

?>
