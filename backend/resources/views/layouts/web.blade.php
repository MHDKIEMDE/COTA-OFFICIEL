<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $pageTitle ?? 'COTA — Espace Membre' }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">

<style>
:root {
  --bg:#0b0d10; --bg2:#15181d; --bg3:#1a1e25; --line:#1d2026; --line2:#2a2e36;
  --ink:#f4efe2; --ink2:#c7c4b8; --dim:#8b8a85; --dim2:#5a5d63;
  --accent:#e8ff36; --win:#3ddc91; --loss:#ff5b3a; --cool:#6fb4d9;
  --cool-dim:rgba(111,180,217,0.12); --accent-dim:rgba(232,255,54,0.1);
  --ui:"Space Grotesk",system-ui,sans-serif;
  --title:"Archivo Black","Archivo",sans-serif;
  --mono:"JetBrains Mono",monospace;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { background: var(--bg); font-family: var(--ui); -webkit-font-smoothing: antialiased; color: var(--ink); min-height: 100vh; }
::-webkit-scrollbar { width: 9px; height: 9px; }
::-webkit-scrollbar-thumb { background: var(--line2); border-radius: 5px; }
::-webkit-scrollbar-track { background: transparent; }

@keyframes cota-pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

/* Shell */
.wd-shell { display: flex; min-height: 100vh; }

/* Sidebar */
.wd-sidebar {
  width: 248px; flex-shrink: 0; background: var(--bg2);
  border-right: 1px solid var(--line); display: flex; flex-direction: column;
  padding: 22px 16px; position: sticky; top: 0; height: 100vh; overflow-y: auto;
}
.wd-brand { display: flex; align-items: center; gap: 11px; padding: 0 6px; cursor: pointer; text-decoration: none; }
.wd-brand-icon {
  width: 30px; height: 30px; border-radius: 8px; background: var(--accent);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.wd-brand-icon svg { display: block; }
.wd-brand-name { font-family: var(--title); font-size: 19px; letter-spacing: -0.02em; color: var(--ink); line-height: 1; }
.wd-brand-sub { font-family: var(--mono); font-size: 8.5px; color: var(--dim); letter-spacing: .22em; margin-top: 3px; }

.wd-profile {
  display: flex; flex-direction: column; align-items: center; text-align: center;
  padding: 22px 0 20px; margin-top: 18px;
  border-top: 1px solid var(--line); border-bottom: 1px solid var(--line);
  cursor: pointer; text-decoration: none;
}
.wd-avatar {
  width: 72px; height: 72px; border-radius: 50%; background: var(--accent);
  color: var(--bg); display: flex; align-items: center; justify-content: center;
  font-family: var(--title); font-size: 26px; letter-spacing: -.02em; position: relative;
}
.wd-avatar-dot {
  position: absolute; bottom: 2px; right: 2px; width: 16px; height: 16px;
  border-radius: 50%; background: var(--win); border: 3px solid var(--bg2);
}
.wd-profile-name { font-family: var(--title); font-size: 18px; color: var(--accent); letter-spacing: -.02em; margin-top: 12px; }
.wd-profile-city { font-size: 12px; color: var(--ink2); margin-top: 3px; }
.wd-profile-pill {
  margin-top: 12px; display: inline-flex; align-items: center; gap: 5px;
  background: var(--accent-dim); color: var(--accent);
  border-radius: 999px; padding: 4px 10px; font-family: var(--mono); font-size: 10px; font-weight: 700;
}

.wd-nav { display: flex; flex-direction: column; gap: 3px; margin-top: 16px; flex: 1; }
.wd-navsep { height: 1px; background: var(--line); margin: 10px 8px; }
.wd-navitem {
  display: flex; align-items: center; gap: 12px; padding: 11px 13px; border-radius: 10px;
  font-size: 13.5px; color: var(--dim); text-decoration: none; cursor: pointer;
  letter-spacing: -.01em; transition: background .15s, color .15s; border: none; background: none; width: 100%; text-align: left;
}
.wd-navitem:hover { background: var(--bg3); color: var(--ink2); }
.wd-navitem.active { background: rgba(232,255,54,.08); color: var(--accent); box-shadow: inset 3px 0 0 var(--accent); font-weight: 600; }
.wd-logout { margin-top: 6px; }

/* Main */
.wd-main { flex: 1; min-width: 0; padding: 26px 30px 64px; }

/* Topbar */
.wd-topbar { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
.wd-topactions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.wd-date { font-family: var(--mono); font-size: 10px; color: var(--dim); letter-spacing: .2em; }
.wd-h1 { font-family: var(--title); font-size: 30px; letter-spacing: -.03em; margin-top: 6px; color: var(--ink); }
.wd-desc { font-size: 13.5px; color: var(--ink2); line-height: 1.5; margin-top: 10px; max-width: 640px; }

.wd-iconbtn {
  width: 42px; height: 42px; border-radius: 11px; background: var(--bg2);
  border: 1px solid var(--line); color: var(--ink); display: flex; align-items: center;
  justify-content: center; position: relative; cursor: pointer; text-decoration: none;
}
.wd-iconbtn:hover { border-color: var(--line2); }
.wd-dot { position: absolute; top: 10px; right: 11px; width: 7px; height: 7px; border-radius: 4px; background: var(--accent); }
.wd-cta {
  display: inline-flex; align-items: center; gap: 7px; background: var(--accent);
  color: var(--bg); border: none; border-radius: 11px; padding: 0 18px; height: 42px;
  font-family: var(--title); font-size: 12.5px; letter-spacing: .02em; cursor: pointer; white-space: nowrap; text-decoration: none;
}
.wd-cta:hover { opacity: .9; color: var(--bg); }
.wd-cta-block { width: 100%; justify-content: center; height: 46px; }

.wd-ghost-btn {
  display: inline-flex; align-items: center; gap: 7px; background: transparent;
  border: 1px solid var(--line2); color: var(--ink); border-radius: 10px;
  padding: 9px 15px; font-family: var(--ui); font-size: 12.5px; font-weight: 600;
  cursor: pointer; white-space: nowrap;
}
.wd-ghost-btn.accent { border-color: var(--accent); color: var(--accent); }

/* Filter bar */
.wd-filterbar { display: flex; gap: 10px; align-items: stretch; margin-bottom: 22px; flex-wrap: wrap; }
.wd-search {
  flex: 1.6; min-width: 200px; display: flex; align-items: center; gap: 10px;
  background: var(--bg2); border: 1px solid var(--line); border-radius: 12px; padding: 0 16px;
}
.wd-search input {
  flex: 1; background: transparent; border: none; outline: none; color: var(--ink);
  font-family: var(--ui); font-size: 13.5px; padding: 14px 0;
}
.wd-search input::placeholder { color: var(--dim2); }
.wd-filter {
  flex: 1; min-width: 130px; text-align: left; background: var(--bg2);
  border: 1px solid var(--line); border-radius: 12px; padding: 9px 14px; cursor: pointer;
  display: flex; flex-direction: column;
}
.wd-filter:hover { border-color: var(--line2); }
.wd-filter-label { font-family: var(--mono); font-size: 8.5px; color: var(--dim); letter-spacing: .15em; }
.wd-filter-val { display: flex; align-items: center; justify-content: space-between; margin-top: 4px; }
.wd-filter-val span { font-size: 13px; color: var(--ink); font-weight: 500; }
.wd-filterbtn {
  background: var(--accent); color: var(--bg); border: none; border-radius: 12px;
  padding: 0 20px; font-family: var(--title); font-size: 12.5px;
  display: flex; align-items: center; gap: 7px; cursor: pointer; white-space: nowrap;
}

/* KPI grid */
.wd-kpis { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 22px; }
.wd-kpi { display: flex; background: var(--bg2); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; }
.wd-kpi-shape { width: 58px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--bg3); border-right: 1px solid var(--line); }
.wd-kpi-body { padding: 16px 18px; }
.wd-kpi-value { font-family: var(--title); font-size: 28px; letter-spacing: -.03em; color: var(--ink); line-height: 1; }
.wd-kpi-label { font-family: var(--mono); font-size: 9.5px; color: var(--dim); letter-spacing: .12em; margin-top: 7px; white-space: nowrap; }
.wd-kpi-delta { font-family: var(--mono); font-size: 10.5px; margin-top: 6px; display: flex; align-items: center; gap: 4px; white-space: nowrap; }

/* Columns */
.wd-cols { display: grid; grid-template-columns: minmax(0,1fr) 332px; gap: 22px; align-items: start; }
.wd-maincol { display: flex; flex-direction: column; gap: 22px; min-width: 0; }
.wd-rail { display: flex; flex-direction: column; gap: 22px; }
.wd-chartrow { display: grid; grid-template-columns: minmax(0,1.55fr) minmax(0,1fr); gap: 22px; }

/* Panel */
.wd-panel { background: var(--bg2); border: 1px solid var(--line); border-radius: 16px; }
.wd-panelhead { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 20px 22px 16px; }
.wd-panel-title { font-family: var(--title); font-size: 16px; letter-spacing: -.02em; color: var(--ink); }
.wd-panel-sub { font-family: var(--mono); font-size: 9.5px; color: var(--dim); letter-spacing: .12em; margin-top: 4px; white-space: nowrap; }
.wd-panel-body { padding: 22px; }
.wd-panel-body.no-top { padding-top: 0; }
.wd-morelink { font-family: var(--mono); font-size: 10px; color: var(--dim); letter-spacing: .1em; cursor: pointer; white-space: nowrap; text-decoration: none; }
.wd-morelink:hover { color: var(--accent); }

/* Pill */
.wd-pill {
  display: inline-flex; align-items: center; gap: 5px;
  border-radius: 999px; padding: 4px 10px; font-family: var(--mono); font-size: 10.5px; font-weight: 700;
}

/* Badge live */
.wd-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 999px; font-family: var(--mono); font-size: 11px; font-weight: 700; letter-spacing: .03em; white-space: nowrap; }
.wd-livedot { width: 6px; height: 6px; border-radius: 3px; background: currentColor; animation: cota-pulse 1.4s ease-in-out infinite; flex-shrink: 0; }

/* Confidence ring */
.wd-conf-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* Summary bars */
.wd-sum-item { margin-bottom: 20px; }
.wd-sum-item:last-child { margin-bottom: 0; }
.wd-sum-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
.wd-sum-track { height: 8px; background: var(--bg3); border-radius: 4px; overflow: hidden; }
.wd-sum-fill { height: 100%; border-radius: 4px; }

/* Match cards */
.wd-matches { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }
.wd-matchcard { background: var(--bg3); border: 1px solid var(--line); border-radius: 14px; overflow: hidden; }
.wd-matchcard-back {
  height: 132px; position: relative; overflow: hidden;
  background: linear-gradient(135deg, var(--bg3) 0%, var(--bg2) 100%);
}

/* Table */
.wd-table { width: 100%; }
.wd-tr { display: grid; grid-template-columns: 2.4fr 1.7fr 0.7fr 1fr 0.9fr; align-items: center; gap: 12px; padding: 14px 22px; border-top: 1px solid var(--line); }
.wd-tr:not(.wd-thead):hover { background: rgba(255,255,255,.015); }
.wd-thead { border-top: none; padding-top: 2px; padding-bottom: 13px; }
.wd-thead .wd-cell { font-family: var(--mono); font-size: 9.5px; color: var(--dim); letter-spacing: .14em; }
.wd-c-gain, .wd-c-odds { text-align: right; justify-self: end; }
.wd-thumb { width: 46px; height: 38px; border-radius: 9px; overflow: hidden; position: relative; flex-shrink: 0; background: var(--bg3); display: flex; align-items: center; justify-content: center; gap: 2px; }
.wd-back-btn { display: inline-flex; align-items: center; gap: 6px; background: none; border: none; color: var(--dim); font-family: var(--ui); font-size: 12.5px; cursor: pointer; padding: 0; margin-bottom: 18px; }
.wd-back-btn:hover { color: var(--ink); }

/* Prediction grid */
.wd-pgrid { display: grid; grid-template-columns: repeat(2,1fr); gap: 18px; }
.wd-pcard { background: var(--bg2); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; cursor: pointer; transition: border-color .15s, transform .15s; text-decoration: none; display: block; }
.wd-pcard:hover { border-color: var(--line2); transform: translateY(-2px); }
.wd-pcard-back { height: 150px; position: relative; overflow: hidden; background: linear-gradient(135deg, var(--bg3), var(--bg2)); }
.wd-pcard-body { padding: 16px; }
.wd-pcard-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 13px; border-top: 1px solid var(--line); }

