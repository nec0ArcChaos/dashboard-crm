<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard CRM — Monitoring Komplain Konsumen</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
  :root { --brand: #1a56db; --brand-dark: #0f1b2d; }
  body { background: #f0f4f8; font-size: 14px; }
  .navbar-brand-sub { font-size: 11px; opacity:.6; display:block; line-height:1; }
  .live-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(16,185,129,.15); border:1px solid rgba(16,185,129,.3);
    color:#10b981; border-radius:100px; padding:3px 10px; font-size:11px; font-weight:600;
  }
  .live-badge::before { content:''; width:7px; height:7px; border-radius:50%; background:#10b981; animation:pulse 1.5s infinite; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }
  .card { border:1px solid #dee2eb; box-shadow:0 1px 4px rgba(0,0,0,.06); border-radius:12px; }
  .card-header { border-bottom:1px solid #dee2eb; background:#f8f9fc; border-radius:12px 12px 0 0 !important; }
  .kpi-card { position:relative; overflow:hidden; transition:transform .15s,box-shadow .15s; }
  .kpi-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.1) !important; }
  .kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--stripe-color, var(--brand)); }
  .kpi-card.stripe-success::before { --stripe-color:#10b981; }
  .kpi-card.stripe-danger::before  { --stripe-color:#ef4444; }
  .kpi-card.stripe-warning::before { --stripe-color:#f59e0b; }
  .kpi-card.stripe-primary::before { --stripe-color:var(--brand); }
  .kpi-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
  .kpi-value { font-size:26px; font-weight:700; line-height:1.1; letter-spacing:-.02em; }
  .section-badge { font-size:11px; font-weight:700; background:#e8f0ff; color:var(--brand); border-radius:6px; padding:2px 8px; font-family:monospace; }
  .prog-wrap { display:flex; align-items:center; gap:8px; }
  .prog-bar-sm { flex:1; height:6px; background:#e5e7eb; border-radius:100px; overflow:hidden; }
  .prog-fill { height:100%; border-radius:100px; }
  .status-row { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px; cursor:pointer; transition:background .1s; }
  .status-row:hover { background:#f1f5f9; }
  .status-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
  .rating-placeholder { border:2px dashed #dee2eb; border-radius:12px; }
  .filter-bar { background:#fff; border:1px solid #dee2eb; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark sticky-top px-4" style="background:#0f1b2d;height:60px;">
  <div class="d-flex align-items-center gap-3">
    <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:var(--brand);flex-shrink:0">
      <i class="bi bi-grid-fill text-white" style="font-size:16px"></i>
    </div>
    <div>
      <span class="navbar-brand mb-0 fw-semibold" style="font-size:15px">Dashboard CRM — Monitoring Komplain</span>
      <span class="navbar-brand-sub">Developer Perumahan Subsidi · Board of Directors</span>
    </div>
  </div>
  <div class="d-flex align-items-center gap-3">
    <div class="live-badge">LIVE</div>
    <span id="clock" class="text-secondary" style="font-family:monospace;font-size:13px">--:--</span>
  </div>
</nav>

<div class="container-fluid px-4 py-4" style="max-width:1600px">

  <!-- FILTER BAR -->
  <div class="filter-bar p-3 mb-4">
    <!-- Form method POST ke controller untuk filter -->
    <form method="post" action="<?= site_url('dashboard/crm') ?>" id="filterForm">
      <?= $this->security->get_csrf_field() /* CSRF CI3 */ ?>
      <div class="row g-2 align-items-center">
        <div class="col-auto">
          <span class="fw-bold text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.06em">Filter</span>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
          <small class="text-muted">Dari</small>
          <input type="date" name="date_from" id="dateFrom" class="form-control form-control-sm"
            value="<?= htmlspecialchars($filter['date_from']) ?>" style="width:140px">
          <small class="text-muted">s.d.</small>
          <input type="date" name="date_to" id="dateTo" class="form-control form-control-sm"
            value="<?= htmlspecialchars($filter['date_to']) ?>" style="width:140px">
        </div>
        <div class="col-auto"><div class="vr" style="height:28px"></div></div>
        <div class="col-auto d-flex align-items-center gap-2">
          <small class="text-muted">Sumber</small>
          <select name="sumber" id="filterSumber" class="form-select form-select-sm" style="width:160px">
            <option value="all" <?= $filter['sumber']==='all'?'selected':'' ?>>Semua Sumber</option>
            <option value="konsumen" <?= $filter['sumber']==='konsumen'?'selected':'' ?>>Dari Konsumen</option>
            <option value="sosmed"   <?= $filter['sumber']==='sosmed'?'selected':'' ?>>Dari Sosmed</option>
          </select>
        </div>
        <div class="col-auto"><div class="vr" style="height:28px"></div></div>
        <div class="col-auto d-flex align-items-center gap-2">
          <small class="text-muted">Divisi</small>
          <select name="divisi" id="filterDivisi" class="form-select form-select-sm" style="width:180px">
            <option value="all" <?= $filter['divisi']==='all'?'selected':'' ?>>Semua Divisi</option>
            <?php foreach ($divisi_list as $d): ?>
            <option value="<?= $d ?>" <?= $filter['divisi']===$d?'selected':'' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto ms-auto d-flex gap-2">
          <a href="<?= site_url('dashboard/crm') ?>" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">Terapkan</button>
        </div>
      </div>
    </form>
  </div>

  <!-- ===== 01 VERIFIKASI ===== -->
  <div class="d-flex align-items-center gap-2 mb-3">
    <span class="section-badge">01</span>
    <h6 class="fw-bold mb-0">Verifikasi Komplain</h6>
    <small class="text-muted ms-auto" style="font-family:monospace">
      <?= htmlspecialchars($filter['date_from']) ?> s.d <?= htmlspecialchars($filter['date_to']) ?>
    </small>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-primary h-100 p-3">
        <div class="kpi-label">Total Komplain</div>
        <div class="kpi-value"><?= number_format($verifikasi['total']) ?></div>
        <small class="text-muted">Semua sumber</small>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-success h-100 p-3">
        <div class="kpi-label">Terverifikasi</div>
        <div class="kpi-value text-success"><?= number_format($verifikasi['terverifikasi']) ?></div>
        <span class="badge bg-success-subtle text-success">✓ <?= $verifikasi['pct_terverifikasi'] ?>% ≥ Target</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-danger h-100 p-3">
        <div class="kpi-label">Belum Terverifikasi</div>
        <div class="kpi-value text-danger"><?= number_format($verifikasi['belum']) ?></div>
        <span class="badge bg-danger-subtle text-danger">⚠ <?= $verifikasi['pct_belum'] ?>% Perlu Aksi</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-danger h-100 p-3">
        <div class="kpi-label">% Verifikasi Sosmed</div>
        <div class="kpi-value text-danger"><?= $verifikasi['pct_sosmed'] ?>%</div>
        <span class="badge bg-danger-subtle text-danger">🚨 Di bawah target</span>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">Verifikasi per Sumber</div>
          <small class="text-muted">Klik bar untuk detail</small>
        </div>
        <div class="card-body p-3" style="height:230px">
          <canvas id="chartVerifSumber"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100" style="cursor:pointer" onclick="openModal('verifTerverifikasi')">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">Terverifikasi vs Belum</div>
          <small class="text-muted">Klik untuk drill-down</small>
        </div>
        <div class="card-body p-3 d-flex align-items-center justify-content-center" style="height:190px">
          <canvas id="chartVerifDonut"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">Ringkasan Verifikasi</div>
          <small class="text-muted">Per sumber & status</small>
        </div>
        <div class="card-body p-2">
          <div class="status-row" onclick="openModal('verifKonsumen')">
            <div class="status-dot" style="background:#10b981"></div>
            <div class="flex-grow-1 fw-medium" style="font-size:12px">Dari Konsumen</div>
            <span class="fw-bold" style="font-family:monospace;font-size:13px"><?= number_format($verifikasi['konsumen_total']) ?></span>
            <span class="badge bg-success-subtle text-success ms-1">100%</span>
          </div>
          <div class="status-row" onclick="openModal('verifSosmedV')">
            <div class="status-dot" style="background:#1a56db"></div>
            <div class="flex-grow-1 fw-medium" style="font-size:12px">Sosmed — Terverifikasi</div>
            <span class="fw-bold" style="font-family:monospace;font-size:13px"><?= number_format($verifikasi['sosmed_terverifikasi']) ?></span>
            <span class="badge bg-danger-subtle text-danger ms-1"><?= $verifikasi['pct_sosmed'] ?>%</span>
          </div>
          <div class="status-row" onclick="openModal('verifSosmedB')">
            <div class="status-dot" style="background:#ef4444"></div>
            <div class="flex-grow-1 fw-medium" style="font-size:12px">Sosmed — Belum Verf.</div>
            <span class="fw-bold" style="font-family:monospace;font-size:13px"><?= number_format($verifikasi['sosmed_belum']) ?></span>
            <span class="badge bg-danger-subtle text-danger ms-1"><?= 100 - $verifikasi['pct_sosmed'] ?>%</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr class="my-4">

  <!-- ===== 02 ESKALASI ===== -->
  <div class="d-flex align-items-center gap-2 mb-3">
    <span class="section-badge">02</span>
    <h6 class="fw-bold mb-0">Eskalasi Komplain</h6>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-primary h-100 p-3">
        <div class="kpi-label">Total Komplain</div>
        <div class="kpi-value"><?= number_format($eskalasi['total']) ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-success h-100 p-3">
        <div class="kpi-label">Sudah Eskalasi</div>
        <div class="kpi-value text-success"><?= number_format($eskalasi['sudah']) ?></div>
        <span class="badge bg-success-subtle text-success">✓ <?= $eskalasi['pct_sudah'] ?>%</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-warning h-100 p-3">
        <div class="kpi-label">Belum Eskalasi</div>
        <div class="kpi-value text-warning"><?= number_format($eskalasi['belum']) ?></div>
        <span class="badge bg-warning-subtle text-warning">⚠ <?= $eskalasi['pct_belum'] ?>%</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-success h-100 p-3">
        <div class="kpi-label">Rate Eskalasi</div>
        <div class="kpi-value text-success"><?= $eskalasi['pct_sudah'] ?>%</div>
        <span class="badge bg-success-subtle text-success">✓ Baik</span>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-8">
      <div class="card h-100">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">Trend Eskalasi</div>
          <small class="text-muted">Distribusi per bulan</small>
        </div>
        <div class="card-body p-3" style="height:220px">
          <canvas id="chartEskalasiTrend"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100" style="cursor:pointer" onclick="openModal('eskSudah')">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">Proporsi Eskalasi</div>
          <small class="text-muted">Klik untuk detail</small>
        </div>
        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center" style="height:190px">
          <canvas id="chartEskalasiDonut"></canvas>
          <div class="d-flex gap-3 mt-2">
            <small><span class="badge" style="background:#10b981">&nbsp;</span> Sudah <?= $eskalasi['pct_sudah'] ?>%</small>
            <small><span class="badge" style="background:#f59e0b">&nbsp;</span> Belum <?= $eskalasi['pct_belum'] ?>%</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr class="my-4">

  <!-- ===== 03 KETEPATAN WAKTU ===== -->
  <div class="d-flex align-items-center gap-2 mb-3">
    <span class="section-badge">03</span>
    <h6 class="fw-bold mb-0">Ketepatan Waktu Pengerjaan</h6>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-6 col-md">
      <div class="card kpi-card stripe-success h-100 p-3">
        <div class="kpi-label">On Time Tertinggi</div>
        <div class="kpi-value text-success"><?= $ketepatan_summary['max_pct'] ?>%</div>
        <small class="text-muted"><?= htmlspecialchars($ketepatan_summary['max_divisi']) ?></small>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="card kpi-card stripe-danger h-100 p-3">
        <div class="kpi-label">On Time Terendah</div>
        <div class="kpi-value text-danger"><?= $ketepatan_summary['min_pct'] ?>%</div>
        <span class="badge bg-danger-subtle text-danger">🚨 <?= htmlspecialchars($ketepatan_summary['min_divisi']) ?></span>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="card kpi-card stripe-danger h-100 p-3">
        <div class="kpi-label">Divisi &lt; 80%</div>
        <div class="kpi-value text-danger"><?= $ketepatan_summary['below_target'] ?></div>
        <small class="text-muted">Perlu perhatian</small>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="card kpi-card stripe-success h-100 p-3">
        <div class="kpi-label">Divisi ≥ 80%</div>
        <div class="kpi-value text-success"><?= $ketepatan_summary['above_target'] ?></div>
        <small class="text-muted">Performa baik</small>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="card kpi-card stripe-primary h-100 p-3">
        <div class="kpi-label">Total Komplain</div>
        <div class="kpi-value"><?= number_format($ketepatan_summary['total']) ?></div>
        <small class="text-muted">Semua divisi</small>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-7">
      <div class="card h-100">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">On Time vs Late per Divisi</div>
          <small class="text-muted">Klik bar untuk detail</small>
        </div>
        <div class="card-body p-3" style="height:310px">
          <canvas id="chartKetepatan"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card h-100">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">% On Time per Divisi</div>
          <small class="text-muted">Hijau ≥ 80% · Merah &lt; 80%</small>
        </div>
        <div class="card-body p-2" style="overflow-y:auto;max-height:310px">
          <?php foreach ($ketepatan as $d):
            $pct = $d['total'] > 0 ? round($d['ontime']/$d['total']*100) : 0;
            $ok  = $pct >= 80;
            $color = $ok ? '#10b981' : '#ef4444';
          ?>
          <div class="status-row" onclick="openModal('divisi', <?= htmlspecialchars(json_encode($d)) ?>)">
            <div class="status-dot" style="background:<?= $color ?>"></div>
            <div class="flex-grow-1" style="font-size:12px;font-weight:500"><?= htmlspecialchars($d['divisi']) ?></div>
            <div class="prog-wrap" style="width:100px">
              <div class="prog-bar-sm">
                <div class="prog-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
              </div>
            </div>
            <span style="font-size:12px;font-weight:700;font-family:monospace;color:<?= $color ?>;width:36px;text-align:right"><?= $pct ?>%</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <hr class="my-4">

  <!-- ===== 04 RATING ===== -->
  <div class="d-flex align-items-center gap-2 mb-3">
    <span class="section-badge">04</span>
    <h6 class="fw-bold mb-0">Rating Konsumen</h6>
  </div>

  <div class="rating-placeholder p-5 text-center mb-4">
    <div style="font-size:40px">⭐</div>
    <h6 class="fw-semibold text-secondary mt-2">Data Rating Belum Tersedia — Future-Ready Placeholder</h6>
    <p class="text-muted small mx-auto" style="max-width:400px">Struktur sudah disiapkan. Setelah sumber data rating konsumen tersedia, koneksikan ke endpoint ini.</p>
    <div class="d-flex justify-content-center gap-2 my-3">
      <?php for ($i=1; $i<=5; $i++): ?>
      <span class="badge bg-light text-dark border" style="font-size:18px;padding:10px 14px"><?= $i ?>⭐</span>
      <?php endfor; ?>
    </div>
    <div class="row g-3 justify-content-center">
      <?php foreach (['Avg Rating','Total Responden','Kepuasan'] as $lbl): ?>
      <div class="col-auto">
        <div class="card bg-light border px-4 py-3 text-center">
          <div class="kpi-label"><?= $lbl ?></div>
          <div style="font-size:28px;font-weight:700;color:#dee2e6">—</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <hr class="my-4">

  <!-- ===== 05 STATUS KOMPLAIN ===== -->
  <div class="d-flex align-items-center gap-2 mb-3">
    <span class="section-badge">05</span>
    <h6 class="fw-bold mb-0">Status Komplain</h6>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-primary h-100 p-3">
        <div class="kpi-label">Total Eskalasi</div>
        <div class="kpi-value"><?= number_format($status_summary['total']) ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-success h-100 p-3">
        <div class="kpi-label">Done</div>
        <div class="kpi-value text-success"><?= number_format($status_summary['done']) ?></div>
        <span class="badge bg-success-subtle text-success">✓ <?= $status_summary['pct_done'] ?>%</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-danger h-100 p-3">
        <div class="kpi-label">Reject</div>
        <div class="kpi-value text-danger"><?= number_format($status_summary['reject']) ?></div>
        <span class="badge bg-danger-subtle text-danger"><?= $status_summary['pct_reject'] ?>%</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card kpi-card stripe-warning h-100 p-3">
        <div class="kpi-label">In Progress</div>
        <div class="kpi-value text-warning"><?= number_format($status_summary['in_progress']) ?></div>
        <span class="badge bg-warning-subtle text-warning"><?= $status_summary['pct_in_progress'] ?>% Working/Reschedule</span>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-5">
    <div class="col-md-7">
      <div class="card h-100">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">Distribusi Status</div>
          <small class="text-muted">Klik segmen untuk detail</small>
        </div>
        <div class="card-body p-3" style="height:260px">
          <canvas id="chartStatus"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card h-100">
        <div class="card-header px-3 py-2">
          <div class="fw-semibold" style="font-size:13px">Breakdown Status</div>
          <small class="text-muted">Klik baris untuk drill-down</small>
        </div>
        <div class="card-body p-2">
          <?php foreach ($status_list as $s):
            $pct = $status_summary['total'] > 0 ? round($s['qty']/$status_summary['total']*100) : 0;
          ?>
          <div class="status-row" onclick="openModal('status', <?= htmlspecialchars(json_encode($s)) ?>)">
            <div class="status-dot" style="background:<?= htmlspecialchars($s['color']) ?>"></div>
            <div class="flex-grow-1 fw-medium" style="font-size:12px"><?= htmlspecialchars($s['label']) ?></div>
            <span style="font-family:monospace;font-size:13px;font-weight:700"><?= number_format($s['qty']) ?></span>
            <span class="badge bg-<?= $s['badge'] ?>-subtle text-<?= $s['badge'] ?> ms-2"><?= $pct ?>%</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

</div><!-- /container -->

<!-- MODAL -->
<div class="modal fade" id="detailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:14px">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalTitle">Detail Komplain</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="mt-2 text-muted small">Memuat data...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
        <a id="btnExport" href="#" class="btn btn-success btn-sm">
          <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ======================================================
     DATA PHP → JavaScript (di-encode JSON agar aman)
     ====================================================== -->
<script>
  // Semua data dikirim dari Controller via $data[]
  // json_encode sudah escape XSS secara default di PHP
  const PHP = {
    verifikasi:   <?= json_encode($verifikasi) ?>,
    eskalasi:     <?= json_encode($eskalasi) ?>,
    ketepatan:    <?= json_encode($ketepatan) ?>,
    status_list:  <?= json_encode($status_list) ?>,
    eskalasi_trend: <?= json_encode($eskalasi_trend) ?>,
    ajax_url:     '<?= site_url('dashboard/crm_detail') ?>',
    export_url:   '<?= site_url('dashboard/crm_export') ?>',
    csrf_name:    '<?= $this->security->get_csrf_token_name() ?>',
    csrf_hash:    '<?= $this->security->get_csrf_hash() ?>',
  };
</script>

<script>
// ─── CLOCK ────────────────────────────────────────────────
function updateClock(){
  document.getElementById('clock').textContent =
    new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
setInterval(updateClock, 1000); updateClock();

// ─── CHART DEFAULTS ───────────────────────────────────────
Chart.defaults.font.family = 'system-ui, sans-serif';
Chart.defaults.plugins.legend.display = false;

// ─── CHART 1: Verifikasi per Sumber ───────────────────────
new Chart(document.getElementById('chartVerifSumber'), {
  type: 'bar',
  data: {
    labels: ['Dari Konsumen', 'Dari Sosmed'],
    datasets: [
      { label:'Terverifikasi', data:[PHP.verifikasi.konsumen_total, PHP.verifikasi.sosmed_terverifikasi], backgroundColor:'#10b981', borderRadius:6 },
      { label:'Belum Verif.',  data:[0, PHP.verifikasi.sosmed_belum],                                    backgroundColor:'#ef4444', borderRadius:6 },
    ]
  },
  options: {
    responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{ display:true, position:'bottom', labels:{boxWidth:10,font:{size:11}} } },
    scales:{ x:{stacked:true,grid:{display:false}}, y:{stacked:true,grid:{color:'#e5e7eb'}} },
    onClick:(e,els)=>{ if(els.length) openModal(els[0].datasetIndex===0?'verifTerverifikasi':'verifBelum'); }
  }
});

// ─── CHART 2: Verifikasi Donut ────────────────────────────
new Chart(document.getElementById('chartVerifDonut'), {
  type:'doughnut',
  data:{
    labels:['Terverifikasi','Belum'],
    datasets:[{ data:[PHP.verifikasi.terverifikasi, PHP.verifikasi.belum], backgroundColor:['#10b981','#ef4444'], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
  },
  options:{
    responsive:true, maintainAspectRatio:false, cutout:'68%',
    plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:11}}} },
    onClick:(e,els)=>{ if(els.length) openModal(els[0].index===0?'verifTerverifikasi':'verifBelum'); }
  }
});

// ─── CHART 3: Eskalasi Trend ──────────────────────────────
new Chart(document.getElementById('chartEskalasiTrend'), {
  type:'line',
  data:{
    labels: PHP.eskalasi_trend.map(r=>r.bulan),
    datasets:[{
      label:'Eskalasi', data: PHP.eskalasi_trend.map(r=>r.jumlah),
      borderColor:'#1a56db', backgroundColor:'rgba(26,86,219,.08)',
      tension:.4, fill:true, pointRadius:4, pointHoverRadius:7,
      pointBackgroundColor:'#fff', pointBorderColor:'#1a56db', pointBorderWidth:2,
    }]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    scales:{ x:{grid:{display:false},ticks:{font:{size:10}}}, y:{grid:{color:'#e5e7eb'},ticks:{font:{size:10}}} }
  }
});

// ─── CHART 4: Eskalasi Donut ──────────────────────────────
new Chart(document.getElementById('chartEskalasiDonut'), {
  type:'doughnut',
  data:{
    labels:['Sudah','Belum'],
    datasets:[{ data:[PHP.eskalasi.sudah, PHP.eskalasi.belum], backgroundColor:['#10b981','#f59e0b'], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
  },
  options:{
    responsive:true, maintainAspectRatio:false, cutout:'68%',
    plugins:{ legend:{display:false} },
    onClick:(e,els)=>{ if(els.length) openModal(els[0].index===0?'eskSudah':'eskBelum'); }
  }
});

// ─── CHART 5: Ketepatan Waktu ─────────────────────────────
new Chart(document.getElementById('chartKetepatan'), {
  type:'bar',
  data:{
    labels: PHP.ketepatan.map(d=>d.divisi),
    datasets:[
      { label:'On Time', data:PHP.ketepatan.map(d=>d.ontime), backgroundColor:PHP.ketepatan.map(d=>d.ontime/d.total>=.8?'#10b981':'#ef4444'), borderRadius:4 },
      { label:'Late',    data:PHP.ketepatan.map(d=>d.late),   backgroundColor:PHP.ketepatan.map(d=>d.late/d.total<=.2?'rgba(16,185,129,.2)':'rgba(239,68,68,.2)'), borderRadius:4 },
    ]
  },
  options:{
    responsive:true, maintainAspectRatio:false, indexAxis:'y',
    plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:11}}} },
    scales:{ x:{grid:{color:'#e5e7eb'}}, y:{grid:{display:false},ticks:{font:{size:10}}} },
    onClick:(e,els)=>{ if(els.length) openModal('divisi', PHP.ketepatan[els[0].index]); }
  }
});

// ─── CHART 6: Status Donut ────────────────────────────────
new Chart(document.getElementById('chartStatus'), {
  type:'doughnut',
  data:{
    labels: PHP.status_list.map(s=>s.label),
    datasets:[{ data:PHP.status_list.map(s=>s.qty), backgroundColor:PHP.status_list.map(s=>s.color), borderWidth:2, borderColor:'#fff', hoverOffset:8 }]
  },
  options:{
    responsive:true, maintainAspectRatio:false, cutout:'55%',
    plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:10},padding:8}} },
    onClick:(e,els)=>{ if(els.length) openModal('status', PHP.status_list[els[0].index]); }
  }
});

// ─── MODAL (AJAX ke Controller) ───────────────────────────
const bsModal = new bootstrap.Modal(document.getElementById('detailModal'));

function openModal(type, extra) {
  document.getElementById('modalTitle').textContent = 'Memuat...';
  document.getElementById('modalBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
  document.getElementById('btnExport').href = PHP.export_url + '?type=' + type + (extra ? '&divisi=' + encodeURIComponent(extra.divisi||'') : '');
  bsModal.show();

  // AJAX POST ke Controller method crm_detail()
  fetch(PHP.ajax_url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      type:  type,
      extra: JSON.stringify(extra || {}),
      [PHP.csrf_name]: PHP.csrf_hash   // sertakan CSRF token CI3
    })
  })
  .then(r => r.json())
  .then(res => {
    document.getElementById('modalTitle').textContent = res.title;
    document.getElementById('modalBody').innerHTML    = res.html;
    // Refresh CSRF token (CI3 regenerate per request jika config regenerate=true)
    if (res.csrf_hash) PHP.csrf_hash = res.csrf_hash;
  })
  .catch(() => {
    document.getElementById('modalBody').innerHTML = '<div class="alert alert-danger">Gagal memuat data. Coba lagi.</div>';
  });
}
</script>
</body>
</html>