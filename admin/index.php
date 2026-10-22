<?php
/**
 * Ledgerly – admin console (single-file PHP port of admin.html)
 *
 * Seed data lives in $default so you can swap it for real data later.
 * Preview: php -S localhost:8000  →  http://localhost:8000/admin.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../auth/_bootstrap.php';

if (!isset($_SESSION['auth_user']['id'])) {
    header('Location: ../index.php?auth=login');
    exit;
}

try {
    $adminDb = auth_db();
    $currentUserQuery = $adminDb->prepare('SELECT id, full_name, email, role FROM users WHERE id = :id LIMIT 1');
    $currentUserQuery->execute(['id' => (int) $_SESSION['auth_user']['id']]);
    $currentAdmin = $currentUserQuery->fetch();
    if (!$currentAdmin || $currentAdmin['role'] !== 'admin') {
        auth_flash('error', 'in', 'Administrator access is required to open the admin dashboard.');
        header('Location: ../index.php?auth=login');
        exit;
    }
    $adminUsersQuery = $adminDb->query(
        "SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC, id DESC"
    );
    $adminUsers = array_map(
        static function (array $user): array {
            return [
                'id' => (int) $user['id'],
                'n' => $user['full_name'],
                'e' => $user['email'],
                'role' => $user['role'] === 'admin' ? 'Admin' : 'User',
                'plan' => 'non-subscribed',
                'since' => substr((string) $user['created_at'], 0, 10),
                'suspended' => false,
            ];
        },
        $adminUsersQuery->fetchAll()
    );
} catch (PDOException $exception) {
    error_log('Admin user-list database error: ' . $exception->getMessage());
    http_response_code(500);
    exit('Unable to load the admin dashboard accounts.');
}

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
function relDay(int $o): string   { return date('Y-m-d', strtotime("{$o} days")); }
function relStamp(float $h): string { return date('c', time() - (int) round($h * 3600)); }

$year = (int) date('Y');

/* ---------- Nav pages ---------- */
$pages = [
    'dashboard' => ['Dashboard',  'Here is where your billing stands today.'],
    'invoices'  => ['Invoices',   'Create, track and collect every invoice.'],
    'clients'   => ['Clients',    'Everyone you bill, with what they owe.'],
    'users'     => ['Users',      'Every registered account and its subscription.'],
    'payments'  => ['Payments',   'Money received and how it arrived.'],
    'audit'     => ['Audit logs', 'Every sign-in attempt across all accounts.'],
    'backups'   => ['Backups',    'Create, download and restore your data backups.'],
    'profile'   => ['Profile',    'Your account, plan and billing.'],
    'settings'  => ['Settings',   'Defaults used for every new invoice.'],
];
$initialPage = defined('ADMIN_INITIAL_PAGE') ? ADMIN_INITIAL_PAGE : 'dashboard';

$icons = [
    'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    'invoices'  => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 12h7M9 16h7"/>',
    'clients'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.5 3.2-5.5 6.5-5.5s5.9 2 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 010 6.8M18 14.8c1.9.7 3.1 2.4 3.5 5.2"/>',
    'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.5 3.2-5.5 6.5-5.5s5.9 2 6.5 5.5"/><circle cx="18" cy="7" r="2.5"/><path d="M16.5 13.2c2 .3 3.4 1.6 4 3.8"/>',
    'payments'  => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/>',
    'audit'     => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    'backups'   => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
    'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>',
];

/* ---------- Seed data ---------- */
function mk_inv(string $id, string $c, int $i, int $d, int $a, string $s): array {
    return [
        'id' => $id, 'c' => $c,
        'i'  => relDay($i), 'd' => relDay($d),
        'a'  => $a, 's' => $s,
        'items' => [['d' => 'Professional services', 'q' => 1, 'p' => $a]],
        't'  => 0,
    ];
}

