<?php require_once __DIR__ . '/_menu_guard.php'; ?>
<div id="v-payments" hidden>
  <section class="kpis" id="pk" style="grid-template-columns:repeat(3,1fr)"></section>
  <section class="card">
    <div class="head" style="padding-bottom:14px"><h2>Payment history</h2><button class="btn sm pri" id="recpay">Record payment</button></div>
    <div class="wrap"><table><thead><tr><th>Received</th><th>Invoice</th><th>Client</th><th>Method</th><th class="r">Amount</th></tr></thead><tbody id="pr"></tbody></table></div>
    <div class="empty" id="pe" hidden>No payments recorded yet.</div>
  </section>
</div>
