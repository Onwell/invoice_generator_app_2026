<?php require_once __DIR__ . '/_menu_guard.php'; ?>
<div id="v-profile" hidden>
  <section class="card pf-hero">
    <div class="prof-id">
      <div class="av big" id="pf-av"><?= e(ini($me['n'])) ?></div>
      <div style="min-width:0">
        <h2 id="pf-name"><?= e($me['n']) ?></h2>
        <p class="sub" id="pf-mail"><?= e($me['e']) ?></p>
        <div class="pf-meta" id="pf-meta"></div>
      </div>
    </div>
    <div class="foot" style="margin:0">
      <button class="btn" id="pf-billing">Billing history</button>
      <button class="btn pri" id="pf-manage">Manage plan</button>
    </div>
  </section>
  <section class="kpis" id="pf-kpis"></section>
  <section class="grid">
    <div class="card">
      <div class="head"><h2>Plan &amp; billing</h2><a href="#" id="pf-change">Change plan</a></div>
      <div class="pf-rows" id="pf-plan"></div>
    </div>
    <div class="card">
      <div class="head"><h2>Usage this cycle</h2><span id="pf-cycle"></span></div>
      <div class="pf-usage" id="pf-usage"></div>
    </div>
  </section>
  <section class="grid">
    <div class="card">
      <div class="head"><h2>Account details</h2></div>
      <div class="m" style="padding:14px 22px 22px">
        <div class="f">
          <label>Full name<input id="pf-e-name"></label>
          <label>Email<input id="pf-e-mail" type="email"></label>
          <label>Phone<input id="pf-e-phone"></label>
          <label>Timezone
            <select id="pf-e-tz">
              <option value="Africa/Harare">Africa/Harare (CAT)</option>
              <option value="Africa/Johannesburg">Africa/Johannesburg (SAST)</option>
              <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
              <option value="Europe/London">Europe/London (GMT/BST)</option>
              <option value="America/New_York">America/New York (ET)</option>
              <option value="UTC">UTC</option>
            </select>
          </label>
        </div>
        <div class="foot" style="margin-top:0;justify-content:flex-start"><button class="btn pri" id="pf-save">Save changes</button></div>
      </div>
    </div>
    <div class="card">
      <div class="head"><h2>Security</h2></div>
      <ul class="list" id="pf-sec"></ul>
    </div>
  </section>
  <section class="card" style="margin-bottom:16px">
    <div class="head" style="padding-bottom:14px"><h2>Billing history</h2><span id="pf-card">Visa •••• 4242</span></div>
    <div class="wrap"><table>
      <thead><tr><th>Date</th><th>Invoice</th><th>Description</th><th>Status</th><th class="r">Amount</th><th></th></tr></thead>
      <tbody id="pf-bills"></tbody></table></div>
  </section>
  <section class="card">
    <div class="head" style="padding-bottom:14px"><h2>Recent sign-in activity</h2><a href="#audit">All audit logs</a></div>
    <div class="wrap"><table>
      <thead><tr><th>Time</th><th>Event</th><th>IP address</th><th>Location</th></tr></thead>
      <tbody id="pf-logins"></tbody></table></div>
  </section>
</div>
