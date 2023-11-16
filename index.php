<?php

/*
    GLOBUS.studio p3333
    2023
    Created by ChatGPT (GPT-3.5-turbo)
*/

ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

$f3 = require('f3/base.php');
$f3->set('DEBUG',3);
$f3->set('FALLBACK','ru');
$f3->set('UI','ui/');
$f3->set('UPLOADS',$f3->get('TEMP'));
$f3->set('ENCODING','UTF-8');

$f3->config('config.ini');

require 'core/TelegramBot.php';

$f3->route('GET /login', function($f3) {
    if ($f3->get('SESSION.logged_in')) {
        $f3->reroute('/');
    } else {
        $telegramBot = new TelegramBot($f3->get('telegramBotToken'), $f3->get('telegramChatId'));
        $channel_link = $f3->get('telegramChatId');
        $channel_info = $telegramBot->getChannelInfo($channel_link);

        if ($channel_info !== null) {
            $f3->set('title', $channel_info['title']);
            $f3->set('id', $channel_info['channel_id']);
            $f3->set('description', $channel_info['description']);
            $f3->set('photo', $channel_info['photo']);
        } else {
            $f3->set('title', '');
            $f3->set('id', '');
            $f3->set('description', '');
            $f3->set('photo', '');            
            $f3->set('error', 'Channel connect error');
        }

        $f3->set('error', '');
        echo \Template::instance()->render('login.html');
    }
});

$f3->route('POST /login', function($f3) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $validUsername = $f3->get('validUsername');
    $validPassword = $f3->get('validPassword');

    if ($username === $validUsername && $password === $validPassword) {
        $f3->set('SESSION.logged_in', true);
        $f3->reroute('/');
    } else {

        $telegramBot = new TelegramBot($f3->get('telegramBotToken'), $f3->get('telegramChatId'));
        $channel_link = $f3->get('telegramChatId');
        $channel_info = $telegramBot->getChannelInfo($channel_link);

        if ($channel_info !== null) {
            $f3->set('title', $channel_info['title']);
            $f3->set('id', $channel_info['channel_id']);
            $f3->set('description', $channel_info['description']);
            $f3->set('photo', $channel_info['photo']);
        } else {
            $f3->set('title', '');
            $f3->set('id', '');
            $f3->set('description', '');
            $f3->set('photo', '');            
            $f3->set('error', 'Channel connect error');
        }


        $f3->set('error', 'Invalid username or password.');
        echo \Template::instance()->render('login.html');
    }
});

$f3->route('GET /', function($f3) {
    if ($f3->get('SESSION.logged_in')) {
        echo \Template::instance()->render('index.html');
    } else {
        $f3->reroute('/login');
    }
});

$f3->route('GET /logout', function($f3) {
    $f3->clear('SESSION');
    $f3->reroute('/login');
    }
);

$f3->route('POST /send-message', function($f3) {
    $telegramBot = new TelegramBot($f3->get('telegramBotToken'), $f3->get('telegramChatId'));
    $messageType = $f3->get('POST.message_type');

    switch ($messageType) {
        case 'text':
            $message = $f3->get('POST.message');
            $telegramBot->sendTextMessage($message);
            break;

        case 'photo':
            $photo_url = $f3->get('POST.photo_url');
            $message = $f3->get('POST.message');
            $telegramBot->sendPhotoMessage($photo_url, $message);
            break;

        case 'button':
            $message = $f3->get('POST.message');
            $button_text = $f3->get('POST.button_text');
            $button_url = $f3->get('POST.button_url');            
            $telegramBot->sendButtonMessage($message, $button_text, $button_url);
            break;            

        case 'poll':
            $question = $f3->get('POST.question');
            $options = preg_split('/\r\n|\r|\n/', $f3->get('POST.options'));
            $options = array_filter($options, function($option) {
                return !empty(trim($option));
            });
            $telegramBot->sendPollMessage($question, $options);
            break;
        default:
            break;
    }

    $f3->reroute('/');
});

$f3->run();