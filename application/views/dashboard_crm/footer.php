<!-- Modal: Ketepatan Waktu Global -->
<div class="modal fade" id="ketepatanGlobalModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold">Ketepatan Waktu Pengerjaan — Detail Semua Divisi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="ketepatanGlobalBody">
        <div id="ketepatanGlobalLoading"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Memuat data...</p></div>
        <div id="ketepatanGlobalFilters" style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #E4E8F0">
          <div class="d-flex flex-wrap gap-3 align-items-center">
            <label class="fw-semibold text-secondary" style="font-size:12px;margin:0">Filter Tanggal:</label>
            <div class="d-flex align-items-center gap-2">
              <label style="font-size:12px;color:#96A3B7;margin:0;white-space:nowrap">Due Date:</label>
              <input type="text" id="kgDueDateRange" class="form-control form-control-sm" style="width:240px;cursor:pointer;background:#F4F6FA" readonly placeholder="Pilih tanggal...">
            </div>
            <div class="d-flex align-items-center gap-2">
              <label style="font-size:12px;color:#96A3B7;margin:0;white-space:nowrap">Done Date:</label>
              <input type="text" id="kgDoneDateRange" class="form-control form-control-sm" style="width:240px;cursor:pointer;background:#F4F6FA" readonly placeholder="Pilih tanggal...">
            </div>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetKetepatanDateFilter()">Reset</button>
          </div>
        </div>
        <div id="ketepatanGlobalContent"></div>
        <div id="ketepatanGlobalPagination" class="mt-3"></div>
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnKetepatanExport">Export Excel</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Detail Komplain -->
<div class="modal fade" id="detailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="modalTitle">Detail Komplain</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody">
        <div id="modalLoading"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Memuat data...</p></div>
        <div id="modalFilters" style="display:none;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #E4E8F0">
          <div class="d-flex flex-wrap gap-3 align-items-center" id="mfSumberStatusRow">
            <label class="fw-semibold text-secondary" style="font-size:12px;margin:0">Filter:</label>
            <div class="d-flex align-items-center gap-2">
              <label style="font-size:12px;color:#96A3B7;margin:0;white-space:nowrap">Sumber:</label>
              <div class="d-flex gap-2">
                <label style="margin:0"><input type="checkbox" id="mfSemua" checked onchange="toggleModalFilterAll(this)"> <span style="font-size:12px">Semua</span></label>
                <label style="margin:0"><input type="checkbox" id="mfKonsumen" onchange="selectSumber(this,'konsumen')"> <span style="font-size:12px">Konsumen</span></label>
                <label style="margin:0"><input type="checkbox" id="mfSosmed" onchange="selectSumber(this,'sosmed')"> <span style="font-size:12px">Sosmed</span></label>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2" id="mfStatusGroup">
              <label style="font-size:12px;color:#96A3B7;margin:0;white-space:nowrap" id="mfStatusLabel">Status:</label>
              <div class="d-flex gap-2">
                <label style="margin:0"><input type="checkbox" id="mfStatus1" onchange="selectStatus(this,1)"> <span style="font-size:12px" id="mfStatus1Label">-</span></label>
                <label style="margin:0"><input type="checkbox" id="mfStatus2" onchange="selectStatus(this,2)"> <span style="font-size:12px" id="mfStatus2Label">-</span></label>
              </div>
            </div>
          </div>
          <hr id="mfSeparator" style="margin:10px 0;border:0;border-top:1px solid #E4E8F0">
          <div class="d-flex flex-wrap gap-3 align-items-center">
            <label class="fw-semibold text-secondary" style="font-size:12px;margin:0">Filter Tanggal:</label>
            <div class="d-flex align-items-center gap-2">
              <input type="text" id="mfDateRange" class="form-control form-control-sm" style="width:240px;cursor:pointer;background:#F4F6FA" readonly placeholder="Pilih tanggal...">
            </div>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetModalDateFilter()">Reset</button>
          </div>
        </div>
        <div id="modalContent"></div>
        <div id="modalPagination" class="mt-3"></div>
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnExport">Export Excel</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Drilldown Rating Konsumen -->
<div class="modal fade" id="ratingDrilldownModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="ratingDrilldownTitle">Detail Rating Konsumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="ratingDrilldownBody">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3 pb-2 border-bottom" id="ratingStarFilterWrap">
          <span style="font-size:11px;color:#96A3B7;font-weight:600;white-space:nowrap">Filter Bintang:</span>
          <div id="ratingStarFilter" class="d-flex gap-1 flex-wrap"></div>
        </div>
        <div id="ratingDrilldownLoading" class="text-center py-4">
          <div class="spinner-border text-warning" role="status"></div>
          <p class="mt-2 text-muted small">Memuat data rating...</p>
        </div>
        <div id="ratingDrilldownContent"></div>
        <div id="ratingDrilldownPagination" class="mt-3"></div>
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnRatingExport">Export Excel</button>
      </div>
    </div>
  </div>
</div>

</div><!-- /container-fluid -->

<!-- Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>

<script>
Chart.defaults.font.family = "system-ui, sans-serif";
Chart.defaults.plugins.legend.display = false;

