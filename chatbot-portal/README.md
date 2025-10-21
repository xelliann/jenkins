# Chatbot Portal — Part 1 (Setup + DB)
This archive contains a starter PHP project skeleton for a Customer Support Chatbot Portal.

**What's included**
- Project folder structure
- Database SQL to create required tables
- Basic PHP files: connection, index, simple chat API placeholder
- Auth skeleton (register/login)
- Admin & chat placeholders
- Minimal purple-themed CSS

**How to use**
1. Extract into your web server root (e.g., `htdocs` for XAMPP).
2. Create a MySQL database (e.g., `chatbot_portal`) and run `sql/setup.sql`.
3. Update `includes/db.php` with your DB credentials.
4. Configure your Azure OpenAI / Azure API keys in `api/chat.php`.
5. Open `index.php` in the browser.

This is Part 1 only — after you test this, we'll implement Part 2 (Auth with full validation).
