import Cerebras from '@cerebras/cerebras_cloud_sdk';
import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Khởi tạo Cerebras client
const cerebras = new Cerebras({
  apiKey: process.env.CEREBRAS_API_KEY
});

// System prompt cho chatbot về văn hóa Khmer Nam Bộ
const SYSTEM_PROMPT = `Bạn là trợ lý AI thông minh chuyên về văn hóa Khmer Nam Bộ. 
Nhiệm vụ của bạn là:
- Trả lời các câu hỏi về văn hóa, lịch sử, truyền thống Khmer Nam Bộ
- Giới thiệu về các lễ hội, chùa chiền, truyện dân gian Khmer
- Hỗ trợ học tiếng Khmer
- Cung cấp thông tin chính xác, thân thiện và dễ hiểu
- Trả lời bằng tiếng Việt, trừ khi được yêu cầu khác

Hãy luôn lịch sự, nhiệt tình và cung cấp thông tin hữu ích.`;

// API endpoint cho chat
app.post('/api/chat', async (req, res) => {
  try {
    const { message, conversationHistory = [] } = req.body;

    if (!message) {
      return res.status(400).json({ error: 'Tin nhắn không được để trống' });
    }

    // Chuẩn bị messages với lịch sử hội thoại
    const messages = [
      { role: 'system', content: SYSTEM_PROMPT },
      ...conversationHistory,
      { role: 'user', content: message }
    ];

    // Gọi Cerebras API
    const completion = await cerebras.chat.completions.create({
      messages: messages,
      model: 'llama3.1-8b',
      max_completion_tokens: 1024,
      temperature: 0.7,
      top_p: 0.95,
      stream: false
    });

    const reply = completion.choices[0]?.message?.content || 'Xin lỗi, tôi không thể trả lời lúc này.';

    res.json({
      success: true,
      reply: reply,
      timestamp: new Date().toISOString()
    });

  } catch (error) {
    console.error('Lỗi Cerebras API:', error);
    res.status(500).json({
      success: false,
      error: 'Đã xảy ra lỗi khi xử lý yêu cầu',
      details: error.message
    });
  }
});

// API endpoint cho streaming chat
app.post('/api/chat/stream', async (req, res) => {
  try {
    const { message, conversationHistory = [] } = req.body;

    if (!message) {
      return res.status(400).json({ error: 'Tin nhắn không được để trống' });
    }

    // Thiết lập SSE headers
    res.setHeader('Content-Type', 'text/event-stream');
    res.setHeader('Cache-Control', 'no-cache');
    res.setHeader('Connection', 'keep-alive');

    const messages = [
      { role: 'system', content: SYSTEM_PROMPT },
      ...conversationHistory,
      { role: 'user', content: message }
    ];

    const stream = await cerebras.chat.completions.create({
      messages: messages,
      model: 'llama3.1-8b',
      stream: true,
      max_completion_tokens: 1024,
      temperature: 0.7,
      top_p: 0.95
    });

    for await (const chunk of stream) {
      const content = chunk.choices[0]?.delta?.content || '';
      if (content) {
        res.write(`data: ${JSON.stringify({ content })}\n\n`);
      }
    }

    res.write('data: [DONE]\n\n');
    res.end();

  } catch (error) {
    console.error('Lỗi streaming:', error);
    res.write(`data: ${JSON.stringify({ error: error.message })}\n\n`);
    res.end();
  }
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

app.listen(PORT, () => {
  console.log(`🤖 Chatbot server đang chạy tại http://localhost:${PORT}`);
  console.log(`📡 API endpoint: http://localhost:${PORT}/api/chat`);
});