/* Detail */
.wd-detail { display: grid; grid-template-columns: minmax(0,1fr) 320px; gap: 22px; align-items: start; }
.wd-detail-rail { display: flex; flex-direction: column; gap: 18px; }
.wd-crit-row { display: grid; grid-template-columns: 22px 1fr auto; gap: 10px; align-items: start; padding: 12px 0; border-bottom: 1px solid var(--line); }
.wd-crit-num { font-family: var(--mono); font-size: 10px; color: var(--dim); padding-top: 2px; }
.wd-crit-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
.wd-crit-badge { font-family: var(--mono); font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 6px; white-space: nowrap; }
.wd-conf-bar-wrap { margin-bottom: 14px; }
.wd-conf-bar-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
.wd-conf-bar-track { height: 8px; background: var(--bg3); border-radius: 4px; overflow: hidden; }
.wd-conf-bar-fill { height: 100%; border-radius: 4px; }

/* Live */
.wd-livegrid { display: grid; grid-template-columns: repeat(2,1fr); gap: 18px; }
.wd-livecard { cursor: pointer; transition: border-color .15s; }
.wd-livecard:hover { border-color: var(--line2); }

/* Competitions */
.wd-cgrid { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; }
.wd-cbanner { height: 96px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; border-radius: 16px 16px 0 0; }

