<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT id, question, answer FROM faqs WHERE is_active=1 ORDER BY created_at DESC LIMIT 20");
$faqs = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>FAQs</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
<h1>Frequently Asked Questions</h1>
<?php foreach($faqs as $f): ?>
  <article class="faq-item">
    <h3><?=htmlspecialchars($f['question'])?></h3>
    <p><?=nl2br(htmlspecialchars($f['answer']))?></p>
  </article>
<?php endforeach; ?>
<p><a href="index.php">Back</a></p>
</div>
</body>
</html>
