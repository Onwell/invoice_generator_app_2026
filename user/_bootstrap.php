<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth/_bootstrap.php';
if (!isset($_SESSION['auth_user']) || !is_array($_SESSION['auth_user'])) {
    header('Location: ../index.php?auth=login');
    exit;
}
if (!defined('LEDGERLY')) define('LEDGERLY', true);

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
function relDay(int $offset): string { return date('Y-m-d', strtotime("{$offset} days")); }
function relStamp(float $hoursAgo): string { return date('c', time() - (int) round($hoursAgo * 3600)); }

$year = (int) date('Y');

$icons = [
    'overview' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    'invoices' => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 12h7M9 16h7"/>',
    'clients'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.5 3.2-5.5 6.5-5.5s5.9 2 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 010 6.8M18 14.8c1.9.7 3.1 2.4 3.5 5.2"/>',
    'payments' => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/>',
    'account'  => '<circle cx="12" cy="8" r="4"/><path d="M4 20c.8-4.2 4-6.5 8-6.5s7.2 2.3 8 6.5"/>',
];
function svg_icon(string $key): string {
    global $icons;
    return isset($icons[$key])
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">' . $icons[$key] . '</svg>'
        : '';
}

/* page key => [title, subtitle, filename] */
$pages = [
    'overview' => ['Overview',    "Welcome back. Here's your workspace.",            'overview.php'],
    'invoices' => ['My invoices', 'Create, track and collect your invoices.',        'invoices.php'],
    'clients'  => ['My clients',  'The people and businesses you bill.',             'clients.php'],
    'payments' => ['Payments',    'Payments recorded against your invoices.',        'payments.php'],
    'account'  => ['Account',     'Your profile, security and plan.',                'account.php'],
];

/* sidebar + bottom-nav, in order */
$nav = [
    'overview' => ['label' => 'Overview',    'file' => 'overview.php'],
    'invoices' => ['label' => 'My invoices', 'file' => 'invoices.php'],
    'clients'  => ['label' => 'My clients',  'file' => 'clients.php'],
    'payments' => ['label' => 'Payments',    'file' => 'payments.php'],
    'account'  => ['label' => 'Account',     'file' => 'account.php'],
];

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
        'n' => 'Amara Moyo', 'e' => 'amara@ledgerly.app', 'role' => 'Administrator',
        'phone' => '+263 77 123 4567', 'tz' => 'Africa/Harare',
        'plan' => 'Ledgerly Pro', 'cycle' => 'Monthly', 'renew' => relDay(12),
        'payment' => 'Visa •••• 4242', 'seats' => 5, 'seatsUsed' => 4,
        'invoicesUsed' => 38, 'invoiceLimit' => 100, 'clientsUsed' => 5, 'clientLimit' => 50,
        'storageUsed' => 1.8, 'storageLimit' => 10, 'pwChanged' => relDay(-42),
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
        ['t' => relStamp(0.3),  'ev' => 'login',  'ip' => '102.65.14.8',  'loc' => 'Harare, ZW'],
        ['t' => relStamp(14.7), 'ev' => 'login',  'ip' => '102.65.14.8',  'loc' => 'Harare, ZW'],
        ['t' => relStamp(38.2), 'ev' => 'failed', 'ip' => '45.221.10.77', 'loc' => 'Lagos, NG'],
        ['t' => relStamp(46.9), 'ev' => 'login',  'ip' => '102.65.14.8',  'loc' => 'Harare, ZW'],
        ['t' => relStamp(72.4), 'ev' => 'login',  'ip' => '102.65.14.8',  'loc' => 'Harare, ZW'],
    ],
    'set' => [
        'name' => 'Ledgerly Studio', 'mail' => 'billing@ledgerly.app',
        'cur' => '$', 'tax' => 15, 'terms' => 30, 'pre' => 'INV-',
        'note' => 'Thank you for your business. Please pay by the due date.',
    ],
];

$authUser = $_SESSION['auth_user'];
$default['me']['n'] = (string) ($authUser['name'] ?? 'User');
$default['me']['e'] = (string) ($authUser['email'] ?? '');
$default['me']['role'] = 'User';
$me = $default['me'];

$jsonFlags   = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$defaultJson = json_encode($default, $jsonFlags) ?: '{}';