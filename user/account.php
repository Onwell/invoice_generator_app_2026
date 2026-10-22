<?php
require_once __DIR__ . '/_bootstrap.php';
$page   = 'account';
$search = '';
ob_start();
?>
<div class="set account-grid">
  <div class="card sec account-profile">
    <h2>Your profile</h2>
    <div class="f">
      <label>Full name<input id="ac-name"></label>
      <label>Email<input id="ac-mail" type="email"></label>
      <label>Phone<input id="ac-phone"></label>
      <label>Timezone
        <select id="ac-tz">
          <option value="Africa/Harare">Africa/Harare (CAT)</option>
          <option value="Africa/Johannesburg">Africa/Johannesburg (SAST)</option>
          <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
          <option value="Europe/London">Europe/London (GMT/BST)</option>
          <option value="America/New_York">America/New York (ET)</option>
          <option value="UTC">UTC</option>
        </select>
      </label>
    </div>
    <div class="foot" style="margin-top:0;justify-content:flex-start"><button class="btn pri" id="ac-save">Save changes</button></div>
  </div>

  <div class="card sec account-payment">
    <h2>Invoice payment details</h2>
    <p class="sub">These details are shown on invoices so clients know how to pay you.</p>
    <div class="f">
      <label>Payment methods
        <textarea id="ac-payment-methods" rows="3" maxlength="500" placeholder="Bank transfer&#10;Cash"></textarea>
      </label>
      <label>Bank name<input id="ac-bank-name" maxlength="120" autocomplete="off"></label>
      <label>Account name<input id="ac-bank-account-name" maxlength="120" autocomplete="off"></label>
      <label>Account number<input id="ac-bank-account-number" maxlength="120" autocomplete="off"></label>
      <label>Branch<input id="ac-bank-branch" maxlength="120" autocomplete="off"></label>
      <label>SWIFT / BIC<input id="ac-bank-swift" maxlength="120" autocomplete="off"></label>
    </div>
    <div class="foot" style="margin-top:0;justify-content:flex-start"><button class="btn pri" id="ac-payment-save">Save payment details</button></div>
  </div>

  <div class="card sec account-security">
    <h2>Security</h2>
    <ul class="list" id="ac-sec"></ul>
  </div>

  <div class="card sec account-subscription">
    <h2>Your subscription</h2>
    <div class="pf-rows" id="ac-plan"></div>
  </div>

  <div class="card sec account-features">
    <h2>What's included</h2>
    <ul class="list" id="ac-feats"></ul>
  </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';