$default = [
    'inv' => [
        mk_inv('INV-1048','Northwind Logistics',-38,-8, 8450,'overdue'),
        mk_inv('INV-1049','Kuda Studios',       -21, 9, 2380,'pending'),
        mk_inv('INV-1050','Baobab Capital',     -18,12,15200,'pending'),
        mk_inv('INV-1051','Meridian Health',    -30,-1, 4975,'overdue'),
        mk_inv('INV-1052','Northwind Logistics',-14,16, 6120,'paid'),
        mk_inv('INV-1053','Zambezi Foods',      -12,18, 3340,'paid'),
        mk_inv('INV-1054','Baobab Capital',      -9,21, 9800,'paid'),
        mk_inv('INV-1055','Kuda Studios',        -4,26, 1560,'pending'),
    ],
    'cl' => [
        ['n'=>'Northwind Logistics','e'=>'accounts@northwind.co'],
        ['n'=>'Kuda Studios','e'=>'hello@kuda.studio'],
        ['n'=>'Baobab Capital','e'=>'finance@baobab.com'],
        ['n'=>'Meridian Health','e'=>'ap@meridian.health'],
        ['n'=>'Zambezi Foods','e'=>'orders@zambezi.foods'],
    ],
    'pay' => [
        ['inv'=>'INV-1052','c'=>'Northwind Logistics','d'=>relDay(-6),'m'=>'Bank transfer','a'=>6120],
        ['inv'=>'INV-1053','c'=>'Zambezi Foods','d'=>relDay(-5),'m'=>'Card','a'=>3340],
        ['inv'=>'INV-1054','c'=>'Baobab Capital','d'=>relDay(-2),'m'=>'Bank transfer','a'=>9800],
    ],
    'users' => [],
    'me' => [
        'e'=>'amara@ledgerly.app','phone'=>'+263 77 123 4567','tz'=>'Africa/Harare',
        'plan'=>'Ledgerly Pro','price'=>49,'cycle'=>'Monthly','renew'=>relDay(12),
        'payment'=>'Visa •••• 4242','seats'=>5,'seatsUsed'=>4,
        'invoicesUsed'=>38,'invoiceLimit'=>100,'clientsUsed'=>5,'clientLimit'=>50,
        'storageUsed'=>1.8,'storageLimit'=>10,'pwChanged'=>relDay(-42),
    ],
    'backups' => [
        ['id'=>'BK-0006','name'=>'Nightly backup','type'=>'auto','created'=>relStamp(6),'size'=>'4.2 MB','status'=>'complete'],
        ['id'=>'BK-0005','name'=>'Nightly backup','type'=>'auto','created'=>relStamp(30),'size'=>'4.1 MB','status'=>'complete'],
        ['id'=>'BK-0004','name'=>'Before settings change','type'=>'manual','created'=>relStamp(52),'size'=>'4.0 MB','status'=>'complete'],
        ['id'=>'BK-0003','name'=>'Nightly backup','type'=>'auto','created'=>relStamp(54),'size'=>'4.0 MB','status'=>'complete'],
        ['id'=>'BK-0002','name'=>'Nightly backup','type'=>'auto','created'=>relStamp(78),'size'=>'3.9 MB','status'=>'failed'],
        ['id'=>'BK-0001','name'=>'Monthly archive','type'=>'manual','created'=>relStamp(120),'size'=>'3.8 MB','status'=>'complete'],
    ],
    'bk' => ['auto'=>true,'freq'=>'Daily','time'=>'02:00','retain'=>30,'dest'=>'Local + S3'],
    'audit' => [
        ['t'=>relStamp(0.3),'u'=>'Amara Moyo','e'=>'amara@ledgerly.app','ev'=>'login','ip'=>'102.65.14.8','loc'=>'Harare, ZW'],
        ['t'=>relStamp(0.9),'u'=>'Unknown account','e'=>'admin@ledgerly.co','ev'=>'failed','ip'=>'45.221.10.77','loc'=>'Lagos, NG'],
        ['t'=>relStamp(2.4),'u'=>'Tendai Ncube','e'=>'tendai@ledgerly.app','ev'=>'login','ip'=>'41.79.62.14','loc'=>'Bulawayo, ZW'],
        ['t'=>relStamp(3.1),'u'=>'Grace Phiri','e'=>'grace@ledgerly.app','ev'=>'login','ip'=>'196.21.44.9','loc'=>'Lusaka, ZM'],
        ['t'=>relStamp(5.2),'u'=>'Kofi Mensah','e'=>'kofi@ledgerly.app','ev'=>'failed','ip'=>'154.160.18.3','loc'=>'Accra, GH'],
        ['t'=>relStamp(5.5),'u'=>'Kofi Mensah','e'=>'kofi@ledgerly.app','ev'=>'login','ip'=>'154.160.18.3','loc'=>'Accra, GH'],
        ['t'=>relStamp(8.4),'u'=>'Lerato Dube','e'=>'lerato@ledgerly.app','ev'=>'login','ip'=>'105.184.22.61','loc'=>'Gaborone, BW'],
        ['t'=>relStamp(12.2),'u'=>'Unknown account','e'=>'test@ledgerly.app','ev'=>'failed','ip'=>'89.207.132.6','loc'=>'Amsterdam, NL'],
        ['t'=>relStamp(14.7),'u'=>'Amara Moyo','e'=>'amara@ledgerly.app','ev'=>'login','ip'=>'102.65.14.8','loc'=>'Harare, ZW'],
        ['t'=>relStamp(20.1),'u'=>'Tendai Ncube','e'=>'tendai@ledgerly.app','ev'=>'failed','ip'=>'41.79.62.14','loc'=>'Bulawayo, ZW'],
        ['t'=>relStamp(22.5),'u'=>'Tendai Ncube','e'=>'tendai@ledgerly.app','ev'=>'login','ip'=>'41.79.62.14','loc'=>'Bulawayo, ZW'],
        ['t'=>relStamp(26.3),'u'=>'Sam Okoro','e'=>'sam@ledgerly.app','ev'=>'login','ip'=>'197.210.53.20','loc'=>'Abuja, NG'],
        ['t'=>relStamp(30.8),'u'=>'Unknown account','e'=>'root@ledgerly.app','ev'=>'failed','ip'=>'185.220.101.44','loc'=>'Frankfurt, DE'],
        ['t'=>relStamp(34.2),'u'=>'Grace Phiri','e'=>'grace@ledgerly.app','ev'=>'login','ip'=>'196.21.44.9','loc'=>'Lusaka, ZM'],
        ['t'=>relStamp(40.5),'u'=>'Nia Banda','e'=>'nia@ledgerly.app','ev'=>'login','ip'=>'41.60.190.12','loc'=>'Lilongwe, MW'],
        ['t'=>relStamp(46.9),'u'=>'Amara Moyo','e'=>'amara@ledgerly.app','ev'=>'login','ip'=>'102.65.14.8','loc'=>'Harare, ZW'],
        ['t'=>relStamp(52.4),'u'=>'Unknown account','e'=>'info@ledgerly.app','ev'=>'failed','ip'=>'103.94.16.5','loc'=>'Mumbai, IN'],
        ['t'=>relStamp(58.7),'u'=>'Thabo Sithole','e'=>'thabo@ledgerly.app','ev'=>'login','ip'=>'41.13.88.4','loc'=>'Johannesburg, ZA'],
    ],
    'set' => [
        'name'=>'Ledgerly Studio','mail'=>'billing@ledgerly.app','cur'=>'$',
        'tax'=>15,'terms'=>30,'pre'=>'INV-',
        'note'=>'Thank you for your business. Please pay by the due date.',
    ],
];

$default['users'] = $adminUsers;
$me = [
    'n' => $currentAdmin['full_name'],
    'e' => $currentAdmin['email'],
    'role' => 'Admin',
];

$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$defaultJson = json_encode($default, $jsonFlags) ?: '{}';
$adminUsersJson = json_encode($adminUsers, $jsonFlags) ?: '[]';
$adminCsrfToken = auth_csrf_token();
$pagesJson   = json_encode($pages,   $jsonFlags) ?: '{}';
$iconsJson   = json_encode($icons,   $jsonFlags) ?: '{}';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e('Ledgerly – Invoice admin') ?></title>
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
.me[data-goto]{cursor:pointer;border-radius:10px}
.me[data-goto]:hover{background:rgba(255,255,255,.08)}
.me .who{flex:1;min-width:0}
.me b{display:block;font-size:13.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.me small{display:block;color:rgba(255,255,255,.66)}
.lout{flex:none;width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.12);color:#fff;display:grid;place-items:center;cursor:pointer;padding:0}
.lout:hover{background:rgba(255,255,255,.24)}
.lout svg{width:16px;height:16px}
.av{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.18);display:grid;place-items:center;font-weight:600;font-size:13px;flex:none}