// ---------------------------------------------------------------
//  Clock
// ---------------------------------------------------------------

function updateClock() {
  const el = document.getElementById('clock');
  if (el) el.textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
}
setInterval(updateClock, 1000);
updateClock();

// ---------------------------------------------------------------
//  DataTables — shared config & init helper
// ---------------------------------------------------------------

const _dtInstances = {};

const _dtLang = {
  search:        'Cari:',
  lengthMenu:    'Tampilkan _MENU_ data',
  info:          'Menampilkan _START_\u2013_END_ dari _TOTAL_ data',
  infoEmpty:     'Tidak ada data',
  infoFiltered:  '(disaring dari _MAX_ total data)',
  zeroRecords:   'Tidak ada data yang cocok',
  emptyTable:    'Tidak ada data tersedia',
  processing:    'Memproses...',
  loadingRecords:'Memuat...',
  paginate:      { first: '\u00ab', last: '\u00bb', next: '\u203a', previous: '\u2039' },
};

function _initDT(tableId, columns, data) {
  if (_dtInstances[tableId] && $.fn.DataTable.isDataTable('#' + tableId)) {
    _dtInstances[tableId].destroy();
    $('#' + tableId).empty();
  }
  _dtInstances[tableId] = $('#' + tableId).DataTable({
    data, columns,
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100, { label: 'Semua', value: -1 }],
    order:      [],
    scrollX:    true,
    language:   _dtLang,
    dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [{
      extend: 'excelHtml5', text: 'Export Excel', title: 'dashboard-crm-export',
      className: 'btn-dt-excel d-none',
      exportOptions: { columns: ':visible', orthogonal: 'export' }
    }],
  });
  return _dtInstances[tableId];
}

/**
 * Triggers the hidden DataTables Excel export button for a given table.
 */
function _exportDT(tableId) {
  if (_dtInstances[tableId] && $.fn.DataTable.isDataTable('#' + tableId)) {
    _dtInstances[tableId].button('.btn-dt-excel').trigger();
  }
}

/**
 * Fixes DataTables column width misalignment after Bootstrap modal animation.
 */
function _fixDTColumnsOnModalShown(modalId, tableId) {
  document.getElementById(modalId).addEventListener('shown.bs.modal', () => {
    if (_dtInstances[tableId] && $.fn.DataTable.isDataTable('#' + tableId)) {
      _dtInstances[tableId].columns.adjust().draw(false);
    }
  });
}

// ---------------------------------------------------------------
//  Chart Data — state & constants
// ---------------------------------------------------------------

let ketepatanData   = [];
let statusData      = [];
let verifChart      = { konsumen_terverifikasi: 0, konsumen_belum: 0, sosmed_terverifikasi: 0, sosmed_belum: 0 };
let eskalasiDonut   = { sudah: 0, belum: 0 };
let trendLabels     = [];
let trendData       = [];
let totalEsk        = 0;
let _verifDonutData = [0, 0];

const BASE_URL     = '<?= base_url() ?>';
const filterGlobal = {
  date_from: '<?= $filter['date_from'] ?>',
  date_to:   '<?= $filter['date_to'] ?>',
  sumber:    '<?= $filter['sumber'] ?>',
  divisi:    '<?= $filter['divisi'] ?>',
};

const _chartInstances = {};

// ---------------------------------------------------------------
//  Chart Rendering
// ---------------------------------------------------------------

