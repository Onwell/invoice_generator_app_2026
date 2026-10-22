<?php
/**
 * Ledgerly – landing page with login and registration modals.
 */
declare(strict_types=1);
require_once __DIR__ . '/auth/_bootstrap.php';

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$year = (int) date('Y');

/* ---- Links & endpoints ---- */
$signInUrl      = '#signin';          // fallback href
$signUpUrl      = '#signup';          // fallback href
$loginEndpoint  = 'auth/login.php';
$signupEndpoint = 'auth/register.php';
$appUrl         = 'user/overview.php';
$loginDemo      = $loginEndpoint === '#';
$signupDemo     = $signupEndpoint === '#';
$authFlash      = auth_pull_flash();
$authCsrf       = auth_csrf_token();
$jsonFlags      = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

/* ---- Primary navigation ---- */
$navLinks = [
    ['href' => '#features', 'label' => 'Features'],
    ['href' => '#how',      'label' => 'How it works'],
    ['href' => '#pricing',  'label' => 'Pricing'],
    ['href' => '#stories',  'label' => 'Stories'],
    ['href' => '#faq',      'label' => 'FAQ'],
];

/* ---- Logo strip ---- */
$logos = [
    ['name' => 'Northwind', 'icon' => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/>'],
    ['name' => 'Kuda',      'icon' => '<circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/>'],
    ['name' => 'Baobab',    'icon' => '<path d="M12 3l9 18H3z"/>'],
    ['name' => 'Meridian',  'icon' => '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 12h8"/>'],
    ['name' => 'Zambezi',   'icon' => '<path d="M4 20c4-10 12-10 16 0"/><circle cx="12" cy="7" r="3"/>'],
];

/* ---- Feature grid ---- */
$features = [
    [
        'title' => 'Invoices in under a minute',
        'desc'  => 'Build a branded invoice with line items, tax and payment terms. Saved clients and products make the next one even faster.',
        'icon'  => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 12h7M9 16h7"/>',
        'wide'  => false,
    ],
    [
        'title' => 'Automatic payment reminders',
        'desc'  => 'Friendly nudges go out before and after the due date, so you stop writing awkward follow-up emails yourself.',
        'icon'  => '<path d="M12 8v5l3 2"/><circle cx="12" cy="12" r="9"/>',
        'wide'  => false,
    ],
    [
        'title' => 'Track every payment',
        'desc'  => "See what's paid, pending and overdue at a glance. Record a payment and the dashboard updates instantly.",
        'icon'  => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/>',
        'wide'  => false,
    ],
    [
        'title' => 'Clients with full history',
        'desc'  => 'Each client gets a page showing every invoice, payment and outstanding balance — so you always know where a relationship stands.',
        'icon'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.5 3.2-5.5 6.5-5.5s5.9 2 6.5 5.5"/><circle cx="18" cy="7" r="2.5"/><path d="M16.5 13.2c2 .3 3.4 1.6 4 3.8"/>',
        'wide'  => true,
    ],
    [
        'title' => 'Audit-ready records',
        'desc'  => "Every sign-in and money movement is logged with a timestamp, so you're never guessing what happened.",
        'icon'  => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'wide'  => false,
    ],
    [
        'title' => 'Roles and access control',
        'desc'  => 'Invite your accountant as a viewer, or a teammate as a billing clerk. Everyone sees exactly what they should.',
        'icon'  => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>',
        'wide'  => false,
    ],
    [
        'title' => 'Clear revenue insights',
        'desc'  => "A simple monthly view of what's come in and what's still owed — no dashboards you need a course to read.",
        'icon'  => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
        'wide'  => false,
    ],
];

/* ---- Pricing plans ---- */
$plans = [
    [
        'name'      => 'Starter',
        'desc'      => 'For freelancers sending their first invoices.',
        'price_m'   => '$0',
        'price_y'   => '$0',
        'unit'      => '/ month',
        'note_m'    => 'Free forever',
        'note_y'    => 'Free forever',
        'features'  => [
            ['text' => 'Up to 5 invoices a month'],
            ['text' => 'Unlimited clients'],
            ['text' => 'PDF export & email sending'],
            ['text' => '1 team seat'],
            ['text' => 'Automatic reminders', 'disabled' => true],
        ],
        'cta'       => 'Get started free',
        'cta_href'  => '#signup',
        'auth'      => 'up',
        'popular'   => false,
        'primary'   => false,
    ],
    [
        'name'      => 'Pro',
        'desc'      => 'For growing teams who invoice every week.',
        'price_m'   => '$19',
        'price_y'   => '$15',
        'unit'      => '/ user / month',
        'note_m'    => 'Billed monthly',
        'note_y'    => '$180 billed yearly',
        'features'  => [
            ['text' => 'Unlimited invoices'],
            ['text' => 'Unlimited clients & products'],
            ['text' => 'Automatic payment reminders'],
            ['text' => 'Up to 10 team seats'],
            ['text' => 'Audit logs & role controls'],
        ],
        'cta'       => 'Start 14-day trial',
        'cta_href'  => '#signup',
        'auth'      => 'up',
        'popular'   => true,
        'primary'   => true,
    ],
    [
        'name'      => 'Business',
        'desc'      => 'For larger finance and ops teams.',
        'price_m'   => '$39',
        'price_y'   => '$31',
        'unit'      => '/ user / month',
        'note_m'    => 'Billed monthly',
        'note_y'    => '$372 billed yearly',
        'features'  => [
            ['text' => 'Everything in Pro'],
            ['text' => 'Unlimited team seats'],
            ['text' => 'Multi-currency invoicing'],
            ['text' => 'Priority support with SLA'],
            ['text' => 'Custom branding on PDFs'],
        ],
        'cta'       => 'Talk to sales',
        'cta_href'  => 'mailto:sales@ledgerly.app',
        'auth'      => null,
        'popular'   => false,
        'primary'   => false,
    ],
];

/* ---- Testimonials ---- */
$testimonials = [
    [
        'quote'    => 'We cut our average payment time from 32 days to 19. The reminders alone paid for the subscription in the first week.',
        'initials' => 'TN',
        'name'     => 'Tendai Ncube',
        'role'     => 'Finance lead · Kuda Studios',
    ],
    [
        'quote'    => "The dashboard finally shows me what I actually need: what's paid, what's pending, and what's late. Nothing else.",
        'initials' => 'GP',
        'name'     => 'Grace Phiri',
        'role'     => 'Founder · Zambezi Foods',
    ],
    [
        'quote'    => 'Our accountant logs in as a viewer and reconciles everything in minutes. No more monthly zip files of PDFs.',
        'initials' => 'SO',
        'name'     => 'Sam Okoro',
        'role'     => 'Ops manager · Northwind Logistics',
    ],
];

/* ---- FAQ ---- */
$faqs = [
    [
        'q' => 'Do I need a credit card to start?',
        'a' => "No. The Starter plan is free forever and doesn't ask for a card. Pro and Business come with a 14-day trial, and you can cancel before it ends without being charged.",
    ],
    [
        'q' => 'Can I send invoices in multiple currencies?',
        'a' => 'Yes — multi-currency invoicing is included on the Business plan. Each invoice can use its own currency, and your reports show totals converted to your base currency.',
    ],
    [
        'q' => "What happens if a client doesn't pay?",
        'a' => 'Automatic reminders are sent before and after the due date based on the schedule you choose. Invoices that pass the due date are flagged as overdue on your dashboard so nothing slips through.',
    ],
    [
        'q' => 'Can my accountant or teammate log in?',
        'a' => 'Yes. Invite teammates with roles — admin, accountant, billing clerk or viewer — and control exactly what each person can see and do. Every sign-in is recorded in the audit log.',
    ],
    [
        'q' => 'Is my data exportable?',
        'a' => 'Always. Export invoices, clients and payments to CSV at any time, and download any invoice as a PDF. Your data belongs to you.',
    ],
    [
        'q' => 'How long does setup take?',
        'a' => 'About five minutes. Sign up, add your business name and logo, then create your first invoice. There are no onboarding calls or consultants required.',
    ],
];

/* ---- Footer link groups ---- */
$footerGroups = [
    'Product' => [
        ['href' => '#features', 'label' => 'Features'],
        ['href' => '#pricing',  'label' => 'Pricing'],
        ['href' => '#how',      'label' => 'How it works'],
        ['href' => '#',         'label' => 'Changelog'],
        ['href' => '#',         'label' => 'Roadmap'],
    ],
    'Company' => [
        ['href' => '#',         'label' => 'About'],
        ['href' => '#stories',  'label' => 'Customers'],
        ['href' => '#',         'label' => 'Careers'],
        ['href' => '#',         'label' => 'Contact'],
        ['href' => '#',         'label' => 'Press'],
    ],
    'Resources' => [
        ['href' => '#',         'label' => 'Help centre'],
        ['href' => '#faq',      'label' => 'FAQ'],
        ['href' => '#',         'label' => 'Invoice templates'],
        ['href' => '#',         'label' => 'API docs'],
        ['href' => '#',         'label' => 'Status'],
    ],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Ledgerly – Invoicing that gets you paid</title>
<meta name="description" content="Ledgerly is the calm, dependable invoicing platform for small teams. Create invoices, track payments, and get paid faster.">
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --accent:#0E5A47;--accent-2:#0A4437;--soft:#E6F0ED;--soft-2:#F2F8F6;
  --bg:#F6F7F7;--surface:#fff;--ink:#111614;--muted:#66716D;--line:#E3E7E6;
  --radius:16px;--max:1160px;color-scheme:light
}
*{box-sizing:border-box;margin:0}
html{scroll-behavior:smooth}
body{font-family:"Hanken Grotesk",system-ui,-apple-system,"Segoe UI",sans-serif;background:var(--bg);color:var(--ink);font-size:16px;line-height:1.6;-webkit-text-size-adjust:100%;font-variant-numeric:tabular-nums}
button,input,select,textarea{font:inherit;color:inherit}
a{color:inherit;text-decoration:none}
:focus-visible{outline:2px solid var(--accent);outline-offset:3px}
img,svg{display:block}
[hidden]{display:none!important}

/* ---------- Layout ---------- */
.wrap{width:min(100% - 40px, var(--max));margin-inline:auto}
.section{padding:96px 0}
.section.tight{padding:72px 0}
.eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--soft);color:var(--accent);font-weight:700;font-size:12.5px;letter-spacing:.04em;text-transform:uppercase;padding:6px 12px;border-radius:20px}
h1,h2,h3{letter-spacing:-.025em;line-height:1.12}
h2{font-size:clamp(28px,3.6vw,42px);font-weight:700}
h3{font-size:19px;font-weight:700;letter-spacing:-.015em}
.lead{color:var(--muted);font-size:clamp(16px,1.7vw,19px);max-width:60ch}
.center{text-align:center}
.center .lead{margin-inline:auto}
.sec-head{display:flex;flex-direction:column;gap:14px;margin-bottom:48px}
.sec-head.center{align-items:center}

