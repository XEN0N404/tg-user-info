from flask import Flask, request
import requests
import os
import json

app = Flask(__name__)

BOT_TOKEN = os.getenv("8925750287:AAHdk9k_vwTkLbNjhX_c21suZX1Bu9B2LiY")
API_URL = f"https://api.telegram.org/bot{BOT_TOKEN}/"


def send_message(chat_id, text, reply_markup=None):
    data = {
        "chat_id": chat_id,
        "text": text,
        "parse_mode": "HTML"
    }

    if reply_markup:
        data["reply_markup"] = json.dumps(reply_markup)

    requests.post(API_URL + "sendMessage", data=data)


@app.route('/', methods=['POST'])
def webhook():
    update = request.get_json()

    if 'message' in update:

        message = update['message']
        chat_id = message['chat']['id']
        text = message.get('text', '')

        if text == '/start':

            keyboard = {
                "inline_keyboard": [
                    [
                        {"text": "User", "callback_data": "user"},
                        {"text": "Premium", "callback_data": "premium"},
                        {"text": "Bot", "callback_data": "bot"}
                    ],
                    [
                        {"text": "Group", "callback_data": "group"},
                        {"text": "Channel", "callback_data": "channel"},
                        {"text": "Forum", "callback_data": "forum"}
                    ]
                ]
            }

            text_msg = """
<b>ADVANCED INFO BOT</b>

<blockquote>Forward any message to get information.</blockquote>

<u>Features</u>
• User Detection
• Premium Detection
• Bot Detection
• Group Information
• Channel Information
• Forum Detection
            """

            send_message(chat_id, text_msg, keyboard)

        elif 'forward_from' in message:

            user = message['forward_from']

            user_id = user.get('id', 'N/A')
            first_name = user.get('first_name', 'N/A')
            last_name = user.get('last_name', 'N/A')
            username = user.get('username', 'No Username')
            language = user.get('language_code', 'Unknown')

            premium = "True" if user.get('is_premium') else "False"
            is_bot = "True" if user.get('is_bot') else "False"

            result = f"""
<b>USER INFORMATION</b>

<blockquote>
ID : <code>{user_id}</code>
First Name : {first_name}
Last Name : {last_name}
Username : @{username}
Language : {language}
Premium : {premium}
Bot : {is_bot}
</blockquote>
            """

            send_message(chat_id, result)

        elif message['chat']['type'] in ['group', 'supergroup']:

            group_id = message['chat']['id']
            title = message['chat'].get('title', 'Unknown')
            group_type = message['chat']['type']

            result = f"""
<b>GROUP INFORMATION</b>

<blockquote>
Group ID : <code>{group_id}</code>
Group Name : {title}
Type : {group_type}
</blockquote>
            """

            send_message(chat_id, result)

    elif 'callback_query' in update:

        callback = update['callback_query']
        data = callback['data']
        chat_id = callback['message']['chat']['id']
        callback_id = callback['id']

        texts = {
            'user': '<b>User Information</b>\n\n<blockquote>Forward user message to get information.</blockquote>',
            'premium': '<b>Premium Detection</b>\n\n<blockquote>Shows whether user has Telegram Premium.</blockquote>',
            'bot': '<b>Bot Detection</b>\n\n<blockquote>Detects Telegram bots.</blockquote>',
            'group': '<b>Group Information</b>\n\n<blockquote>Add bot into group.</blockquote>',
            'channel': '<b>Channel Information</b>\n\n<blockquote>Add bot as admin in channel.</blockquote>',
            'forum': '<b>Forum Information</b>\n\n<blockquote>Detects Telegram forum groups.</blockquote>'
        }

        requests.post(API_URL + 'answerCallbackQuery', data={
            'callback_query_id': callback_id,
            'text': 'Opened'
        })

        send_message(chat_id, texts.get(data, 'Unknown'))

    return 'ok'


if __name__ == '__main__':
    app.run(debug=True)