/* Favorites */
.wd-teamrow { display: flex; flex-wrap: wrap; gap: 12px; }
.wd-teamchip { display: flex; align-items: center; gap: 10px; padding: 10px 14px 10px 10px; background: var(--bg3); border: 1px solid var(--line); border-radius: 999px; }
.wd-teamadd { cursor: pointer; border-style: dashed; padding: 10px 18px; }

/* Stats bar */
.wd-statbar { padding: 13px 0; border-bottom: 1px solid var(--line); }
.wd-statbar-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
.wd-statbar-track { height: 8px; background: var(--bg3); border-radius: 4px; overflow: hidden; }
.wd-statbar-fill { height: 100%; border-radius: 4px; }

/* Plans */
.wd-plans { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; align-items: start; }
.wd-plan { padding: 26px 24px; position: relative; }
.wd-plan.hot { border-color: var(--accent); box-shadow: 0 0 0 1px var(--accent); }
.wd-plan-ribbon { position: absolute; top: -1px; right: 22px; background: var(--accent); color: var(--bg); font-family: var(--mono); font-size: 9.5px; font-weight: 700; letter-spacing: .14em; padding: 5px 11px; border-radius: 0 0 8px 8px; }
.wd-plan-price { display: flex; align-items: baseline; gap: 4px; margin: 18px 0; }
.wd-plan-price-main { font-family: var(--title); font-size: 40px; letter-spacing: -.04em; color: var(--ink); }
.wd-plan-price-per { font-family: var(--mono); font-size: 12px; color: var(--dim); }
.wd-plan-features { display: flex; flex-direction: column; gap: 11px; margin-bottom: 22px; }
.wd-plan-feat { display: flex; align-items: center; gap: 9px; font-size: 13px; color: var(--ink2); }
.wd-plan-btn { width: 100%; height: 44px; border-radius: 11px; background: var(--bg3); border: 1px solid var(--line2); color: var(--ink); font-family: var(--ui); font-size: 13px; font-weight: 600; cursor: pointer; }
.wd-plan-btn:hover:not(:disabled) { border-color: var(--accent); }
.wd-plan-btn.cta { background: var(--accent); border: none; color: var(--bg); font-family: var(--title); letter-spacing: .02em; }
.wd-plan-btn.current { background: transparent; color: var(--dim); cursor: default; border-style: dashed; }