function initAllCharts() {
  Object.keys(_chartInstances).forEach(key => {
    if (_chartInstances[key]) { _chartInstances[key].destroy(); _chartInstances[key] = null; }
  });

  // Verifikasi — stacked bar per sumber
  const verifLabels = [];
  const verifTerverifikasiData = [];
  const verifBelumData = [];

  if (filterGlobal.sumber !== 'sosmed') {
    verifLabels.push('Dari Konsumen');
    verifTerverifikasiData.push(verifChart.konsumen_terverifikasi);
    verifBelumData.push(verifChart.konsumen_belum);
  }
  if (filterGlobal.sumber !== 'konsumen') {
    verifLabels.push('Dari Sosmed');
    verifTerverifikasiData.push(verifChart.sosmed_terverifikasi);
    verifBelumData.push(verifChart.sosmed_belum);
  }

  _chartInstances.verifSumber = new Chart('chartVerifSumber', {
    type: 'bar',
    data: {
      labels: verifLabels,
      datasets: [
        { label: 'Terverifikasi', data: verifTerverifikasiData, backgroundColor: '#0E9F6E', borderRadius: 6 },
        { label: 'Belum Verif.',  data: verifBelumData,         backgroundColor: '#E02424', borderRadius: 6 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true, position: 'bottom',
          labels: { boxWidth: 10, font: { size: 11 }, usePointStyle: true, padding: 12, cursor: 'pointer' },
          onClick: (e) => { e.native.stopImmediatePropagation(); openModal('verif_total'); }
        }
      },
      scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { color: '#E4E8F0' } } },
      onClick: (e, els) => { if (els.length) openModal('verif_total'); }
    }
  });

  // Verifikasi — pie chart
  _chartInstances.verifDonut = new Chart('chartVerifDonut', {
    type: 'pie',
    data: {
      labels: ['Terverifikasi', 'Belum Terverifikasi'],
      datasets: [{ data: _verifDonutData, backgroundColor: ['#0E9F6E', '#E02424'], borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
      onClick: (e, els) => { if (els.length) openModal('verif_total'); }
    }
  });

  // Eskalasi — trend line
  _chartInstances.eskalasiTrend = new Chart('chartEskalasiTrend', {
    type: 'line',
    data: {
      labels: trendLabels,
      datasets: [{
        label: 'Eskalasi', data: trendData,
        borderColor: '#1A56DB', backgroundColor: 'rgba(26,86,219,.08)',
        tension: .4, fill: true, pointRadius: 4, pointHoverRadius: 7,
        pointBackgroundColor: '#fff', pointBorderColor: '#1A56DB', pointBorderWidth: 2,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { grid: { color: '#E4E8F0' }, ticks: { font: { size: 10 } } }
      }
    }
  });

  // Eskalasi — pie chart
  _chartInstances.eskalasiDonut = new Chart('chartEskalasiDonut', {
    type: 'pie',
    data: {
      labels: ['Sudah Eskalasi', 'Belum Eskalasi'],
      datasets: [{ data: [eskalasiDonut.sudah, eskalasiDonut.belum], backgroundColor: ['#0E9F6E', '#D97706'], borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      onClick: (e, els) => {
        if (els.length) {
          openModal('esk_gabungan');
          setTimeout(() => setSumberEskalasiFilter(els[0].index === 0 ? 'sudah' : 'belum'), 100);
        }
      }
    }
  });

  // Ketepatan Waktu — horizontal bar
  _chartInstances.ketepatan = new Chart('chartKetepatan', {
    type: 'bar',
    data: {
      labels: ketepatanData.map(d => d.label),
      datasets: [
        { label: 'On Time', data: ketepatanData.map(d => d.ontime), backgroundColor: '#0E9F6E', borderRadius: 4 },
        { label: 'Late',    data: ketepatanData.map(d => d.late),   backgroundColor: '#E02424', borderRadius: 4 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false, indexAxis: 'y',
      plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
      scales: {
        x: { grid: { color: '#E4E8F0' }, ticks: { font: { size: 10 } } },
        y: { grid: { display: false }, ticks: { font: { size: 10 } } }
      },
      onClick: (e, els) => {
        if (els.length) {
          openModal('divisi', {
            divisi: ketepatanData[els[0].index].divisi,
            ketepatan_type: els[0].datasetIndex === 0 ? 'ontime' : 'late'
          });
        }
      }
    }
  });

  _renderKetepatanList();

  // Status Komplain — pie chart
  _chartInstances.status = new Chart('chartStatus', {
    type: 'pie',
    data: {
      labels: statusData.map(d => d.label),
      datasets: [{ data: statusData.map(d => d.qty), backgroundColor: statusData.map(d => d.color), borderWidth: 2, borderColor: '#fff', hoverOffset: 8 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 10 } } },
      onClick: (e, els) => { if (els.length) openModal('status', { status_id: statusData[els[0].index].id }); }
    }
  });

  _renderStatusList();
}

function _renderKetepatanList() {
  const el = document.getElementById('ketepatanList');
  if (!el) return;
  el.innerHTML = '';
  ketepatanData.forEach(d => {
    const pct = d.pct, isGreen = pct >= 80;
    const div = document.createElement('div');
    div.className = 'status-item';
    div.onclick = () => openModal('divisi', { divisi: d.divisi });
    div.innerHTML = `
      <div class="status-dot-sm" style="background:${isGreen ? '#0E9F6E' : '#E02424'}"></div>
      <div class="status-name" style="font-size:12px">${d.label}</div>
      <div class="flex-grow-1 mx-2">
        <div class="progress" style="height:6px">
          <div class="progress-bar ${isGreen ? 'bg-success' : 'bg-danger'}" style="width:${pct}%"></div>
        </div>
      </div>
      <div class="prog-pct" style="color:${isGreen ? '#0E9F6E' : '#E02424'}">${pct}%</div>`;
    el.appendChild(div);
  });
}

function _renderStatusList() {
  const el = document.getElementById('statusList');
  if (!el) return;
  el.innerHTML = '';
  statusData.forEach(s => {
    const pct = totalEsk > 0 ? Math.round(s.qty / totalEsk * 100) : 0;
    const div = document.createElement('div');
    div.className = 'status-item';
    div.onclick = () => openModal('status', { status_id: s.id });
    div.innerHTML = `
      <div class="status-dot-sm" style="background:${s.color}"></div>
      <div class="status-name">${s.label}</div>
      <div class="status-qty">${s.qty.toLocaleString('id')}</div>
      <div class="status-pct">${pct}%</div>
      <span class="badge-status badge-${s.badge}">${s.badge === 'done' ? '✓' : '→'}</span>`;
    el.appendChild(div);
  });
}

// ---------------------------------------------------------------
//  Chart Data — AJAX loader
// ---------------------------------------------------------------

function loadChartDataViaAjax() {
  const params = new URLSearchParams({
    date_from: filterGlobal.date_from, date_to: filterGlobal.date_to,
    sumber: filterGlobal.sumber, divisi_filter: filterGlobal.divisi,
  });

  fetch(BASE_URL + 'dash_crm/chart_data?' + params.toString(), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
      if (!res.success) throw new Error(res.error || 'Unknown error');
      const d = res.data;
      ketepatanData   = d.ketepatan     || [];
      statusData      = d.status        || [];
      verifChart      = d.verifChart    || verifChart;
      eskalasiDonut   = d.eskalasiDonut || eskalasiDonut;
      trendLabels     = d.trendLabels   || [];
      trendData       = d.trendData     || [];
      totalEsk        = d.totalEskalasi || 0;
      _verifDonutData = [d.terverifikasi || 0, d.belumVerifikasi || 0];
      initAllCharts();
    })
    .catch(err => {
      console.warn('[Chart] AJAX load failed, rendering empty charts:', err.message);
      initAllCharts();
    });
}

// Boot
loadChartDataViaAjax();

// ---------------------------------------------------------------
//  Detail Modal — state & config
// ---------------------------------------------------------------

const bsModal = new bootstrap.Modal(document.getElementById('detailModal'));
_fixDTColumnsOnModalShown('detailModal', 'dtModal');

let _currentModal   = {};
let _mfSumber       = 'all';
let _mfStatus       = 'all';
let _mfStatusVal1   = '';
let _mfStatusVal2   = '';
let _mfDueDateFrom  = '';
let _mfDueDateTo    = '';
let _mfDoneDateFrom = '';
let _mfDoneDateTo   = '';

// Types where sumber & status are already encoded in the type name
const _ringkasanTypes = [
  'verif_konsumen', 'verif_konsumen_belum', 'verif_sosmed_v', 'verif_sosmed_b',
  'esk_konsumen_sudah', 'esk_konsumen_belum', 'esk_sosmed_sudah', 'esk_sosmed_belum'
];

// Maps modal type prefix to filter checkbox labels & values
const _mfStatusConfig = {
  verif:     { label: 'Verifikasi:', l1: 'Sudah',   l2: 'Belum', v1: 'verified', v2: 'unverified' },
  esk:       { label: 'Eskalasi:',   l1: 'Sudah',   l2: 'Belum', v1: 'sudah',    v2: 'belum' },
  ketepatan: { label: 'Status:',     l1: 'On Time', l2: 'Late',  v1: 'ontime',   v2: 'late' },
  divisi:    { label: 'Status:',     l1: 'On Time', l2: 'Late',  v1: 'ontime',   v2: 'late' },
};

function _getStatusConfig(type) {
  if (type.startsWith('verif'))   return _mfStatusConfig.verif;
  if (type.startsWith('esk'))     return _mfStatusConfig.esk;
  if (type === 'ketepatan_total') return _mfStatusConfig.ketepatan;
  if (type === 'divisi')          return _mfStatusConfig.divisi;
  return null;
}

// ---------------------------------------------------------------
//  Detail Modal — open & filter handlers
// ---------------------------------------------------------------

function openModal(type, extra = {}) {
  _currentModal = { type, extra };
  _mfSumber = 'all';
  _mfStatus = 'all';
  _mfDueDateFrom = _mfDueDateTo = '';
  _mfDoneDateFrom = _mfDoneDateTo = '';

  const modalFilters    = document.getElementById('modalFilters');
  const statusGroup     = document.getElementById('mfStatusGroup');
  const sumberStatusRow = document.getElementById('mfSumberStatusRow');
  const separator       = document.getElementById('mfSeparator');

  document.getElementById('mfSemua').checked   = true;
  document.getElementById('mfKonsumen').checked = false;
  document.getElementById('mfSosmed').checked   = false;
  document.getElementById('mfStatus1').checked  = false;
  document.getElementById('mfStatus2').checked  = false;
  document.getElementById('mfDateRange').value  = '';

  const cfg = _getStatusConfig(type);

  if (_ringkasanTypes.includes(type)) {
    modalFilters.style.display = 'block';
    sumberStatusRow.classList.add('d-none');
    separator.classList.add('d-none');
    statusGroup.classList.add('d-none');
    _mfStatusVal1 = _mfStatusVal2 = '';
  } else if (type === 'esk_sudah' || type === 'esk_belum') {
    modalFilters.style.display = 'block';
    sumberStatusRow.classList.remove('d-none');
    separator.classList.remove('d-none');
    statusGroup.classList.add('d-none');
    _mfStatusVal1 = _mfStatusVal2 = '';
  } else if (cfg) {
    modalFilters.style.display = 'block';
    sumberStatusRow.classList.remove('d-none');
    separator.classList.remove('d-none');
    statusGroup.classList.remove('d-none');
    document.getElementById('mfStatusLabel').textContent  = cfg.label;
    document.getElementById('mfStatus1Label').textContent = cfg.l1;
    document.getElementById('mfStatus2Label').textContent = cfg.l2;
    _mfStatusVal1 = cfg.v1;
    _mfStatusVal2 = cfg.v2;
  } else if (type === 'status') {
    modalFilters.style.display = 'block';
    sumberStatusRow.classList.remove('d-none');
    separator.classList.remove('d-none');
    statusGroup.classList.add('d-none');
    _mfStatusVal1 = _mfStatusVal2 = '';
  } else {
    modalFilters.style.display = 'none';
  }

  document.getElementById('modalTitle').textContent    = 'Memuat data...';
  document.getElementById('modalContent').innerHTML    = '';
  document.getElementById('modalPagination').innerHTML = '';
  document.getElementById('modalLoading').style.display = 'block';
  bsModal.show();
  loadModalPage(1);
}

function toggleModalFilterAll(checkbox) {
  if (checkbox.checked) {
    document.getElementById('mfKonsumen').checked = false;
    document.getElementById('mfSosmed').checked   = false;
    document.getElementById('mfStatus1').checked  = false;
    document.getElementById('mfStatus2').checked  = false;
    _mfSumber = 'all';
    _mfStatus = 'all';
  }
  loadModalPage(1);
}

function _checkResetToAll() {
  if (!document.getElementById('mfKonsumen').checked &&
      !document.getElementById('mfSosmed').checked &&
      !document.getElementById('mfStatus1').checked &&
      !document.getElementById('mfStatus2').checked) {
    document.getElementById('mfSemua').checked = true;
  }
}

function selectSumber(checkbox, value) {
  if (checkbox.checked) {
    document.getElementById(value === 'konsumen' ? 'mfSosmed' : 'mfKonsumen').checked = false;
    document.getElementById('mfSemua').checked = false;
    _mfSumber = value;
  } else {
    _mfSumber = 'all';
    _checkResetToAll();
  }
  loadModalPage(1);
}

function selectStatus(checkbox, idx) {
  if (checkbox.checked) {
    document.getElementById(idx === 1 ? 'mfStatus2' : 'mfStatus1').checked = false;
    document.getElementById('mfSemua').checked = false;
    _mfStatus = idx === 1 ? _mfStatusVal1 : _mfStatusVal2;
  } else {
    _mfStatus = 'all';
    _checkResetToAll();
  }
  loadModalPage(1);
}

// ---------------------------------------------------------------
//  Detail Modal — daterangepicker
// ---------------------------------------------------------------

$(function() {
  $('#mfDateRange').daterangepicker({
    autoUpdateInput: false,
    locale: _datePickerLocale(),
    ranges: _datePickerRanges(),
    alwaysShowCalendars: true,
    opens: 'right'
  });

  $('#mfDateRange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    _mfDueDateFrom  = picker.startDate.format('YYYY-MM-DD');
    _mfDueDateTo    = picker.endDate.format('YYYY-MM-DD');
    _mfDoneDateFrom = _mfDoneDateTo = '';
    _showModalLoading();
    loadModalPage(1);
  });
});

function resetModalDateFilter() {
  _mfDueDateFrom = _mfDueDateTo = '';
  _mfDoneDateFrom = _mfDoneDateTo = '';
  document.getElementById('mfDateRange').value = '';
  _showModalLoading();
  loadModalPage(1);
}

function _showModalLoading() {
  document.getElementById('modalContent').innerHTML    = '';
  document.getElementById('modalPagination').innerHTML = '';
  document.getElementById('modalLoading').style.display = 'block';
}

// ---------------------------------------------------------------
//  Detail Modal — AJAX data loader
// ---------------------------------------------------------------

function loadModalPage(page) {
  const isRingkasan = _ringkasanTypes.includes(_currentModal.type);

  const params = new URLSearchParams({
    type: _currentModal.type,
    status_id: _currentModal.extra?.status_id || '',
    divisi:    _currentModal.extra?.divisi    || '',
    page: 1, per_page: 2000,
    date_from: filterGlobal.date_from, date_to: filterGlobal.date_to,
    sumber: filterGlobal.sumber, divisi_filter: filterGlobal.divisi,
    mf_due_date_from: _mfDueDateFrom, mf_due_date_to: _mfDueDateTo,
    mf_done_date_from: _mfDoneDateFrom, mf_done_date_to: _mfDoneDateTo,
  });

  if (!isRingkasan) {
    params.set('mf_sumber', _mfSumber);
    params.set('mf_status', _mfStatus);
  }

  fetch(BASE_URL + 'dash_crm/modal_detail?' + params.toString(), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`); return r.json(); })
    .then(res => {
      document.getElementById('modalLoading').style.display = 'none';
      document.getElementById('modalPagination').innerHTML  = '';

      if (!res.success) {
        document.getElementById('modalContent').innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
        return;
      }

      document.getElementById('modalTitle').textContent = _getModalTitle(_currentModal.type, res.total);

      if (res.data.length === 0) {
        document.getElementById('modalContent').innerHTML = '<p class="text-muted text-center py-4">Tidak ada data.</p>';
        return;
      }

      const jenisHeader = ['divisi', 'ketepatan_total'].includes(_currentModal.type) ? 'Kategori' : 'Jenis';
      document.getElementById('modalContent').innerHTML =
        '<table id="dtModal" class="table table-sm modal-table align-middle" style="width:100%"></table>';

      _initDT('dtModal', [
        { title: 'No. Komplain', data: 'id_task',
          render: (d, t) => t === 'display' ? `<code style="font-size:11px">${d}</code>` : d },
        { title: 'Konsumen',  data: 'konsumen', defaultContent: '-' },
        { title: 'Lokasi',    data: 'lokasi',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: jenisHeader, data: 'jenis',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Due Date',  data: 'due_date',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Done Date', data: 'done_date',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Status', data: 'status',
          render: (d, t, row) => t === 'display'
            ? `<span class="badge-status badge-${getBadgeClass(row.status_id)}">${d || '-'}</span>`
            : (d || '-') },
        { title: 'Waktu', data: 'waktu_status',
          render: (d, t) => {
            if (t !== 'display') return d;
            const cls = d === 'On Time' ? 'ontime' : d === 'Late' ? 'late' : 'working';
            return `<span class="badge-status badge-${cls}">${d}</span>`;
          }},
      ], res.data);
    })
    .catch(err => {
      console.error('Modal Error:', err);
      document.getElementById('modalLoading').style.display = 'none';
      document.getElementById('modalContent').innerHTML = '<p class="text-danger">Koneksi error: ' + err.message + '</p>';
    });
}

const _modalTitleMap = {
  verif_total:          'Total Komplain — Drill-Down',
  verif_terverifikasi:  'Komplain Terverifikasi',
  verif_belum:          'Belum Terverifikasi',
  verif_konsumen:       'Konsumen Terverifikasi',
  verif_konsumen_belum: 'Konsumen Belum Verifikasi',
  verif_sosmed_v:       'Sosmed Terverifikasi',
  verif_sosmed_b:       'Sosmed Belum Verifikasi',
  esk_sudah:            'Sudah Eskalasi',
  esk_belum:            'Belum Eskalasi',
  esk_gabungan:         'Data Eskalasi',
  esk_konsumen_sudah:   'Konsumen — Sudah Eskalasi',
  esk_konsumen_belum:   'Konsumen — Belum Eskalasi',
  esk_sosmed_sudah:     'Sosmed — Sudah Eskalasi',
  esk_sosmed_belum:     'Sosmed — Belum Eskalasi',
  status:               'Status Komplain',
  ketepatan_total:      'Total Komplain — Ketepatan Waktu',
};

function _getModalTitle(type, total) {
  const fmt = total.toLocaleString('id');
  if (type === 'divisi') return `Divisi ${_currentModal.extra?.divisi || ''} (${fmt})`;
  return (_modalTitleMap[type] || 'Detail Komplain') + ` (${fmt})`;
}

function getBadgeClass(status_id) {
  const map = { 1: 'waiting', 2: 'waiting', 3: 'reject', 4: 'working', 5: 'reject', 6: 'done', 7: 'reject', 8: 'waiting', 9: 'waiting' };
  return map[status_id] || 'working';
}

// Legacy stubs — DataTables handles pagination now
function renderPagination() {}

// Export button
if (document.getElementById('btnExport')) {
  document.getElementById('btnExport').addEventListener('click', () => _exportDT('dtModal'));
}

// ---------------------------------------------------------------
//  Ketepatan Global Modal
// ---------------------------------------------------------------

const bsKetepatanModal = new bootstrap.Modal(document.getElementById('ketepatanGlobalModal'));
_fixDTColumnsOnModalShown('ketepatanGlobalModal', 'dtKetepatan');

let _ketepatanGlobalFilter = 'all';
let _kgDueDateFrom  = '';
let _kgDueDateTo    = '';
let _kgDoneDateFrom = '';
let _kgDoneDateTo   = '';

function openKetepatanGlobal(initialFilter = 'all') {
  _ketepatanGlobalFilter = initialFilter;
  _kgDueDateFrom = _kgDueDateTo = _kgDoneDateFrom = _kgDoneDateTo = '';
  document.getElementById('kgDueDateRange').value  = '';
  document.getElementById('kgDoneDateRange').value = '';
  document.getElementById('ketepatanGlobalContent').innerHTML    = '';
  document.getElementById('ketepatanGlobalPagination').innerHTML = '';
  document.getElementById('ketepatanGlobalLoading').style.display = 'block';

  const titles = { all: 'Ketepatan Waktu Pengerjaan — Detail Semua Divisi', ontime: 'Ketepatan Waktu — Data On Time', late: 'Ketepatan Waktu — Data Late / Terlambat' };
  document.querySelector('#ketepatanGlobalModal .modal-title').textContent = titles[initialFilter] || titles.all;

  bsKetepatanModal.show();
  loadKetepatanGlobalPage(1);
}

function loadKetepatanGlobalPage(page) {
  const params = new URLSearchParams({
    page: 1, per_page: 2000,
    ketepatan: _ketepatanGlobalFilter,
    date_from: filterGlobal.date_from, date_to: filterGlobal.date_to,
    sumber: filterGlobal.sumber, divisi: filterGlobal.divisi,
    due_date_from: _kgDueDateFrom, due_date_to: _kgDueDateTo,
    done_date_from: _kgDoneDateFrom, done_date_to: _kgDoneDateTo,
  });

  fetch(BASE_URL + 'dash_crm/ketepatan_global?' + params.toString(), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`); return r.json(); })
    .then(res => {
      document.getElementById('ketepatanGlobalLoading').style.display = 'none';
      document.getElementById('ketepatanGlobalPagination').innerHTML  = '';

      if (!res.success) {
        document.getElementById('ketepatanGlobalContent').innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
        return;
      }
      if (res.data.length === 0) {
        document.getElementById('ketepatanGlobalContent').innerHTML = '<p class="text-muted text-center py-4">Tidak ada data.</p>';
        return;
      }

      document.getElementById('ketepatanGlobalContent').innerHTML =
        '<table id="dtKetepatan" class="table table-sm modal-table align-middle" style="width:100%"></table>';

      _initDT('dtKetepatan', [
        { title: 'No. Komplain', data: 'id_task',
          render: (d, t) => t === 'display' ? `<code style="font-size:11px">${d}</code>` : d },
        { title: 'Konsumen', data: 'konsumen',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Lokasi', data: 'lokasi',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Divisi', data: 'divisi',
          render: (d, t) => t === 'display' ? `<small><strong>${d || '-'}</strong></small>` : (d || '-') },
        { title: 'Kategori', data: 'jenis',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Due Date', data: 'due_date',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Done Date', data: 'done_date',
          render: (d, t) => t === 'display' ? `<small>${d || '-'}</small>` : (d || '-') },
        { title: 'Status', data: 'waktu_status',
          render: (d, t) => {
            if (t !== 'display') return d;
            const cls = d === 'On Time' ? 'ontime' : d === 'Late' ? 'late' : 'working';
            return `<span class="badge-status badge-${cls}">${d}</span>`;
          }},
      ], res.data);
    })
    .catch(err => {
      console.error('Ketepatan Global Error:', err);
      document.getElementById('ketepatanGlobalLoading').style.display = 'none';
      document.getElementById('ketepatanGlobalContent').innerHTML = '<p class="text-danger">Koneksi error: ' + err.message + '</p>';
    });
}

// Ketepatan daterangepickers
$(function() {
  const opts = {
    autoUpdateInput: false,
    locale: _datePickerLocale(),
    ranges: _datePickerRanges(),
    alwaysShowCalendars: true,
    opens: 'right'
  };

  $('#kgDueDateRange').daterangepicker(opts);
  $('#kgDueDateRange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    _kgDueDateFrom = picker.startDate.format('YYYY-MM-DD');
    _kgDueDateTo   = picker.endDate.format('YYYY-MM-DD');
    _showKetepatanLoading();
    loadKetepatanGlobalPage(1);
  });

  $('#kgDoneDateRange').daterangepicker(opts);
  $('#kgDoneDateRange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    _kgDoneDateFrom = picker.startDate.format('YYYY-MM-DD');
    _kgDoneDateTo   = picker.endDate.format('YYYY-MM-DD');
    _showKetepatanLoading();
    loadKetepatanGlobalPage(1);
  });
});

