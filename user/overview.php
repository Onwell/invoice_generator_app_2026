<?php
require_once __DIR__ . '/_bootstrap.php';
$page   = 'overview';
$search = '';
ob_start();
?>
<section class="card hero-card">
  <div class="hero-l">
    <div class="av big" id="ov-av"><?= e(ini($me['n'])) ?></div>
    <div style="min-width:0">
      <h2 id="ov-greet">Welcome, <?= e(explode(' ', $me['n'])[0]) ?></h2>
      <p>Here's what's happening in your workspace.</p>
      <div class="hero-meta" id="ov-meta"></div>
    </div>
  </div>
  <div class="hero-r">
    <button class="btn pri" data-act="new">New invoice</button>
    <button class="btn" data-act="client">Add client</button>
  </div>
</section>

<section class="kpis" id="ov-kpis"></section>

<section class="card" style="margin-bottom:16px">
  <div class="head"><h2>Revenue collected</h2><span>Last 7 months</span></div>
  <div class="chart" id="ov-chart"></div>
</section>

<section class="grid">
  <div class="card">
    <div class="head"><h2>Usage this cycle</h2><span id="ov-cycle"></span></div>
    <div class="pf-usage" id="ov-usage"></div>
  </div>
  <div class="card">
    <div class="head"><h2>Your plan</h2><a href="account.php">Account</a></div>
    <div class="pf-rows" id="ov-plan"></div>
  </div>
</section>

<section class="card" style="margin-bottom:16px">
  <div class="head" style="padding-bottom:14px"><h2>Recent invoices</h2><a href="invoices.php">View all</a></div>
  <div class="wrap"><table><tbody id="ov-recent"></tbody></table></div>
</section>

<section class="card">
  <div class="head" style="padding-bottom:14px"><h2>Recent sign-in activity</h2><a href="account.php">Security</a></div>
  <div class="wrap"><table>
    <thead><tr><th>Time</th><th>Event</th><th>IP address</th><th>Location</th></tr></thead>
    <tbody id="ov-activity"></tbody></table></div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';