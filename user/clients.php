<?php
require_once __DIR__ . '/_bootstrap.php';
$page   = 'clients';
$search = '';

/* Per-client invoice totals computed server-side */
$counts = [];
foreach ($default['inv'] as $inv) {
    $c = $inv['c'];
    if (!isset($counts[$c])) $counts[$c] = ['total' => 0, 'open' => 0];
    $counts[$c]['total']++;
    if ($inv['s'] !== 'paid') $counts[$c]['open']++;
}

ob_start();
?>
<style>
  /* Scoped styles — guarantees correct layout even if the shared layout CSS is stale */
  .clients-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
  .client-card{background:#fff;border:1px solid #E3E7E6;border-radius:14px;padding:22px;display:flex;flex-direction:column;gap:14px}
  .client-card .t{display:flex;gap:12px;align-items:center}
  .client-card .av{background:#E6F0ED;color:#0E5A47;width:42px;height:42px;border-radius:50%;display:grid;place-items:center;font-weight:700;font-size:13px;flex:none}
  .client-card .t b{display:block;font-weight:600;color:#111614;font-size:14px}
  .client-card .t .sub{display:block;color:#66716D;font-size:13px;margin-top:2px}
  .client-card dl{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px 0;border-top:1px solid #E3E7E6;border-bottom:1px solid #E3E7E6;margin:0}
  .client-card dt{color:#66716D;font-size:12.5px}
  .client-card dd{font-weight:600;font-size:16px;color:#111614;margin:2px 0 0}
  .client-card .row{display:flex;gap:8px}
  .client-card .row .btn{flex:1}
  .clients-empty{padding:44px;text-align:center;color:#66716D;background:#fff;border:1px solid #E3E7E6;border-radius:14px;grid-column:1/-1}
</style>
<div style="margin-bottom:16px"><button class="btn" id="addc">Add client</button></div>
<div class="clients-grid" id="cg">
  <?php if (empty($default['cl'])): ?>
    <div class="clients-empty">No clients yet. Add your first one to start invoicing.</div>
  <?php else: foreach ($default['cl'] as $c):
    $n     = $c['n'];
    $email = $c['e'] ?? '';
    $total = $counts[$n]['total'] ?? 0;
    $open  = $counts[$n]['open']  ?? 0;
  ?>
    <div class="client-card">
      <div class="t">
        <div class="av"><?= e(ini($n)) ?></div>
        <div>
          <b><?= e($n) ?></b>
          <span class="sub"><?= e($email !== '' ? $email : 'No email') ?></span>
        </div>
      </div>
      <dl>
        <div><dt>Invoices</dt><dd><?= (int) $total ?></dd></div>
        <div><dt>Open</dt><dd><?= (int) $open ?></dd></div>
      </dl>
      <div class="row">
        <button class="btn sm pri" data-nc="<?= e($n) ?>">New invoice</button>
        <button class="btn sm" data-vc="<?= e($n) ?>">View invoices</button>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';