function resetKetepatanDateFilter() {
  _kgDueDateFrom = _kgDueDateTo = _kgDoneDateFrom = _kgDoneDateTo = '';
  document.getElementById('kgDueDateRange').value  = '';
  document.getElementById('kgDoneDateRange').value = '';
  _showKetepatanLoading();
  loadKetepatanGlobalPage(1);
}

function _showKetepatanLoading() {
  document.getElementById('ketepatanGlobalContent').innerHTML    = '';
  document.getElementById('ketepatanGlobalPagination').innerHTML = '';
  document.getElementById('ketepatanGlobalLoading').style.display = 'block';
}

function renderKetepatanGlobalPagination() {}

if (document.getElementById('btnKetepatanExport')) {
  document.getElementById('btnKetepatanExport').addEventListener('click', () => _exportDT('dtKetepatan'));
}

// ---------------------------------------------------------------
//  Rating Drilldown Modal
// ---------------------------------------------------------------

const bsRatingModal = new bootstrap.Modal(document.getElementById('ratingDrilldownModal'));
_fixDTColumnsOnModalShown('ratingDrilldownModal', 'dtRating');

let _ratingBintangFilter = null;

function starBadge(val) {
  if (val === null || val === undefined || val === '') return '<span class="text-muted">—</span>';
  const n = parseFloat(val);
  const color = n >= 4 ? '#0E9F6E' : n >= 3 ? '#D97706' : '#E02424';
  return `<span style="font-weight:700;color:${color};font-family:monospace">${n.toFixed(1)} ⭐</span>`;
}