main{padding:28px 40px 56px;min-width:0}
.top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:26px}
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
.kpis.u3{grid-template-columns:repeat(3,1fr)}
.kpi{padding:20px 22px}
.kpi span{color:var(--muted);font-weight:500}
.kpi b{display:block;font-size:27px;font-weight:600;letter-spacing:-.03em;margin:6px 0 2px}
.kpi small{color:var(--accent);font-weight:600}
.kpi.hero{background:var(--accent);border-color:var(--accent);color:#fff}
.kpi.hero span,.kpi.hero small{color:rgba(255,255,255,.8)}
.grid{display:grid;grid-template-columns:1.7fr 1fr;gap:16px;margin-bottom:16px}
h2{font-size:16px;font-weight:600;letter-spacing:-.01em}
.head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:20px 22px 0;flex-wrap:wrap}
.head span,.head a{color:var(--muted)}
.head a{font-weight:600;color:var(--accent)}
.chart{display:flex;align-items:flex-end;gap:14px;height:220px;padding:34px 22px 14px}
.col{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end}
.bar{width:100%;max-width:46px;background:var(--soft);border-radius:8px 8px 4px 4px;position:relative}
.bar.cur,.col:hover .bar{background:var(--accent)}
.col em{font-style:normal;color:var(--muted);font-size:12px}
.tip{position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:var(--ink);color:#fff;padding:3px 8px;border-radius:6px;font-size:12px;white-space:nowrap;opacity:0;pointer-events:none}
.col:hover .tip{opacity:1}
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

.cgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.cc{padding:22px;display:flex;flex-direction:column;gap:14px}
.cc .t{display:flex;gap:12px;align-items:center}
.cc .av{background:var(--soft);color:var(--accent);width:42px;height:42px}
.cc dl{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px 0;border-block:1px solid var(--line)}
.cc dt{color:var(--muted);font-size:12.5px}.cc dd{font-weight:600;font-size:16px}
.cc .row{display:flex;gap:8px}.cc .row .btn{flex:1}

.set{max-width:680px}
.set .sec{padding:24px}
.set .f{margin-top:16px}
.set .sec+.sec{margin-top:16px}

.pf-hero{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;padding:22px;margin-bottom:16px}
.prof-id{display:flex;gap:14px;align-items:center;min-width:0}
.av.big{width:58px;height:58px;font-size:19px;background:var(--soft);color:var(--accent);font-weight:700}
.pf-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;align-items:center}
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
  .mtop .muser{display:flex;align-items:center;gap:8px;min-width:0;cursor:pointer}
  .mtop .mname{font-weight:600;font-size:13px;max-width:130px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .mtop .lout{width:34px;height:34px;border-color:rgba(255,255,255,.3)}
  main{padding:20px 16px calc(96px + env(safe-area-inset-bottom))}
  h1{font-size:22px}
  .top{margin-bottom:18px}
  .tools{width:100%}.search{flex:1}.search input{width:100%}
  .bnav{display:grid;grid-template-columns:repeat(8,1fr);position:fixed;left:0;right:0;bottom:0;background:var(--surface);border-top:1px solid var(--line);padding:6px 4px calc(6px + env(safe-area-inset-bottom));z-index:25}
  .bnav a{display:flex;flex-direction:column;align-items:center;gap:2px;padding:6px 0;color:var(--muted);font-size:8.5px;font-weight:500;border-radius:10px;white-space:nowrap;overflow:hidden}
  .bnav a.on{color:var(--accent);font-weight:700}
  .bnav svg{width:21px;height:21px}
  .pop{position:fixed;left:12px;right:12px;top:70px;width:auto;max-width:none}
  .pf-hero{padding:18px}
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
  .chart{padding:34px 16px 14px;gap:8px}
}
@media (max-width:520px){
  .kpis{grid-template-columns:1fr 1fr;gap:10px}.kpi{padding:16px}.kpi b{font-size:21px}
  #kpis2.kpis{gap:8px}#kpis2 .kpi{padding:13px 12px}
  #kpis2 .kpi span{font-size:12px}#kpis2 .kpi b{font-size:19px;margin:4px 0 0}
  #kpis2 .kpi small{font-size:10.5px;line-height:1.25}
  .f{grid-template-columns:1fr}.m{padding:22px 18px}
  .sheet .meta{grid-template-columns:1fr 1fr}
  .tools .btn.pri span{display:none}.tools .btn.pri{width:42px;padding:0}
  dialog{width:100vw;max-width:100vw;margin:auto 0 0;border-radius:18px 18px 0 0;max-height:94vh}
  .li{grid-template-columns:1fr 54px 80px 24px}
  .bnav a{font-size:8px}
  .av.big{width:48px;height:48px;font-size:16px}
  .pf-hero .foot{width:100%}
  .pf-hero .foot .btn{flex:1}
}
@media (max-width:400px){.mtop .mname{display:none}}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<div class="mtop">
  <div class="brand"><div class="mark">L</div>Ledgerly</div>
  <div class="muser" data-goto="profile" role="link" tabindex="0" aria-label="Open your profile">
    <b class="mname" id="mname"><?= e($me['n']) ?></b>
    <div class="av" id="mav" aria-label="<?= e($me['n']) ?>"><?= e(ini($me['n'])) ?></div>
    <button class="lout" id="logout2" title="Log out" aria-label="Log out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3"/><path d="M10 17l-5-5 5-5"/><path d="M5 12h11"/></svg></button>
  </div>
</div>
<div class="app">
  <aside class="side">
    <div class="brand"><div class="mark">L</div>Ledgerly</div>
    <nav class="nav" id="nav" aria-label="Main"></nav>
    <div class="me" data-goto="profile" role="link" tabindex="0" aria-label="Open your profile">
      <div class="av" id="me-av"><?= e(ini($me['n'])) ?></div>
      <div class="who"><b id="me-name"><?= e($me['n']) ?></b><small id="me-role"><?= e($me['role']) ?></small></div>
      <button class="lout" id="logout" title="Log out" aria-label="Log out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3"/><path d="M10 17l-5-5 5-5"/><path d="M5 12h11"/></svg></button>
    </div>
  </aside>
  <main>
    <header class="top">
      <div><h1 id="ttl"></h1><p id="sub"></p></div>
      <div class="tools">
        <label class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg><input id="q" type="search" placeholder="Search invoices, clients, users or logs" aria-label="Search"></label>
        <button class="btn ico" id="bell" aria-label="Notifications"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 17V11a6 6 0 1112 0v6l1.5 2h-15z"/><path d="M10 21h4"/></svg><b class="dot" id="bc"></b></button>
        <button class="btn pri" id="new"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg><span>New invoice</span></button>
        <div class="pop" id="pop" hidden></div>
      </div>
    </header>
    <?php
    if (!defined('ADMIN_MENU_INCLUDE')) {
        define('ADMIN_MENU_INCLUDE', true);
    }
    foreach (['dashboard', 'invoices', 'clients', 'users', 'payments', 'audit', 'backups', 'profile', 'settings'] as $menu) {
        require __DIR__ . '/' . $menu . '.php';
    }
    ?>
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

<dialog id="pdlg"><div class="m"><h2>Record payment</h2><p>Pick an open invoice to mark as paid.</p>
  <div class="f"><label>Invoice<select id="p-i"></select></label><label>Method<select id="p-m"><option>Bank transfer</option><option>Card</option><option>Cash</option><option>Mobile money</option></select></label></div>
  <div class="foot"><button class="btn" id="p-x" type="button">Cancel</button><button class="btn pri" id="p-s" type="button">Record payment</button></div></div></dialog>

<div class="so" id="so" hidden>
  <div class="card so-card">
    <div class="mark so-mark">L</div>
    <h2>You've been signed out</h2>
    <p>Your session has ended securely. Sign back in to keep managing invoices.</p>
    <button class="btn pri" id="back">Sign back in as <?= e($me['n']) ?></button>
  </div>
</div>

<div class="toast" id="toast" role="status"></div>

<script>
/* Seed data, page titles and icons come from PHP */
const IC    = <?= $iconsJson ?>;
const PAGES = <?= $pagesJson ?>;
const __INITIAL__ = <?= $defaultJson ?>;
const SERVER_USERS = <?= $adminUsersJson ?>;
const ADMIN_CSRF = <?= json_encode($adminCsrfToken, $jsonFlags) ?>;
const DEF   = () => JSON.parse(JSON.stringify(__INITIAL__));

