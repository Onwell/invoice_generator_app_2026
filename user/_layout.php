<?php
require_once __DIR__ . '/_bootstrap.php';
if (!isset($page) || !isset($pages[$page]) || !isset($content)) {
    http_response_code(500);
    exit('Layout requires $page and $content.');
}
[$title, $sub] = $pages[$page];
$search = $search ?? '';
$invoiceCsrfToken = auth_csrf_token();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e('Ledgerly – ' . $title) ?></title>
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

/* Chart (revenue) */
.chart{display:flex;align-items:flex-end;gap:14px;height:200px;padding:30px 22px 14px}
.col{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end}
.bar{width:100%;max-width:46px;background:var(--soft);border-radius:8px 8px 4px 4px;position:relative}
.bar.cur,.col:hover .bar{background:var(--accent)}
.col em{font-style:normal;color:var(--muted);font-size:12px}
.tip{position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:var(--ink);color:#fff;padding:3px 8px;border-radius:6px;font-size:12px;white-space:nowrap;opacity:0;pointer-events:none}
.col:hover .tip{opacity:1}

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

.hero-card{padding:22px;display:flex;justify-content:space-between;gap:18px;align-items:center;flex-wrap:wrap;margin-bottom:16px}
.hero-l{display:flex;gap:14px;align-items:center;min-width:0}
.hero-l h2{font-size:20px}
.hero-l p{color:var(--muted);margin-top:2px}
.hero-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;align-items:center}
.hero-r{display:flex;gap:8px;flex-wrap:wrap}
.av.big{width:56px;height:56px;font-size:18px;background:var(--soft);color:var(--accent);font-weight:700}

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

.cgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.cc{padding:22px;display:flex;flex-direction:column;gap:14px}
.cc .t{display:flex;gap:12px;align-items:center}
.cc .av{background:var(--soft);color:var(--accent);width:42px;height:42px}
.cc dl{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px 0;border-block:1px solid var(--line)}
.cc dt{color:var(--muted);font-size:12.5px}.cc dd{font-weight:600;font-size:16px}
.cc .row{display:flex;gap:8px}.cc .row .btn{flex:1}

.set{width:100%;max-width:none}
.set .sec{padding:24px}
.set .f{margin-top:16px}
.set .sec+.sec{margin-top:0}
.account-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:start}
.account-grid .sec{min-width:0}
.account-features{grid-column:1/-1}
.account-payment .f{grid-template-columns:repeat(2,minmax(0,1fr))}
@media (max-width:820px){
  .account-grid{grid-template-columns:1fr}
  .account-features{grid-column:auto}
}

