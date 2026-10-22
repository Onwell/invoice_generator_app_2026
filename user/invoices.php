<?php
require_once __DIR__ . '/_bootstrap.php';
$page   = 'invoices';
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

/* Pre-filter rows server-side so a shared/bookmarked ?q= link is correct on first paint */
$all    = $default['inv'];
$counts = ['all' => count($all), 'paid' => 0, 'pending' => 0, 'overdue' => 0];
foreach ($all as $x) {
    if (isset($counts[$x['s']])) $counts[$x['s']]++;
}
$q = strtolower($search);
$initial = array_values(array_filter($all, function ($x) use ($q) {
    return $q === '' || str_contains(strtolower($x['id'] . ' ' . $x['c']), $q);
}));
$show = function (array $x): string {
    return date('j M Y', strtotime($x['i']));
};
$showDue = function (array $x): string {
    return date('j M Y', strtotime($x['d']));
};
ob_start();
?>
<section class="card">
  <div class="tabs" id="tabs">
    <button class="tab on" data-k="all">All<i><?= (int) $counts['all'] ?></i></button>
    <button class="tab" data-k="paid">Paid<i><?= (int) $counts['paid'] ?></i></button>
    <button class="tab" data-k="pending">Pending<i><?= (int) $counts['pending'] ?></i></button>
    <button class="tab" data-k="overdue">Overdue<i><?= (int) $counts['overdue'] ?></i></button>
  </div>
  <div class="bar2">
    <span class="sub" id="cnt"><?= count($initial) ?> invoice<?= count($initial) === 1 ? '' : 's' ?></span>
    <div style="display:flex;gap:8px"><button class="btn sm" id="csv">Export CSV</button></div>
  </div>
  <div class="wrap"><table>
    <thead><tr>
      <th data-s="id">Invoice</th><th data-s="c">Client</th><th data-s="i">Issued</th>
      <th data-s="d">Due</th><th data-s="s">Status</th><th class="r" data-s="a">Amount</th><th></th>
    </tr></thead>
    <tbody id="rows">
      <?php if (!$initial): ?>
        <tr><td colspan="7" class="sub" style="padding:22px;text-align:center">No invoices match. Try another search or filter.</td></tr>
      <?php else: foreach ($initial as $x): ?>
        <tr class="go" data-o="<?= e($x['id']) ?>">
          <td class="id"><?= e($x['id']) ?></td>
          <td class="cl"><?= e($x['c']) ?></td>
          <td class="dt" data-l="Issued"><?= e($show($x)) ?></td>
          <td class="dt" data-l="Due"><?= e($showDue($x)) ?></td>
          <td class="st"><span class="pill <?= e($x['s']) ?>"><?= e(ucfirst($x['s'])) ?></span></td>
          <td class="amt r"><b><?= e($default['set']['cur'] . number_format((float) $x['a'], 2)) ?></b></td>
          <td class="ac dn r">
            <?php if ($x['s'] !== 'paid'): ?>
              <button class="act" data-p="<?= e($x['id']) ?>">Mark as paid</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table></div>
  <div class="empty" id="empty" <?= $initial ? 'hidden' : '' ?>>No invoices match. Try another search or filter.</div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';