const $=s=>document.querySelector(s);
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const day=o=>{const d=new Date();d.setDate(d.getDate()+o);return d.toISOString().slice(0,10)};
const addDays=(d,n)=>{const x=new Date(d+'T00:00');x.setDate(x.getDate()+n);return x.toISOString().slice(0,10)};
const stamp=h=>new Date(Date.now()-h*3600000).toISOString();
const when=t=>new Date(t).toLocaleString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
const show=d=>new Date(d+'T00:00').toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'});
const ini=n=>n.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
const svg=n=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">${IC[n]||''}</svg>`;

let S=DEF();
try{
  const x=JSON.parse(localStorage.getItem('ledgerly')||'null');
  if(x&&x.inv){
    const d=DEF();
    S=Object.assign(d,x);
    if(!Array.isArray(S.users)||!S.users.length)S.users=d.users;
    if(!Array.isArray(S.audit)||!S.audit.length)S.audit=d.audit;
    if(!S.set)S.set=d.set;
  }
}catch(e){}
if(!S.me||!S.me.plan)S.me=DEF().me;
if(!Array.isArray(S.backups))S.backups=DEF().backups;
if(!S.bk)S.bk=DEF().bk;
S.users=SERVER_USERS;
const save=()=>{try{localStorage.setItem('ledgerly',JSON.stringify(S))}catch(e){}};
const fmt=n=>S.set.cur+n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
let filter='all',ufilter='all',afilter='all',term='',sort={k:'i',dir:-1},page=<?= json_encode($initialPage, $jsonFlags) ?>;
const rev=[18.2,22.5,19.8,27.4,24.1,31.6,0],mon=['Apr','May','Jun','Jul','Aug','Sep','Oct'];

function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('show');clearTimeout(t._h);t._h=setTimeout(()=>t.classList.remove('show'),2400)}
const sum=f=>S.inv.filter(f).reduce((t,x)=>t+x.a,0);
const cnt=s=>S.inv.filter(x=>x.s==s).length;
const total=x=>x.items.reduce((t,i)=>t+i.q*i.p,0)*(1+x.t/100);
const ME=()=>(S.users||[]).find(u=>u.e===S.me.e)||(S.users||[])[0]||{};

/* Nav */
const navKeys=['dashboard','invoices','clients','users','payments','audit','backups','settings'];
const navHTML=navKeys.map(k=>`<a href="${k}.php" data-p="${k}">${svg(k)}<span>${PAGES[k][0]}</span></a>`).join('');
$('#nav').innerHTML=navHTML;$('#bnav').innerHTML=navHTML;

/* Signed-in identity */
function syncMe(){
  const u=ME(),n=u.n||'Amara Moyo';
  $('#me-name').textContent=n;
  $('#me-role').textContent=u.role||'Administrator';
  $('#me-av').textContent=ini(n);
  $('#mname').textContent=n;
  $('#mav').textContent=ini(n);
}

