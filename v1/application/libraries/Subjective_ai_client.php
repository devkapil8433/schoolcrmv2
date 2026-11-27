<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Minimal OpenAI-compatible chat completions HTTP client.
 */
class Subjective_ai_client
{
    /** @var CI_Controller */
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->config->load('subjective_ai', true);
    }

    /**
     * @param array $messages OpenAI-style messages
     * @return array{ok:bool,content:string,error:string,raw:string,http_code:int}
     */
    public function chat(array $messages)
    {
        $cfg = $this->ci->config->item('subjective_ai_api_key', 'subjective_ai');
        $url = $this->ci->config->item('subjective_ai_api_url', 'subjective_ai');
        $model = $this->ci->config->item('subjective_ai_model', 'subjective_ai');
        $timeout = (int) $this->ci->config->item('subjective_ai_timeout', 'subjective_ai');
        $temp = (float) $this->ci->config->item('subjective_ai_temperature', 'subjective_ai');

        if (empty($cfg) || empty($url)) {
            return array('ok' => false, 'content' => '', 'error' => 'AI API not configured', 'raw' => '', 'http_code' => 0);
        }

        $payload = array(
            'model' => $model,
            'temperature' => $temp,
            'messages' => $messages,
        );
        // OpenAI supports json_object mode; omit on gateways that reject unknown fields.
        if (strpos((string) $url, 'api.openai.com') !== false) {
            $payload['response_format'] = array('type' => 'json_object');
        }
        $body = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $cfg,
            ),
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
        ));
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            return array('ok' => false, 'content' => '', 'error' => 'cURL: ' . $err, 'raw' => (string) $raw, 'http_code' => $http);
        }
        if ($http < 200 || $http >= 300) {
            return array('ok' => false, 'content' => '', 'error' => 'HTTP ' . $http, 'raw' => (string) $raw, 'http_code' => $http);
        }

        $decoded = json_decode($raw, true);
        $content = '';
        if (isset($decoded['choices'][0]['message']['content'])) {
            $content = (string) $decoded['choices'][0]['message']['content'];
        }
        if ($content === '') {
            return array('ok' => false, 'content' => '', 'error' => 'Empty model content', 'raw' => (string) $raw, 'http_code' => $http);
        }

        return array('ok' => true, 'content' => $content, 'error' => '', 'raw' => (string) $raw, 'http_code' => $http);
    }
}