/* Toggle */
.wd-toggle { width: 42px; height: 24px; border-radius: 12px; background: var(--bg3); border: 1px solid var(--line2); position: relative; flex-shrink: 0; cursor: pointer; }
.wd-toggle.on { background: var(--accent); border-color: var(--accent); }
.wd-toggle-knob { position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; border-radius: 50%; background: var(--dim); transition: left .15s, background .15s; pointer-events: none; }
.wd-toggle.on .wd-toggle-knob { left: 20px; background: var(--bg); }

/* Referral */
.wd-refhero { padding: 26px 24px; }
.wd-refcode { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-top: 12px; padding: 16px 20px; background: var(--bg3); border: 1px dashed var(--line2); border-radius: 13px; }

/* Avatar */
.wd-av { border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--title); letter-spacing: -.02em; color: var(--bg); flex-shrink: 0; }

/* Review */
.wd-review-stars { display: flex; gap: 1px; }

/* Profile field */
.wd-field { padding: 13px 0; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 12px; }
.wd-field-label { font-family: var(--mono); font-size: 10px; color: var(--dim); letter-spacing: .12em; white-space: nowrap; }
.wd-field-val { font-size: 13.5px; color: var(--ink); text-align: right; white-space: nowrap; }

/* Pref row */
.wd-pref-row { display: flex; justify-content: space-between; align-items: center; padding: 13px 0; border-bottom: 1px solid var(--line); }