/* Views */
function rowHTML(x,full){
  return `<tr class="go" data-o="${x.id}"><td class="id">${x.id}</td><td class="cl">${esc(x.c)}</td>${full?`<td class="dt" data-l="Issued">${show(x.i)}</td>`:''}<td class="dt" data-l="Due">${show(x.d)}</td>
  <td class="st"><span class="pill ${x.s}">${x.s[0].toUpperCase()+x.s.slice(1)}</span></td><td class="amt r"><b>${fmt(x.a)}</b></td>
  <td class="ac dn r">${x.s=='paid'?'':'<button class="act" data-p="'+x.id+'">Mark as paid</button>'}</td></tr>`;
}
function dash(){
  const paid=sum(x=>x.s=='paid'),pend=sum(x=>x.s=='pending'),over=sum(x=>x.s=='overdue');
  $('#kpis').innerHTML=`<div class="card kpi hero"><span>Total billed</span><b>${fmt(paid+pend+over)}</b><small>${S.inv.length} invoices</small></div>
  <div class="card kpi"><span>Collected</span><b>${fmt(paid)}</b><small>${cnt('paid')} paid</small></div>
  <div class="card kpi"><span>Awaiting payment</span><b>${fmt(pend)}</b><small>${cnt('pending')} open</small></div>
  <div class="card kpi"><span>Overdue</span><b>${fmt(over)}</b><small>${cnt('overdue')} need follow-up</small></div>`;

  const u=S.users||[],sub=u.filter(x=>x.plan=='subscribed').length,non=u.length-sub;
  $('#kpis2').innerHTML=`<div class="card kpi"><span>Registered users</span><b>${u.length}</b><small>${sub} subscribed · ${non} free</small></div>
  <div class="card kpi"><span>Subscribed</span><b>${sub}</b><small>${u.length?Math.round(sub/u.length*100):0}% of accounts</small></div>
  <div class="card kpi"><span>Not subscribed</span><b>${non}</b><small>awaiting upgrade</small></div>`;

  const r=rev.slice();r[6]=S.pay.reduce((t,p)=>t+p.a,0)/1000;const mx=Math.max(...r);
  $('#chart').innerHTML=r.map((v,i)=>`<div class="col"><div class="bar ${i==6?'cur':''}" style="height:${Math.max(v/mx*82,3)}%"><span class="tip">${S.set.cur}${v.toFixed(1)}k</span></div><em>${mon[i]}</em></div>`).join('');
  const by={};S.inv.forEach(x=>by[x.c]=(by[x.c]||0)+x.a);
  $('#top').innerHTML=Object.entries(by).sort((a,b)=>b[1]-a[1]).slice(0,5).map(([n,v])=>`<li><div class="av">${ini(n)}</div><div><b>${esc(n)}</b><small>${S.inv.filter(x=>x.c==n).length} invoices</small></div><b>${fmt(v)}</b></li>`).join('');
  $('#recent').innerHTML=S.inv.slice().sort((a,b)=>b.i.localeCompare(a.i)).slice(0,5).map(x=>rowHTML(x,false)).join('');
}
function invoices(){
  const n=k=>k=='all'?S.inv.length:cnt(k);
  $('#tabs').innerHTML=[['all','All'],['paid','Paid'],['pending','Pending'],['overdue','Overdue']].map(([k,l])=>`<button class="tab ${filter==k?'on':''}" data-k="${k}">${l}<i>${n(k)}</i></button>`).join('');
  const t=term.toLowerCase();
  const l=S.inv.filter(x=>(filter=='all'||x.s==filter)&&(x.id+x.c).toLowerCase().includes(t)).sort((a,b)=>{const A=a[sort.k],B=b[sort.k];return (A>B?1:A<B?-1:0)*sort.dir});
  $('#rows').innerHTML=l.map(x=>rowHTML(x,true)).join('');
  $('#empty').hidden=l.length>0;$('#cnt').textContent=l.length+' invoice'+(l.length==1?'':'s');
}
function clients(){
  $('#cg').innerHTML=S.cl.map(c=>{
    const l=S.inv.filter(x=>x.c==c.n),owed=l.filter(x=>x.s!='paid').reduce((t,x)=>t+x.a,0),billed=l.reduce((t,x)=>t+x.a,0);
    return `<div class="card cc"><div class="t"><div class="av">${ini(c.n)}</div><div><b>${esc(c.n)}</b><span class="sub">${esc(c.e||'No email')}</span></div></div>
    <dl><div><dt>Outstanding</dt><dd>${fmt(owed)}</dd></div><div><dt>Total billed</dt><dd>${fmt(billed)}</dd></div></dl>
    <div class="row"><button class="btn sm pri" data-nc="${esc(c.n)}">New invoice</button><button class="btn sm" data-vc="${esc(c.n)}">View invoices</button></div></div>`}).join('');
}
function usersView(){
  const u=S.users||[];
  const n=k=>k=='all'?u.length:u.filter(x=>(x.role==='Admin'?'admin':'user')==k).length;
  $('#utabs').innerHTML=[['all','All users'],['admin','Admins'],['user','Users']].map(([k,l])=>`<button class="tab ${ufilter==k?'on':''}" data-uk="${k}">${l}<i>${n(k)}</i></button>`).join('');
  const t=term.toLowerCase();
  const l=u.filter(x=>(ufilter=='all'||(x.role==='Admin'?'admin':'user')==ufilter)&&(x.n+' '+x.e+' '+x.role).toLowerCase().includes(t));
  $('#ug').innerHTML=l.map(x=>`<div class="card cc">
    <div class="t"><div class="av">${ini(x.n)}</div><div><b>${esc(x.n)}</b><span class="sub">${esc(x.e)}</span></div></div>
    <dl><div><dt>Role</dt><dd style="font-size:14px"><select class="btn sm" data-role-id="${x.id}" aria-label="Role for ${esc(x.n)}"><option value="user"${x.role=='User'?' selected':''}>User</option><option value="admin"${x.role=='Admin'?' selected':''}>Admin</option></select></dd></div><div><dt>Registered</dt><dd style="font-size:14px">${show(x.since)}</dd></div></dl>
    <div class="row"><span class="sub">Role changes are saved immediately.</span></div>
  </div>`).join('');
  $('#uempty').hidden=l.length>0;
  $('#ucnt').textContent=l.length+' user'+(l.length==1?'':'s');
}
function payments(){
  const t=S.pay.reduce((a,p)=>a+p.a,0);
  $('#pk').innerHTML=`<div class="card kpi hero"><span>Total received</span><b>${fmt(t)}</b><small>${S.pay.length} payments</small></div>
  <div class="card kpi"><span>Still to collect</span><b>${fmt(sum(x=>x.s!='paid'))}</b><small>${S.inv.filter(x=>x.s!='paid').length} invoices</small></div>
  <div class="card kpi"><span>Average payment</span><b>${fmt(S.pay.length?t/S.pay.length:0)}</b><small>per invoice</small></div>`;
  $('#pr').innerHTML=S.pay.slice().reverse().map(p=>`<tr><td>${show(p.d)}</td><td class="id">${p.inv}</td><td>${esc(p.c)}</td><td>${p.m}</td><td class="r"><b>${fmt(p.a)}</b></td></tr>`).join('');
  $('#pe').hidden=S.pay.length>0;
}
function auditLogs(){
  const n=k=>k=='all'?S.audit.length:S.audit.filter(a=>a.ev==k).length;
  $('#atabs').innerHTML=[['all','All events'],['login','Successful logins'],['failed','Failed logins']].map(([k,l])=>`<button class="tab ${afilter==k?'on':''}" data-ak="${k}">${l}<i>${n(k)}</i></button>`).join('');
  const t=term.toLowerCase();
  const l=S.audit.filter(a=>(afilter=='all'||a.ev==afilter)&&(a.u+' '+a.e+' '+a.ip+' '+a.loc).toLowerCase().includes(t)).slice().sort((a,b)=>b.t.localeCompare(a.t));
  $('#arows').innerHTML=l.map(a=>`<tr><td class="dt" data-l="Time">${when(a.t)}</td>
    <td><b>${esc(a.u)}</b><span class="sub">${esc(a.e)}</span></td>
    <td><span class="pill ${a.ev=='login'?'paid':'overdue'}">${a.ev=='login'?'Login success':'Failed login'}</span></td>
    <td class="dn">${esc(a.ip)}</td><td class="dn">${esc(a.loc)}</td></tr>`).join('');
  $('#acnt').textContent=l.length+' event'+(l.length==1?'':'s');
  $('#aempty').hidden=l.length>0;
}
function backupsView(){
  const list=(S.backups||[]).slice();
  const bk=S.bk||{};
  const last=list.slice().sort((a,b)=>b.created.localeCompare(a.created))[0];
  const autoCount=list.filter(x=>x.type=='auto').length;
  const okCount=list.filter(x=>x.status=='complete').length;

  $('#bk-kpis').innerHTML=`
    <div class="card kpi hero"><span>Total backups</span><b>${list.length}</b><small>${okCount} healthy · ${autoCount} automatic</small></div>
    <div class="card kpi"><span>Last backup</span><b>${last?when(last.created).split(',')[0]:'—'}</b><small>${last?esc(last.name):'No backups yet'}</small></div>
    <div class="card kpi"><span>Auto-backup</span><b>${bk.auto?'On':'Off'}</b><small>${esc(bk.freq||'Daily')} · keep ${bk.retain||30} days</small></div>`;

  const ab=$('#bk-autobtn');
  if(ab){ab.textContent='Auto-backup: '+(bk.auto?'On':'Off');ab.classList.toggle('pri',!!bk.auto);}

  const t=term.toLowerCase();
  const shown=list.filter(x=>(x.id+' '+x.name+' '+x.type+' '+x.status).toLowerCase().includes(t));

  $('#bk-rows').innerHTML=shown.map(x=>`<tr>
    <td class="id" data-l="Backup">${x.id}<span class="sub">${esc(x.name)}</span></td>
    <td data-l="Type"><span class="pill ${x.type=='auto'?'paid':'pending'}">${x.type=='auto'?'Automatic':'Manual'}</span></td>
    <td class="dt" data-l="Created">${when(x.created)}</td>
    <td class="dt" data-l="Size">${x.size}</td>
    <td class="st" data-l="Status"><span class="pill ${x.status=='complete'?'paid':'overdue'}">${x.status[0].toUpperCase()+x.status.slice(1)}</span></td>
    <td class="ac r" data-l="Actions">
      <button class="act" data-bkdl="${x.id}">Download</button>
      <button class="act" data-bkrs="${x.id}">Restore</button>
      <button class="act" data-bkdel="${x.id}">Delete</button>
    </td>
  </tr>`).join('');
  $('#bk-empty').hidden=shown.length>0;

  $('#bk-settings').innerHTML=[
    ['Auto-backup', bk.auto?'<span class="pill paid">On</span>':'<span class="pill pending">Off</span>'],
    ['Frequency', esc(bk.freq||'Daily')],
    ['Time', esc(bk.time||'02:00')],
    ['Retention', (bk.retain||30)+' days'],
    ['Destination', esc(bk.dest||'Local + S3')]
  ].map(([k,v])=>`<div><span>${k}</span><b>${v}</b></div>`).join('');

  $('#bk-included').innerHTML=[
    'Invoices and line items',
    'Client records',
    'Payments',
    'User accounts and roles',
    'Business and invoice settings',
    'Audit logs'
  ].map(t=>`<li><div class="av">✓</div><div><b>${t}</b></div></li>`).join('');
}
function profileView(){
  const me=S.me,u=ME();
  const name=u.n||'Amara Moyo',mail=u.e||me.e;
  $('#pf-av').textContent=ini(name);
  $('#pf-name').textContent=name;
  $('#pf-mail').textContent=mail;
  $('#pf-meta').innerHTML=`<span class="pill paid">Subscribed</span>
    <span class="sub">${esc(u.role||'Administrator')}${u.since?' · Member since '+show(u.since):''}</span>`;

  $('#pf-kpis').innerHTML=`
    <div class="card kpi hero"><span>Current plan</span><b>${esc(me.plan)}</b><small>Renews ${show(me.renew)}</small></div>
    <div class="card kpi"><span>Monthly cost</span><b>${fmt(me.price)}</b><small>${esc(me.cycle)} billing</small></div>
    <div class="card kpi"><span>Team seats</span><b>${me.seatsUsed} of ${me.seats}</b><small>${Math.max(0,me.seats-me.seatsUsed)} seat available</small></div>
    <div class="card kpi"><span>Next payment</span><b>${fmt(me.price)}</b><small>on ${show(me.renew)}</small></div>`;

  $('#pf-plan').innerHTML=[
    ['Plan',esc(me.plan)],
    ['Billing cycle',esc(me.cycle)],
    ['Next payment',fmt(me.price)+' · '+show(me.renew)],
    ['Payment method',esc(me.payment)],
    ['Status','<span class="pill paid">Active</span>']
  ].map(([k,v])=>`<div><span>${k}</span><b>${v}</b></div>`).join('');

  const ub=(label,used,limit,unit='')=>{
    const p=Math.min(100,Math.max(0,Math.round(used/limit*100)));
    return `<div class="ub"><span><em>${label}</em><b>${used}${unit} of ${limit}${unit}</b></span>
      <div class="track"><div class="fill" style="width:${p}%"></div></div></div>`;
  };
  $('#pf-cycle').textContent='Resets '+show(me.renew);
  $('#pf-usage').innerHTML=
    ub('Invoices created',me.invoicesUsed,me.invoiceLimit)+
    ub('Clients',me.clientsUsed,me.clientLimit)+
    ub('Team seats',me.seatsUsed,me.seats)+
    ub('Storage',me.storageUsed,me.storageLimit,' GB');

  $('#pf-e-name').value=name;
  $('#pf-e-mail').value=mail;
  $('#pf-e-phone').value=me.phone||'';
  $('#pf-e-tz').value=me.tz||'Africa/Harare';

  $('#pf-sec').innerHTML=`
    <li><div class="av">✓</div><div><b>Email verified</b><small>${esc(mail)}</small></div><span class="pill paid">Verified</span></li>
    <li><div class="av">2F</div><div><b>Two-factor authentication</b><small>Authenticator app</small></div><span class="pill paid">Enabled</span></li>
    <li><div class="av">PW</div><div><b>Password</b><small>Last changed ${show(me.pwChanged||day(-42))}</small></div><button class="btn sm" data-pw>Change</button></li>
    <li><div class="av">DV</div><div><b>Active sessions</b><small>2 devices signed in</small></div><button class="btn sm" data-so>Sign out all</button></li>`;

  $('#pf-card').textContent=me.payment;
  const bills=[];
  for(let i=0;i<6;i++){
    bills.push({d:addDays(me.renew,-30*(i+1)),id:'SUB-'+(1042-i),a:me.price});
  }
  $('#pf-bills').innerHTML=bills.map(b=>`<tr>
    <td class="dt" data-l="Date">${show(b.d)}</td>
    <td class="id" data-l="Invoice">${b.id}</td>
    <td data-l="Description">${esc(me.plan)} · ${esc(me.cycle)}</td>
    <td data-l="Status"><span class="pill paid">Paid</span></td>
    <td class="r amt" data-l="Amount"><b>${fmt(b.a)}</b></td>
    <td class="ac dn r"><button class="act" data-rcpt="${b.id}">Receipt</button></td></tr>`).join('');

  const lg=S.audit.filter(a=>a.e==mail).slice().sort((a,b)=>b.t.localeCompare(a.t)).slice(0,5);
  $('#pf-logins').innerHTML=lg.length?lg.map(a=>`<tr>
    <td class="dt" data-l="Time">${when(a.t)}</td>
    <td data-l="Event"><span class="pill ${a.ev=='login'?'paid':'overdue'}">${a.ev=='login'?'Login success':'Failed login'}</span></td>
    <td class="dn">${esc(a.ip)}</td>
    <td class="dn">${esc(a.loc)}</td></tr>`).join(''):'<tr><td colspan="4" class="sub">No sign-in activity recorded yet.</td></tr>';
}
function settings(){const s=S.set;$('#s-name').value=s.name;$('#s-mail').value=s.mail;$('#s-cur').value=s.cur;$('#s-tax').value=s.tax;$('#s-terms').value=s.terms;$('#s-pre').value=s.pre;$('#s-note').value=s.note}
function bell(){
  const o=S.inv.filter(x=>x.s=='overdue');$('#bc').textContent=o.length;$('#bc').hidden=!o.length;
  $('#pop').innerHTML=`<h3>Needs attention</h3>`+(o.length?o.map(x=>`<div class="it"><div><b>${x.id} · ${esc(x.c)}</b><small>${fmt(x.a)} overdue since ${show(x.d)}</small></div><button class="btn sm" data-r="${x.id}">Remind</button></div>`).join(''):'<div class="it"><small>Nothing is overdue.</small></div>');
}

