<?php require_once __DIR__ . '/_menu_guard.php'; ?>
<div id="v-invoices" hidden>
  <section class="card">
    <div class="tabs" id="tabs"></div>
    <div class="bar2"><span class="sub" id="cnt"></span><div style="display:flex;gap:8px"><button class="btn sm" id="csv">Export CSV</button></div></div>
    <div class="wrap"><table>
      <thead><tr><th data-s="id">Invoice</th><th data-s="c">Client</th><th data-s="i">Issued</th><th data-s="d">Due</th><th data-s="s">Status</th><th class="r" data-s="a">Amount</th><th></th></tr></thead>
      <tbody id="rows"></tbody></table></div>
    <div class="empty" id="empty" hidden>No invoices match. Try another search or filter.</div>
  </section>
</div>
