<?php
declare(strict_types=1);
header('Location: overview.php');
exit;

/**
 * Ledgerly – signed-in user workspace
 *
 * Seed data lives in PHP so it's easy to edit and easy to swap
 * for real data from a database or session.
 *
 * Quick preview: php -S localhost:8000 → http://localhost:8000/user.php
 */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function ini(string $name): string {
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        if ($p !== '') $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out ?: '?';
}
function relDay(int $offset): string {
    return date('Y-m-d', strtotime("{$offset} days"));
}
function relStamp(float $hoursAgo): string {
    return date('c', time() - (int) round($hoursAgo * 3600));
}

$year = (int) date('Y');

/* ---------- Nav labels, titles and icons ---------- */
$icons = [
    'overview' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    'invoices' => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 12h7M9 16h7"/>',
    'clients'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.5 3.2-5.5 6.5-5.5s5.9 2 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 010 6.8M18 14.8c1.9.7 3.1 2.4 3.5 5.2"/>',
    'payments' => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/>',
    'account'  => '<circle cx="12" cy="8" r="4"/><path d="M4 20c.8-4.2 4-6.5 8-6.5s7.2 2.3 8 6.5"/>',
];

$pages = [
    'overview' => ['Overview',    "Welcome back. Here's your workspace."],
    'invoices' => ['My invoices', 'Create, track and collect your invoices.'],
    'clients'  => ['My clients',  'The people and businesses you bill.'],
    'payments' => ['Payments',    'Payments recorded against your invoices.'],
    'account'  => ['Account',     'Your profile, security and plan.'],
];

/* ---------- Seed data (used the first time, and by "Reset") ---------- */
function inv(string $id, string $client, int $issued, int $due, int $amount, string $status): array {
    return [
        'id'    => $id,
        'c'     => $client,
        'i'     => relDay($issued),
        'd'     => relDay($due),
        'a'     => $amount,
        's'     => $status,
        'items' => [['d' => 'Professional services', 'q' => 1, 'p' => $amount]],
        't'     => 0,
    ];
}

$default = [
    'me' => [
        'n'            => 'Amara Moyo',
        'e'            => 'amara@ledgerly.app',
        'role'         => 'Administrator',
        'phone'        => '+263 77 123 4567',
        'tz'           => 'Africa/Harare',
        'plan'         => 'Ledgerly Pro',
        'cycle'        => 'Monthly',
        'renew'        => relDay(12),
        'payment'      => 'Visa •••• 4242',
        'seats'        => 5,
        'seatsUsed'    => 4,
        'invoicesUsed' => 38,
        'invoiceLimit' => 100,
        'clientsUsed'  => 5,
        'clientLimit'  => 50,
        'storageUsed'  => 1.8,
        'storageLimit' => 10,
        'pwChanged'    => relDay(-42),
    ],
    'inv' => [
        inv('INV-1048', 'Northwind Logistics', -38, -8,  8450, 'overdue'),
        inv('INV-1049', 'Kuda Studios',        -21,  9,  2380, 'pending'),
        inv('INV-1050', 'Baobab Capital',      -18, 12, 15200, 'pending'),
        inv('INV-1051', 'Meridian Health',     -30, -1,  4975, 'overdue'),
        inv('INV-1052', 'Northwind Logistics', -14, 16,  6120, 'paid'),
        inv('INV-1053', 'Zambezi Foods',       -12, 18,  3340, 'paid'),
        inv('INV-1054', 'Baobab Capital',       -9, 21,  9800, 'paid'),
        inv('INV-1055', 'Kuda Studios',         -4, 26,  1560, 'pending'),
    ],
    'cl' => [
        ['n' => 'Northwind Logistics', 'e' => 'accounts@northwind.co'],
        ['n' => 'Kuda Studios',        'e' => 'hello@kuda.studio'],
        ['n' => 'Baobab Capital',      'e' => 'finance@baobab.com'],
        ['n' => 'Meridian Health',     'e' => 'ap@meridian.health'],
        ['n' => 'Zambezi Foods',       'e' => 'orders@zambezi.foods'],
    ],
    'pay' => [
        ['inv' => 'INV-1052', 'c' => 'Northwind Logistics', 'd' => relDay(-6), 'm' => 'Bank transfer', 'a' => 6120],
        ['inv' => 'INV-1053', 'c' => 'Zambezi Foods',       'd' => relDay(-5), 'm' => 'Card',          'a' => 3340],
        ['inv' => 'INV-1054', 'c' => 'Baobab Capital',      'd' => relDay(-2), 'm' => 'Bank transfer', 'a' => 9800],
    ],
    'audit' => [
        ['t' => relStamp(0.3),  'ev' => 'login',  'ip' => '102.65.14.8',   'loc' => 'Harare, ZW'],
        ['t' => relStamp(14.7), 'ev' => 'login',  'ip' => '102.65.14.8',   'loc' => 'Harare, ZW'],
        ['t' => relStamp(38.2), 'ev' => 'failed', 'ip' => '45.221.10.77',  'loc' => 'Lagos, NG'],
        ['t' => relStamp(46.9), 'ev' => 'login',  'ip' => '102.65.14.8',   'loc' => 'Harare, ZW'],
        ['t' => relStamp(72.4), 'ev' => 'login',  'ip' => '102.65.14.8',   'loc' => 'Harare, ZW'],
    ],
    'set' => [
        'name'  => 'Ledgerly Studio',
        'mail'  => 'billing@ledgerly.app',
        'cur'   => '$',
        'tax'   => 15,
        'terms' => 30,
        'pre'   => 'INV-',
        'note'  => 'Thank you for your business. Please pay by the due date.',
    ],
];

