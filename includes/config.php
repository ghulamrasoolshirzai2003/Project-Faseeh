<?php
// includes/config.php
// Centralized configuration for API keys and environment settings.

define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'YOUR_GEMINI_API_KEY_HERE');
define('OPENROUTER_API_KEY', getenv('OPENROUTER_API_KEY') ?: 'YOUR_OPENROUTER_API_KEY_HERE');