/* ---------- Buttons ---------- */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:13px 22px;border-radius:12px;border:1px solid var(--line);background:var(--surface);font-weight:600;font-size:15px;cursor:pointer;white-space:nowrap;transition:transform .15s ease, background .15s ease, border-color .15s ease, box-shadow .15s ease}
.btn:hover{transform:translateY(-1px);border-color:var(--accent)}
.btn:active{transform:translateY(0)}
.btn.pri{background:var(--accent);border-color:var(--accent);color:#fff;box-shadow:0 8px 20px -8px rgba(14,90,71,.5)}
.btn.pri:hover{background:var(--accent-2);border-color:var(--accent-2);box-shadow:0 12px 26px -10px rgba(14,90,71,.6)}
.btn.ghost{background:transparent}
.btn.lg{padding:15px 28px;font-size:16px;border-radius:13px}
.btn svg{width:18px;height:18px;flex:none}
.btn.block{width:100%}

/* ---------- Header ---------- */
.hd{position:sticky;top:0;z-index:50;background:rgba(246,247,247,.85);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid transparent;transition:border-color .2s ease,background .2s ease}
.hd.scrolled{border-color:var(--line);background:rgba(255,255,255,.9)}
.hd-in{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 0}
.brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;letter-spacing:-.03em}
.mark{width:34px;height:34px;border-radius:9px;background:var(--accent);color:#fff;display:grid;place-items:center;font-weight:800;font-size:16px;flex:none}
.hd-nav{display:flex;align-items:center;gap:6px}
.hd-nav a{padding:9px 14px;border-radius:9px;font-weight:500;color:#3c4743;font-size:15px}
.hd-nav a:hover{background:var(--soft);color:var(--accent)}
.hd-cta{display:flex;align-items:center;gap:10px}
.hd-cta .btn{padding:10px 18px;font-size:14.5px}
.burger{display:none;width:44px;height:44px;border-radius:11px;border:1px solid var(--line);background:var(--surface);cursor:pointer;place-items:center}
.burger svg{width:20px;height:20px}
.mmenu{display:none}

/* ---------- Hero ---------- */
.hero{position:relative;overflow:hidden;padding:64px 0 40px}
.hero::before{content:"";position:absolute;inset:-200px -100px auto -100px;height:620px;background:radial-gradient(60% 60% at 50% 20%, rgba(14,90,71,.10), transparent 70%);pointer-events:none}
.hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:56px;align-items:center;position:relative}
.hero-copy{display:flex;flex-direction:column;gap:22px;align-items:flex-start}
.hero h1{font-size:clamp(36px,5.4vw,62px);font-weight:800;letter-spacing:-.035em;line-height:1.02}
.hero h1 em{font-style:normal;color:var(--accent);position:relative;white-space:nowrap}
.hero h1 em::after{content:"";position:absolute;left:0;right:0;bottom:.04em;height:.28em;background:var(--soft);z-index:-1;border-radius:4px}
.hero .lead{font-size:clamp(16px,1.8vw,19px);max-width:52ch}
.hero-cta{display:flex;gap:12px;flex-wrap:wrap}
.hero-note{display:flex;align-items:center;gap:10px;color:var(--muted);font-size:13.5px}
.hero-note svg{width:16px;height:16px;color:var(--accent);flex:none}
.hero-stats{display:flex;gap:32px;flex-wrap:wrap;padding-top:8px}
.hero-stats div b{display:block;font-size:24px;font-weight:800;letter-spacing:-.03em}
.hero-stats div span{color:var(--muted);font-size:13px}

/* ---------- Product preview ---------- */
.preview{position:relative}
.preview-card{background:var(--surface);border:1px solid var(--line);border-radius:20px;box-shadow:0 40px 80px -40px rgba(10,30,24,.35),0 8px 24px -12px rgba(10,30,24,.12);overflow:hidden;transform:rotate(-1deg);transition:transform .4s ease}
.preview:hover .preview-card{transform:rotate(0) translateY(-4px)}
.pv-top{display:flex;align-items:center;gap:7px;padding:12px 16px;border-bottom:1px solid var(--line);background:var(--soft-2)}
.pv-dot{width:10px;height:10px;border-radius:50%;background:#cdd8d4}
.pv-url{margin-left:10px;flex:1;height:26px;border-radius:7px;background:#fff;border:1px solid var(--line);display:flex;align-items:center;padding:0 10px;font-size:11.5px;color:var(--muted)}
.pv-body{padding:20px}
.pv-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
.pv-head h4{font-size:15px;font-weight:700;letter-spacing:-.01em}
.pv-head span{font-size:12px;color:var(--muted)}
.pv-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px}
.pv-kpi{background:var(--soft-2);border:1px solid var(--line);border-radius:11px;padding:11px 12px}
.pv-kpi.hero{background:var(--accent);border-color:var(--accent);color:#fff}
.pv-kpi small{font-size:10.5px;opacity:.75;display:block}
.pv-kpi b{font-size:17px;font-weight:700;letter-spacing:-.02em;display:block;margin-top:2px}
.pv-kpi.hero small{color:rgba(255,255,255,.78)}
.pv-table{display:flex;flex-direction:column;gap:1px;border-radius:11px;overflow:hidden;border:1px solid var(--line)}
.pv-row{display:grid;grid-template-columns:1fr auto auto;gap:12px;align-items:center;padding:10px 12px;background:#fff;font-size:12px}
.pv-row:nth-child(even){background:#fcfdfd}
.pv-row b{font-weight:600}
.pv-row small{color:var(--muted);display:block;font-size:10.5px}
.pv-pill{font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:5px}
.pv-pill::before{content:"";width:5px;height:5px;border-radius:50%;background:currentColor}
.pv-pill.paid{background:var(--soft);color:var(--accent)}
.pv-pill.pending{color:#5b6560;box-shadow:inset 0 0 0 1px var(--line)}
.pv-pill.overdue{background:var(--accent);color:#fff}
.pv-amt{font-weight:700;font-variant-numeric:tabular-nums;text-align:right;min-width:74px}
.pv-float{position:absolute;background:#fff;border:1px solid var(--line);border-radius:13px;box-shadow:0 20px 40px -20px rgba(10,30,24,.3);padding:12px 14px;display:flex;gap:11px;align-items:center;animation:float 5s ease-in-out infinite}
.pv-float .ic{width:34px;height:34px;border-radius:10px;background:var(--soft);color:var(--accent);display:grid;place-items:center;flex:none}
.pv-float .ic svg{width:17px;height:17px}
.pv-float b{display:block;font-size:12.5px;font-weight:700;letter-spacing:-.01em}
.pv-float small{color:var(--muted);font-size:11px}
.pv-float.f1{top:-14px;right:-14px}
.pv-float.f2{bottom:-18px;left:-18px;animation-delay:-2.5s}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}

/* ---------- Logo strip ---------- */
.logos{padding:8px 0 24px}
.logos p{text-align:center;color:var(--muted);font-size:13px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:24px}
.logo-row{display:flex;justify-content:center;gap:16px 44px;flex-wrap:wrap;align-items:center;opacity:.7}
.logo-row span{font-weight:800;font-size:19px;letter-spacing:-.02em;color:#7d8783;display:flex;align-items:center;gap:8px;transition:color .2s ease,opacity .2s ease}
.logo-row span:hover{color:var(--accent);opacity:1}
.logo-row svg{width:20px;height:20px}

/* ---------- Features ---------- */
.fgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feat{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:28px;display:flex;flex-direction:column;gap:14px;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
.feat:hover{transform:translateY(-3px);box-shadow:0 24px 40px -28px rgba(10,30,24,.35);border-color:#cfdbd7}
.feat .fic{width:46px;height:46px;border-radius:13px;background:var(--soft);color:var(--accent);display:grid;place-items:center}
.feat .fic svg{width:22px;height:22px}
.feat p{color:var(--muted);font-size:14.5px}
.feat.wide{grid-column:span 2;flex-direction:row;align-items:center;gap:28px;background:linear-gradient(135deg,var(--soft-2),#fff)}
.feat.wide .fic{width:56px;height:56px;border-radius:16px}
.feat.wide .fic svg{width:26px;height:26px}
.feat.wide>div:last-child{flex:1}

/* ---------- How it works ---------- */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;counter-reset:step}
.step{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:28px;position:relative}
.step::before{counter-increment:step;content:counter(step,decimal-leading-zero);position:absolute;top:22px;right:24px;font-size:34px;font-weight:800;color:var(--soft);letter-spacing:-.04em;line-height:1}
.step h3{margin-bottom:8px;padding-right:52px}
.step p{color:var(--muted);font-size:14.5px}

/* ---------- Pricing ---------- */
.pricing{background:var(--surface);border-block:1px solid var(--line)}
.toggle{display:inline-flex;align-items:center;gap:4px;background:var(--soft-2);border:1px solid var(--line);padding:5px;border-radius:12px;margin-top:6px}
.toggle button{border:0;background:transparent;padding:9px 18px;border-radius:9px;font-weight:600;font-size:14px;cursor:pointer;color:var(--muted);transition:background .2s ease,color .2s ease}
.toggle button.on{background:var(--surface);color:var(--ink);box-shadow:0 1px 3px rgba(10,30,24,.1)}
.toggle .save{font-size:11px;font-weight:700;color:var(--accent);background:var(--soft);padding:2px 8px;border-radius:20px;margin-left:6px}
.pgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:start}
.plan{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:30px;display:flex;flex-direction:column;gap:18px;position:relative;transition:transform .2s ease,box-shadow .2s ease}
.plan:hover{transform:translateY(-3px);box-shadow:0 24px 40px -28px rgba(10,30,24,.3)}
.plan.pop{border-color:var(--accent);box-shadow:0 24px 48px -30px rgba(14,90,71,.5);border-width:2px}
.plan .tag{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:var(--accent);color:#fff;font-size:11.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:5px 14px;border-radius:20px}
.plan h3{font-size:17px}
.plan .desc{color:var(--muted);font-size:14px;margin-top:-8px}
.price{display:flex;align-items:baseline;gap:6px}
.price b{font-size:42px;font-weight:800;letter-spacing:-.04em;line-height:1}
.price span{color:var(--muted);font-size:14px;font-weight:500}
.price small{color:var(--muted);font-size:12.5px;display:block;margin-top:2px;width:100%}
.plan ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:11px}
.plan li{display:flex;gap:10px;align-items:flex-start;font-size:14.5px;color:#2c3733}
.plan li svg{width:18px;height:18px;color:var(--accent);flex:none;margin-top:2px}
.plan li.mut{color:var(--muted)}
.plan li.mut svg{color:#c3cdc9}
.plan .btn{margin-top:4px}

/* ---------- Testimonials ---------- */
.tgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.quote{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:28px;display:flex;flex-direction:column;gap:18px}
.quote .stars{display:flex;gap:2px;color:#E8A33D}
.quote .stars svg{width:16px;height:16px;fill:currentColor}
.quote p{font-size:15px;line-height:1.65;color:#2c3733}
.quote footer{display:flex;gap:12px;align-items:center;margin-top:auto}
.quote .av{width:42px;height:42px;border-radius:50%;background:var(--soft);color:var(--accent);display:grid;place-items:center;font-weight:700;font-size:14px;flex:none}
.quote b{display:block;font-size:14px}
.quote small{color:var(--muted);font-size:12.5px}

/* ---------- FAQ ---------- */
.faq{max-width:780px;margin-inline:auto;display:flex;flex-direction:column;gap:12px}
.qa{background:var(--surface);border:1px solid var(--line);border-radius:14px;overflow:hidden;transition:border-color .2s ease}
.qa.open{border-color:#cfdbd7}
.qa summary{list-style:none;cursor:pointer;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;gap:16px;font-weight:600;font-size:15.5px}
.qa summary::-webkit-details-marker{display:none}
.qa summary .chev{width:22px;height:22px;border-radius:50%;background:var(--soft);color:var(--accent);display:grid;place-items:center;flex:none;transition:transform .25s ease}
.qa summary .chev svg{width:13px;height:13px}
.qa[open] summary .chev{transform:rotate(180deg)}
.qa .ans{padding:0 24px 22px;color:var(--muted);font-size:14.5px;max-width:65ch}

/* ---------- CTA ---------- */
.cta-band{background:var(--accent);border-radius:24px;padding:64px 48px;display:flex;justify-content:space-between;align-items:center;gap:32px;flex-wrap:wrap;position:relative;overflow:hidden;color:#fff}
.cta-band::before{content:"";position:absolute;right:-120px;top:-120px;width:420px;height:420px;border-radius:50%;background:rgba(255,255,255,.06)}
.cta-band::after{content:"";position:absolute;left:-80px;bottom:-160px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.05)}
.cta-band h2{font-size:clamp(26px,3.4vw,38px);max-width:20ch;position:relative}
.cta-band p{color:rgba(255,255,255,.8);max-width:46ch;margin-top:10px;position:relative}
.cta-band .btns{display:flex;gap:12px;flex-wrap:wrap;position:relative}
.cta-band .btn{background:#fff;border-color:#fff;color:var(--accent)}
.cta-band .btn:hover{background:var(--soft);border-color:var(--soft)}
.cta-band .btn.ghost{background:transparent;border-color:rgba(255,255,255,.35);color:#fff}
.cta-band .btn.ghost:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.6)}

/* ---------- Footer ---------- */
.ft{padding:72px 0 32px;border-top:1px solid var(--line);background:var(--surface)}
.ft-grid{display:grid;grid-template-columns:1.6fr repeat(3,1fr);gap:40px;margin-bottom:48px}
.ft-brand p{color:var(--muted);font-size:14px;margin:14px 0 20px;max-width:34ch}
.ft-social{display:flex;gap:10px}
.ft-social a{width:38px;height:38px;border-radius:10px;border:1px solid var(--line);display:grid;place-items:center;color:var(--muted);transition:color .2s ease,border-color .2s ease,background .2s ease}
.ft-social a:hover{color:var(--accent);border-color:var(--accent);background:var(--soft)}
.ft-social svg{width:17px;height:17px}
.ft h4{font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink);margin-bottom:16px}
.ft ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:11px}
.ft ul a{color:var(--muted);font-size:14.5px}
.ft ul a:hover{color:var(--accent)}
.ft-bot{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;padding-top:28px;border-top:1px solid var(--line);color:var(--muted);font-size:13.5px}
.ft-bot .links{display:flex;gap:22px;flex-wrap:wrap}
.ft-bot a:hover{color:var(--accent)}

/* ---------- Auth modal ---------- */
.auth{position:fixed;inset:0;z-index:100;display:grid;place-items:center;padding:20px;opacity:0;transition:opacity .2s ease}
.auth.open{opacity:1}
.auth-back{position:absolute;inset:0;background:rgba(10,18,15,.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
.auth-card{position:relative;width:min(100%,460px);max-height:calc(100vh - 40px);overflow-y:auto;overscroll-behavior:contain;background:var(--surface);border:1px solid var(--line);border-radius:22px;box-shadow:0 40px 90px -30px rgba(10,30,24,.5);padding:28px;transform:translateY(14px) scale(.98);transition:transform .22s ease}
.auth.open .auth-card{transform:none}
.auth-x{position:absolute;top:14px;right:14px;width:36px;height:36px;border-radius:50%;border:1px solid var(--line);background:var(--surface);color:var(--muted);display:grid;place-items:center;cursor:pointer;padding:0}
.auth-x:hover{background:var(--soft);color:var(--accent);border-color:var(--soft)}
.auth-x svg{width:16px;height:16px}
.auth-brand{display:flex;align-items:center;gap:9px;font-weight:800;font-size:17px;letter-spacing:-.03em;margin-bottom:18px}
.auth-brand .mark{width:30px;height:30px;font-size:14px;border-radius:8px}
.auth-tabs{display:grid;grid-template-columns:1fr 1fr;gap:4px;background:var(--soft-2);border:1px solid var(--line);padding:4px;border-radius:12px;margin-bottom:22px}
.auth-tabs button{border:0;background:transparent;padding:10px;border-radius:9px;font-weight:600;font-size:14px;color:var(--muted);cursor:pointer;transition:background .2s ease,color .2s ease,box-shadow .2s ease}
.auth-tabs button[aria-selected="true"]{background:var(--surface);color:var(--ink);box-shadow:0 1px 3px rgba(10,30,24,.12)}
.auth.done .auth-tabs{display:none}
.auth-form h2{font-size:23px;font-weight:800;letter-spacing:-.03em}
.auth-sub{color:var(--muted);font-size:14px;margin:4px 0 20px}
.auth-message{padding:11px 13px;border-radius:10px;margin:0 0 16px;font-size:13.5px}
.auth-message.error{background:#fff0ee;color:#9b2c20}
.auth-message.success{background:var(--soft);color:var(--accent)}
.field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.field label{font-size:13.5px;font-weight:600;color:#2c3733}
.field label .opt{color:var(--muted);font-weight:500}
.field input{width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:11px;background:var(--surface);font-size:15px;transition:border-color .15s ease,box-shadow .15s ease}
.field input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(14,90,71,.14)}
.field input[aria-invalid="true"]{border-color:#B42318;box-shadow:0 0 0 3px rgba(180,35,24,.12)}
.err{color:#B42318;font-size:12.5px;font-weight:500;display:none}
.err.show{display:block}
.pw{position:relative}
.pw input{padding-right:44px}
.pw-t{position:absolute;top:50%;right:6px;transform:translateY(-50%);width:34px;height:34px;border:0;background:transparent;color:var(--muted);display:grid;place-items:center;cursor:pointer;border-radius:8px;padding:0}
.pw-t:hover{background:var(--soft);color:var(--accent)}
.pw-t svg{width:17px;height:17px}
.pw-t .eye-off{display:none}
.pw-t.on .eye-on{display:none}
.pw-t.on .eye-off{display:block}
.pw-t.on{color:var(--accent)}
.auth-row{display:flex;justify-content:space-between;align-items:center;gap:12px;margin:2px 0 18px;flex-wrap:wrap}
.check{display:flex;gap:9px;align-items:flex-start;font-size:13.5px;color:#3c4743;font-weight:500;cursor:pointer}
.check input{width:17px;height:17px;margin-top:2px;accent-color:var(--accent);flex:none}
.check a{color:var(--accent);font-weight:600}
.link{background:none;border:0;color:var(--accent);font-weight:600;font-size:13.5px;cursor:pointer;padding:0;font-family:inherit}
.link:hover{text-decoration:underline}
.meter{display:grid;grid-template-columns:repeat(4,1fr);gap:5px;margin-top:8px}
.meter i{height:5px;border-radius:20px;background:var(--line);transition:background .2s ease}
.meter.s1 i:nth-child(-n+1){background:#D92D20}
.meter.s2 i:nth-child(-n+2){background:#E8A33D}
.meter.s3 i:nth-child(-n+3){background:#7BA05B}
.meter.s4 i:nth-child(-n+4){background:var(--accent)}
.hint{color:var(--muted);font-size:12.5px}
.auth-form .btn[type=submit]{margin-top:4px}
.auth-alt{text-align:center;color:var(--muted);font-size:13.5px;margin-top:16px}
.auth-success{text-align:center;padding:10px 0 4px}
.ok-ic{width:56px;height:56px;border-radius:50%;background:var(--soft);color:var(--accent);display:grid;place-items:center;margin:0 auto 16px}
.ok-ic svg{width:26px;height:26px}
.auth-success h2{font-size:21px;margin-bottom:6px}
.auth-success p{color:var(--muted);font-size:14px;margin-bottom:20px}
.auth-success .note{font-size:12.5px;margin-top:12px;margin-bottom:0}

/* ---------- Reveal animation ---------- */
.rev{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease}
.rev.in{opacity:1;transform:none}

/* ---------- Responsive ---------- */
@media (max-width:1000px){
  .hero-grid{grid-template-columns:1fr;gap:48px}
  .hero-copy{align-items:center;text-align:center}
  .hero .lead{margin-inline:auto}
  .hero-cta,.hero-stats,.hero-note{justify-content:center}
  .preview{max-width:560px;margin-inline:auto;width:100%}
  .fgrid,.tgrid,.pgrid,.steps{grid-template-columns:1fr 1fr}
  .feat.wide{grid-column:span 2}
  .ft-grid{grid-template-columns:1fr 1fr;gap:32px}
  .ft-brand{grid-column:1/-1}
  .cta-band{padding:48px 32px}
}
@media (max-width:820px){
  .hd-nav{display:none}
  .hd-cta .btn.ghost{display:none}
  .burger{display:grid}
  .mmenu{position:fixed;inset:0;background:rgba(10,18,15,.45);z-index:60;display:none}
  .mmenu.open{display:block}
  .mmenu-in{position:absolute;top:0;right:0;bottom:0;width:min(88vw,340px);background:var(--surface);padding:22px;display:flex;flex-direction:column;gap:6px;box-shadow:-20px 0 60px rgba(10,30,24,.2);animation:slideIn .25s ease}
  @keyframes slideIn{from{transform:translateX(100%)}to{transform:translateX(0)}}
  .mmenu-head{display:flex;justify-content:space-between;align-items:center;padding-bottom:16px;border-bottom:1px solid var(--line);margin-bottom:10px}
  .mmenu-in a{padding:14px 12px;border-radius:11px;font-weight:600;font-size:16px}
  .mmenu-in a:hover{background:var(--soft);color:var(--accent)}
  .mmenu-in .btn{margin-top:10px}
  .section{padding:72px 0}
  .section.tight{padding:56px 0}
  .fgrid,.tgrid,.pgrid,.steps{grid-template-columns:1fr}
  .feat.wide{grid-column:span 1;flex-direction:column;align-items:flex-start;gap:16px}
}
@media (max-width:560px){
  .wrap{width:calc(100% - 32px)}
  .hero{padding:44px 0 24px}
  .hero-stats{gap:22px}
  .hero-stats div b{font-size:21px}
  .pv-float{display:none}
  .preview-card{transform:none}
  .pv-kpis{grid-template-columns:1fr 1fr}
  .pv-kpis .pv-kpi:nth-child(3){display:none}
  .cta-band{padding:40px 24px;border-radius:18px}
  .cta-band .btns{width:100%}
  .cta-band .btns .btn{flex:1}
  .ft-grid{grid-template-columns:1fr;gap:28px}
  .ft-bot{flex-direction:column;align-items:flex-start;gap:12px}
  .plan{padding:24px}
  .auth{padding:0;align-items:end;justify-items:stretch}
  .auth-card{width:100%;max-width:none;max-height:94vh;border-radius:22px 22px 0 0;padding:24px 20px calc(24px + env(safe-area-inset-bottom))}
  .auth-brand{margin-bottom:14px}
  .auth-form h2{font-size:21px}
}
@media (prefers-reduced-motion:reduce){
  html{scroll-behavior:auto}
  *,*::before,*::after{animation:none!important;transition:none!important}
  .rev{opacity:1;transform:none}
  .auth,.auth-card{transition:none}
}
</style>
</head>
<body>

<!-- ============ Header ============ -->
<header class="hd" id="hd">
  <div class="wrap hd-in">
    <a href="#" class="brand"><span class="mark">L</span>Ledgerly</a>
    <nav class="hd-nav" aria-label="Primary">
      <?php foreach ($navLinks as $link): ?>
        <a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="hd-cta">
      <a href="<?= e($signInUrl) ?>" class="btn ghost" data-auth="in">Sign in</a>
      <a href="<?= e($signUpUrl) ?>" class="btn pri" data-auth="up">Start free
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
      <button class="burger" id="burger" aria-label="Open menu" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<!-- Mobile menu -->
<div class="mmenu" id="mmenu" aria-hidden="true">
  <div class="mmenu-in">
    <div class="mmenu-head">
      <span class="brand" style="font-size:18px"><span class="mark" style="width:30px;height:30px;font-size:14px">L</span>Ledgerly</span>
      <button class="burger" id="mclose" aria-label="Close menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <?php foreach ($navLinks as $link): ?>
      <a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
    <?php endforeach; ?>
    <a href="<?= e($signInUrl) ?>" class="btn block" data-auth="in">Sign in</a>
    <a href="<?= e($signUpUrl) ?>" class="btn pri block" data-auth="up">Start free</a>
  </div>
</div>

<!-- ============ Hero ============ -->
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
        Built for small teams
      </span>
      <h1>Invoicing that <em>gets you paid</em>, without the busywork.</h1>
      <p class="lead">Ledgerly keeps every invoice, client and payment in one calm workspace. Send a professional invoice in under a minute, then let automatic reminders do the chasing.</p>
      <div class="hero-cta">
        <a href="<?= e($signUpUrl) ?>" class="btn pri lg" data-auth="up">Start free — no card needed
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <a href="#how" class="btn lg ghost">See how it works</a>
      </div>
      <p class="hero-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
        14-day free trial · Cancel anytime · No setup fees
      </p>
      <div class="hero-stats">
        <div><b>12,400+</b><span>invoices sent monthly</span></div>
        <div><b>9 days</b><span>faster average payment</span></div>
        <div><b>4.9/5</b><span>from 380 reviews</span></div>
      </div>
    </div>

    <!-- Product preview -->
    <div class="preview">
      <div class="preview-card" aria-hidden="true">
        <div class="pv-top">
          <span class="pv-dot"></span><span class="pv-dot"></span><span class="pv-dot"></span>
          <span class="pv-url">app.ledgerly.io/invoices</span>
        </div>
        <div class="pv-body">
          <div class="pv-head">
            <h4>Invoices</h4>
            <span>This month</span>
          </div>
          <div class="pv-kpis">
            <div class="pv-kpi hero"><small>Collected</small><b>$24,120</b></div>
            <div class="pv-kpi"><small>Awaiting</small><b>$8,450</b></div>
            <div class="pv-kpi"><small>Overdue</small><b>$1,240</b></div>
          </div>
          <div class="pv-table">
            <div class="pv-row">
              <div><b>Northwind Logistics</b><small>INV-1048 · due 12 Mar</small></div>
              <span class="pv-pill overdue">Overdue</span>
              <span class="pv-amt">$8,450</span>
            </div>
            <div class="pv-row">
              <div><b>Kuda Studios</b><small>INV-1049 · due 28 Mar</small></div>
              <span class="pv-pill pending">Pending</span>
              <span class="pv-amt">$2,380</span>
            </div>
            <div class="pv-row">
              <div><b>Baobab Capital</b><small>INV-1050 · due 02 Apr</small></div>
              <span class="pv-pill paid">Paid</span>
              <span class="pv-amt">$15,200</span>
            </div>
            <div class="pv-row">
              <div><b>Meridian Health</b><small>INV-1051 · due 19 Mar</small></div>
              <span class="pv-pill paid">Paid</span>
              <span class="pv-amt">$4,975</span>
            </div>
          </div>
        </div>
      </div>

      <div class="pv-float f1">
        <div class="ic">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <div><b>Payment received</b><small>Baobab Capital · $15,200</small></div>
      </div>
      <div class="pv-float f2">
        <div class="ic">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/></svg>
        </div>
        <div><b>Reminder scheduled</b><small>Goes out in 3 days</small></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ Logos ============ -->
<section class="logos">
  <div class="wrap">
    <p>Trusted by finance and ops teams at</p>
    <div class="logo-row">
      <?php foreach ($logos as $logo): ?>
        <span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $logo['icon'] ?></svg><?= e($logo['name']) ?>
        </span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ Features ============ -->
<section class="section" id="features">
  <div class="wrap">
    <div class="sec-head center rev">
      <span class="eyebrow">Everything you need</span>
      <h2>One place for the whole billing cycle</h2>
      <p class="lead">From the first invoice to the last payment, Ledgerly keeps the money side of your business organised — without spreadsheets.</p>
    </div>

    <div class="fgrid">
      <?php foreach ($features as $f): ?>
        <article class="feat rev<?= $f['wide'] ? ' wide' : '' ?>">
          <div class="fic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $f['icon'] ?></svg>
          </div>
          <?php if ($f['wide']): ?>
            <div>
              <h3><?= e($f['title']) ?></h3>
              <p style="color:var(--muted);font-size:14.5px;margin-top:6px"><?= e($f['desc']) ?></p>
            </div>
          <?php else: ?>
            <h3><?= e($f['title']) ?></h3>
            <p><?= e($f['desc']) ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ How it works ============ -->
<section class="section tight" id="how" style="background:var(--surface);border-block:1px solid var(--line)">
  <div class="wrap">
    <div class="sec-head center rev">
      <span class="eyebrow">How it works</span>
      <h2>Three steps from draft to paid</h2>
      <p class="lead">No onboarding calls, no configuration maze. Sign up and send your first invoice the same afternoon.</p>
    </div>
    <div class="steps">
      <article class="step rev">
        <h3>Add your clients</h3>
        <p>Import or type in the people and businesses you bill. Store their email and billing details once, reuse forever.</p>
      </article>
      <article class="step rev">
        <h3>Send an invoice</h3>
        <p>Pick a client, add line items, set a due date. Your branded PDF is generated and emailed in seconds.</p>
      </article>
      <article class="step rev">
        <h3>Get paid</h3>
        <p>Track status live, record payments as they land, and let reminders handle anyone who runs late.</p>
      </article>
    </div>
  </div>
</section>

<!-- ============ Pricing ============ -->
<section class="section pricing" id="pricing">
  <div class="wrap">
    <div class="sec-head center rev">
      <span class="eyebrow">Pricing</span>
      <h2>Simple plans, no surprises</h2>
      <p class="lead">Start free. Upgrade when your team grows. Every plan includes unlimited clients and no per-invoice fees.</p>
      <div class="toggle" role="group" aria-label="Billing period">
        <button id="bmo" class="on" aria-pressed="true">Monthly</button>
        <button id="byr" aria-pressed="false">Yearly <span class="save">Save 20%</span></button>
      </div>
    </div>

    <div class="pgrid">
      <?php foreach ($plans as $plan): ?>
        <article class="plan rev<?= $plan['popular'] ? ' pop' : '' ?>">
          <?php if ($plan['popular']): ?><span class="tag">Most popular</span><?php endif; ?>
          <h3><?= e($plan['name']) ?></h3>
          <p class="desc"><?= e($plan['desc']) ?></p>
          <div class="price">
            <b data-m="<?= e($plan['price_m']) ?>" data-y="<?= e($plan['price_y']) ?>"><?= e($plan['price_m']) ?></b>
            <span><?= e($plan['unit']) ?></span>
            <small data-m="<?= e($plan['note_m']) ?>" data-y="<?= e($plan['note_y']) ?>"><?= e($plan['note_m']) ?></small>
          </div>
          <ul>
            <?php foreach ($plan['features'] as $f): $off = !empty($f['disabled']); ?>
              <li<?= $off ? ' class="mut"' : '' ?>>
                <?php if ($off): ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 6l12 12M18 6L6 18"/></svg>
                <?php else: ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                <?php endif; ?>
                <?= e($f['text']) ?>
              </li>
            <?php endforeach; ?>
          </ul>
          <a href="<?= e($plan['cta_href']) ?>" class="btn<?= $plan['primary'] ? ' pri' : '' ?> block"<?= !empty($plan['auth']) ? ' data-auth="' . e($plan['auth']) . '"' : '' ?>><?= e($plan['cta']) ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ Testimonials ============ -->
<section class="section" id="stories">
  <div class="wrap">
    <div class="sec-head center rev">
      <span class="eyebrow">Customer stories</span>
      <h2>Teams that stopped chasing payments</h2>
      <p class="lead">Real feedback from small businesses using Ledgerly every week.</p>
    </div>
    <div class="tgrid">
      <?php foreach ($testimonials as $t): ?>
        <figure class="quote rev">
          <div class="stars" aria-label="5 out of 5 stars">
            <?php for ($i = 0; $i < 5; $i++): ?>
              <svg viewBox="0 0 24 24"><path d="M12 2l3 6.5 7 .9-5 4.9 1.3 7L12 18l-6.3 3.3L7 14.3 2 9.4l7-.9z"/></svg>
            <?php endfor; ?>
          </div>
          <p>&ldquo;<?= e($t['quote']) ?>&rdquo;</p>
          <footer>
            <div class="av"><?= e($t['initials']) ?></div>
            <div><b><?= e($t['name']) ?></b><small><?= e($t['role']) ?></small></div>
          </footer>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="section tight" id="faq" style="background:var(--surface);border-block:1px solid var(--line)">
  <div class="wrap">
    <div class="sec-head center rev">
      <span class="eyebrow">Questions</span>
      <h2>Frequently asked</h2>
      <p class="lead">Everything you need to know before you start.</p>
    </div>
    <div class="faq">
      <?php foreach ($faqs as $i => $q): ?>
        <details class="qa"<?= $i === 0 ? ' open' : '' ?>>
          <summary><?= e($q['q']) ?> <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M6 9l6 6 6-6"/></svg></span></summary>
          <p class="ans"><?= e($q['a']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ CTA ============ -->
<section class="section" id="cta">
  <div class="wrap">
    <div class="cta-band rev">
      <div>
        <h2>Start sending invoices that get paid on time.</h2>
        <p>Join thousands of small teams using Ledgerly to stay on top of their billing. Free to start — no card required.</p>
      </div>
      <div class="btns">
        <a href="<?= e($signUpUrl) ?>" class="btn lg" data-auth="up">Create free account
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <a href="#pricing" class="btn lg ghost">Compare plans</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ Footer ============ -->
<footer class="ft">
  <div class="wrap">
    <div class="ft-grid">
      <div class="ft-brand">
        <a href="#" class="brand"><span class="mark">L</span>Ledgerly</a>
        <p>The calm invoicing workspace for small teams. Send invoices, track payments, get paid faster.</p>
        <div class="ft-social">
          <a href="#" aria-label="Twitter"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4 4 0 00-6.9 3.7A11.4 11.4 0 013 4.9a4 4 0 001.2 5.4c-.6 0-1.2-.2-1.8-.5v.1a4 4 0 003.2 3.9c-.5.1-1.1.2-1.7.1a4 4 0 003.7 2.8A8 8 0 012 18.6a11.3 11.3 0 006.1 1.8c7.4 0 11.4-6.1 11.4-11.4v-.5c.8-.6 1.5-1.3 2-2.1z"/></svg></a>
          <a href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5A2.5 2.5 0 002.5 6a2.5 2.5 0 005 0 2.5 2.5 0 00-2.52-2.5zM3 9h4v12H3zM10 9h3.8v1.7h.1c.5-1 1.8-2 3.7-2 4 0 4.7 2.6 4.7 6V21h-4v-5.5c0-1.3 0-3-1.9-3s-2.1 1.4-2.1 2.9V21h-4z"/></svg></a>
          <a href="#" aria-label="GitHub"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-3.2 19.5c.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.3-3.4-1.3-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.5 2.3 1.1 2.9.8.1-.6.3-1.1.6-1.3-2.2-.3-4.6-1.1-4.6-4.9 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.4 9.4 0 015 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.8-2.4 4.6-4.6 4.9.3.3.7 1 .7 2v2.9c0 .3.2.6.7.5A10 10 0 0012 2z"/></svg></a>
        </div>
      </div>
      <?php foreach ($footerGroups as $title => $links): ?>
        <div>
          <h4><?= e($title) ?></h4>
          <ul>
            <?php foreach ($links as $link): ?>
              <li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="ft-bot">
      <span>&copy; <?= e((string) $year) ?> Ledgerly. All rights reserved.</span>
      <div class="links">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Security</a>
        <a href="#">Cookies</a>
      </div>
    </div>
  </div>
</footer>

<!-- ============ Auth modal ============ -->
<div class="auth" id="auth" hidden aria-hidden="true">
  <div class="auth-back" data-auth-close></div>

  <div class="auth-card" role="dialog" aria-modal="true" aria-label="Sign in or create an account">
    <button class="auth-x" type="button" data-auth-close aria-label="Close">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    <div class="auth-brand"><span class="mark">L</span>Ledgerly</div>

    <div class="auth-tabs" role="tablist" aria-label="Sign in or create account">
      <button type="button" role="tab" id="tab-in" data-auth-tab="in" aria-selected="true" aria-controls="form-in">Sign in</button>
      <button type="button" role="tab" id="tab-up" data-auth-tab="up" aria-selected="false" aria-controls="form-up">Create account</button>
    </div>
    <div class="auth-message" id="auth-message" role="alert" hidden></div>

    <!-- Sign in -->
    <form id="form-in" class="auth-form" action="<?= e($loginEndpoint) ?>" method="post"<?= $loginDemo ? ' data-demo="1"' : '' ?> novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($authCsrf) ?>">
      <h2>Welcome back</h2>
      <p class="auth-sub">Sign in to keep managing your invoices.</p>

      <div class="field">
        <label for="in-email">Email address</label>
        <input id="in-email" name="email" type="email" inputmode="email" autocomplete="email" placeholder="you@company.com" required>
        <span class="err" data-err-for="in-email"></span>
      </div>

      <div class="field">
        <label for="in-pass">Password</label>
        <div class="pw">
          <input id="in-pass" name="password" type="password" autocomplete="current-password" placeholder="Your password" required>
          <button type="button" class="pw-t" data-pw="in-pass" aria-label="Show password" aria-pressed="false">
            <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3l18 18"/><path d="M10.6 6.1A9.7 9.7 0 0112 6c6.4 0 10 6 10 6a17 17 0 01-3.3 3.9M6.7 6.7A17 17 0 002 12s3.6 6 10 6a9.8 9.8 0 004.1-.9"/><path d="M9.9 9.9a3 3 0 004.2 4.2"/></svg>
          </button>
        </div>
        <span class="err" data-err-for="in-pass"></span>
      </div>

      <div class="auth-row">
        <label class="check"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
        <button type="button" class="link" id="forgot">Forgot password?</button>
      </div>
      <p class="hint" id="forgot-note" hidden></p>

      <button class="btn pri block" type="submit">Sign in</button>
      <p class="auth-alt">New to Ledgerly? <button type="button" class="link" data-auth-tab="up">Create an account</button></p>
    </form>

    <!-- Sign up -->
    <form id="form-up" class="auth-form" action="<?= e($signupEndpoint) ?>" method="post"<?= $signupDemo ? ' data-demo="1"' : '' ?> novalidate hidden>
      <input type="hidden" name="csrf_token" value="<?= e($authCsrf) ?>">
      <h2>Create your account</h2>
      <p class="auth-sub">Free forever. No credit card required.</p>

      <div class="field">
        <label for="up-name">Full name</label>
        <input id="up-name" name="name" autocomplete="name" placeholder="Amara Moyo" required>
        <span class="err" data-err-for="up-name"></span>
      </div>

      <div class="field">
        <label for="up-biz">Business name <span class="opt">(optional)</span></label>
        <input id="up-biz" name="business" autocomplete="organization" placeholder="Northwind Logistics">
      </div>

      <div class="field">
        <label for="up-email">Work email</label>
        <input id="up-email" name="email" type="email" inputmode="email" autocomplete="email" placeholder="you@company.com" required>
        <span class="err" data-err-for="up-email"></span>
      </div>

      <div class="field">
        <label for="up-pass">Password</label>
        <div class="pw">
          <input id="up-pass" name="password" type="password" autocomplete="new-password" placeholder="At least 8 characters" required>
          <button type="button" class="pw-t" data-pw="up-pass" aria-label="Show password" aria-pressed="false">
            <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3l18 18"/><path d="M10.6 6.1A9.7 9.7 0 0112 6c6.4 0 10 6 10 6a17 17 0 01-3.3 3.9M6.7 6.7A17 17 0 002 12s3.6 6 10 6a9.8 9.8 0 004.1-.9"/><path d="M9.9 9.9a3 3 0 004.2 4.2"/></svg>
          </button>
        </div>
        <div class="meter" id="pw-meter" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
        <span class="hint">Use 8+ characters with a letter and a number.</span>
        <span class="err" data-err-for="up-pass"></span>
      </div>

      <label class="check" for="up-terms">
        <input type="checkbox" id="up-terms" name="terms" required>
        <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
      </label>
      <span class="err" data-err-for="up-terms"></span>

      <button class="btn pri block" type="submit" style="margin-top:16px">Create account</button>
      <p class="auth-alt">Already have an account? <button type="button" class="link" data-auth-tab="in">Sign in</button></p>
    </form>

    <!-- Success states -->
    <div class="auth-success" id="ok-in" hidden>
      <div class="ok-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></div>
      <h2>You're signed in</h2>
      <p id="ok-in-msg">Welcome back.</p>
      <a class="btn pri block" href="<?= e($appUrl) ?>" data-auth-close>Continue to dashboard</a>
      <p class="note sub">Demo mode — point <code>$loginEndpoint</code> at your PHP handler to sign in for real.</p>
    </div>

    <div class="auth-success" id="ok-up" hidden>
      <div class="ok-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></div>
      <h2>Account created</h2>
      <p id="ok-up-msg">Your account is ready. Sign in to continue.</p>
      <button class="btn pri block" type="button" data-auth-tab="in">Sign in</button>
    </div>
  </div>
</div>

<script>
/* Header shadow on scroll */
const hd = document.getElementById('hd');
const onScroll = () => hd.classList.toggle('scrolled', window.scrollY > 8);
window.addEventListener('scroll', onScroll, {passive:true});
onScroll();

/* Mobile menu */
const mmenu = document.getElementById('mmenu');
const burger = document.getElementById('burger');
const mclose = document.getElementById('mclose');
const openMenu = () => { mmenu.classList.add('open'); mmenu.setAttribute('aria-hidden','false'); burger.setAttribute('aria-expanded','true'); };
const closeMenu = () => { mmenu.classList.remove('open'); mmenu.setAttribute('aria-hidden','true'); burger.setAttribute('aria-expanded','false'); };
burger.addEventListener('click', openMenu);
mclose.addEventListener('click', closeMenu);
mmenu.addEventListener('click', e => { if(e.target === mmenu) closeMenu(); });
mmenu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeMenu(); });

/* Pricing toggle */
const bmo = document.getElementById('bmo');
const byr = document.getElementById('byr');
function setBilling(period){
  const monthly = period === 'm';
  bmo.classList.toggle('on', monthly);
  byr.classList.toggle('on', !monthly);
  bmo.setAttribute('aria-pressed', String(monthly));
  byr.setAttribute('aria-pressed', String(!monthly));
  document.querySelectorAll('.plan .price b').forEach(el => {
    el.textContent = monthly ? el.dataset.m : el.dataset.y;
  });
  document.querySelectorAll('.plan .price small').forEach(el => {
    el.textContent = monthly ? el.dataset.m : el.dataset.y;
  });
}
bmo.addEventListener('click', () => setBilling('m'));
byr.addEventListener('click', () => setBilling('y'));

/* FAQ accordion */
document.querySelectorAll('.qa').forEach(d => {
  d.addEventListener('toggle', () => {
    if(d.open){
      document.querySelectorAll('.qa').forEach(o => { if(o !== d) o.open = false; });
      d.classList.add('open');
    } else {
      d.classList.remove('open');
    }
  });
});

/* Reveal on scroll */
const revEls = document.querySelectorAll('.rev');
if('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches){
  const io = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if(en.isIntersecting){ en.target.classList.add('in'); io.unobserve(en.target); }
    });
  }, {threshold:.12, rootMargin:'0px 0px -40px 0px'});
  revEls.forEach(el => io.observe(el));
} else {
  revEls.forEach(el => el.classList.add('in'));
}

/* ---------- Auth modal ---------- */
(function(){
  const auth   = document.getElementById('auth');
  const card   = auth.querySelector('.auth-card');
  const tabs   = Array.from(auth.querySelectorAll('[data-auth-tab]'));
  const formIn = document.getElementById('form-in');
  const formUp = document.getElementById('form-up');
  const okIn   = document.getElementById('ok-in');
  const okUp   = document.getElementById('ok-up');
  const meter  = document.getElementById('pw-meter');
  const forgotNote = document.getElementById('forgot-note');
  const authMessage = document.getElementById('auth-message');
  const authFlash = <?= json_encode($authFlash, $jsonFlags) ?>;
  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  let lastFocus = null;

  /* helpers */
  const byId = id => document.getElementById(id);

  function setErr(id, msg){
    const input = byId(id);
    const err = document.querySelector('[data-err-for="' + id + '"]');
    if(input) input.setAttribute('aria-invalid','true');
    if(err){ err.textContent = msg; err.classList.add('show'); }
  }
  function clearErr(id){
    const input = byId(id);
    const err = document.querySelector('[data-err-for="' + id + '"]');
    if(input) input.removeAttribute('aria-invalid');
    if(err){ err.textContent = ''; err.classList.remove('show'); }
  }
  function clearAllErrs(){
    document.querySelectorAll('.err.show').forEach(x => { x.classList.remove('show'); x.textContent = ''; });
    document.querySelectorAll('[aria-invalid]').forEach(x => x.removeAttribute('aria-invalid'));
  }

  function setMode(mode){
    auth.classList.remove('done');
    authMessage.hidden = true;
    authMessage.textContent = '';
    okIn.hidden = true;
    okUp.hidden = true;
    formIn.hidden = mode !== 'in';
    formUp.hidden = mode !== 'up';
    tabs.forEach(t => t.setAttribute('aria-selected', String(t.dataset.authTab === mode)));
    clearAllErrs();
    const first = auth.querySelector('form:not([hidden]) input');
    if(first && auth.classList.contains('open')) setTimeout(() => first.focus(), 0);
  }

  function resetAuth(){
    auth.classList.remove('done');
    formIn.reset();
    formUp.reset();
    formIn.hidden = false;
    formUp.hidden = true;
    okIn.hidden = true;
    okUp.hidden = true;
    forgotNote.hidden = true;
    forgotNote.textContent = '';
    meter.className = 'meter';
    clearAllErrs();
    document.querySelectorAll('.pw-t').forEach(b => {
      b.classList.remove('on');
      b.setAttribute('aria-pressed','false');
      b.setAttribute('aria-label','Show password');
      const input = byId(b.dataset.pw);
      if(input) input.type = 'password';
    });
    tabs.forEach(t => t.setAttribute('aria-selected', String(t.dataset.authTab === 'in')));
  }

  function openAuth(mode){
    lastFocus = document.activeElement;
    resetAuth();
    auth.hidden = false;
    auth.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
    setMode(mode === 'up' ? 'up' : 'in');
    requestAnimationFrame(() => auth.classList.add('open'));
    setTimeout(() => {
      const first = auth.querySelector('form:not([hidden]) input');
      if(first) first.focus();
    }, 90);
  }

  function closeAuth(){
    auth.classList.remove('open');
    auth.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
    setTimeout(() => { auth.hidden = true; resetAuth(); }, 200);
    if(lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
  }

  function showSuccess(mode, email){
    auth.classList.add('done');
    formIn.hidden = true;
    formUp.hidden = true;
    clearAllErrs();
    if(mode === 'in'){
      byId('ok-in-msg').textContent = 'Signed in as ' + email + '.';
      okIn.hidden = false;
      setTimeout(() => { const b = okIn.querySelector('button,a'); if(b) b.focus(); }, 0);
    } else {
      byId('ok-up-msg').textContent = "Your account is ready. Sign in to continue.";
      okUp.hidden = false;
      setTimeout(() => { const b = okUp.querySelector('button,a'); if(b) b.focus(); }, 0);
    }
  }

  /* open / close / tabs */
  document.addEventListener('click', e => {
    const trigger = e.target.closest('[data-auth]');
    if(trigger){ e.preventDefault(); openAuth(trigger.dataset.auth || 'in'); return; }
    if(e.target.closest('[data-auth-close]')){ e.preventDefault(); closeAuth(); return; }
  });

  tabs.forEach(t => t.addEventListener('click', () => setMode(t.dataset.authTab)));

  if(authFlash && typeof authFlash.message === 'string'){
    const mode = authFlash.form === 'up' ? 'up' : 'in';
    openAuth(mode);
    if(authFlash.type === 'success'){
      showSuccess('up', '');
      byId('ok-up-msg').textContent = authFlash.message;
    } else {
      authMessage.textContent = authFlash.message;
      authMessage.className = 'auth-message error';
      authMessage.hidden = false;
    }
  } else {
    const query = new URLSearchParams(window.location.search);
    if(query.get('auth') === 'login' || query.get('auth') === 'register'){
      openAuth(query.get('auth') === 'register' ? 'up' : 'in');
    }
  }

  /* focus trap + escape */
  auth.addEventListener('keydown', e => {
    if(e.key === 'Escape'){ e.preventDefault(); closeAuth(); return; }
    if(e.key !== 'Tab') return;
    const items = Array.from(card.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select,textarea,[tabindex]:not([tabindex="-1"])'))
      .filter(el => el.offsetParent !== null);
    if(!items.length) return;
    const first = items[0], last = items[items.length - 1];
    if(e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
    else if(!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
  });

  /* password visibility */
  document.querySelectorAll('.pw-t').forEach(b => {
    b.addEventListener('click', () => {
      const input = byId(b.dataset.pw);
      if(!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      b.classList.toggle('on', show);
      b.setAttribute('aria-pressed', String(show));
      b.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });

  /* live clear + strength */
  ['in-email','in-pass','up-name','up-email','up-pass'].forEach(id => {
    const el = byId(id);
    if(el) el.addEventListener('input', () => clearErr(id));
  });
  byId('up-terms').addEventListener('change', () => clearErr('up-terms'));
  byId('up-pass').addEventListener('input', () => {
    const v = byId('up-pass').value;
    let s = 0;
    if(v.length >= 8) s++;
    if(/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
    if(/\d/.test(v)) s++;
    if(/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
    meter.className = 'meter' + (v ? ' s' + Math.max(1, s) : '');
  });

  /* forgot password */
  byId('forgot').addEventListener('click', () => {
    const email = byId('in-email');
    const val = email.value.trim();
    if(!emailRe.test(val)){
      setErr('in-email', 'Enter your email first, then tap “Forgot password”.');
      email.focus();
      return;
    }
    clearErr('in-email');
    forgotNote.hidden = false;
    forgotNote.textContent = 'If an account exists for ' + val + ', a reset link is on its way.';
  });

  /* sign in submit */
  formIn.addEventListener('submit', e => {
    e.preventDefault();
    const email = byId('in-email');
    const pass = byId('in-pass');
    clearAllErrs();
    let ok = true;
    if(!emailRe.test(email.value.trim())){ setErr('in-email', 'Enter a valid email address.'); ok = false; }
    if(!pass.value){ setErr('in-pass', 'Enter your password.'); ok = false; }
    if(!ok) return;
    if(formIn.dataset.demo === '1'){
      showSuccess('in', email.value.trim());
    } else {
      formIn.submit();
    }
  });

  /* sign up submit */
  formUp.addEventListener('submit', e => {
    e.preventDefault();
    const name = byId('up-name');
    const email = byId('up-email');
    const pass = byId('up-pass');
    const terms = byId('up-terms');
    clearAllErrs();
    let ok = true;
    if(name.value.trim().length < 2){ setErr('up-name', 'Enter your full name.'); ok = false; }
    if(!emailRe.test(email.value.trim())){ setErr('up-email', 'Enter a valid email address.'); ok = false; }
    if(pass.value.length < 8 || !/[A-Za-z]/.test(pass.value) || !/\d/.test(pass.value)){
      setErr('up-pass', 'Use at least 8 characters with a letter and a number.'); ok = false;
    }
    if(!terms.checked){ setErr('up-terms', 'Please accept the terms to continue.'); ok = false; }
    if(!ok) return;
    if(formUp.dataset.demo === '1'){
      showSuccess('up', email.value.trim());
    } else {
      formUp.submit();
    }
  });
})();
</script>
</body>
</html>