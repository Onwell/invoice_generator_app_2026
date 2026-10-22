<?php require_once __DIR__ . '/_menu_guard.php'; ?>
<div id="v-dashboard" hidden>
  <section class="kpis" id="kpis"></section>
  <div class="head" style="padding:0 0 12px"><h2>Accounts</h2><a href="#users">Manage users</a></div>
  <section class="kpis u3" id="kpis2"></section>
  <section class="grid">
    <div class="card"><div class="head"><h2>Revenue collected</h2><span>Last 7 months</span></div><div class="chart" id="chart"></div></div>
    <div class="card"><div class="head"><h2>Top clients</h2><a href="#clients">View all</a></div><ul class="list" id="top"></ul></div>
  </section>
  <section class="card"><div class="head" style="padding-bottom:14px"><h2>Recent invoices</h2><a href="#invoices">View all</a></div><div class="wrap"><table><tbody id="recent"></tbody></table></div></section>
</div>