/* Router */
function route(){
  const h=(location.hash||'#'+page).slice(1);page=PAGES[h]?h:'dashboard';
  Object.keys(PAGES).forEach(k=>$('#v-'+k).hidden=k!=page);
  document.querySelectorAll('[data-p]').forEach(a=>{if(a.tagName=='A')a.classList.toggle('on',a.dataset.p==page)});
  $('#ttl').textContent=PAGES[page][0];$('#sub').textContent=PAGES[page][1];
  $('#pop').hidden=true;window.scrollTo(0,0);syncMe();render();
}
function render(){bell();({dashboard:dash,invoices,clients,users:usersView,payments,audit:auditLogs,backups:backupsView,profile:profileView,settings})[page]();}
window.addEventListener('hashchange',route);

/* Actions */
function markPaid(id,m){
  const x=S.inv.find(i=>i.id==id);if(!x||x.s=='paid')return;
  x.s='paid';S.pay.push({inv:x.id,c:x.c,d:day(0),m:m||'Bank transfer',a:x.a});save();render();toast(id+' marked as paid');
}
function remind(id){const x=S.inv.find(i=>i.id==id);toast('Reminder sent to '+x.c)}
function openInv(id){
  const x=S.inv.find(i=>i.id==id);if(!x)return;const sub=x.items.reduce((t,i)=>t+i.q*i.p,0),tax=sub*x.t/100,cl=S.cl.find(c=>c.n==x.c);
  $('#sheet').innerHTML=`<div class="hd"><div><h2>${esc(S.set.name)}</h2><span class="sub">${esc(S.set.mail)}</span></div><div style="text-align:right"><b style="font-size:18px">${x.id}</b><br><span class="pill ${x.s}">${x.s[0].toUpperCase()+x.s.slice(1)}</span></div></div>
  <div class="meta"><div><small>Billed to</small><b>${esc(x.c)}</b><small>${esc(cl&&cl.e||'')}</small></div><div><small>Issued</small><b>${show(x.i)}</b></div><div><small>Due</small><b>${show(x.d)}</b></div></div>
  <table><thead><tr><th>Description</th><th>Qty</th><th>Unit price</th><th>Amount</th></tr></thead><tbody>${x.items.map(i=>`<tr><td>${esc(i.d||'Item')}</td><td>${i.q}</td><td>${fmt(i.p)}</td><td>${fmt(i.q*i.p)}</td></tr>`).join('')}</tbody></table>
  <div class="tot" style="margin-top:14px"><div><span>Subtotal</span><span>${fmt(sub)}</span></div><div><span>Tax (${x.t}%)</span><span>${fmt(tax)}</span></div><div class="g"><span>Total</span><span>${fmt(sub+tax)}</span></div></div>
  <p class="note">${esc(S.set.note)}</p>
  <div class="foot noprint"><button class="btn" data-del="${x.id}">Delete</button><button class="btn" data-dup="${x.id}">Duplicate</button>${x.s!='paid'?`<button class="btn" data-r="${x.id}">Send reminder</button><button class="btn" data-p="${x.id}">Mark as paid</button>`:''}<button class="btn pri" id="prt">Print or save PDF</button><button class="btn" id="vx">Close</button></div>`;
  $('#vw').showModal();
}
function nextId(){const n=Math.max(1000,...S.inv.map(x=>+x.id.replace(/\D/g,'')||0))+1;return S.set.pre+n}

