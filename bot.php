<?php

$apikey = "";
$botToken = "6139701911:AAGHNIoj2CDluQPc5dl7ET_UpbrGUa5GGHg";
$website = "https://api.telegram.org/bot" . $botToken;
$update = file_get_contents('php://input');
$update = json_decode($update, TRUE);
$e = print_r($update);

$chatId = $update["message"]["chat"]["id"];

$gId = $update["message"]["from"]["id"];
$userId = $update["message"]["from"]["id"];
$firstname = $update["message"]["from"]["first_name"];
$username = $update["message"]["from"]["username"];
$message = $update["message"]["text"];
$message_id = $update["message"]["message_id"];
$info = json_encode($update, JSON_PRETTY_PRINT);

$commands = array("/start", "!start", ".start", "/help", "!help", ".help", ".ask", "!ask", "/ask", ".weather", "!weather", "/weather");

if (in_array($message, $commands)) {
    switch ($message) {
        case "/start":
        case "!start":
        case ".start":
            sendMessage($chatId, "Hi There!, I'm a ChatGPT Integrated Telegram Based ChatBot. To Get Started, Type in: /help or !help or .help");
            break;
        case "/help":
        case "!help":
        case ".help":
            $help = "Hey, Here are the commands for the bot\n\n";
            $help .= "/ask, !ask, .ask {question}\n\n";
            $help .= "/weather, !weather, .weather {city}";
            sendMessage($chatId, $help);
            break;
        case ".ask":
        case "!ask":
        case "/ask":
            $msg = substr($message, 5);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apikey . ''
            ));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                "model" => "gpt-3.5-turbo",
                "messages" => array(array("role" => "user", "content" => $msg))
            )));
            $curl = curl_exec($ch);
            // $curl = json_decode($curl, true);
            sendMessage($chatId, $curl['choices'][0]['message']['content']);
            break;
        case ".weather":
        case "!weather":
        case "/weather":
            $location = substr($message, 9);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://api.openweathermap.org/data/2.5/weather?q=' . urlencode($location) . '&appid=bd7c144d6f446bfed0546f70fab98751');
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Host: api.openweathermap.org',
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
            ));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            $wth = curl_exec($ch);
            $wth = json_decode($wth, true);
            $weather = $wth["weather"][0]["main"];
            $descp = $wth["weather"][0]["description"];
            $name = $wth["name"];
            $temp = $wth["main"]["temp"];
            $pres = $wth["main"]["pressure"];
            $humid = $wth["main"]["humidity"];
            $country = $wth["sys"]["country"];
            $ch = curl_close();
            $cel = $temp - 273.15;
            if ($wth["name"] == '' . $location . '') {
                $weatherInfo = "Here's the Weather Info for $location:\n";
                $weatherInfo .= "Temperature: $cel\n";
                $weatherInfo .= "Climate: $weather\n";
                $weatherInfo .= "Pressure: $pres\n";
                $weatherInfo .= "Humidity: $humid\n";
                $weatherInfo .= "Country: $country";
                sendMessage($chatId, $weatherInfo);
            } else {
                sendMessage($chatId, 'Invalid City');
            }
            break;
    }
}

function sendMessage($chatId, $message, $message_id = null)
{
    $url = $GLOBALS['website'] . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message) . "&reply_to_message_id=" . $message_id . "&parse_mode=HTML";
    file_get_contents($url);
}
?>