function _renderRatingStarFilter() {
  const btns = [null, 5, 4, 3, 2, 1].map(b => {
    const active = _ratingBintangFilter === b;
    const label  = b === null ? 'Semua ⭐' : `${b} ⭐`;
    const cls    = active ? 'btn-warning' : 'btn-outline-secondary';
    const onclick = b === null ? 'openRatingDrilldown(null)' : `openRatingDrilldown(${b})`;
    return `<button class="btn btn-xs ${cls}" style="font-size:11px;padding:2px 8px;border-radius:100px" onclick="${onclick}">${label}</button>`;
  });
  document.getElementById('ratingStarFilter').innerHTML = btns.join('');
}

function openRatingDrilldown(bintang) {
  _ratingBintangFilter = bintang === null || bintang === undefined ? null : parseInt(bintang);

  document.getElementById('ratingDrilldownContent').innerHTML    = '';
  document.getElementById('ratingDrilldownPagination').innerHTML = '';
  document.getElementById('ratingDrilldownLoading').style.display = 'block';
  document.getElementById('ratingDrilldownTitle').textContent =
    _ratingBintangFilter !== null
      ? `Detail Rating Konsumen — ${_ratingBintangFilter} ⭐`
      : 'Detail Rating Konsumen — Semua';

  _renderRatingStarFilter();
  bsRatingModal.show();
  loadRatingDrilldownPage(1);
}

