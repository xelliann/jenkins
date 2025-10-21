<?php
// Simple landing page
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Chatbot Portal — Demo</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
  <header>
    <h1>Chatbot Portal</h1>
    <nav>
      <a href="faq.php">FAQs</a> |
      <a href="auth/login.php">Login</a> |
      <a href="auth/register.php">Register</a>
    </nav>
  </header>

  <main>
    <section class="hero">
      <h2>AI-powered customer support</h2>
      <p>Chat with the bot, browse FAQs or create a support ticket.</p>
      <a class="btn" href="chat/chat.php">Open Chat</a>
    </section>

    <section class="features">
      <div class="card">FAQ Management</div>
      <div class="card">Ticketing</div>
      <div class="card">Live Agent Escalation</div>
    </section>
  </main>

  <footer>Made with ❤️ — Starter skeleton (Part 1)</footer>
</div>
</body>
</html>