/* Invoice form */
const dlg=$('#dlg');
function line(d='',q=1,p=''){
  const r=document.createElement('div');r.className='li';
  r.innerHTML=`<input placeholder="Description" value="${esc(d)}" aria-label="Description"><input type="number" min="1" value="${q}" aria-label="Quantity"><input type="number" min="0" step="0.01" placeholder="Price" value="${p}" aria-label="Unit price"><button type="button" aria-label="Remove line">×</button>`;
  r.querySelector('button').onclick=()=>{r.remove();calc()};$('#items').appendChild(r);
}
function calc(){
  let s=0;document.querySelectorAll('.li').forEach(r=>{const i=r.querySelectorAll('input');s+=(+i[1].value||0)*(+i[2].value||0)});
  const t=s*(+$('#tax').value||0)/100;$('#sb').textContent=fmt(s);$('#tx').textContent=fmt(t);$('#gr').textContent=fmt(s+t);return s+t;
}
function newInv(client){
  $('#cls').innerHTML=S.cl.map(c=>`<option value="${esc(c.n)}">`).join('');
  $('#items').innerHTML='';line('',1,'');$('#cl').value=client||'';$('#tax').value=S.set.tax;$('#iss').value=day(0);$('#due').value=day(+S.set.terms||30);calc();
  if($('#vw').open)$('#vw').close();dlg.showModal();(client?document.querySelector('.li input'):$('#cl')).focus();
}

/* Session */
function doLogout(){
  $('#pop').hidden=true;
  if(confirm('Sign out of Ledgerly?')){
    location.href='../auth/logout.php';
  }
}
$('#logout').onclick=doLogout;
$('#logout2').onclick=doLogout;
$('#back').onclick=()=>{$('#so').hidden=true;toast('Signed in as '+ME().n);route()};

/* Profile actions */
$('#pf-manage').onclick=()=>toast('Opening plan manager…');
$('#pf-change').onclick=e=>{e.preventDefault();toast('Choose a new plan in the plan manager')};
$('#pf-billing').onclick=()=>{
  const el=document.querySelector('#pf-bills');
  if(el)el.closest('.card').scrollIntoView({behavior:'smooth',block:'start'});
};
$('#pf-save').onclick=()=>{
  const n=$('#pf-e-name').value.trim(),m=$('#pf-e-mail').value.trim();
  if(!n){$('#pf-e-name').focus();toast('Enter your full name');return}
  if(!/^.+@.+\..+$/.test(m)){$('#pf-e-mail').focus();toast('Enter a valid email address');return}
  const u=ME(),old=u.e;
  if((S.users||[]).some(x=>x!==u&&x.e.toLowerCase()==m.toLowerCase())){toast('That email belongs to another account');return}
  u.n=n;u.e=m;S.me.e=m;S.me.phone=$('#pf-e-phone').value.trim();S.me.tz=$('#pf-e-tz').value;
  S.audit.forEach(a=>{if(a.e===old){a.e=m;a.u=n}});
  save();syncMe();profileView();toast('Profile updated');
};

/* Backup actions */
$('#bk-create').onclick=()=>{
  const maxId=Math.max(0,...(S.backups||[]).map(x=>+x.id.replace(/\D/g,'')||0));
  const id='BK-'+String(maxId+1).padStart(4,'0');
  S.backups.unshift({id,name:'Manual backup',type:'manual',created:new Date().toISOString(),size:'4.3 MB',status:'complete'});
  save();backupsView();render();toast('Backup '+id+' created');
};
$('#bk-autobtn').onclick=()=>{
  S.bk.auto=!S.bk.auto;
  save();backupsView();toast('Auto-backup '+(S.bk.auto?'enabled':'disabled'));
};