function loadRatingDrilldownPage(page) {
  const params = new URLSearchParams({
    page: 1, per_page: 2000,
    date_from: filterGlobal.date_from, date_to: filterGlobal.date_to,
    sumber: filterGlobal.sumber, divisi: filterGlobal.divisi,
  });
  if (_ratingBintangFilter !== null) params.set('bintang', _ratingBintangFilter);

  fetch(BASE_URL + 'dash_crm/rating_drilldown?' + params.toString(), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(res => {
      document.getElementById('ratingDrilldownLoading').style.display = 'none';
      document.getElementById('ratingDrilldownPagination').innerHTML  = '';

      if (!res.success) {
        document.getElementById('ratingDrilldownContent').innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
        return;
      }

      _renderRatingStarFilter();

      if (res.data.length === 0) {
        document.getElementById('ratingDrilldownContent').innerHTML = '<p class="text-muted text-center py-4">Tidak ada data rating.</p>';
        return;
      }

      document.getElementById('ratingDrilldownContent').innerHTML =
        '<table id="dtRating" class="table table-sm modal-table align-middle" style="width:100%"></table>';

      _initDT('dtRating', [
        { title: 'No. Komplain', data: 'id_task',
          render: (d, t) => t === 'display' ? `<code style="font-size:11px">${d}</code>` : d },
        { title: 'Konsumen', data: 'konsumen', defaultContent: '-' },
        { title: 'Project',  data: 'project',  defaultContent: '-' },
        { title: 'Blok',     data: 'blok',     defaultContent: '-' },
        { title: 'Jenis',    data: 'jenis',    defaultContent: '-' },
        { title: '⭐ Avg',   data: 'avg_rating',
          render: (d, t) => t === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Pelayanan', data: 'pelayanan',
          render: (d, t) => t === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Kualitas', data: 'kualitas',
          render: (d, t) => t === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Respons', data: 'respons',
          render: (d, t) => t === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Feedback', data: 'feedback', defaultContent: '-' },
      ], res.data);
    })
    .catch(err => {
      document.getElementById('ratingDrilldownLoading').style.display = 'none';
      document.getElementById('ratingDrilldownContent').innerHTML = `<p class="text-danger">Koneksi error: ${err.message}</p>`;
    });
}

function renderRatingDrilldownPagination() {}

if (document.getElementById('btnRatingExport')) {
  document.getElementById('btnRatingExport').addEventListener('click', () => _exportDT('dtRating'));
}

// ---------------------------------------------------------------
//  Global Filter Bar — daterangepicker & form submit
// ---------------------------------------------------------------

$(function() {
  $('#dateRangePicker').daterangepicker({
    startDate: moment('<?php echo $filter['date_from']; ?>'),
    endDate:   moment('<?php echo $filter['date_to']; ?>'),
    locale: _datePickerLocale(),
    ranges: {
      'Year to Date': [moment().startOf('year'), moment()],
      ..._datePickerRanges(),
    },
    alwaysShowCalendars: true,
    opens: 'right'
  }, function(start, end) {
    $('#dateFrom').val(start.format('YYYY-MM-DD'));
    $('#dateTo').val(end.format('YYYY-MM-DD'));
    applyFilter();
  });
});

function applyFilter() {
  const f = document.getElementById('dateFrom').value;
  const t = document.getElementById('dateTo').value;
  const s = document.getElementById('filterSumber').value;
  const d = document.getElementById('filterDivisi').value;
  window.location.href = BASE_URL + 'dash_crm?date_from=' + f + '&date_to=' + t + '&sumber=' + s + '&divisi=' + d;
}

function resetFilter() {
  const today = moment().format('YYYY-MM-DD');
  const jan1  = new Date().getFullYear() + '-01-01';
  window.location.href = BASE_URL + 'dash_crm?date_from=' + jan1 + '&date_to=' + today + '&sumber=all&divisi=all';
}

// ---------------------------------------------------------------
//  Shared daterangepicker config — avoids repeating locale/ranges
// ---------------------------------------------------------------

function _datePickerLocale() {
  return {
    format: 'DD/MM/YYYY',
    applyLabel: 'Terapkan',
    cancelLabel: 'Batal',
    customRangeLabel: 'Custom',
    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    firstDay: 1
  };
}

function _datePickerRanges() {
  return {
    'Today':       [moment(), moment()],
    'Yesterday':   [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    'Last 30 Days':[moment().subtract(29, 'days'), moment()],
    'This Month':  [moment().startOf('month'), moment().endOf('month')],
    'Last Month':  [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
  };
}
</script>
</body>
</html>
