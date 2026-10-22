<?php require_once __DIR__ . '/_menu_guard.php'; ?>
<div id="v-audit" hidden>
  <section class="card">
    <div class="tabs" id="atabs"></div>
    <div class="bar2"><span class="sub" id="acnt"></span></div>
    <div class="wrap"><table>
      <thead><tr><th>Time</th><th>Account</th><th>Event</th><th>IP address</th><th>Location</th></tr></thead>
      <tbody id="arows"></tbody></table></div>
    <div class="empty" id="aempty" hidden>No log entries match. Try another search or filter.</div>
  </section>
</div>