dialog{border:0;border-radius:18px;padding:0;width:min(640px,94vw);max-height:92vh;background:var(--surface);color:var(--ink);box-shadow:0 30px 80px rgba(0,0,0,.28)}
dialog::backdrop{background:rgba(10,18,15,.5)}
dialog[open]{animation:pop .18s ease-out}
@keyframes pop{from{opacity:0;transform:translateY(8px)}}
.m{padding:26px 28px}
.invoice-dialog{width:min(1180px,96vw);max-height:94vh;overflow:auto;margin:auto}
.invoice-editor-layout{display:grid;grid-template-columns:minmax(300px,.82fr) minmax(0,1.18fr);gap:22px;align-items:start}
.invoice-editor-fields{min-width:0}
.invoice-preview-stage{min-width:0;padding:16px;background:#eceeef;border-radius:14px}
.invoice-preview-label{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin:0 0 10px}
.invoice-paper{max-width:720px;margin:0 auto;background:#fff;color:#17191b;box-shadow:0 8px 24px rgba(20,25,30,.12);font-size:12px;line-height:1.45;overflow:hidden}
.invoice-cover{display:grid;grid-template-columns:1fr .85fr;gap:28px;padding:30px 34px;color:#fff;background:linear-gradient(112deg,#0E5A47 0%,#146B55 56%,#0B493A 100%)}
.invoice-cover-brand{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:700}
.invoice-cover-mark{display:grid;place-items:center;width:31px;height:31px;border:1px solid rgba(255,255,255,.7);border-radius:9px;font-size:17px}
.invoice-paper .invoice-cover h2{font-size:30px;letter-spacing:.02em;margin:25px 0 0}
.invoice-cover-id{font-weight:700;font-size:13px}
.invoice-cover-byline{margin-top:24px;color:rgba(255,255,255,.88)}
.invoice-cover-info{display:grid;align-content:start;gap:12px}
.invoice-cover-info b,.invoice-cover-info small{display:block}
.invoice-cover-info small{font-size:10px;color:rgba(255,255,255,.8);margin-top:2px;overflow-wrap:anywhere}
.invoice-cover-total{font-size:13px;font-weight:700;margin-top:2px}
.invoice-paper-body{padding:0 28px 28px}
.invoice-paper-table-wrap{position:relative;margin-top:-2px;background:#fff;border-radius:18px 18px 0 0;padding:14px 0 0}
.invoice-paper table{table-layout:fixed;width:100%;border-collapse:separate;border-spacing:0;font-size:11px}
.invoice-paper th{padding:10px 8px;background:#e9eaeb;color:#202225;border:0;font-size:10px;font-weight:700}
.invoice-paper th:first-child{border-radius:9px 0 0 9px;width:8%}
.invoice-paper th:nth-child(2){width:42%}
.invoice-paper th:nth-child(3){width:17%}
.invoice-paper th:nth-child(4){width:10%}
.invoice-paper th:last-child{border-radius:0 9px 9px 0;width:23%;text-align:right}
.invoice-paper td{padding:12px 8px;border:0;vertical-align:top;overflow-wrap:anywhere}
.invoice-paper td:first-child{font-weight:700}
.invoice-paper td:last-child{text-align:right;font-weight:600}
.invoice-paper tbody tr:nth-child(even){background:#f0f0f1}
.invoice-paper tbody tr:nth-child(even) td:first-child{border-radius:9px 0 0 9px}
.invoice-paper tbody tr:nth-child(even) td:last-child{border-radius:0 9px 9px 0}
.invoice-paper-item-description{font-weight:700}
.invoice-paper-item-description small{display:block;font-weight:400;color:#65696d}
.invoice-paper-bottom{display:grid;grid-template-columns:1fr minmax(180px,.8fr);gap:24px;padding-top:22px}
.invoice-paper-thanks{font-size:16px;font-weight:700;margin-bottom:8px}
.invoice-paper-contact{color:#52565a;font-size:10px;margin-bottom:15px}
.invoice-paper-terms{font-size:10px;color:#52565a;margin-top:5px;white-space:pre-line}
.invoice-payment-lines{font-size:10px;color:#52565a;margin:5px 0 12px;white-space:pre-line;overflow-wrap:anywhere}
.invoice-paper-totals{display:grid;align-content:start;gap:8px}
.invoice-paper-totals>div{display:flex;justify-content:space-between;gap:10px}
.invoice-paper-total{padding:9px 12px;border-radius:9px;color:#fff;background:linear-gradient(112deg,#0E5A47,#146B55 56%,#0B493A);font-size:14px;font-weight:700}
.invoice-paper-signature{text-align:center;margin-top:14px;font-size:14px;font-weight:700}
.invoice-paper-signature small{display:block;font-size:9px;font-weight:400;color:#65696d}
.invoice-paper-footer{margin-top:18px;padding-top:10px;border-top:1px solid #dfe6e3;text-align:center;color:#66716d;font-size:9px}
.invoice-preview-dialog{width:min(900px,94vw);max-height:92vh;overflow:auto;margin:auto}
.invoice-preview-dialog .sheet{padding:24px}
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
.invoice-preview-dialog .invoice-paper{font-size:13px}
.toast{position:fixed;bottom:calc(24px + env(safe-area-inset-bottom));left:50%;transform:translateX(-50%) translateY(20px);background:var(--ink);color:#fff;padding:11px 18px;border-radius:10px;font-weight:600;opacity:0;transition:.2s;pointer-events:none;z-index:99;max-width:90vw}
.toast.show{opacity:1;transform:translateX(-50%)}
.bnav{display:none}
.mtop{display:none}

.so{position:fixed;inset:0;background:var(--bg);display:grid;place-items:center;z-index:200;padding:24px}
.so-card{padding:36px 32px;max-width:380px;width:100%;text-align:center;display:flex;flex-direction:column;align-items:center;gap:10px}
.so-mark{background:var(--accent);color:#fff;width:44px;height:44px;font-size:19px;border-radius:12px;margin-bottom:6px}
.so-card h2{font-size:20px}
.so-card p{color:var(--muted);margin-bottom:10px}

@media print{
  body *{visibility:hidden!important}
  #vw,#vw *{visibility:visible!important}
  #vw{position:static!important;display:block!important;width:100%!important;max-width:none!important;max-height:none!important;overflow:visible!important;margin:0!important;padding:0!important;box-shadow:none!important;border-radius:0!important}
  #vw::backdrop{display:none}
  #vw .noprint{display:none!important}
  #sheet{padding:0!important}
  .invoice-paper{max-width:none;box-shadow:none;break-inside:avoid}
  .invoice-cover,.invoice-paper-total{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}

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
  .chart{padding:30px 16px 14px;gap:8px}
}
@media (max-width:520px){
  .kpis{grid-template-columns:1fr 1fr;gap:10px}.kpi{padding:16px}.kpi b{font-size:21px}
  .f{grid-template-columns:1fr}.m{padding:22px 18px}
  .account-payment .f{grid-template-columns:1fr}
  .sheet .meta{grid-template-columns:1fr 1fr}
  .tools .btn.pri span{display:none}.tools .btn.pri{width:42px;padding:0}
  dialog{width:100vw;max-width:100vw;margin:auto 0 0;border-radius:18px 18px 0 0;max-height:94vh}
  .li{grid-template-columns:1fr 54px 80px 24px}
  .hero-card{padding:18px}
  .hero-l h2{font-size:18px}
  .av.big{width:48px;height:48px;font-size:16px}
  .hero-r{width:100%}.hero-r .btn{flex:1}
  .invoice-dialog,.invoice-preview-dialog{width:96vw;max-width:720px;max-height:94vh;border-radius:16px;margin:auto}
  .invoice-dialog>.m{padding:20px 16px}
  .invoice-editor-layout{grid-template-columns:1fr}
  .invoice-preview-stage{padding:8px}
  .invoice-cover{padding:20px 16px;gap:14px}
  .invoice-paper .invoice-cover h2{font-size:22px;margin-top:18px}
  .invoice-paper-body{padding:0 14px 18px}
  .invoice-paper-bottom{grid-template-columns:1fr;gap:14px}
  .invoice-paper-table-wrap{overflow-x:auto}
  .invoice-paper table{min-width:430px}
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
    <nav class="nav" aria-label="Main">
      <?php foreach ($nav as $k => $item): ?>
        <a href="<?= e($item['file']) ?>"<?= $k === $page ? ' class="on"' : '' ?>><?= svg_icon($k) ?><span><?= e($item['label']) ?></span></a>
      <?php endforeach; ?>
    </nav>
    <div class="me">
      <div class="av" id="me-av"><?= e(ini($me['n'])) ?></div>
      <div class="who"><b id="me-name"><?= e($me['n']) ?></b><small id="me-role"><?= e($me['role']) ?></small></div>
      <button class="lout" id="logout" title="Log out" aria-label="Log out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3"/><path d="M10 17l-5-5 5-5"/><path d="M5 12h11"/></svg></button>
    </div>
  </aside>
  <main>
    <header class="top">
      <div><h1><?= e($title) ?></h1><p><?= e($sub) ?></p></div>
      <div class="tools">
        <label class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg><input id="q" type="search" placeholder="Search invoices or clients" aria-label="Search" value="<?= e($search) ?>"></label>
        <button class="btn ico" id="bell" aria-label="Notifications"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 17V11a6 6 0 1112 0v6l1.5 2h-15z"/><path d="M10 21h4"/></svg><b class="dot" id="bc"></b></button>
        <button class="btn pri" id="new"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg><span>New invoice</span></button>
        <div class="pop" id="pop" hidden></div>
      </div>
    </header>
    <?= $content ?>
  </main>
</div>
<nav class="bnav" aria-label="Main">
  <?php foreach ($nav as $k => $item): ?>
    <a href="<?= e($item['file']) ?>"<?= $k === $page ? ' class="on"' : '' ?>><?= svg_icon($k) ?><span><?= e($item['label']) ?></span></a>
  <?php endforeach; ?>
</nav>

<dialog id="dlg" class="invoice-dialog"><div class="m">
  <h2 id="invoice-editor-title">Create invoice</h2><p id="invoice-editor-description">Fill in the details. The total updates as you type.</p>
  <div class="invoice-editor-layout">
    <div class="invoice-editor-fields">
      <div class="f"><label>Client<input id="cl" list="cls" placeholder="Choose or type a client"></label><datalist id="cls"></datalist>
      <label>Tax (%)<input id="tax" type="number" min="0"></label>
      <label>Issue date<input id="iss" type="date"></label><label>Due date<input id="due" type="date"></label></div>
      <div id="items"></div>
      <button class="btn add sm" id="addItem" type="button">Add line item</button>
      <div class="tot"><div><span>Subtotal</span><span id="sb">0</span></div><div><span>Tax</span><span id="tx">0</span></div><div class="g"><span>Total</span><span id="gr">0</span></div></div>
      <div class="foot"><button class="btn" id="cancel" type="button">Cancel</button><button class="btn pri" id="save" type="button">Generate invoice</button></div>
    </div>
    <div class="invoice-preview-stage"><p class="invoice-preview-label">Live invoice preview</p><div id="draft-preview"></div></div>
  </div>
</div></dialog>

<dialog id="vw" class="invoice-preview-dialog"><div class="m sheet" id="sheet"></div></dialog>

<dialog id="emaildlg"><form class="m" id="email-form">
  <h2>Email invoice as PDF</h2>
  <p>Where supported, your device's share sheet can send the PDF as an attachment. Otherwise, download the attached email draft and open it with your email app.</p>
  <label>Recipient email<input id="invoice-recipient" type="email" autocomplete="email" required></label>
  <div class="foot"><button class="btn" id="email-cancel" type="button">Cancel</button><button class="btn pri" id="email-send" type="submit">Email PDF</button></div>
</form></dialog>

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
const PAGE   = <?= json_encode($page, $jsonFlags) ?>;
const SEARCH = <?= json_encode($search, $jsonFlags) ?>;
const __INITIAL__ = <?= $defaultJson ?>;
const AUTH_USER = <?= json_encode(['id' => $authUser['id'], 'n' => $authUser['name'], 'e' => $authUser['email'], 'role' => 'User'], $jsonFlags) ?>;
const AUTH_NOTICE = <?= json_encode($_SESSION['dashboard_notice'] ?? '', $jsonFlags) ?>;
const INVOICE_CSRF = <?= json_encode($invoiceCsrfToken, $jsonFlags) ?>;
<?php unset($_SESSION['dashboard_notice']); ?>
const USER_DATA_KEY = 'ledgerly-user-' + AUTH_USER.id;
const DEF = () => JSON.parse(JSON.stringify(__INITIAL__));

const $=s=>document.querySelector(s);
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const day=o=>{const d=new Date();d.setDate(d.getDate()+o);return d.toISOString().slice(0,10)};
const when=t=>new Date(t).toLocaleString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
const show=d=>new Date(d+'T00:00').toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'});
const daysUntil=d=>Math.max(0,Math.round((new Date(d+'T00:00')-new Date(new Date().toISOString().slice(0,10)+'T00:00'))/86400000));
const ini=n=>n.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
const greet=()=>{const h=new Date().getHours();return h<12?'Good morning':h<18?'Good afternoon':'Good evening'};

let S;
try{
  const saved=JSON.parse(localStorage.getItem(USER_DATA_KEY)||'null');
  S = saved && saved.inv ? Object.assign(DEF(), saved) : DEF();
  if(!S.me) S.me = DEF().me;
  if(!Array.isArray(S.audit)||!S.audit.length) S.audit = DEF().audit;
}catch(e){ S = DEF(); }
S.me = Object.assign({}, S.me || DEF().me, AUTH_USER);
S.set = Object.assign({}, DEF().set, S.set || {});
const save=()=>{try{localStorage.setItem(USER_DATA_KEY,JSON.stringify(S))}catch(e){}};
const fmt=n=>S.set.cur+n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});

let filter='all', term=SEARCH, sort={k:'i',dir:-1};

function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('show');clearTimeout(t._h);t._h=setTimeout(()=>t.classList.remove('show'),2400)}
const openCount=()=>S.inv.filter(x=>x.s!='paid').length;
const paidCount=()=>S.inv.filter(x=>x.s=='paid').length;

function syncMe(){
  const m=S.me;
  $('#me-name').textContent=m.n;
  $('#me-role').textContent=m.role;
  $('#me-av').textContent=ini(m.n);
  $('#mname').textContent=m.n;
  $('#mav').textContent=ini(m.n);
}

function usageBlock(label,used,limit,unit=''){
  const p=Math.min(100,Math.max(0,Math.round(used/limit*100)));
  return `<div class="ub"><span><em>${label}</em><b>${used}${unit} of ${limit}${unit}</b></span>
    <div class="track"><div class="fill" style="width:${p}%"></div></div></div>`;
}

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

  // Revenue chart — current month is derived from payments, others are demo history
  const rev = [18.2, 22.5, 19.8, 27.4, 24.1, 31.6, 0];
  const mon = ['Apr','May','Jun','Jul','Aug','Sep','Oct'];
  rev[6] = S.pay.reduce((t,p)=>t+p.a,0)/1000;
  const mx = Math.max(...rev, 1);
  const chartEl = $('#ov-chart');
  if (chartEl) {
    chartEl.innerHTML = rev.map((v,i) =>
      `<div class="col"><div class="bar ${i==6?'cur':''}" style="height:${Math.max(v/mx*82,3)}%"><span class="tip">${S.set.cur}${v.toFixed(1)}k</span></div><em>${mon[i]}</em></div>`
    ).join('');
  }

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

function account(){
  const m=S.me;
  $('#ac-name').value=m.n;
  $('#ac-mail').value=m.e;
  $('#ac-phone').value=m.phone||'';
  $('#ac-tz').value=m.tz||'Africa/Harare';
  $('#ac-payment-methods').value=S.set.paymentMethods||'';
  $('#ac-bank-name').value=S.set.bankName||'';
  $('#ac-bank-account-name').value=S.set.bankAccountName||'';
  $('#ac-bank-account-number').value=S.set.bankAccountNumber||'';
  $('#ac-bank-branch').value=S.set.bankBranch||'';
  $('#ac-bank-swift').value=S.set.bankSwift||'';

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

function bell(){
  const o=S.inv.filter(x=>x.s=='overdue');
  $('#bc').textContent=o.length; $('#bc').hidden=!o.length;
  $('#pop').innerHTML=`<h3>Needs attention</h3>`+(o.length?o.map(x=>`<div class="it"><div><b>${x.id} · ${esc(x.c)}</b><small>Overdue since ${show(x.d)}</small></div><button class="btn sm" data-r="${x.id}">Remind</button></div>`).join(''):'<div class="it"><small>Nothing is overdue.</small></div>');
}

function render(){
  try { bell(); } catch(e){ console.warn(e); }
  const view = {overview,invoices,clients,payments,account}[PAGE];
  if (view) { try { view(); } catch(e){ console.warn(e); } }
}

function markPaid(id,m){
  const x=S.inv.find(i=>i.id==id);
  if(!x||x.s=='paid')return;
  x.s='paid';
  S.pay.push({inv:x.id,c:x.c,d:day(0),m:m||'Bank transfer',a:x.a});
  save(); render(); toast(id+' marked as paid');
}
function invoicePaperMarkup(invoice, clientEmail=''){
  const items=invoice.items||[];
  const subtotal=items.reduce((sum,item)=>sum+(+item.q||0)*(+item.p||0),0);
  const taxRate=+invoice.t||0;
  const tax=subtotal*taxRate/100;
  const grand=subtotal+tax;
  const displayNumber=invoice.id||'Draft';
  const issuer=S.set.name||'Your business';
  const issued=invoice.i||day(0);
  const due=invoice.d||day(30);
  const customer=invoice.c||'Customer name';
  const lines=items.length?items:[{d:'Add an item description',q:1,p:0}];
  const paymentMethods=(S.set.paymentMethods||'').trim();
  const bankDetails=[
    ['Bank',S.set.bankName],
    ['Account name',S.set.bankAccountName],
    ['Account number',S.set.bankAccountNumber],
    ['Branch',S.set.bankBranch],
    ['SWIFT / BIC',S.set.bankSwift]
  ].filter(([,value])=>String(value||'').trim());
  return `<article class="invoice-paper">
    <header class="invoice-cover">
      <div><div class="invoice-cover-brand"><span class="invoice-cover-mark">L</span><span>${esc(issuer)}</span></div>
        <h2>INVOICE</h2><p class="invoice-cover-id"># ${esc(displayNumber)}</p>
        <p class="invoice-cover-byline">${esc(issuer)}, ${show(issued)}</p>
      </div>
      <div class="invoice-cover-info">
        <div><b>Date information</b><small>Issued ${show(issued)} · Due ${show(due)}</small></div>
        <div><b>Invoice number</b><small>${esc(displayNumber)}</small></div>
        <div><b>Invoice to</b><small>${esc(customer)}${clientEmail?`<br>${esc(clientEmail)}`:''}</small></div>
        <div class="invoice-cover-total">Total due: ${fmt(grand)}</div>
      </div>
    </header>
    <div class="invoice-paper-body">
      <div class="invoice-paper-table-wrap"><table>
        <thead><tr><th>No.</th><th>Item description</th><th>Price</th><th>Qty.</th><th>Total</th></tr></thead>
        <tbody>${lines.map((item,index)=>`<tr><td>${String(index+1).padStart(2,'0')}.</td>
          <td><span class="invoice-paper-item-description">${esc(item.d||'Item')}</span></td>
          <td>${fmt(+item.p||0)}</td><td>${+item.q||0}</td><td>${fmt((+item.q||0)*(+item.p||0))}</td></tr>`).join('')}</tbody>
      </table></div>
      <div class="invoice-paper-bottom">
        <div><p class="invoice-paper-thanks">Thank you for your business</p>
          <p class="invoice-paper-contact">${esc(S.set.mail||'')}</p>
          ${(paymentMethods||bankDetails.length)?`<b>PAYMENT DETAILS</b>`:''}
          ${paymentMethods?`<p class="invoice-payment-lines">${esc(paymentMethods)}</p>`:''}
          ${bankDetails.length?`<p class="invoice-payment-lines">${bankDetails.map(([label,value])=>`<b>${esc(label)}:</b> ${esc(String(value))}`).join('<br>')}</p>`:''}
          <b>PAYMENT DUE</b><p class="invoice-paper-terms">By ${show(due)}</p>
          <b>TERMS &amp; CONDITIONS</b><p class="invoice-paper-terms">${esc(S.set.note||'Please pay by the due date.')}</p>
        </div>
        <div class="invoice-paper-totals">
          <div><span>Subtotal</span><b>${fmt(subtotal)}</b></div>
          <div><span>Tax (${taxRate}%)</span><b>${fmt(tax)}</b></div>
          <div class="invoice-paper-total"><span>Total</span><span>${fmt(grand)}</span></div>
          <div class="invoice-paper-signature">${esc(issuer)}<small>Thank you</small></div>
        </div>
      </div>
      <footer class="invoice-paper-footer">Mari Flow - Invoice Generator App by Smug Solutions.</footer>
    </div>
  </article>`;
}
function openInv(id,generated=false){
  const x=S.inv.find(i=>i.id==id); if(!x)return;
  const cl=S.cl.find(c=>c.n==x.c);
  $('#sheet').innerHTML=`${invoicePaperMarkup(x,cl&&cl.e||'')}
  <div class="foot noprint">
    <button class="btn" data-edit-invoice="${esc(x.id)}">Edit invoice</button>
    <button class="btn" data-email-invoice="${esc(x.id)}">Email PDF</button>
    <button class="btn pri" data-print-invoice="${esc(x.id)}">Print PDF</button>
    ${generated?'':`<button class="btn" data-del="${esc(x.id)}">Delete</button>
    <button class="btn" data-dup="${esc(x.id)}">Duplicate</button>
    ${x.s!='paid'?`<button class="btn" data-r="${esc(x.id)}">Send reminder</button><button class="btn" data-p="${esc(x.id)}">Mark as paid</button>`:''}
    `}
    <button class="btn" id="vx">Close</button>
  </div>`;
  $('#vw').showModal();
}
function nextId(){const n=Math.max(1000,...S.inv.map(x=>+x.id.replace(/\D/g,'')||0))+1;return S.set.pre+n}

const dlg=$('#dlg');
let editingInvoiceId=null;
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
  updateDraftPreview();
  return s+t;
}
function updateDraftPreview(){
  const preview=$('#draft-preview');
  if(!preview)return;
  const customer=$('#cl').value.trim();
  const client=S.cl.find(c=>c.n===customer);
  const items=[...document.querySelectorAll('#items .li')].map(row=>{
    const fields=row.querySelectorAll('input');
    return {d:fields[0].value.trim(),q:+fields[1].value||0,p:+fields[2].value||0};
  });
  preview.innerHTML=invoicePaperMarkup({
    id:editingInvoiceId||nextId(),c:customer,i:$('#iss').value,d:$('#due').value,t:+$('#tax').value||0,items
  },client&&client.e||'');
}
function newInv(client, invoiceId=null){
  const existing=invoiceId?S.inv.find(item=>item.id===invoiceId):null;
  if(invoiceId&&!existing)return;
  editingInvoiceId=existing?existing.id:null;
  $('#cls').innerHTML=S.cl.map(c=>`<option value="${esc(c.n)}">`).join('');
  $('#items').innerHTML='';
  const items=existing?existing.items||[]:[];
  if(items.length)items.forEach(item=>line(item.d,item.q,item.p));
  else line('',1,'');
  $('#cl').value=existing?existing.c:client||'';
  $('#tax').value=existing?existing.t:S.set.tax;
  $('#iss').value=existing?existing.i:day(0);
  $('#due').value=existing?existing.d:day(+S.set.terms||30);
  $('#invoice-editor-title').textContent=existing?'Edit invoice':'Create invoice';
  $('#invoice-editor-description').textContent=existing?'Update the invoice details. Its invoice number and payment status will remain unchanged.':'Fill in the details. The total updates as you type.';
  $('#save').textContent=existing?'Save changes':'Generate invoice';
  calc();
  if($('#vw').open)$('#vw').close();
  dlg.showModal();
  (existing||client?document.querySelector('.li input'):$('#cl')).focus();
}

function doLogout(){
  $('#pop').hidden=true;
  if(confirm('Sign out of Ledgerly?')) location.href='../auth/logout.php';
}

/* ---------- RENDER FIRST (never a blank page) ---------- */
syncMe();
if(term) $('#q').value = term;
render();
if(AUTH_NOTICE) toast(AUTH_NOTICE);

/* ---------- Then wire up handlers ---------- */
$('#logout').onclick=doLogout;
$('#logout2').onclick=doLogout;
$('#back').onclick=()=>{$('#so').hidden=true;toast('Signed back in as '+S.me.n);render()};

const acSave = $('#ac-save');
if (acSave) acSave.onclick = () => {
  const n=$('#ac-name').value.trim(), m=$('#ac-mail').value.trim();
  if(!n){$('#ac-name').focus();toast('Enter your full name');return}
  if(!/^.+@.+\..+$/.test(m)){$('#ac-mail').focus();toast('Enter a valid email address');return}
  S.me.n=n; S.me.e=m;
  S.me.phone=$('#ac-phone').value.trim();
  S.me.tz=$('#ac-tz').value;
  save(); syncMe(); account(); toast('Profile updated');
};
const acPaymentSave=$('#ac-payment-save');
if(acPaymentSave)acPaymentSave.onclick=()=>{
  S.set.paymentMethods=$('#ac-payment-methods').value.trim();
  S.set.bankName=$('#ac-bank-name').value.trim();
  S.set.bankAccountName=$('#ac-bank-account-name').value.trim();
  S.set.bankAccountNumber=$('#ac-bank-account-number').value.trim();
  S.set.bankBranch=$('#ac-bank-branch').value.trim();
  S.set.bankSwift=$('#ac-bank-swift').value.trim();
  save();
  toast('Invoice payment details saved');
};

document.addEventListener('click',e=>{
  const t=e.target;
  if(!t.closest('.tools')) $('#pop').hidden=true;

  if(t.dataset && t.dataset.act==='new'){newInv();return}
  if(t.dataset && t.dataset.act==='client'){$('#c-n').value='';$('#c-e').value='';$('#cdlg').showModal();$('#c-n').focus();return}

  const b=t.closest('button');
  if(b){
    const d=b.dataset;
    if(d.editInvoice!==undefined){
      const invoice=S.inv.find(item=>item.id===d.editInvoice);
      if(!invoice)return;
      newInv(invoice.c,invoice.id);
      return;
    }
    if(d.emailInvoice!==undefined){
      const invoice=S.inv.find(item=>item.id===d.emailInvoice);
      if(!invoice)return;
      const client=S.cl.find(item=>item.n===invoice.c);
      emailDialog.dataset.invoiceId=invoice.id;
      $('#invoice-recipient').value=client&&client.e||'';
      emailDialog.showModal();
      $('#invoice-recipient').focus();
      return;
    }
    if(d.printInvoice!==undefined){
      const invoice=S.inv.find(item=>item.id===d.printInvoice);
      if(!invoice)return;
      const printWindow=window.open('about:blank','_blank');
      if(!printWindow){toast('Allow pop-ups to open the printable PDF.');return}
      requestInvoicePdf(invoice).then(pdf=>{
        const url=URL.createObjectURL(pdf);
        printWindow.location.href=url;
        setTimeout(()=>URL.revokeObjectURL(url),60000);
      }).catch(error=>{
        printWindow.close();
        toast(error.message||'Unable to generate this invoice PDF.');
      });
      return;
    }
    if(d.p){e.stopPropagation();markPaid(d.p);if($('#vw').open)openInv(d.p);return}
    if(d.r){const x=S.inv.find(i=>i.id==d.r);toast('Reminder sent to '+(x?x.c:''));return}
    if(d.k){filter=d.k;invoices();return}
    if(d.pw!==undefined){toast('Password reset link sent to your email');return}
    if(d.so!==undefined){if(confirm('Sign out of all devices?'))toast('All other sessions signed out');return}
    if(d.nc!==undefined){newInv(d.nc);return}
    if(d.vc!==undefined){location.href='invoices.php?q='+encodeURIComponent(d.vc);return}
    if(d.dup){const x=S.inv.find(i=>i.id==d.dup);S.inv.unshift({...JSON.parse(JSON.stringify(x)),id:nextId(),s:'pending',i:day(0),d:day(+S.set.terms||30)});save();$('#vw').close();render();toast('Invoice duplicated');return}
    if(d.del){if(confirm('Delete '+d.del+'? This cannot be undone.')){S.inv=S.inv.filter(i=>i.id!=d.del);S.pay=S.pay.filter(p=>p.inv!=d.del);save();$('#vw').close();render();toast(d.del+' deleted')}return}
  }
  if(t.id=='prt'){window.print();return}
  if(t.id=='vx'){$('#vw').close();return}
  const tr=t.closest('tr[data-o]'); if(tr)openInv(tr.dataset.o);
  const th=t.closest('th[data-s]');
  if(th){const k=th.dataset.s;sort=sort.k==k?{k,dir:-sort.dir}:{k,dir:1};invoices()}
});

const bellEl = $('#bell');
if (bellEl) bellEl.onclick = e => { e.stopPropagation(); $('#pop').hidden = !$('#pop').hidden; };
const popEl = $('#pop');
if (popEl) popEl.onclick = e => e.stopPropagation();
const newEl = $('#new');
if (newEl) newEl.onclick = () => newInv();

const qEl = $('#q');
if (qEl) {
  qEl.oninput = e => {
    term = e.target.value;
    if (PAGE === 'invoices') invoices();
    else if (PAGE === 'clients') clients();
  };
  qEl.onkeydown = e => {
    if (e.key === 'Enter' && PAGE !== 'invoices' && PAGE !== 'clients') {
      e.preventDefault();
      location.href = 'invoices.php?q=' + encodeURIComponent(e.target.value);
    }
  };
}

const itemsEl = $('#items');
if (itemsEl) itemsEl.oninput = calc;
const taxEl = $('#tax');
if (taxEl) taxEl.oninput = calc;
['cl','iss','due'].forEach(id=>{const input=$('#'+id);if(input)input.oninput=updateDraftPreview});
const addItemEl = $('#addItem');
if (addItemEl) addItemEl.onclick = () => line();
const cancelEl = $('#cancel');
if (cancelEl) cancelEl.onclick = () => dlg.close();

const saveEl = $('#save');
if (saveEl) saveEl.onclick = () => {
  const c=$('#cl').value.trim(); if(!c){$('#cl').focus();toast('Enter a client name');return}
  const items=[...document.querySelectorAll('.li')].map(r=>{const i=r.querySelectorAll('input');return{d:i[0].value.trim(),q:+i[1].value||1,p:+i[2].value||0}}).filter(i=>i.p>0);
  if(!items.length){toast('Add at least one priced line item');return}
  if(!S.cl.some(x=>x.n==c))S.cl.push({n:c,e:''});
  const t=+$('#tax').value||0;
  const id=editingInvoiceId||nextId();
  const amount=Math.round(items.reduce((s,i)=>s+i.q*i.p,0)*(1+t/100)*100)/100;
  const invoice=S.inv.find(item=>item.id===id);
  if(editingInvoiceId&&!invoice){toast('This invoice is no longer available.');dlg.close();return}
  if(invoice){
    Object.assign(invoice,{c,i:$('#iss').value||day(0),d:$('#due').value||day(30),a:amount,items,t});
  }else{
    S.inv.unshift({id,c,i:$('#iss').value||day(0),d:$('#due').value||day(30),a:amount,s:'pending',items,t});
  }
  editingInvoiceId=null;
  save();
  dlg.close();
  render();
  openInv(id,!invoice);
  toast(invoice?id+' updated':id+' generated');
};

const emailDialog=$('#emaildlg');
const emailForm=$('#email-form');
const emailCancel=$('#email-cancel');
if(emailCancel)emailCancel.onclick=()=>emailDialog.close();
async function requestInvoicePdf(invoice){
  const response=await fetch('email_invoice.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
    body:new URLSearchParams({
      csrf_token:INVOICE_CSRF,
      invoice:JSON.stringify(invoice),
      business:JSON.stringify({name:S.set.name,mail:S.set.mail,note:S.set.note,cur:S.set.cur,paymentMethods:S.set.paymentMethods||'',bankName:S.set.bankName||'',bankAccountName:S.set.bankAccountName||'',bankAccountNumber:S.set.bankAccountNumber||'',bankBranch:S.set.bankBranch||'',bankSwift:S.set.bankSwift||''})
    })
  });
  if(!response.ok){
    const result=await response.json();
    throw new Error(result.message||'Unable to generate this invoice PDF.');
  }
  const contentType=response.headers.get('Content-Type')||'';
  if(!contentType.toLowerCase().includes('application/pdf')){
    throw new Error('The invoice service returned an invalid PDF response.');
  }
  return response.blob();
}
function downloadBlob(blob,filename){
  const url=URL.createObjectURL(blob);
  const link=document.createElement('a');
  link.href=url;
  link.download=filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  setTimeout(()=>URL.revokeObjectURL(url),60000);
}
async function base64Blob(blob){
  const bytes=new Uint8Array(await blob.arrayBuffer());
  let binary='';
  const chunkSize=0x8000;
  for(let offset=0;offset<bytes.length;offset+=chunkSize){
    binary+=String.fromCharCode(...bytes.subarray(offset,offset+chunkSize));
  }
  return btoa(binary).replace(/.{1,76}/g,'$&\r\n').trim();
}
function utf8Base64(value){
  const bytes=new TextEncoder().encode(value);
  let binary='';
  for(let offset=0;offset<bytes.length;offset+=0x8000){
    binary+=String.fromCharCode(...bytes.subarray(offset,offset+0x8000));
  }
  return btoa(binary);
}
async function makeInvoiceEmailDraft(invoice,recipient,pdf){
  const boundary='----=_Invoice_'+Date.now().toString(16)+Math.random().toString(16).slice(2);
  const subject='Invoice '+invoice.id+' from '+S.set.name;
  const encodedSubject='=?UTF-8?B?'+utf8Base64(subject)+'?=';
  const message='Hello,\r\n\r\nPlease find invoice '+invoice.id+' attached as a PDF.\r\n\r\nThank you,\r\n'+S.set.name;
  const encodedMessage=utf8Base64(message).replace(/.{1,76}/g,'$&\r\n').trim();
  const encodedPdf=await base64Blob(pdf);
  const content=[
    'To: '+recipient.replace(/[\r\n]/g,''),
    'Subject: '+encodedSubject,
    'MIME-Version: 1.0',
    'Content-Type: multipart/mixed; boundary="'+boundary+'"',
    '',
    '--'+boundary,
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: base64',
    '',
    encodedMessage,
    '--'+boundary,
    'Content-Type: application/pdf; name="invoice-'+invoice.id+'.pdf"',
    'Content-Transfer-Encoding: base64',
    'Content-Disposition: attachment; filename="invoice-'+invoice.id+'.pdf"',
    '',
    encodedPdf,
    '--'+boundary+'--',
    ''
  ].join('\r\n');
  return new Blob([content],{type:'message/rfc822'});
}
if(emailForm)emailForm.onsubmit=async e=>{
  e.preventDefault();
  const invoice=S.inv.find(item=>item.id===emailDialog.dataset.invoiceId);
  const recipient=$('#invoice-recipient').value.trim();
  if(!invoice||!emailForm.reportValidity())return;
  const send=$('#email-send');
  send.disabled=true;
  send.textContent='Preparing PDF…';
  try{
    const pdf=await requestInvoicePdf(invoice);
    emailDialog.close();
    const filename='invoice-'+invoice.id+'.pdf';
    const pdfFile=new File([pdf],filename,{type:'application/pdf'});
    if(navigator.canShare&&navigator.share&&navigator.canShare({files:[pdfFile]})){
      await navigator.share({
        files:[pdfFile],
        title:'Invoice '+invoice.id,
        text:'To: '+recipient+'\nInvoice '+invoice.id+' from '+S.set.name
      });
      toast('Invoice shared as a PDF attachment.');
    }else{
      const draft=await makeInvoiceEmailDraft(invoice,recipient,pdf);
      downloadBlob(draft,'invoice-'+invoice.id+'-email.eml');
      toast('Open the downloaded email draft to send the attached PDF.');
    }
  }catch(error){
    if(error.name==='AbortError'){
      toast('Email sharing was canceled.');
    }else{
      toast(error.message||'Unable to prepare the invoice email.');
    }
  }finally{
    send.disabled=false;
    send.textContent='Email PDF';
  }
};

const csvEl = $('#csv');
if (csvEl) csvEl.onclick = () => {
  const rows=[['Invoice','Client','Issued','Due','Status','Amount']].concat(S.inv.map(x=>[x.id,x.c,x.i,x.d,x.s,x.a]));
  const csv=rows.map(r=>r.map(v=>'"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
  const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'})); a.download='invoices.csv';
  document.body.appendChild(a); a.click(); a.remove(); toast('Invoices exported');
};

const addcEl = $('#addc');
if (addcEl) addcEl.onclick = () => {$('#c-n').value='';$('#c-e').value='';$('#cdlg').showModal();$('#c-n').focus()};
const cxEl = $('#c-x');
if (cxEl) cxEl.onclick = () => $('#cdlg').close();
const csEl = $('#c-s');
if (csEl) csEl.onclick = () => {
  const n=$('#c-n').value.trim();
  if(!n){toast('Enter a client name');return}
  if(S.cl.some(c=>c.n.toLowerCase()==n.toLowerCase())){toast('That client already exists');return}
  S.cl.push({n,e:$('#c-e').value.trim()});
  save(); $('#cdlg').close();
  if(PAGE==='clients'){render();toast(n+' added')}
  else{location.href='clients.php'}
};

document.querySelectorAll('dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()}));
document.addEventListener('keydown',e=>{
  if(!$('#so').hidden)return;
  if(e.target.matches('input,textarea,select')||e.metaKey||e.ctrlKey)return;
  if(e.key=='n'){e.preventDefault();newInv()}
  if(e.key=='/'){e.preventDefault();$('#q').focus()}
});
</script>
</body>
</html>