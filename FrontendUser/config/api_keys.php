<?php
/**
 * API Keys Configuration
 * Văn hóa Khmer Nam Bộ - Frontend User
 * 
 * LƯU Ý: File này chứa thông tin nhạy cảm
 * Không commit file này lên git repository công khai
 * Thay YOUR_GROQ_API_KEY bằng API key thực của bạn
 */

// Groq API Configuration
define('GROQ_API_KEY', 'YOUR_GROQ_API_KEY');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL', 'llama-3.3-70b-versatile');

// Các API keys khác có thể thêm ở đây
// define('GOOGLE_API_KEY', 'your-google-api-key');
// define('OPENAI_API_KEY', 'your-openai-api-key');
