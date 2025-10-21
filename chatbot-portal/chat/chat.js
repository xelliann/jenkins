// chat.js — connects frontend chat UI to /api/chat_handler.php

const form = document.getElementById('chat-form');
const input = document.getElementById('user-input');
const chatBox = document.getElementById('chat-box');

function appendMessage(sender, text) {
  const div = document.createElement('div');
  div.className = 'msg ' + sender;
  div.textContent = (sender === 'user' ? 'You: ' : 'Bot: ') + text;
  chatBox.appendChild(div);
  chatBox.scrollTop = chatBox.scrollHeight;
}

async function sendMessage(message) {
  try {
    const res = await fetch('/api/chat_handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message })
    });

    if (!res.ok) {
      console.error('API error:', res.status);
      appendMessage('bot', '⚠️ Server error, try again.');
      return;
    }

    const data = await res.json();
    if (data.error) {
      appendMessage('bot', '⚠️ ' + data.error);
    } else {
      appendMessage('bot', data.reply || '(no reply)');
      saveMessage('bot', data.reply || '');
    }
  } catch (err) {
    console.error('Fetch error:', err);
    appendMessage('bot', '⚠️ Network error.');
  }
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const text = input.value.trim();
  if (!text) return;
  appendMessage('user', text);
  saveMessage('user', text);
  input.value = '';
  await sendMessage(text);
});

// --- Local storage for history ---
function saveMessage(sender, message) {
  const history = JSON.parse(localStorage.getItem('chatHistory') || '[]');
  history.push({ sender, message });
  if (history.length > 100) history.shift();
  localStorage.setItem('chatHistory', JSON.stringify(history));
}

function loadMessages() {
  const history = JSON.parse(localStorage.getItem('chatHistory') || '[]');
  chatBox.innerHTML = '';
  history.forEach(m => appendMessage(m.sender, m.message));
}

loadMessages();