/* Events */
document.addEventListener('click',e=>{
  const t=e.target;
  if(!t.closest('.tools'))$('#pop').hidden=true;
  const go=t.closest('[data-goto]');
  if(go&&!t.closest('.lout')){e.preventDefault();location.hash='#profile';return}
  const b=t.closest('button');
  if(b){
    const d=b.dataset;
    if(d.p){e.stopPropagation();markPaid(d.p);if($('#vw').open)openInv(d.p);return}
    if(d.r){remind(d.r);return}
    if(d.k){filter=d.k;invoices();return}
    if(d.uk){ufilter=d.uk;usersView();return}
    if(d.ak){afilter=d.ak;auditLogs();return}
    if(d.susp!==undefined){
      const u=S.users.find(x=>x.e==d.susp);
      if(u){u.suspended=!u.suspended;save();usersView();toast(u.n+(u.suspended?' — login suspended':' — login reactivated'))}
      return;
    }
    if(d.ulog!==undefined){term=d.ulog;$('#q').value=d.ulog;afilter='all';location.hash='#audit';return}
    if(d.rcpt!==undefined){toast(d.rcpt+' receipt downloaded');return}
    if(d.pw!==undefined){toast('Password reset link sent to your email');return}
    if(d.so!==undefined){if(confirm('Sign out of all devices?'))toast('All other sessions signed out');return}
    if(d.bkdl!==undefined){toast('Downloading backup '+d.bkdl+'…');return}
    if(d.bkrs!==undefined){
      if(confirm('Restore from '+d.bkrs+'? This will replace your current data.')){
        toast('Restoring from '+d.bkrs+'… (demo)');
      }
      return;
    }
    if(d.bkdel!==undefined){
      if(confirm('Delete backup '+d.bkdel+'? This cannot be undone.')){
        S.backups=S.backups.filter(x=>x.id!=d.bkdel);
        save();backupsView();render();toast(d.bkdel+' deleted');
      }
      return;
    }
    if(d.nc!==undefined){newInv(d.nc);return}
    if(d.vc!==undefined){term=d.vc;$('#q').value=d.vc;filter='all';location.hash='#invoices';return}
    if(d.dup){const x=S.inv.find(i=>i.id==d.dup);S.inv.unshift({...JSON.parse(JSON.stringify(x)),id:nextId(),s:'pending',i:day(0),d:day(+S.set.terms||30)});save();$('#vw').close();render();toast('Invoice duplicated');return}
    if(d.del){if(confirm('Delete '+d.del+'? This cannot be undone.')){S.inv=S.inv.filter(i=>i.id!=d.del);S.pay=S.pay.filter(p=>p.inv!=d.del);save();$('#vw').close();render();toast(d.del+' deleted')}return}
  }
  if(t.id=='prt'){window.print();return}
  if(t.id=='vx'){$('#vw').close();return}
  const tr=t.closest('tr[data-o]');if(tr)openInv(tr.dataset.o);
  const th=t.closest('th[data-s]');if(th){const k=th.dataset.s;sort=sort.k==k?{k,dir:-sort.dir}:{k,dir:1};invoices()}
});

document.addEventListener('change', async e=>{
  const select=e.target.closest('[data-role-id]');
  if(!select)return;
  const user=S.users.find(x=>String(x.id)===select.dataset.roleId);
  if(!user)return;
  const previous=user.role==='Admin'?'admin':'user';
  const next=select.value;
  select.disabled=true;
  try{
    const response=await fetch('update_role.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
      body:new URLSearchParams({csrf_token:ADMIN_CSRF,user_id:select.dataset.roleId,role:next})
    });
    const result=await response.json();
    if(!response.ok||!result.success)throw new Error(result.message||'Unable to update this role.');
    user.role=next==='admin'?'Admin':'User';
    save();
    usersView();
    toast(result.message);
  }catch(error){
    select.value=previous;
    toast(error.message||'Unable to update this role.');
  }finally{
    select.disabled=false;
  }
});
$('#bell').onclick=e=>{e.stopPropagation();$('#pop').hidden=!$('#pop').hidden};
$('#pop').onclick=e=>e.stopPropagation();
$('#new').onclick=()=>newInv();
$('#q').oninput=e=>{
  term=e.target.value;
  if(page=='invoices')invoices();
  else if(page=='audit')auditLogs();
  else if(page=='users')usersView();
  else if(page=='backups')backupsView();
  else{location.hash='#invoices'}
};
$('#items').oninput=calc;$('#tax').oninput=calc;$('#addItem').onclick=()=>line();$('#cancel').onclick=()=>dlg.close();
$('#save').onclick=()=>{
  const c=$('#cl').value.trim();if(!c){$('#cl').focus();toast('Enter a client name');return}
  const items=[...document.querySelectorAll('.li')].map(r=>{const i=r.querySelectorAll('input');return{d:i[0].value.trim(),q:+i[1].value||1,p:+i[2].value||0}}).filter(i=>i.p>0);
  if(!items.length){toast('Add at least one priced line item');return}
  if(!S.cl.some(x=>x.n==c))S.cl.push({n:c,e:''});
  const t=+$('#tax').value||0,id=nextId();
  S.inv.unshift({id,c,i:$('#iss').value||day(0),d:$('#due').value||day(30),a:Math.round(items.reduce((s,i)=>s+i.q*i.p,0)*(1+t/100)*100)/100,s:'pending',items,t});
  save();dlg.close();filter='all';term='';$('#q').value='';location.hash='#invoices';render();toast(id+' generated');
};
$('#csv').onclick=()=>{
  const rows=[['Invoice','Client','Issued','Due','Status','Amount']].concat(S.inv.map(x=>[x.id,x.c,x.i,x.d,x.s,x.a]));
  const csv=rows.map(r=>r.map(v=>'"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
  const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));a.download='invoices.csv';document.body.appendChild(a);a.click();a.remove();toast('Invoices exported');
};
$('#addc').onclick=()=>{$('#c-n').value='';$('#c-e').value='';$('#cdlg').showModal();$('#c-n').focus()};
$('#c-x').onclick=()=>$('#cdlg').close();
$('#c-s').onclick=()=>{const n=$('#c-n').value.trim();if(!n){toast('Enter a client name');return}
  if(S.cl.some(c=>c.n.toLowerCase()==n.toLowerCase())){toast('That client already exists');return}
  S.cl.push({n,e:$('#c-e').value.trim()});save();$('#cdlg').close();render();toast(n+' added')};
$('#recpay').onclick=()=>{const o=S.inv.filter(x=>x.s!='paid');
  if(!o.length){toast('Every invoice is already paid');return}
  $('#p-i').innerHTML=o.map(x=>`<option value="${x.id}">${x.id} · ${esc(x.c)} · ${fmt(x.a)}</option>`).join('');$('#pdlg').showModal()};
$('#p-x').onclick=()=>$('#pdlg').close();
$('#p-s').onclick=()=>{markPaid($('#p-i').value,$('#p-m').value);$('#pdlg').close()};
$('#ssave').onclick=()=>{S.set={name:$('#s-name').value.trim()||'My business',mail:$('#s-mail').value.trim(),cur:$('#s-cur').value,tax:+$('#s-tax').value||0,terms:+$('#s-terms').value||0,pre:$('#s-pre').value||'INV-',note:$('#s-note').value};save();toast('Settings saved')};
$('#reset').onclick=()=>{if(confirm('Replace all data with the demo data?')){S=DEF();if(!S.me)S.me=DEF().me;if(!S.bk)S.bk=DEF().bk;if(!S.backups)S.backups=DEF().backups;save();syncMe();settings();render();toast('Demo data restored')}};
document.querySelectorAll('dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()}));
document.addEventListener('keydown',e=>{
  if(!$('#so').hidden)return;
  if((e.key==='Enter'||e.key===' ')&&e.target.closest&&e.target.closest('[data-goto]')){e.preventDefault();location.hash='#profile';return}
  if(e.target.matches('input,textarea,select')||e.metaKey||e.ctrlKey)return;
  if(e.key=='n'){e.preventDefault();newInv()}
  if(e.key=='/'){e.preventDefault();$('#q').focus()}
});
route();
</script>
</body>
</html>