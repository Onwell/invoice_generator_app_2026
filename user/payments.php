<?php
require_once __DIR__ . '/_bootstrap.php';
$page   = 'payments';
$search = '';
ob_start();
?>
<section class="card">
  <div class="head" style="padding-bottom:14px"><h2>Payments recorded</h2><span class="sub" id="pcnt"></span></div>
  <div class="wrap"><table>
    <thead><tr><th>Received</th><th>Invoice</th><th>Client</th><th>Method</th><th class="r">Amount</th></tr></thead>
    <tbody id="pr"></tbody></table></div>
  <div class="empty" id="pe" hidden>No payments recorded yet.</div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';