$me = $default['me'];
$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$defaultJson = json_encode($default, $jsonFlags) ?: '{}';
$pagesJson   = json_encode($pages,   $jsonFlags) ?: '{}';
$iconsJson   = json_encode($icons,   $jsonFlags) ?: '{}';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e('Ledgerly – My workspace') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--accent:#0E5A47;--soft:#E6F0ED;--bg:#F6F7F7;--surface:#fff;--ink:#111614;--muted:#66716D;--line:#E3E7E6;color-scheme:light}
*{box-sizing:border-box;margin:0}
body{font-family:"Hanken Grotesk",system-ui,-apple-system,"Segoe UI",sans-serif;background:var(--bg);color:var(--ink);font-size:14px;line-height:1.5;font-variant-numeric:tabular-nums;-webkit-text-size-adjust:100%}
button,input,select,textarea{font:inherit;color:inherit}
a{color:inherit;text-decoration:none}
:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
[hidden]{display:none!important}

.app{display:grid;grid-template-columns:248px 1fr;min-height:100vh}
.side{background:var(--accent);color:#fff;padding:28px 18px;display:flex;flex-direction:column;gap:28px;position:sticky;top:0;height:100vh}
.brand{display:flex;align-items:center;gap:10px;font-size:19px;font-weight:700;letter-spacing:-.02em;padding:0 10px}
.mark{width:30px;height:30px;border-radius:8px;background:#fff;color:var(--accent);display:grid;place-items:center;font-weight:700}
.nav{display:flex;flex-direction:column;gap:2px}
.nav a{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:rgba(255,255,255,.74);font-weight:500}
.nav a:hover{background:rgba(255,255,255,.1);color:#fff}
.nav a.on{background:#fff;color:var(--accent)}
.nav svg{width:18px;height:18px;flex:none}
.me{margin-top:auto;display:flex;gap:10px;align-items:center;padding:12px;border-top:1px solid rgba(255,255,255,.2)}
.me .who{flex:1;min-width:0}
.me b{display:block;font-size:13.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.me small{display:block;color:rgba(255,255,255,.66)}
.lout{flex:none;width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.12);color:#fff;display:grid;place-items:center;cursor:pointer;padding:0}
.lout:hover{background:rgba(255,255,255,.24)}
.lout svg{width:16px;height:16px}
.av{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.18);display:grid;place-items:center;font-weight:600;font-size:13px;flex:none}

main{padding:28px 40px 56px;min-width:0}
.top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px}
h1{font-size:26px;font-weight:600;letter-spacing:-.025em}
.top p{color:var(--muted)}
.tools{display:flex;gap:10px;align-items:center;position:relative}
.search{position:relative}
.search input{width:250px;padding:10px 14px 10px 38px;border:1px solid var(--line);background:var(--surface);border-radius:10px}
.search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--muted)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border-radius:10px;border:1px solid var(--line);background:var(--surface);cursor:pointer;font-weight:600;white-space:nowrap}
.btn:hover{border-color:var(--accent)}
.btn.pri{background:var(--accent);border-color:var(--accent);color:#fff}
.btn.pri:hover{filter:brightness(1.15)}
.btn.sm{padding:6px 12px;font-size:13px}
.btn svg{width:16px;height:16px}
.ico{width:42px;padding:0;height:42px;position:relative}
.dot{position:absolute;top:8px;right:9px;min-width:16px;height:16px;border-radius:8px;background:var(--accent);color:#fff;font-size:10px;display:grid;place-items:center;padding:0 4px;font-weight:700}
.pop{position:absolute;top:50px;right:0;width:340px;max-width:92vw;background:var(--surface);border:1px solid var(--line);border-radius:14px;box-shadow:0 20px 50px rgba(10,30,24,.16);z-index:30;padding:8px}
.pop h3{padding:10px 12px 6px;font-size:14px}
.pop .it{display:flex;gap:10px;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px}
.pop .it:hover{background:var(--bg)}
.pop small{color:var(--muted);display:block}

.card{background:var(--surface);border:1px solid var(--line);border-radius:14px}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px}
.kpi{padding:20px 22px}
.kpi span{color:var(--muted);font-weight:500}
.kpi b{display:block;font-size:27px;font-weight:600;letter-spacing:-.03em;margin:6px 0 2px}
.kpi small{color:var(--accent);font-weight:600}
.kpi.hero{background:var(--accent);border-color:var(--accent);color:#fff}
.kpi.hero span,.kpi.hero small{color:rgba(255,255,255,.8)}
.grid{display:grid;grid-template-columns:1.2fr 1fr;gap:16px;margin-bottom:16px}
h2{font-size:16px;font-weight:600;letter-spacing:-.01em}
.head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:20px 22px 0;flex-wrap:wrap}
.head span,.head a{color:var(--muted)}
.head a{font-weight:600;color:var(--accent)}
.list{list-style:none;padding:12px 22px 18px}
.list li{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--line)}
.list li:last-child{border:0}
.list .av{background:var(--soft);color:var(--accent);width:36px;height:36px;font-size:12px}
.list div{flex:1;min-width:0}
.list small,.sub{display:block;color:var(--muted)}

.tabs{display:flex;gap:4px;padding:14px 22px 0;border-bottom:1px solid var(--line);overflow-x:auto}
.tab{background:none;border:0;padding:10px 12px;cursor:pointer;color:var(--muted);font-weight:500;border-bottom:2px solid transparent;margin-bottom:-1px;white-space:nowrap}
.tab.on{color:var(--ink);border-color:var(--accent);font-weight:600}
.tab i{font-style:normal;background:var(--bg);border-radius:20px;padding:1px 8px;margin-left:6px;font-size:12px}
.wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{text-align:left;font-weight:500;color:var(--muted);padding:14px 22px;border-bottom:1px solid var(--line);white-space:nowrap}
th[data-s]{cursor:pointer}th[data-s]:hover{color:var(--ink)}
td{padding:14px 22px;border-bottom:1px solid var(--line);vertical-align:middle}
tbody tr:last-child td{border-bottom:0}
tbody tr.go{cursor:pointer}tbody tr.go:hover{background:var(--bg)}
.r{text-align:right}
.id{font-weight:600}
.pill{display:inline-flex;align-items:center;gap:6px;padding:3px 11px;border-radius:20px;font-size:12.5px;font-weight:600}
.pill::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.paid{background:var(--soft);color:var(--accent)}
.pending{color:var(--ink);box-shadow:inset 0 0 0 1px var(--line)}
.overdue{background:var(--accent);color:#fff}
.act{background:none;border:0;color:var(--accent);font-weight:600;cursor:pointer;padding:4px 8px;border-radius:6px}
.act:hover{background:var(--soft)}
.empty{padding:44px;text-align:center;color:var(--muted)}
.bar2{display:flex;justify-content:space-between;gap:10px;align-items:center;padding:14px 22px;flex-wrap:wrap}

/* Overview hero */
.hero-card{padding:22px;display:flex;justify-content:space-between;gap:18px;align-items:center;flex-wrap:wrap;margin-bottom:16px}
.hero-l{display:flex;gap:14px;align-items:center;min-width:0}
.hero-l h2{font-size:20px}
.hero-l p{color:var(--muted);margin-top:2px}
.hero-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;align-items:center}
.hero-r{display:flex;gap:8px;flex-wrap:wrap}
.av.big{width:56px;height:56px;font-size:18px;background:var(--soft);color:var(--accent);font-weight:700}

/* Usage */
.pf-rows{padding:6px 22px 18px}
.pf-rows>div{display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid var(--line);align-items:center}
.pf-rows>div:last-child{border:0}
.pf-rows span{color:var(--muted)}
.pf-usage{padding:18px 22px 22px;display:grid;gap:16px}
.ub>span{display:flex;justify-content:space-between;gap:12px;font-weight:500;align-items:baseline}
.ub em{font-style:normal;color:var(--muted)}
.ub b{font-size:13px}
.ub .track{height:8px;background:var(--bg);border-radius:20px;overflow:hidden;margin-top:7px;box-shadow:inset 0 0 0 1px var(--line)}
.ub .fill{height:100%;background:var(--accent);border-radius:20px}

/* Clients */
.cgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.cc{padding:22px;display:flex;flex-direction:column;gap:14px}
.cc .t{display:flex;gap:12px;align-items:center}
.cc .av{background:var(--soft);color:var(--accent);width:42px;height:42px}
.cc dl{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px 0;border-block:1px solid var(--line)}
.cc dt{color:var(--muted);font-size:12.5px}.cc dd{font-weight:600;font-size:16px}
.cc .row{display:flex;gap:8px}.cc .row .btn{flex:1}

/* Account */
.set{max-width:760px}
.set .sec{padding:24px}
.set .f{margin-top:16px}
.set .sec+.sec{margin-top:16px}

dialog{border:0;border-radius:18px;padding:0;width:min(640px,94vw);max-height:92vh;background:var(--surface);color:var(--ink);box-shadow:0 30px 80px rgba(0,0,0,.28)}
dialog::backdrop{background:rgba(10,18,15,.5)}
dialog[open]{animation:pop .18s ease-out}
@keyframes pop{from{opacity:0;transform:translateY(8px)}}
.m{padding:26px 28px}
.m h2{font-size:20px;margin-bottom:4px}
.m>p{color:var(--muted);margin-bottom:20px}
.f{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px}
label{display:flex;flex-direction:column;gap:6px;font-weight:500}
input,select,textarea{padding:10px 12px;border:1px solid var(--line);border-radius:9px;background:var(--surface);width:100%}
.li{display:grid;grid-template-columns:1fr 64px 96px 28px;gap:8px;margin-bottom:8px}
.li button{background:none;border:0;color:var(--muted);cursor:pointer;font-size:18px}
.add{margin:4px 0 18px}
.tot{border-top:1px solid var(--line);padding-top:14px;display:grid;gap:6px;margin-left:auto;width:min(260px,100%)}
.tot div{display:flex;justify-content:space-between;color:var(--muted)}
.tot .g{color:var(--ink);font-size:18px;font-weight:700}
.foot{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;flex-wrap:wrap}
.sheet .hd{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;padding-bottom:20px;border-bottom:2px solid var(--accent)}
.sheet .hd h2{font-size:24px;color:var(--accent)}
.sheet .meta{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;padding:20px 0}
.sheet .meta small{color:var(--muted);display:block}
.sheet table th,.sheet table td{padding:10px 0}
.sheet table th:last-child,.sheet table td:last-child{text-align:right}
.sheet .note{margin-top:20px;color:var(--muted);font-size:13px}
.toast{position:fixed;bottom:calc(24px + env(safe-area-inset-bottom));left:50%;transform:translateX(-50%) translateY(20px);background:var(--ink);color:#fff;padding:11px 18px;border-radius:10px;font-weight:600;opacity:0;transition:.2s;pointer-events:none;z-index:99;max-width:90vw}
.toast.show{opacity:1;transform:translateX(-50%)}
.bnav{display:none}
.mtop{display:none}

/* Signed-out overlay */
.so{position:fixed;inset:0;background:var(--bg);display:grid;place-items:center;z-index:200;padding:24px}
.so-card{padding:36px 32px;max-width:380px;width:100%;text-align:center;display:flex;flex-direction:column;align-items:center;gap:10px}
.so-mark{background:var(--accent);color:#fff;width:44px;height:44px;font-size:19px;border-radius:12px;margin-bottom:6px}
.so-card h2{font-size:20px}
.so-card p{color:var(--muted);margin-bottom:10px}

@media print{.app,.toast,.noprint,.so{display:none!important}dialog{position:static;box-shadow:none;width:100%;max-height:none}dialog::backdrop{display:none}}

@media (max-width:1100px){.kpis{grid-template-columns:1fr 1fr}.grid{grid-template-columns:1fr}main{padding:24px 24px 56px}}
@media (max-width:820px){
  .app{grid-template-columns:1fr}
  .side{display:none}
  .mtop{display:flex;align-items:center;justify-content:space-between;padding:calc(12px + env(safe-area-inset-top)) 16px 12px;background:var(--accent);color:#fff;position:sticky;top:0;z-index:20}
  .mtop .brand{padding:0}
  .mtop .muser{display:flex;align-items:center;gap:8px;min-width:0}
  .mtop .mname{font-weight:600;font-size:13px;max-width:130px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .mtop .lout{width:34px;height:34px;border-color:rgba(255,255,255,.3)}
  main{padding:20px 16px calc(96px + env(safe-area-inset-bottom))}
  h1{font-size:22px}
  .top{margin-bottom:18px}
  .tools{width:100%}.search{flex:1}.search input{width:100%}
  .bnav{display:grid;grid-template-columns:repeat(5,1fr);position:fixed;left:0;right:0;bottom:0;background:var(--surface);border-top:1px solid var(--line);padding:6px 6px calc(6px + env(safe-area-inset-bottom));z-index:25}
  .bnav a{display:flex;flex-direction:column;align-items:center;gap:2px;padding:6px 0;color:var(--muted);font-size:10px;font-weight:500;border-radius:10px;white-space:nowrap}
  .bnav a.on{color:var(--accent);font-weight:700}
  .bnav svg{width:22px;height:22px}
  .pop{position:fixed;left:12px;right:12px;top:70px;width:auto;max-width:none}
}
@media (max-width:720px){
  table,thead,tbody,tr,td{display:block}
  thead{display:none}
  tbody tr{padding:14px 18px;border-bottom:1px solid var(--line);display:grid;grid-template-columns:1fr auto;gap:2px 12px}
  tbody tr:last-child{border:0}
  td{padding:0;border:0}
  td[data-l]::before{content:attr(data-l) " ";color:var(--muted)}
  td.id{grid-column:1}td.amt{grid-column:2;grid-row:1;text-align:right}
  td.cl{grid-column:1/-1;font-weight:500}
  td.dt{grid-column:1/-1;color:var(--muted)}
  td.st{grid-column:1;margin-top:6px}td.ac{grid-column:2;text-align:right;margin-top:4px}
  td.dn{display:none}
  .head,.bar2,.tabs{padding-left:16px;padding-right:16px}
}
@media (max-width:520px){
  .kpis{grid-template-columns:1fr 1fr;gap:10px}.kpi{padding:16px}.kpi b{font-size:21px}
  .f{grid-template-columns:1fr}.m{padding:22px 18px}
  .sheet .meta{grid-template-columns:1fr 1fr}
  .tools .btn.pri span{display:none}.tools .btn.pri{width:42px;padding:0}
  dialog{width:100vw;max-width:100vw;margin:auto 0 0;border-radius:18px 18px 0 0;max-height:94vh}
  .li{grid-template-columns:1fr 54px 80px 24px}
  .hero-card{padding:18px}
  .hero-l h2{font-size:18px}
  .av.big{width:48px;height:48px;font-size:16px}
  .hero-r{width:100%}.hero-r .btn{flex:1}
}
@media (max-width:400px){.mtop .mname{display:none}}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<div class="mtop">
  <div class="brand"><div class="mark">L</div>Ledgerly</div>
  <div class="muser">
    <b class="mname" id="mname"><?= e($me['n']) ?></b>
    <div class="av" id="mav"><?= e(ini($me['n'])) ?></div>
    <button class="lout" id="logout2" title="Log out" aria-label="Log out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3"/><path d="M10 17l-5-5 5-5"/><path d="M5 12h11"/></svg></button>
  </div>
</div>
<div class="app">
  <aside class="side">
    <div class="brand"><div class="mark">L</div>Ledgerly</div>
    <nav class="nav" id="nav" aria-label="Main"></nav>
    <div class="me">
      <div class="av" id="me-av"><?= e(ini($me['n'])) ?></div>
      <div class="who"><b id="me-name"><?= e($me['n']) ?></b><small id="me-role"><?= e($me['role']) ?></small></div>
      <button class="lout" id="logout" title="Log out" aria-label="Log out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3"/><path d="M10 17l-5-5 5-5"/><path d="M5 12h11"/></svg></button>
    </div>
  </aside>
  <main>
    <header class="top">
      <div><h1 id="ttl"></h1><p id="sub"></p></div>
      <div class="tools">
        <label class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg><input id="q" type="search" placeholder="Search invoices or clients" aria-label="Search"></label>
        <button class="btn ico" id="bell" aria-label="Notifications"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 17V11a6 6 0 1112 0v6l1.5 2h-15z"/><path d="M10 21h4"/></svg><b class="dot" id="bc"></b></button>
        <button class="btn pri" id="new"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg><span>New invoice</span></button>
        <div class="pop" id="pop" hidden></div>
      </div>
    </header>

    <div id="v-overview" hidden>
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
      <section class="grid">
        <div class="card">
          <div class="head"><h2>Usage this cycle</h2><span id="ov-cycle"></span></div>
          <div class="pf-usage" id="ov-usage"></div>
        </div>
        <div class="card">
          <div class="head"><h2>Your plan</h2><a href="#account">Account</a></div>
          <div class="pf-rows" id="ov-plan"></div>
        </div>
      </section>
      <section class="card" style="margin-bottom:16px">
        <div class="head" style="padding-bottom:14px"><h2>Recent invoices</h2><a href="#invoices">View all</a></div>
        <div class="wrap"><table><tbody id="ov-recent"></tbody></table></div>
      </section>
      <section class="card">
        <div class="head" style="padding-bottom:14px"><h2>Recent sign-in activity</h2><a href="#account">Security</a></div>
        <div class="wrap"><table>
          <thead><tr><th>Time</th><th>Event</th><th>IP address</th><th>Location</th></tr></thead>
          <tbody id="ov-activity"></tbody></table></div>
      </section>
    </div>

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

    <div id="v-clients" hidden>
      <div style="margin-bottom:16px"><button class="btn" id="addc">Add client</button></div>
      <div class="cgrid" id="cg"></div>
    </div>

    <div id="v-payments" hidden>
      <section class="card">
        <div class="head" style="padding-bottom:14px"><h2>Payments recorded</h2><span class="sub" id="pcnt"></span></div>
        <div class="wrap"><table>
          <thead><tr><th>Received</th><th>Invoice</th><th>Client</th><th>Method</th><th class="r">Amount</th></tr></thead>
          <tbody id="pr"></tbody></table></div>
        <div class="empty" id="pe" hidden>No payments recorded yet.</div>
      </section>
    </div>

    <div id="v-account" hidden>
      <div class="set">
        <div class="card sec">
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

        <div class="card sec">
          <h2>Security</h2>
          <ul class="list" id="ac-sec"></ul>
        </div>

        <div class="card sec">
          <h2>Your subscription</h2>
          <div class="pf-rows" id="ac-plan"></div>
        </div>

        <div class="card sec">
          <h2>What's included</h2>
          <ul class="list" id="ac-feats"></ul>
        </div>
      </div>
    </div>
  </main>
</div>
<nav class="bnav" id="bnav" aria-label="Main"></nav>

<dialog id="dlg"><div class="m">
  <h2>Create invoice</h2><p>Fill in the details. The total updates as you type.</p>
  <div class="f"><label>Client<input id="cl" list="cls" placeholder="Choose or type a client"></label><datalist id="cls"></datalist>
  <label>Tax (%)<input id="tax" type="number" min="0"></label>
  <label>Issue date<input id="iss" type="date"></label><label>Due date<input id="due" type="date"></label></div>
  <div id="items"></div>
  <button class="btn add sm" id="addItem" type="button">Add line item</button>
  <div class="tot"><div><span>Subtotal</span><span id="sb">0</span></div><div><span>Tax</span><span id="tx">0</span></div><div class="g"><span>Total</span><span id="gr">0</span></div></div>
  <div class="foot"><button class="btn" id="cancel" type="button">Cancel</button><button class="btn pri" id="save" type="button">Generate invoice</button></div>
</div></dialog>

<dialog id="vw"><div class="m sheet" id="sheet"></div></dialog>

<dialog id="cdlg"><div class="m"><h2>Add client</h2><p>Saved clients appear when you create invoices.</p>
  <div class="f"><label>Name<input id="c-n"></label><label>Email<input id="c-e" type="email"></label></div>
  <div class="foot"><button class="btn" id="c-x" type="button">Cancel</button><button class="btn pri" id="c-s" type="button">Save client</button></div></div></dialog>

<div class="so" id="so" hidden>
  <div class="card so-card">
    <div class="mark so-mark">L</div>
    <h2>You've been signed out</h2>
    <p>Your session has ended securely. Sign back in to keep working.</p>
    <button class="btn pri" id="back">Sign back in</button>
  </div>
</div>

<div class="toast" id="toast" role="status"></div>

<script>
const $=s=>document.querySelector(s);
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const day=o=>{const d=new Date();d.setDate(d.getDate()+o);return d.toISOString().slice(0,10)};
const stamp=h=>new Date(Date.now()-h*3600000).toISOString();
const when=t=>new Date(t).toLocaleString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
const show=d=>new Date(d+'T00:00').toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'});
const daysUntil=d=>Math.max(0,Math.round((new Date(d+'T00:00')-new Date(new Date().toISOString().slice(0,10)+'T00:00'))/86400000));
const ini=n=>n.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
const greet=()=>{const h=new Date().getHours();return h<12?'Good morning':h<18?'Good afternoon':'Good evening'};

/* Icons, pages and seed data are provided by PHP */
const IC    = <?= $iconsJson ?>;
const PAGES = <?= $pagesJson ?>;
const __INITIAL__ = <?= $defaultJson ?>;
const DEF   = () => JSON.parse(JSON.stringify(__INITIAL__));
const svg=n=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">${IC[n]}</svg>`;

let S;
try{
  const saved=JSON.parse(localStorage.getItem('ledgerly-user')||'null');
  S = saved && saved.inv ? Object.assign(DEF(), saved) : DEF();
  if(!S.me) S.me = DEF().me;
  if(!Array.isArray(S.audit)||!S.audit.length) S.audit = DEF().audit;
}catch(e){ S = DEF(); }
const save=()=>{try{localStorage.setItem('ledgerly-user',JSON.stringify(S))}catch(e){}};
const fmt=n=>S.set.cur+n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});

let page='overview', filter='all', term='', sort={k:'i',dir:-1};

function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('show');clearTimeout(t._h);t._h=setTimeout(()=>t.classList.remove('show'),2400)}
const openCount=()=>S.inv.filter(x=>x.s!='paid').length;
const paidCount=()=>S.inv.filter(x=>x.s=='paid').length;

/* Nav */
const navHTML=Object.keys(PAGES).map(k=>`<a href="#${k}" data-p="${k}">${svg(k)}<span>${PAGES[k][0]}</span></a>`).join('');
$('#nav').innerHTML=navHTML; $('#bnav').innerHTML=navHTML;

/* Signed-in identity */
function syncMe(){
  const m=S.me;
  $('#me-name').textContent=m.n;
  $('#me-role').textContent=m.role;
  $('#me-av').textContent=ini(m.n);
  $('#mname').textContent=m.n;
  $('#mav').textContent=ini(m.n);
}

/* Shared bits */
function usageBlock(label,used,limit,unit=''){
  const p=Math.min(100,Math.max(0,Math.round(used/limit*100)));
  return `<div class="ub"><span><em>${label}</em><b>${used}${unit} of ${limit}${unit}</b></span>
    <div class="track"><div class="fill" style="width:${p}%"></div></div></div>`;
}

/* Overview */
function overview(){
  const m=S.me;
  $('#ov-av').textContent=ini(m.n);
  $('#ov-greet').textContent=greet()+', '+m.n.split(' ')[0];
  const days=daysUntil(m.renew);
  $('#ov-meta').innerHTML=`<span class="pill paid">${esc(m.plan)}</span>
    <span class="sub">Renews ${show(m.renew)} · in ${days} day${days==1?'':'s'}</span>`;

  $('#ov-kpis').innerHTML=`
    <div class="card kpi hero"><span>Current plan</span><b>${esc(m.plan)}</b><small>${esc(m.cycle)} · active</small></div>
    <div class="card kpi"><span>Renewal</span><b>${days} day${days==1?'':'s'}</b><small>${show(m.renew)}</small></div>
    <div class="card kpi"><span>Invoices sent</span><b>${S.inv.length}</b><small>${paidCount()} paid · ${openCount()} open</small></div>
    <div class="card kpi"><span>Clients</span><b>${S.cl.length}</b><small>${Math.max(0,m.clientLimit-S.cl.length)} slots left</small></div>`;

  $('#ov-cycle').textContent='Resets '+show(m.renew);
  $('#ov-usage').innerHTML=
    usageBlock('Invoices created',m.invoicesUsed,m.invoiceLimit)+
    usageBlock('Clients',m.clientsUsed,m.clientLimit)+
    usageBlock('Team seats',m.seatsUsed,m.seats)+
    usageBlock('Storage',m.storageUsed,m.storageLimit,' GB');

  $('#ov-plan').innerHTML=[
    ['Plan',esc(m.plan)],
    ['Billing cycle',esc(m.cycle)],
    ['Renews on',show(m.renew)],
    ['Payment method',esc(m.payment)],
    ['Status','<span class="pill paid">Active</span>']
  ].map(([k,v])=>`<div><span>${k}</span><b>${v}</b></div>`).join('');

  const rec=S.inv.slice().sort((a,b)=>b.i.localeCompare(a.i)).slice(0,5);
  $('#ov-recent').innerHTML=rec.map(x=>`<tr class="go" data-o="${x.id}">
    <td class="id">${x.id}</td><td class="cl">${esc(x.c)}</td>
    <td class="dt" data-l="Due">${show(x.d)}</td>
    <td class="st"><span class="pill ${x.s}">${x.s[0].toUpperCase()+x.s.slice(1)}</span></td>
    <td class="amt r"><b>${fmt(x.a)}</b></td></tr>`).join('')||'<tr><td class="sub">No invoices yet.</td></tr>';

  const lg=S.audit.slice().sort((a,b)=>b.t.localeCompare(a.t)).slice(0,5);
  $('#ov-activity').innerHTML=lg.map(a=>`<tr>
    <td class="dt" data-l="Time">${when(a.t)}</td>
    <td data-l="Event"><span class="pill ${a.ev=='login'?'paid':'overdue'}">${a.ev=='login'?'Login success':'Failed login'}</span></td>
    <td class="dn">${esc(a.ip)}</td><td class="dn">${esc(a.loc)}</td></tr>`).join('');
}

/* Invoices */
function rowHTML(x,full){
  return `<tr class="go" data-o="${x.id}"><td class="id">${x.id}</td><td class="cl">${esc(x.c)}</td>${full?`<td class="dt" data-l="Issued">${show(x.i)}</td>`:''}<td class="dt" data-l="Due">${show(x.d)}</td>
  <td class="st"><span class="pill ${x.s}">${x.s[0].toUpperCase()+x.s.slice(1)}</span></td><td class="amt r"><b>${fmt(x.a)}</b></td>
  <td class="ac dn r">${x.s=='paid'?'':'<button class="act" data-p="'+x.id+'">Mark as paid</button>'}</td></tr>`;
}
function invoices(){
  const n=k=>k=='all'?S.inv.length:S.inv.filter(x=>x.s==k).length;
  $('#tabs').innerHTML=[['all','All'],['paid','Paid'],['pending','Pending'],['overdue','Overdue']].map(([k,l])=>`<button class="tab ${filter==k?'on':''}" data-k="${k}">${l}<i>${n(k)}</i></button>`).join('');
  const t=term.toLowerCase();
  const l=S.inv.filter(x=>(filter=='all'||x.s==filter)&&(x.id+x.c).toLowerCase().includes(t)).sort((a,b)=>{const A=a[sort.k],B=b[sort.k];return (A>B?1:A<B?-1:0)*sort.dir});
  $('#rows').innerHTML=l.map(x=>rowHTML(x,true)).join('');
  $('#empty').hidden=l.length>0; $('#cnt').textContent=l.length+' invoice'+(l.length==1?'':'s');
}

/* Clients — counts only, no money */
function clients(){
  $('#cg').innerHTML=S.cl.map(c=>{
    const l=S.inv.filter(x=>x.c==c.n), open=l.filter(x=>x.s!='paid').length;
    return `<div class="card cc">
      <div class="t"><div class="av">${ini(c.n)}</div><div><b>${esc(c.n)}</b><span class="sub">${esc(c.e||'No email')}</span></div></div>
      <dl><div><dt>Invoices</dt><dd>${l.length}</dd></div><div><dt>Open</dt><dd>${open}</dd></div></dl>
      <div class="row"><button class="btn sm pri" data-nc="${esc(c.n)}">New invoice</button><button class="btn sm" data-vc="${esc(c.n)}">View invoices</button></div>
    </div>`;
  }).join('')||'<div class="card empty">No clients yet. Add your first one to start invoicing.</div>';
}

/* Payments — history only, no totals */
function payments(){
  $('#pcnt').textContent=S.pay.length+' payment'+(S.pay.length==1?'':'s');
  $('#pr').innerHTML=S.pay.slice().reverse().map(p=>`<tr>
    <td class="dt" data-l="Received">${show(p.d)}</td>
    <td class="id" data-l="Invoice">${p.inv}</td>
    <td data-l="Client">${esc(p.c)}</td>
    <td data-l="Method">${esc(p.m)}</td>
    <td class="r amt" data-l="Amount"><b>${fmt(p.a)}</b></td></tr>`).join('');
  $('#pe').hidden=S.pay.length>0;
}

/* Account */
function account(){
  const m=S.me;
  $('#ac-name').value=m.n;
  $('#ac-mail').value=m.e;
  $('#ac-phone').value=m.phone||'';
  $('#ac-tz').value=m.tz||'Africa/Harare';

  $('#ac-sec').innerHTML=`
    <li><div class="av">✓</div><div><b>Email verified</b><small>${esc(m.e)}</small></div><span class="pill paid">Verified</span></li>
    <li><div class="av">2F</div><div><b>Two-factor authentication</b><small>Authenticator app</small></div><span class="pill paid">Enabled</span></li>
    <li><div class="av">PW</div><div><b>Password</b><small>Last changed ${show(m.pwChanged||day(-42))}</small></div><button class="btn sm" data-pw>Change</button></li>
    <li><div class="av">DV</div><div><b>Active sessions</b><small>2 devices signed in</small></div><button class="btn sm" data-so>Sign out all</button></li>`;

  $('#ac-plan').innerHTML=[
    ['Plan',esc(m.plan)],
    ['Billing cycle',esc(m.cycle)],
    ['Renews on',show(m.renew)],
    ['Seats',m.seatsUsed+' of '+m.seats+' in use'],
    ['Payment method',esc(m.payment)],
    ['Status','<span class="pill paid">Active</span>']
  ].map(([k,v])=>`<div><span>${k}</span><b>${v}</b></div>`).join('');

  $('#ac-feats').innerHTML=[
    `Up to ${m.invoiceLimit} invoices every month`,
    `Up to ${m.clientLimit} client records`,
    `${m.seats} team seats with role-based access`,
    `${m.storageLimit} GB of document storage`,
    'Automated payment reminders',
    'Priority email support'
  ].map(f=>`<li><div class="av">✓</div><div><b>${f}</b></div></li>`).join('');
}

/* Notifications bell */
function bell(){
  const o=S.inv.filter(x=>x.s=='overdue');
  $('#bc').textContent=o.length; $('#bc').hidden=!o.length;
  $('#pop').innerHTML=`<h3>Needs attention</h3>`+(o.length?o.map(x=>`<div class="it"><div><b>${x.id} · ${esc(x.c)}</b><small>Overdue since ${show(x.d)}</small></div><button class="btn sm" data-r="${x.id}">Remind</button></div>`).join(''):'<div class="it"><small>Nothing is overdue.</small></div>');
}

/* Router */
function route(){
  const h=(location.hash||'#overview').slice(1);
  page=PAGES[h]?h:'overview';
  Object.keys(PAGES).forEach(k=>$('#v-'+k).hidden=k!=page);
  document.querySelectorAll('[data-p]').forEach(a=>{if(a.tagName=='A')a.classList.toggle('on',a.dataset.p==page)});
  $('#ttl').textContent=PAGES[page][0];
  $('#sub').textContent=PAGES[page][1];
  $('#pop').hidden=true;
  window.scrollTo(0,0);
  syncMe();
  render();
}
function render(){
  bell();
  ({overview,invoices,clients,payments,account})[page]();
}
window.addEventListener('hashchange',route);

/* Actions */
function markPaid(id,m){
  const x=S.inv.find(i=>i.id==id);
  if(!x||x.s=='paid')return;
  x.s='paid';
  S.pay.push({inv:x.id,c:x.c,d:day(0),m:m||'Bank transfer',a:x.a});
  save(); render(); toast(id+' marked as paid');
}
function openInv(id){
  const x=S.inv.find(i=>i.id==id); if(!x)return;
  const sub=x.items.reduce((t,i)=>t+i.q*i.p,0), tax=sub*x.t/100, cl=S.cl.find(c=>c.n==x.c);
  $('#sheet').innerHTML=`<div class="hd">
    <div><h2>${esc(S.set.name)}</h2><span class="sub">${esc(S.set.mail)}</span></div>
    <div style="text-align:right"><b style="font-size:18px">${x.id}</b><br><span class="pill ${x.s}">${x.s[0].toUpperCase()+x.s.slice(1)}</span></div>
  </div>
  <div class="meta">
    <div><small>Billed to</small><b>${esc(x.c)}</b><small>${esc(cl&&cl.e||'')}</small></div>
    <div><small>Issued</small><b>${show(x.i)}</b></div>
    <div><small>Due</small><b>${show(x.d)}</b></div>
  </div>
  <table><thead><tr><th>Description</th><th>Qty</th><th>Unit price</th><th>Amount</th></tr></thead>
  <tbody>${x.items.map(i=>`<tr><td>${esc(i.d||'Item')}</td><td>${i.q}</td><td>${fmt(i.p)}</td><td>${fmt(i.q*i.p)}</td></tr>`).join('')}</tbody></table>
  <div class="tot" style="margin-top:14px">
    <div><span>Subtotal</span><span>${fmt(sub)}</span></div>
    <div><span>Tax (${x.t}%)</span><span>${fmt(tax)}</span></div>
    <div class="g"><span>Total</span><span>${fmt(sub+tax)}</span></div>
  </div>
  <p class="note">${esc(S.set.note)}</p>
  <div class="foot noprint">
    <button class="btn" data-del="${x.id}">Delete</button>
    <button class="btn" data-dup="${x.id}">Duplicate</button>
    ${x.s!='paid'?`<button class="btn" data-r="${x.id}">Send reminder</button><button class="btn" data-p="${x.id}">Mark as paid</button>`:''}
    <button class="btn pri" id="prt">Print or save PDF</button>
    <button class="btn" id="vx">Close</button>
  </div>`;
  $('#vw').showModal();
}
function nextId(){const n=Math.max(1000,...S.inv.map(x=>+x.id.replace(/\D/g,'')||0))+1;return S.set.pre+n}

/* Invoice form */
const dlg=$('#dlg');
function line(d='',q=1,p=''){
  const r=document.createElement('div'); r.className='li';
  r.innerHTML=`<input placeholder="Description" value="${esc(d)}" aria-label="Description"><input type="number" min="1" value="${q}" aria-label="Quantity"><input type="number" min="0" step="0.01" placeholder="Price" value="${p}" aria-label="Unit price"><button type="button" aria-label="Remove line">×</button>`;
  r.querySelector('button').onclick=()=>{r.remove();calc()};
  $('#items').appendChild(r);
}
function calc(){
  let s=0;
  document.querySelectorAll('.li').forEach(r=>{const i=r.querySelectorAll('input');s+=(+i[1].value||0)*(+i[2].value||0)});
  const t=s*(+$('#tax').value||0)/100;
  $('#sb').textContent=fmt(s); $('#tx').textContent=fmt(t); $('#gr').textContent=fmt(s+t);
  return s+t;
}
function newInv(client){
  $('#cls').innerHTML=S.cl.map(c=>`<option value="${esc(c.n)}">`).join('');
  $('#items').innerHTML=''; line('',1,'');
  $('#cl').value=client||''; $('#tax').value=S.set.tax;
  $('#iss').value=day(0); $('#due').value=day(+S.set.terms||30);
  calc();
  if($('#vw').open)$('#vw').close();
  dlg.showModal();
  (client?document.querySelector('.li input'):$('#cl')).focus();
}

/* Session */
function doLogout(){
  $('#pop').hidden=true;
  if(confirm('Sign out of Ledgerly?')) $('#so').hidden=false;
}
$('#logout').onclick=doLogout;
$('#logout2').onclick=doLogout;
$('#back').onclick=()=>{$('#so').hidden=true;toast('Signed back in as '+S.me.n);route()};

/* Account save */
function bindAccountSave(){
  $('#ac-save').onclick=()=>{
    const n=$('#ac-name').value.trim(), m=$('#ac-mail').value.trim();
    if(!n){$('#ac-name').focus();toast('Enter your full name');return}
    if(!/^.+@.+\..+$/.test(m)){$('#ac-mail').focus();toast('Enter a valid email address');return}
    S.me.n=n; S.me.e=m;
    S.me.phone=$('#ac-phone').value.trim();
    S.me.tz=$('#ac-tz').value;
    save(); syncMe(); account(); toast('Profile updated');
  };
}

/* Events */
document.addEventListener('click',e=>{
  const t=e.target;
  if(!t.closest('.tools')) $('#pop').hidden=true;

  if(t.dataset && t.dataset.act==='new'){newInv();return}
  if(t.dataset && t.dataset.act==='client'){$('#c-n').value='';$('#c-e').value='';$('#cdlg').showModal();$('#c-n').focus();return}

  const b=t.closest('button');
  if(b){
    const d=b.dataset;
    if(d.p){e.stopPropagation();markPaid(d.p);if($('#vw').open)openInv(d.p);return}
    if(d.r){const x=S.inv.find(i=>i.id==d.r);toast('Reminder sent to '+(x?x.c:''));return}
    if(d.k){filter=d.k;invoices();return}
    if(d.pw!==undefined){toast('Password reset link sent to your email');return}
    if(d.so!==undefined){if(confirm('Sign out of all devices?'))toast('All other sessions signed out');return}
    if(d.nc!==undefined){newInv(d.nc);return}
    if(d.vc!==undefined){term=d.vc;$('#q').value=d.vc;filter='all';location.hash='#invoices';return}
    if(d.dup){const x=S.inv.find(i=>i.id==d.dup);S.inv.unshift({...JSON.parse(JSON.stringify(x)),id:nextId(),s:'pending',i:day(0),d:day(+S.set.terms||30)});save();$('#vw').close();render();toast('Invoice duplicated');return}
    if(d.del){if(confirm('Delete '+d.del+'? This cannot be undone.')){S.inv=S.inv.filter(i=>i.id!=d.del);S.pay=S.pay.filter(p=>p.inv!=d.del);save();$('#vw').close();render();toast(d.del+' deleted')}return}
  }
  if(t.id=='prt'){window.print();return}
  if(t.id=='vx'){$('#vw').close();return}
  const tr=t.closest('tr[data-o]'); if(tr)openInv(tr.dataset.o);
  const th=t.closest('th[data-s]');
  if(th){const k=th.dataset.s;sort=sort.k==k?{k,dir:-sort.dir}:{k,dir:1};invoices()}
});
$('#bell').onclick=e=>{e.stopPropagation();$('#pop').hidden=!$('#pop').hidden};
$('#pop').onclick=e=>e.stopPropagation();
$('#new').onclick=()=>newInv();
$('#q').oninput=e=>{
  term=e.target.value;
  if(page=='invoices')invoices();
  else if(page=='clients')clients();
  else{location.hash='#invoices'}
};
$('#items').oninput=calc; $('#tax').oninput=calc;
$('#addItem').onclick=()=>line();
$('#cancel').onclick=()=>dlg.close();
$('#save').onclick=()=>{
  const c=$('#cl').value.trim(); if(!c){$('#cl').focus();toast('Enter a client name');return}
  const items=[...document.querySelectorAll('.li')].map(r=>{const i=r.querySelectorAll('input');return{d:i[0].value.trim(),q:+i[1].value||1,p:+i[2].value||0}}).filter(i=>i.p>0);
  if(!items.length){toast('Add at least one priced line item');return}
  if(!S.cl.some(x=>x.n==c))S.cl.push({n:c,e:''});
  const t=+$('#tax').value||0, id=nextId();
  S.inv.unshift({id,c,i:$('#iss').value||day(0),d:$('#due').value||day(30),a:Math.round(items.reduce((s,i)=>s+i.q*i.p,0)*(1+t/100)*100)/100,s:'pending',items,t});
  save(); dlg.close();
  filter='all'; term=''; $('#q').value='';
  location.hash='#invoices'; render(); toast(id+' generated');
};
$('#csv').onclick=()=>{
  const rows=[['Invoice','Client','Issued','Due','Status','Amount']].concat(S.inv.map(x=>[x.id,x.c,x.i,x.d,x.s,x.a]));
  const csv=rows.map(r=>r.map(v=>'"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
  const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'})); a.download='invoices.csv';
  document.body.appendChild(a); a.click(); a.remove(); toast('Invoices exported');
};
$('#addc').onclick=()=>{$('#c-n').value='';$('#c-e').value='';$('#cdlg').showModal();$('#c-n').focus()};
$('#c-x').onclick=()=>$('#cdlg').close();
$('#c-s').onclick=()=>{
  const n=$('#c-n').value.trim();
  if(!n){toast('Enter a client name');return}
  if(S.cl.some(c=>c.n.toLowerCase()==n.toLowerCase())){toast('That client already exists');return}
  S.cl.push({n,e:$('#c-e').value.trim()});
  save(); $('#cdlg').close(); render(); toast(n+' added');
};
document.querySelectorAll('dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()}));
document.addEventListener('keydown',e=>{
  if(!$('#so').hidden)return;
  if(e.target.matches('input,textarea,select')||e.metaKey||e.ctrlKey)return;
  if(e.key=='n'){e.preventDefault();newInv()}
  if(e.key=='/'){e.preventDefault();$('#q').focus()}
});

bindAccountSave();
route();
</script>
</body>
</html>