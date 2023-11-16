<?php 

class TelegramBot {
    private $token;
    private $chat_id;
    private $curl;

    function __construct($token, $chat_id) {
        $this->token = $token;
        $this->chat_id = $chat_id;
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

    function sendTextMessage($message, $parse_mode = 'HTML') {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";
        $data = array(
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode' => $parse_mode
        );
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($this->curl);
        return $result;
    }

    function sendPhotoMessage($photo_url, $message) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendPhoto";
        $data = array(
            'chat_id' => $this->chat_id,
            'photo' => $photo_url,
            'caption' => $message,
            'parse_mode' => 'HTML'
        );
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($this->curl);
        return $result;
    }

    function sendPollMessage($question, $options) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendPoll";
        $data = array(
            'chat_id' => $this->chat_id,
            'question' => $question,
            'options' => json_encode($options),
            'is_anonymous' => true
        );
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($this->curl);
        return $result;
    }

    function getChannelInfo($channel_link) {
        $url = "https://api.telegram.org/bot" . $this->token . "/getChat";
        $data = array(
            'chat_id' => $channel_link
        );
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($data)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $json = json_decode($result, true);

        if ($json['ok'] == true) {
            $channel_id = $json['result']['id'];
            $title = $json['result']['title'];
            $description = $json['result']['description'];
            $photo = null;
            if (isset($json['result']['photo'])) {
                $photo_array = $json['result']['photo'];
                $photo = $photo_array['small_file_id'];
            }

            $photo = $this->getChatPhotoUrl($photo);

            return array(
                'channel_id' => $channel_id,
                'title' => $title,
                'description' => $description,
                'photo' => $photo
            );
        } else {
            return null;
        }
    }

    function getChatPhotoUrl($file_id) {
        $url = "https://api.telegram.org/bot" . $this->token . "/getFile";
        $data = array(
            'file_id' => $file_id
        );
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($this->curl);
        $result_array = json_decode($result, true);
    
        if ($result_array['ok']) {
            $file_path = $result_array['result']['file_path'];
            $url = "https://api.telegram.org/file/bot" . $this->token . "/" . $file_path;
            return $url;
        } else {
            return null;
        }
    }    

    function sendButtonMessage($message, $button_text, $button_chat_id) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";
        $data = array(
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => $button_text,
                            'url' =>  $button_chat_id
                        )
                    )
                )
            ))
        );
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($this->curl);
        return $result;
    }

    function __destruct() {
        curl_close($this->curl);
    }

}
