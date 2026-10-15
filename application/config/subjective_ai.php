<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| OpenAI-compatible Chat Completions API (HTTPS).
| Set SUBJECTIVE_AI_API_KEY (and optionally SUBJECTIVE_AI_API_URL, SUBJECTIVE_AI_MODEL) in the server environment.
*/
$config['subjective_ai_enabled'] = (getenv('SUBJECTIVE_AI_API_KEY') !== false && getenv('SUBJECTIVE_AI_API_KEY') !== '');
$config['subjective_ai_api_url'] = getenv('SUBJECTIVE_AI_API_URL') ?: 'https://api.openai.com/v1/chat/completions';
$config['subjective_ai_api_key'] = getenv('SUBJECTIVE_AI_API_KEY') ?: '';
$config['subjective_ai_model'] = getenv('SUBJECTIVE_AI_MODEL') ?: 'gpt-4o-mini';
$config['subjective_ai_timeout'] = (int) (getenv('SUBJECTIVE_AI_TIMEOUT') ?: 90);
$config['subjective_ai_max_retries'] = (int) (getenv('SUBJECTIVE_AI_MAX_RETRIES') ?: 2);
$config['subjective_ai_temperature'] = 0.2;