/* Referral grid */
.wd-ref-kpis { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-top: 22px; }
.wd-ref-kpi { text-align: center; padding: 14px 8px; background: var(--bg3); border-radius: 12px; border: 1px solid var(--line); }

/* How-it-works step */
.wd-step { display: flex; gap: 13px; padding: 12px 0; }
.wd-step:not(:last-child) { border-bottom: 1px solid var(--line); }
.wd-step-num { width: 30px; height: 30px; border-radius: 9px; background: var(--accent-dim); color: var(--accent); display: flex; align-items: center; justify-content: center; font-family: var(--title); font-size: 14px; flex-shrink: 0; }

/* Responsive */
@media (max-width:1180px) {
  .wd-cols { grid-template-columns: 1fr; }
  .wd-rail { display: grid; grid-template-columns: 1fr 1fr; }
  .wd-detail { grid-template-columns: 1fr; }
  .wd-cgrid { grid-template-columns: repeat(2,1fr); }
}
@media (max-width:960px) {
  .wd-chartrow { grid-template-columns: 1fr; }
  .wd-kpis { grid-template-columns: repeat(2,1fr); }
  .wd-matches { grid-template-columns: 1fr 1fr; }
  .wd-pgrid, .wd-livegrid, .wd-plans { grid-template-columns: 1fr; }
  .wd-filterbar { flex-wrap: wrap; }
  .wd-search { flex-basis: 100%; }
}
@media (max-width:760px) {
  .wd-shell { flex-direction: column; }
  .wd-sidebar { width: auto; height: auto; position: sticky; top: 0; z-index: 20; flex-direction: row; align-items: center; padding: 12px 14px; gap: 10px; }
  .wd-profile { display: none; }
  .wd-nav { flex-direction: row; overflow-x: auto; margin-top: 0; gap: 6px; -ms-overflow-style: none; scrollbar-width: none; }
  .wd-nav::-webkit-scrollbar { display: none; }
  .wd-navitem { padding: 9px 11px; white-space: nowrap; }
  .wd-navitem.active { box-shadow: inset 0 -3px 0 var(--accent); }
  .wd-logout { display: none; }
  .wd-main { padding: 18px 16px 56px; }
  .wd-rail { grid-template-columns: 1fr; }
  .wd-matches { grid-template-columns: 1fr; }
  .wd-cgrid { grid-template-columns: 1fr; }
  .wd-detail-rail { gap: 14px; }
  .wd-table .wd-tr { grid-template-columns: 1.8fr 1fr 0.9fr; }
  .wd-c-pick, .wd-c-odds, .wd-thead .wd-c-pick, .wd-thead .wd-c-odds { display: none; }
}
</style>
</head>
<body>
<div class="wd-shell">
  <!-- Sidebar -->
  <aside class="wd-sidebar">
    <a href="{{ route('home') }}" class="wd-brand">
      <div class="wd-brand-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M12 2L3 7l9 5 9-5-9-5zM3 12l9 5 9-5M3 17l9 5 9-5" stroke="#0b0d10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div>
        <div class="wd-brand-name">COTA</div>
        <div class="wd-brand-sub">ESPACE MEMBRE</div>
      </div>
    </a>

    @auth
    <a href="{{ route('profile') }}" class="wd-profile">
      <div class="wd-avatar">
        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        <span class="wd-avatar-dot"></span>
      </div>
      <div class="wd-profile-name">{{ auth()->user()->name ?? 'Utilisateur' }}</div>
      <div class="wd-profile-city">{{ auth()->user()->city ?? 'Membre COTA' }}</div>
      <div class="wd-profile-pill">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ auth()->user()->is_premium ? 'ABONNÉ PRO' : 'DÉCOUVERTE' }}
      </div>
    </a>
    @endauth

    <nav class="wd-nav">
      <a href="{{ route('home') }}" class="wd-navitem {{ request()->routeIs('home') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Dashboard
      </a>
      <a href="{{ route('predictions.index') }}" class="wd-navitem {{ request()->routeIs('predictions.*') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 10h18M7 14h7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Prédictions
      </a>
      <a href="{{ route('live') }}" class="wd-navitem {{ request()->routeIs('live') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 8.5l6 3.5-6 3.5z" fill="currentColor" stroke="none"/></svg>
        Matchs live
      </a>
      <a href="{{ route('competitions') }}" class="wd-navitem {{ request()->routeIs('competitions') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 4h12v4a6 6 0 0 1-12 0V4Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 6H3v2a3 3 0 0 0 3 3M18 6h3v2a3 3 0 0 1-3 3M9 20h6M12 14v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Compétitions
      </a>
      <a href="{{ route('favorites') }}" class="wd-navitem {{ request()->routeIs('favorites') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.6-9.2-9C1.3 8 3 4.5 6.4 4.5c2 0 3.2 1.2 3.6 2 .4-.8 1.6-2 3.6-2 3.4 0 5.1 3.5 3.6 6.5C19 15.4 12 20 12 20Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Favoris
      </a>
      <a href="{{ route('history') }}" class="wd-navitem {{ request()->routeIs('history') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Historique
      </a>
      <a href="{{ route('statistics') }}" class="wd-navitem {{ request()->routeIs('statistics') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Statistiques
      </a>

      <div class="wd-navsep"></div>

      <a href="{{ route('subscription') }}" class="wd-navitem {{ request()->routeIs('subscription') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Abonnement
      </a>
      <a href="{{ route('referral') }}" class="wd-navitem {{ request()->routeIs('referral') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="8" width="18" height="4" rx="1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 12v8h14v-8M12 8v12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 8S10.5 3 8 4.5 9.5 8 12 8Zm0 0s1.5-5 4-3.5S14.5 8 12 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Parrainage
      </a>
      @auth
      <a href="{{ route('profile') }}" class="wd-navitem {{ request()->routeIs('profile') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Profil
      </a>
      @endauth
    </nav>

    @auth
    <form method="POST" action="{{ route('logout') }}" style="margin-top:6px">
      @csrf
      <button type="submit" class="wd-navitem wd-logout" style="border:none; cursor:pointer; width:100%">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 4h-7v16h7M10 12h10m0 0-3-3m3 3-3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Déconnexion
      </button>
    </form>
    @else
    <a href="{{ route('login') }}" class="wd-navitem wd-logout" style="text-decoration:none">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 4h-7v16h7M10 12h10m0 0-3-3m3 3-3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Connexion
    </a>
    @endauth
  </aside>

  <!-- Content -->
  <main class="wd-main">
    {{ $slot }}
  </main>
</div>

<script>
// Toggle sidebar nav on mobile
document.querySelectorAll('.wd-toggle').forEach(function(el) {
  el.addEventListener('click', function() {
    el.classList.toggle('on');
    var knob = el.querySelector('.wd-toggle-knob');
    if (knob) knob.style.left = el.classList.contains('on') ? '20px' : '2px';
  });
});
</script>
</body>
</html>
