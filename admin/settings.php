<?php require_once __DIR__ . '/_menu_guard.php'; ?>
<div id="v-settings" hidden>
  <div class="set">
    <div class="card sec"><h2>Business profile</h2>
      <div class="f"><label>Business name<input id="s-name"></label><label>Billing email<input id="s-mail" type="email"></label></div></div>
    <div class="card sec"><h2>Invoice defaults</h2>
      <div class="f"><label>Currency<select id="s-cur"><option value="$">USD ($)</option><option value="€">EUR (€)</option><option value="£">GBP (£)</option><option value="R">ZAR (R)</option></select></label>
      <label>Default tax (%)<input id="s-tax" type="number" min="0"></label>
      <label>Payment terms (days)<input id="s-terms" type="number" min="0"></label>
      <label>Invoice prefix<input id="s-pre"></label></div>
      <label>Footer note<textarea id="s-note" rows="2"></textarea></label></div>
    <div class="foot" style="justify-content:space-between"><button class="btn" id="reset">Reset demo data</button><button class="btn pri" id="ssave">Save settings</button></div>
  </div>
</div>
