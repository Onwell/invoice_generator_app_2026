<?php require_once __DIR__ . '/_menu_guard.php'; ?>
<div id="v-backups" hidden>
  <section class="kpis" id="bk-kpis" style="grid-template-columns:repeat(3,1fr)"></section>
  <section class="card" style="margin-bottom:16px">
    <div class="head" style="padding-bottom:14px">
      <h2>Backup history</h2>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn sm" id="bk-autobtn">Auto-backup: On</button>
        <button class="btn sm pri" id="bk-create">Create backup now</button>
      </div>
    </div>
    <div class="wrap"><table>
      <thead><tr><th>Backup</th><th>Type</th><th>Created</th><th>Size</th><th>Status</th><th class="r">Actions</th></tr></thead>
      <tbody id="bk-rows"></tbody></table></div>
    <div class="empty" id="bk-empty" hidden>No backups yet. Create your first one.</div>
  </section>
  <section class="grid">
    <div class="card">
      <div class="head"><h2>Backup settings</h2></div>
      <div class="pf-rows" id="bk-settings"></div>
    </div>
    <div class="card">
      <div class="head"><h2>What gets backed up</h2></div>
      <ul class="list" id="bk-included"></ul>
    </div>
  </section>
</div>
