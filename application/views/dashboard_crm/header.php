<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard CRM — Monitoring Komplain Konsumen</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
<style>
  :root {
    --bs-body-bg: #F4F6FA;
    --navy: #0F1B2D;
  }
  body { background: #F4F6FA; font-size: 14px; }

  /* CARDS */
  .kpi-card { border: 1px solid #E4E8F0; border-radius: 14px; background: #fff; padding: 20px 22px; position: relative; overflow: hidden; transition: box-shadow .15s, transform .15s; box-shadow: 0 1px 3px rgba(15,27,45,.06); }
  .kpi-card:hover { box-shadow: 0 4px 16px rgba(15,27,45,.10); transform: translateY(-2px); }
  .kpi-card::before { content:''; position:absolute; top:0;left:0;right:0;height:3px; background:var(--kpi-color,#1A56DB); }
  .kpi-card.green { --kpi-color:#0E9F6E; }
  .kpi-card.red   { --kpi-color:#E02424; }
  .kpi-card.orange{ --kpi-color:#D97706; }
  .kpi-label { font-size: 11px; font-weight: 600; color: #96A3B7; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
  .kpi-value { font-size: 28px; font-weight: 700; letter-spacing: -.03em; line-height: 1; margin-bottom: 6px; }
  .kpi-meta  { font-size: 12px; color: #5A6A85; }

  /* PILLS */
  .pill { display: inline-flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 100px; }
  .pill-green  { background: #E8F8F2; color: #0E9F6E; }
  .pill-red    { background: #FEF2F2; color: #E02424; }
  .pill-orange { background: #FFFBEB; color: #D97706; }
  .pill-blue   { background: #EBF0FF; color: #1A56DB; }

  /* CHART CARD */
  .chart-card { background: #fff; border: 1px solid #E4E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(15,27,45,.06); height: 100%; }
  .chart-title { font-size: 13px; font-weight: 600; margin-bottom: 2px; }
  .chart-sub   { font-size: 11px; color: #96A3B7; margin-bottom: 14px; }

  /* SECTION */
  .section-badge { font-size: 11px; font-weight: 700; font-family: monospace; color: #1A56DB; background: #EBF0FF; border-radius: 6px; padding: 2px 8px; }
  .section-title { font-size: 17px; font-weight: 700; letter-spacing: -.02em; }
  .section-period{ font-size: 12px; color: #96A3B7; font-family: monospace; }

  /* FILTER */
  .filter-bar { background: #fff; border: 1px solid #E4E8F0; border-radius: 14px; padding: 14px 20px; box-shadow: 0 1px 3px rgba(15,27,45,.06); }
  .filter-label { font-size: 11px; font-weight: 700; color: #5A6A85; text-transform: uppercase; letter-spacing: .06em; white-space: nowrap; }
  .filter-bar .form-control, .filter-bar .form-select { font-size: 13px; border-color: #E4E8F0; background: #F4F6FA; }
  .filter-bar .form-control:focus, .filter-bar .form-select:focus { border-color: #1A56DB; box-shadow: 0 0 0 3px rgba(26,86,219,.12); }

  /* STATUS LIST */
  .status-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: background .12s; }
  .status-item:hover { background: #F4F6FA; }
  .status-dot-sm { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
  .status-name { font-size: 13px; font-weight: 500; flex: 1; }
  .status-qty  { font-size: 13px; font-weight: 700; font-family: monospace; }
  .status-pct  { font-size: 11px; color: #96A3B7; font-family: monospace; width: 32px; text-align: right; }

  /* PROGRESS */
  .prog-row { display: flex; align-items: center; gap: 8px; }
  .prog-label { font-size: 12px; width: 120px; flex-shrink: 0; }
  .prog-pct { font-size: 12px; font-weight: 700; font-family: monospace; width: 36px; text-align: right; }

  /* RATING */
  .rating-ph { background: #fff; border: 2px dashed #E4E8F0; border-radius: 14px; padding: 48px 24px; text-align: center; }

  /* TABLE */
  .modal-table th { font-size: 11px; font-weight: 700; color: #96A3B7; text-transform: uppercase; letter-spacing: .06em; background: #F8F9FC; }
  .modal-table td { font-size: 13px; vertical-align: middle; }
  .badge-status { display: inline-block; padding: 2px 10px; border-radius: 100px; font-size: 11px; font-weight: 600; }
  .badge-done    { background:#E8F8F2; color:#0E9F6E; }
  .badge-late    { background:#FEF2F2; color:#E02424; }
  .badge-ontime  { background:#E8F8F2; color:#0E9F6E; }
  .badge-working { background:#EBF0FF; color:#1A56DB; }
  .badge-waiting { background:#FFFBEB; color:#D97706; }
  .badge-reject  { background:#FEF2F2; color:#E02424; }

  .section-divider { height: 1px; background: #E4E8F0; margin: 4px 0 28px; }

  /* Loading overlay for modal */
  #modalLoading { display:none; text-align:center; padding:40px; }

  /* DATATABLES inside modal */
  .dataTables_wrapper { font-size: 13px; }
  .dataTables_wrapper .dataTables_filter input { border: 1px solid #E4E8F0; border-radius: 6px; padding: 3px 8px; font-size: 12px; }
  .dataTables_wrapper .dataTables_filter input:focus { border-color: #1A56DB; box-shadow: 0 0 0 3px rgba(26,86,219,.12); outline: 0; }
  .dataTables_wrapper .dataTables_length select { border: 1px solid #E4E8F0; border-radius: 6px; padding: 2px 6px; font-size: 12px; }
  .dataTables_wrapper .dataTables_info { font-size: 12px; color: #96A3B7; padding-top: 6px; }
  .dataTables_wrapper .dataTables_paginate { padding-top: 4px; }
  .dataTables_wrapper .dataTables_paginate .paginate_button { font-size: 12px; padding: 2px 8px; border-radius: 5px; }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current,
  .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #1A56DB; color: #fff !important; border-color: #1A56DB; }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #EBF0FF; color: #1A56DB !important; border-color: #E4E8F0; }
  table.dataTable thead th { font-size: 11px; font-weight: 700; color: #96A3B7; text-transform: uppercase; letter-spacing: .06em; background: #F8F9FC; border-bottom: 1px solid #E4E8F0; }
  table.dataTable tbody td { vertical-align: middle; }
  table.dataTable.no-footer { border-bottom: 1px solid #E4E8F0; }
</style>
</head>
<body>