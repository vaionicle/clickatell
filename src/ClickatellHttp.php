<?php

namespace NotificationChannels\Clickatell;

use Illuminate\Support\Facades\Log;

class ClickatellHttp
{
    private $_url = 'https://platform.clickatell.com/v1/message';
    private $_apiKey = null;
    private $_curl = null;

    private $_phoneNumbers = null;
    private $_message = null;

    public function __construct($apiKey)
    {
        $this->_apiKey = $apiKey;

        $this->_curl_init();
    }

    public function sendMessage(array $phoneNumbers, string $message): self
    {
        $this->_message = $message;
        $this->_phoneNumbers = $phoneNumbers;

        $this->_curl_send();

        return $this;
    }

    private function _requestBody(): array
    {
        $messages = [];

        foreach($this->_phoneNumbers as $phoneNumber) {
            $messages[] += ['channel' => 'sms', 'to' => "$phoneNumber", 'content' => "$this->_message"];
            $messages[] += ['channel' => 'whatsapp', 'to' => "$phoneNumber", 'content' => "$this->_message"];
        }

        return ['messages' => $messages];
    }

    private function _curl_init()
    {
        if ($this->_curl == null) {
            $this->_curl = curl_init();

            curl_setopt($this->_curl, CURLOPT_URL, $this->_url);
            curl_setopt($this->_curl, CURLOPT_POST, true);
            curl_setopt($this->_curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->_curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: '.$this->_apiKey
            ]);
        }

        return $this->_curl;
    }

    private function _curl_send()
    {
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, json_encode( $this->_requestBody()));

        $content = curl_exec($this->_curl);
        $response = curl_getinfo($this->_curl);

        curl_close($this->_curl);

        Log::debug($content);
        Log::debug($response);
    }
}