<!-- ============================================================ -->
<!-- MODAL KETEPATAN WAKTU GLOBAL                                 -->
<!-- ============================================================ -->
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

<!-- ============================================================ -->
<!-- MODAL DETAIL KOMPLAIN                                        -->
<!-- ============================================================ -->
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
          <!-- Baris 1: Filter Sumber & Status -->
          <div class="d-flex flex-wrap gap-3 align-items-center" id="mfSumberStatusRow">
            <label class="fw-semibold text-secondary" style="font-size:12px;margin:0">Filter:</label>
            <!-- Filter Sumber -->
            <div class="d-flex align-items-center gap-2">
              <label style="font-size:12px;color:#96A3B7;margin:0;white-space:nowrap">Sumber:</label>
              <div class="d-flex gap-2">
                <label style="margin:0"><input type="checkbox" id="mfSemua" checked onchange="toggleModalFilterAll(this)"> <span style="font-size:12px">Semua</span></label>
                <label style="margin:0"><input type="checkbox" id="mfKonsumen" onchange="selectSumber(this,'konsumen')"> <span style="font-size:12px">Konsumen</span></label>
                <label style="margin:0"><input type="checkbox" id="mfSosmed" onchange="selectSumber(this,'sosmed')"> <span style="font-size:12px">Sosmed</span></label>
              </div>
            </div>
            <!-- Filter Status (dynamic label) -->
            <div class="d-flex align-items-center gap-2" id="mfStatusGroup">
              <label style="font-size:12px;color:#96A3B7;margin:0;white-space:nowrap" id="mfStatusLabel">Status:</label>
              <div class="d-flex gap-2">
                <label style="margin:0"><input type="checkbox" id="mfStatus1" onchange="selectStatus(this,1)"> <span style="font-size:12px" id="mfStatus1Label">-</span></label>
                <label style="margin:0"><input type="checkbox" id="mfStatus2" onchange="selectStatus(this,2)"> <span style="font-size:12px" id="mfStatus2Label">-</span></label>
              </div>
            </div>
          </div>
          <!-- Garis pemisah -->
          <hr id="mfSeparator" style="margin:10px 0;border:0;border-top:1px solid #E4E8F0">
          <!-- Baris 2: Filter Tanggal -->
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

<!-- ============================================================ -->
<!-- MODAL DRILLDOWN RATING KONSUMEN                             -->
<!-- ============================================================ -->
<div class="modal fade" id="ratingDrilldownModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="ratingDrilldownTitle">Detail Rating Konsumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="ratingDrilldownBody">
        <!-- Filter bintang — tampil di bagian atas modal -->
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

<!-- ============================================================ -->
<!-- DATATABLES + BUTTONS + JSZIP                                -->
<!-- ============================================================ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>

<script>
Chart.defaults.font.family = "system-ui, sans-serif";
Chart.defaults.plugins.legend.display = false;

// ============================================================
// CLOCK (if header element exists)
// ============================================================
function updateClock() {
  const clockEl = document.getElementById('clock');
  if (clockEl) {
    clockEl.textContent = new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
  }
}
setInterval(updateClock,1000); updateClock();

// ============================================================
// DATATABLES — Global instances, language, dan helper
// ============================================================
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

/**
 * _initDT — Init (atau re-init) instance DataTables.
 * @param {string} tableId  — id <table> element (tanpa #)
 * @param {Array}  columns  — kolom DataTables
 * @param {Array}  data     — array of row objects
 * @returns DataTables instance
 */
function _initDT(tableId, columns, data) {
  if (_dtInstances[tableId] && $.fn.DataTable.isDataTable('#' + tableId)) {
    _dtInstances[tableId].destroy();
    $('#' + tableId).empty();
  }
  _dtInstances[tableId] = $('#' + tableId).DataTable({
    data:       data,
    columns:    columns,
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100, { label: 'Semua', value: -1 }],
    order:      [],
    scrollX:    true,
    language:   _dtLang,
    dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [{
      extend:        'excelHtml5',
      text:          'Export Excel',
      title:         'dashboard-crm-export',
      className:     'btn-dt-excel d-none',
      exportOptions: { columns: ':visible', orthogonal: 'export' }
    }],
  });
  return _dtInstances[tableId];
}

// ============================================================
// DATA CHART — dimuat via AJAX (fallback: set false untuk inline)
// ============================================================
const USE_AJAX_CHART = true;

let ketepatanData = [];
let statusData    = [];
let verifChart    = {konsumen_terverifikasi:0,konsumen_belum:0,sosmed_terverifikasi:0,sosmed_belum:0};
let eskalasiDonut = {sudah:0,belum:0};
let trendLabels   = [];
let trendData     = [];
let totalEsk      = 0;
let _verifDonutData = [0,0];

const BASE_URL      = '<?= base_url() ?>';
const filterGlobal  = {
  date_from: '<?= $filter['date_from'] ?>',
  date_to:   '<?= $filter['date_to'] ?>',
  sumber:    '<?= $filter['sumber'] ?>',
  divisi:    '<?= $filter['divisi'] ?>',
};

// Track chart instances untuk destroy sebelum re-init
const _chartInstances = {};

// ============================================================
// INIT SEMUA CHART — bisa dipanggil ulang setelah AJAX load
// ============================================================
function initAllCharts() {
  // Destroy existing chart instances
  Object.keys(_chartInstances).forEach(key => {
    if (_chartInstances[key]) { _chartInstances[key].destroy(); _chartInstances[key] = null; }
  });

  // --- SECTION 01: VERIFIKASI ---
  const verifLabels = [];
  const verifTerverifikasiData = [];
  const verifBelumData = [];
  const verifBarTypes = [];

  if (filterGlobal.sumber !== 'sosmed') {
    verifLabels.push('Dari Konsumen');
    verifTerverifikasiData.push(verifChart.konsumen_terverifikasi);
    verifBelumData.push(verifChart.konsumen_belum);
    verifBarTypes.push('konsumen');
  }
  if (filterGlobal.sumber !== 'konsumen') {
    verifLabels.push('Dari Sosmed');
    verifTerverifikasiData.push(verifChart.sosmed_terverifikasi);
    verifBelumData.push(verifChart.sosmed_belum);
    verifBarTypes.push('sosmed');
  }

  _chartInstances.verifSumber = new Chart('chartVerifSumber', {
    type: 'bar',
    data: {
      labels: verifLabels,
      datasets: [
        { label:'Terverifikasi', data:verifTerverifikasiData, backgroundColor:'#0E9F6E', borderRadius:6 },
        { label:'Belum Verif.',  data:verifBelumData,         backgroundColor:'#E02424', borderRadius:6 },
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{
          display:true,
          position:'bottom',
          labels:{
            boxWidth:10,
            font:{size:11},
            usePointStyle:true,
            padding:12,
            cursor:'pointer'
          },
          onClick:(e, legendItem, chart) => {
            e.native.stopImmediatePropagation();
            openModal('verif_total');
          }
        }
      },
      scales:{ x:{stacked:true,grid:{display:false}}, y:{stacked:true,grid:{color:'#E4E8F0'}} },
      onClick:(e,els)=>{
        if(els.length) openModal('verif_total');
      }
    }
  });

  _chartInstances.verifDonut = new Chart('chartVerifDonut', {
    type: 'pie',
    data: {
      labels: ['Terverifikasi','Belum Terverifikasi'],
      datasets:[{ data:_verifDonutData, backgroundColor:['#0E9F6E','#E02424'], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:11}}} },
      onClick:(e,els)=>{ if(els.length) openModal('verif_total'); }
    }
  });

  // --- SECTION 02: ESKALASI ---
  _chartInstances.eskalasiTrend = new Chart('chartEskalasiTrend', {
    type: 'line',
    data: {
      labels: trendLabels,
      datasets:[{
        label:'Eskalasi',
        data: trendData,
        borderColor:'#1A56DB', backgroundColor:'rgba(26,86,219,.08)',
        tension:.4, fill:true, pointRadius:4, pointHoverRadius:7,
        pointBackgroundColor:'#fff', pointBorderColor:'#1A56DB', pointBorderWidth:2,
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false} },
      scales:{
        x:{grid:{display:false},ticks:{font:{size:10}}},
        y:{grid:{color:'#E4E8F0'},ticks:{font:{size:10}}}
      }
    }
  });

  _chartInstances.eskalasiDonut = new Chart('chartEskalasiDonut', {
    type: 'pie',
    data: {
      labels:['Sudah Eskalasi','Belum Eskalasi'],
      datasets:[{ data:[eskalasiDonut.sudah, eskalasiDonut.belum], backgroundColor:['#0E9F6E','#D97706'], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false} },
      onClick:(e,els)=>{
        if(els.length) {
          openModal('esk_gabungan');
          setTimeout(() => {
            setSumberEskalasiFilter(els[0].index===0?'sudah':'belum');
          }, 100);
        }
      }
    }
  });

  // --- SECTION 03: KETEPATAN WAKTU ---
  _chartInstances.ketepatan = new Chart('chartKetepatan', {
    type:'bar',
    data:{
      labels: ketepatanData.map(d => d.label),
      datasets:[
        { label:'On Time', data:ketepatanData.map(d=>d.ontime), backgroundColor:'#0E9F6E', borderRadius:4 },
        { label:'Late',    data:ketepatanData.map(d=>d.late),  backgroundColor:'#E02424', borderRadius:4 },
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false, indexAxis:'y',
      plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:11}}} },
      scales:{
        x:{grid:{color:'#E4E8F0'},ticks:{font:{size:10}}},
        y:{grid:{display:false},ticks:{font:{size:10}}}
      },
      onClick:(e,els)=>{
        if(els.length) {
          const ketepatan_type = els[0].datasetIndex === 0 ? 'ontime' : 'late';
          openModal('divisi', {divisi: ketepatanData[els[0].index].divisi, ketepatan_type});
        }
      }
    }
  });

  // Render ketepatan list
  const klEl = document.getElementById('ketepatanList');
  if (klEl) {
    klEl.innerHTML = '';
    ketepatanData.forEach(d => {
      const pct = d.pct, isGreen = pct >= 80;
      const div = document.createElement('div');
      div.className = 'status-item';
      div.onclick = () => openModal('divisi', {divisi: d.divisi});
      div.innerHTML = `
        <div class="status-dot-sm" style="background:${isGreen?'#0E9F6E':'#E02424'}"></div>
        <div class="status-name" style="font-size:12px">${d.label}</div>
        <div class="flex-grow-1 mx-2">
          <div class="progress" style="height:6px">
            <div class="progress-bar ${isGreen?'bg-success':'bg-danger'}" style="width:${pct}%"></div>
          </div>
        </div>
        <div class="prog-pct" style="color:${isGreen?'#0E9F6E':'#E02424'}">${pct}%</div>`;
      klEl.appendChild(div);
    });
  }

  // --- SECTION 05: STATUS KOMPLAIN ---
  _chartInstances.status = new Chart('chartStatus', {
    type:'pie',
    data:{
      labels: statusData.map(d=>d.label),
      datasets:[{ data:statusData.map(d=>d.qty), backgroundColor:statusData.map(d=>d.color), borderWidth:2, borderColor:'#fff', hoverOffset:8 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:10},padding:10}} },
      onClick:(e,els)=>{ if(els.length) openModal('status', {status_id: statusData[els[0].index].id}); }
    }
  });

  // Render status list
  const slEl = document.getElementById('statusList');
  if (slEl) {
    slEl.innerHTML = '';
    statusData.forEach(s => {
      const pct = totalEsk > 0 ? Math.round(s.qty/totalEsk*100) : 0;
      const div = document.createElement('div');
      div.className = 'status-item';
      div.onclick = () => openModal('status', {status_id: s.id});
      div.innerHTML = `
        <div class="status-dot-sm" style="background:${s.color}"></div>
        <div class="status-name">${s.label}</div>
        <div class="status-qty">${s.qty.toLocaleString('id')}</div>
        <div class="status-pct">${pct}%</div>
        <span class="badge-status badge-${s.badge}">${s.badge==='done'?'✓':'→'}</span>`;
      slEl.appendChild(div);
    });
  }
}

// ============================================================
// LOAD CHART DATA VIA AJAX
// ============================================================
function loadChartDataViaAjax() {
  const params = new URLSearchParams({
    date_from: filterGlobal.date_from,
    date_to:   filterGlobal.date_to,
    sumber:    filterGlobal.sumber,
    divisi_filter: filterGlobal.divisi,
  });

  fetch(BASE_URL + 'dash_crm/chart_data?' + params.toString(), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(res => {
      if (!res.success) throw new Error(res.error || 'Unknown error');

      const d = res.data;
      ketepatanData   = d.ketepatan   || [];
      statusData      = d.status      || [];
      verifChart      = d.verifChart  || verifChart;
      eskalasiDonut   = d.eskalasiDonut || eskalasiDonut;
      trendLabels     = d.trendLabels || [];
      trendData       = d.trendData   || [];
      totalEsk        = d.totalEskalasi || 0;
      _verifDonutData = [d.terverifikasi || 0, d.belumVerifikasi || 0];

      initAllCharts();
    })
    .catch(err => {
      console.warn('[Chart AJAX fallback] Gagal memuat via AJAX, chart kosong:', err.message);
      initAllCharts();
    });
}

// ============================================================
// BOOT — pilih mode AJAX atau langsung init
// ============================================================
if (USE_AJAX_CHART) {
  loadChartDataViaAjax();
} else {
  initAllCharts();
}

// ============================================================
// MODAL LOGIC — AJAX ke controller
// ============================================================
const bsModal = new bootstrap.Modal(document.getElementById('detailModal'));
// Fix: recalculate column widths setelah animasi modal selesai (mengatasi misalignment scrollX)
document.getElementById('detailModal').addEventListener('shown.bs.modal', () => {
  if (_dtInstances['dtModal'] && $.fn.DataTable.isDataTable('#dtModal')) {
    _dtInstances['dtModal'].columns.adjust().draw(false);
  }
});
let _currentModal = {};
// Unified modal filter state
let _mfSumber = 'all';
let _mfStatus = 'all';
let _mfStatusVal1 = '';  // Value for status checkbox 1 (e.g. 'verified', 'sudah', 'ontime')
let _mfStatusVal2 = '';  // Value for status checkbox 2 (e.g. 'unverified', 'belum', 'late')
let _mfDueDateFrom  = '';
let _mfDueDateTo    = '';
let _mfDoneDateFrom = '';
let _mfDoneDateTo   = '';

// Tipe Ringkasan Verifikasi & Eskalasi — sumber & status sudah implicit dari tipe
const _ringkasanTypes = [
  'verif_konsumen', 'verif_konsumen_belum', 'verif_sosmed_v', 'verif_sosmed_b',
  'esk_konsumen_sudah', 'esk_konsumen_belum', 'esk_sosmed_sudah', 'esk_sosmed_belum'
];

// Status config per modal type group
const _mfStatusConfig = {
  verif:     { label: 'Verifikasi:', l1: 'Sudah', l2: 'Belum', v1: 'verified', v2: 'unverified' },
  esk:       { label: 'Eskalasi:',   l1: 'Sudah', l2: 'Belum', v1: 'sudah',    v2: 'belum' },
  ketepatan: { label: 'Status:',     l1: 'On Time', l2: 'Late', v1: 'ontime',  v2: 'late' },
  divisi:    { label: 'Status:',     l1: 'On Time', l2: 'Late', v1: 'ontime',  v2: 'late' },
};

function _getStatusConfig(type) {
  if (type.startsWith('verif'))     return _mfStatusConfig.verif;
  if (type.startsWith('esk'))       return _mfStatusConfig.esk;
  if (type === 'ketepatan_total')   return _mfStatusConfig.ketepatan;
  if (type === 'divisi')            return _mfStatusConfig.divisi;
  return null; // 'status' type — no sub-status filter
}

function openModal(type, extra = {}) {
  _currentModal = { type, extra };

  // Reset unified filter state
  _mfSumber = 'all';
  _mfStatus = 'all';
  _mfDueDateFrom = _mfDueDateTo = '';
  _mfDoneDateFrom = _mfDoneDateTo = '';

  const modalFilters = document.getElementById('modalFilters');
  const statusGroup  = document.getElementById('mfStatusGroup');

  // Reset all checkboxes & date inputs
  document.getElementById('mfSemua').checked    = true;
  document.getElementById('mfKonsumen').checked  = false;
  document.getElementById('mfSosmed').checked    = false;
  document.getElementById('mfStatus1').checked   = false;
  document.getElementById('mfStatus2').checked   = false;
  document.getElementById('mfDateRange').value = '';

  const sumberStatusRow = document.getElementById('mfSumberStatusRow');
  const separator       = document.getElementById('mfSeparator');

  // Configure status labels based on type
  const cfg = _getStatusConfig(type);
  if (_ringkasanTypes.includes(type)) {
    // Ringkasan drilldown: only show date filter, hide sumber & status checkboxes
    modalFilters.style.display = 'block';
    sumberStatusRow.classList.add('d-none');
    separator.classList.add('d-none');
    statusGroup.classList.add('d-none');
    _mfStatusVal1 = '';
    _mfStatusVal2 = '';
  } else if (type === 'esk_sudah' || type === 'esk_belum') {
    // Sudah/Belum Eskalasi: show sumber + date, hide status (sudah pasti dari tipenya)
    modalFilters.style.display = 'block';
    sumberStatusRow.classList.remove('d-none');
    separator.classList.remove('d-none');
    statusGroup.classList.add('d-none');
    _mfStatusVal1 = '';
    _mfStatusVal2 = '';
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
    // Status drilldown: show sumber + date, hide status sub-filter
    modalFilters.style.display = 'block';
    sumberStatusRow.classList.remove('d-none');
    separator.classList.remove('d-none');
    statusGroup.classList.add('d-none');
    _mfStatusVal1 = '';
    _mfStatusVal2 = '';
  } else {
    modalFilters.style.display = 'none';
  }

  document.getElementById('modalTitle').textContent = 'Memuat data...';
  document.getElementById('modalContent').innerHTML = '';
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

// Cek apakah semua filter tidak aktif → kembalikan ke Semua
function _checkResetToAll() {
  const konsumen = document.getElementById('mfKonsumen').checked;
  const sosmed   = document.getElementById('mfSosmed').checked;
  const s1       = document.getElementById('mfStatus1').checked;
  const s2       = document.getElementById('mfStatus2').checked;
  if (!konsumen && !sosmed && !s1 && !s2) {
    document.getElementById('mfSemua').checked = true;
  }
}

// Handler untuk filter Sumber — mutual exclusive
function selectSumber(checkbox, value) {
  if (checkbox.checked) {
    // Uncheck pasangan, uncheck Semua
    if (value === 'konsumen') document.getElementById('mfSosmed').checked = false;
    else                       document.getElementById('mfKonsumen').checked = false;
    document.getElementById('mfSemua').checked = false;
    _mfSumber = value;
  } else {
    _mfSumber = 'all';
    _checkResetToAll();
  }
  loadModalPage(1);
}

// Handler untuk filter Status — mutual exclusive
function selectStatus(checkbox, idx) {
  if (checkbox.checked) {
    // Uncheck pasangan, uncheck Semua
    if (idx === 1) document.getElementById('mfStatus2').checked = false;
    else            document.getElementById('mfStatus1').checked = false;
    document.getElementById('mfSemua').checked = false;
    _mfStatus = (idx === 1) ? _mfStatusVal1 : _mfStatusVal2;
  } else {
    _mfStatus = 'all';
    _checkResetToAll();
  }
  loadModalPage(1);
}

// Init daterangepicker untuk modal Detail Komplain
$(function() {
  $('#mfDateRange').daterangepicker({
    autoUpdateInput: false,
    locale: {
      format: 'DD/MM/YYYY',
      applyLabel: 'Terapkan',
      cancelLabel: 'Batal',
      customRangeLabel: 'Custom',
      daysOfWeek: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
      monthNames: ['Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'],
      firstDay: 1
    },
    ranges: {
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
      'Last 7 Days': [moment().subtract(6,'days'), moment()],
      'Last 30 Days': [moment().subtract(29,'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')]
    },
    alwaysShowCalendars: true,
    opens: 'right'
  });
  $('#mfDateRange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    _mfDueDateFrom  = picker.startDate.format('YYYY-MM-DD');
    _mfDueDateTo    = picker.endDate.format('YYYY-MM-DD');
    _mfDoneDateFrom = '';
    _mfDoneDateTo   = '';
    document.getElementById('modalContent').innerHTML = '';
    document.getElementById('modalPagination').innerHTML = '';
    document.getElementById('modalLoading').style.display = 'block';
    loadModalPage(1);
  });
});

function resetModalDateFilter() {
  _mfDueDateFrom = _mfDueDateTo = '';
  _mfDoneDateFrom = _mfDoneDateTo = '';
  document.getElementById('mfDateRange').value = '';
  document.getElementById('modalContent').innerHTML = '';
  document.getElementById('modalPagination').innerHTML = '';
  document.getElementById('modalLoading').style.display = 'block';
  loadModalPage(1);
}

function loadModalPage(page) {
  const isRingkasan = _ringkasanTypes.includes(_currentModal.type);

  const params = new URLSearchParams({
    type:      _currentModal.type,
    status_id: _currentModal.extra?.status_id || '',
    divisi:    _currentModal.extra?.divisi    || '',
    page:      1,
    per_page:  2000,
    date_from: filterGlobal.date_from,
    date_to:   filterGlobal.date_to,
    sumber:    filterGlobal.sumber,
    divisi_filter: filterGlobal.divisi,
    mf_due_date_from:  _mfDueDateFrom,
    mf_due_date_to:    _mfDueDateTo,
    mf_done_date_from: _mfDoneDateFrom,
    mf_done_date_to:   _mfDoneDateTo,
  });

  // Ringkasan: sumber & status sudah ditentukan oleh tipe, tidak perlu dikirim
  if (!isRingkasan) {
    params.set('mf_sumber', _mfSumber);
    params.set('mf_status', _mfStatus);
  }

  const fetchUrl = BASE_URL + 'dash_crm/modal_detail?' + params.toString();

  fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
      return r.json();
    })
    .then(res => {
      document.getElementById('modalLoading').style.display = 'none';
      document.getElementById('modalPagination').innerHTML = '';
      if (!res.success) {
        document.getElementById('modalContent').innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
        return;
      }

      // Set judul modal
      const titleMap = {
        verif_total:         `Total Komplain — Drill-Down (${res.total.toLocaleString('id')})`,
        verif_terverifikasi: `Komplain Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_belum:         `Belum Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_konsumen:      `Konsumen Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_konsumen_belum: `Konsumen Belum Verifikasi (${res.total.toLocaleString('id')})`,
        verif_sosmed_v:      `Sosmed Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_sosmed_b:      `Sosmed Belum Verifikasi (${res.total.toLocaleString('id')})`,
        esk_sudah:           `Sudah Eskalasi (${res.total.toLocaleString('id')})`,
        esk_belum:           `Belum Eskalasi (${res.total.toLocaleString('id')})`,
        esk_gabungan:        `Data Eskalasi (${res.total.toLocaleString('id')})`,
        esk_konsumen_sudah:  `Konsumen — Sudah Eskalasi (${res.total.toLocaleString('id')})`,
        esk_konsumen_belum:  `Konsumen — Belum Eskalasi (${res.total.toLocaleString('id')})`,
        esk_sosmed_sudah:    `Sosmed — Sudah Eskalasi (${res.total.toLocaleString('id')})`,
        esk_sosmed_belum:    `Sosmed — Belum Eskalasi (${res.total.toLocaleString('id')})`,
        status:              `Status Komplain (${res.total.toLocaleString('id')})`,
        divisi:              `Divisi ${_currentModal.extra?.divisi || ''} (${res.total.toLocaleString('id')})`,
        ketepatan_total:     `Total Komplain — Ketepatan Waktu (${res.total.toLocaleString('id')})`,
      };
      document.getElementById('modalTitle').textContent = titleMap[_currentModal.type] || 'Detail Komplain';

      if (res.data.length === 0) {
        document.getElementById('modalContent').innerHTML = '<p class="text-muted text-center py-4">Tidak ada data.</p>';
        return;
      }

      // Render DataTable
      const jenisHeader = ['divisi', 'ketepatan_total'].includes(_currentModal.type) ? 'Kategori' : 'Jenis';
      document.getElementById('modalContent').innerHTML =
        '<table id="dtModal" class="table table-sm modal-table align-middle" style="width:100%"></table>';

      const columns = [
        { title: 'No. Komplain', data: 'id_task',
          render: (d, type) => type === 'display' ? `<code style="font-size:11px">${d}</code>` : d },
        { title: 'Konsumen', data: 'konsumen', defaultContent: '-' },
        { title: 'Lokasi', data: 'lokasi',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: jenisHeader, data: 'jenis',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Due Date', data: 'due_date',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Done Date', data: 'done_date',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Status', data: 'status',
          render: (d, type, row) => type === 'display'
            ? `<span class="badge-status badge-${getBadgeClass(row.status_id)}">${d||'-'}</span>`
            : (d||'-') },
        { title: 'Waktu', data: 'waktu_status',
          render: (d, type) => {
            if (type !== 'display') return d;
            const cls = d==='On Time'?'ontime':d==='Late'?'late':'working';
            return `<span class="badge-status badge-${cls}">${d}</span>`;
          }},
      ];

      _initDT('dtModal', columns, res.data);
    })
    .catch(err => {
      console.error('Modal Error:', err);
      document.getElementById('modalLoading').style.display = 'none';
      document.getElementById('modalContent').innerHTML = '<p class="text-danger">Koneksi error: ' + err.message + '</p>';
    });
}

function getBadgeClass(status_id) {
  const map = {1:'waiting',2:'waiting',3:'reject',4:'working',5:'reject',6:'done',7:'reject',8:'waiting',9:'waiting'};
  return map[status_id] || 'working';
}

// renderPagination — digantikan oleh DataTables pagination (tetap ada untuk backward compat)
function renderPagination() { /* DataTables handles pagination */ }

// Export Excel — trigger DataTables Buttons (.xlsx native via JSZip)
if (document.getElementById('btnExport')) {
  document.getElementById('btnExport').addEventListener('click', () => {
    if (_dtInstances['dtModal'] && $.fn.DataTable.isDataTable('#dtModal')) {
      _dtInstances['dtModal'].button('.btn-dt-excel').trigger();
    }
  });
}

// ============================================================
// GLOBAL KETEPATAN WAKTU MODAL — Detail semua divisi
// ============================================================
const bsKetepatanModal = new bootstrap.Modal(document.getElementById('ketepatanGlobalModal'));
// Fix: recalculate column widths setelah animasi modal selesai
document.getElementById('ketepatanGlobalModal').addEventListener('shown.bs.modal', () => {
  if (_dtInstances['dtKetepatan'] && $.fn.DataTable.isDataTable('#dtKetepatan')) {
    _dtInstances['dtKetepatan'].columns.adjust().draw(false);
  }
});
let _ketepatanGlobalFilter = 'all'; // all, ontime, late
let _kgDueDateFrom  = '';
let _kgDueDateTo    = '';
let _kgDoneDateFrom = '';
let _kgDoneDateTo   = '';

function openKetepatanGlobal(initialFilter = 'all') {
  _ketepatanGlobalFilter = initialFilter; // Set filter awal (all, ontime, late)
  // Reset date filters
  _kgDueDateFrom = _kgDueDateTo = _kgDoneDateFrom = _kgDoneDateTo = '';
  document.getElementById('kgDueDateRange').value  = '';
  document.getElementById('kgDoneDateRange').value = '';

  document.getElementById('ketepatanGlobalContent').innerHTML = '';
  document.getElementById('ketepatanGlobalPagination').innerHTML = '';
  document.getElementById('ketepatanGlobalLoading').style.display = 'block';

  // Update judul modal sesuai filter
  const titleMap = { all: 'Ketepatan Waktu Pengerjaan — Detail Semua Divisi', ontime: 'Ketepatan Waktu — Data On Time', late: 'Ketepatan Waktu — Data Late / Terlambat' };
  document.querySelector('#ketepatanGlobalModal .modal-title').textContent = titleMap[initialFilter] || titleMap['all'];

  bsKetepatanModal.show();
  loadKetepatanGlobalPage(1);
}

function loadKetepatanGlobalPage(page) {
  const params = new URLSearchParams({
    page:           1,
    per_page:       2000,
    ketepatan:      _ketepatanGlobalFilter, // all, ontime, late
    date_from:      filterGlobal.date_from,
    date_to:        filterGlobal.date_to,
    sumber:         filterGlobal.sumber,
    divisi:         filterGlobal.divisi,
    due_date_from:  _kgDueDateFrom,
    due_date_to:    _kgDueDateTo,
    done_date_from: _kgDoneDateFrom,
    done_date_to:   _kgDoneDateTo,
  });

  const fetchUrl = BASE_URL + 'dash_crm/ketepatan_global?' + params.toString();

  fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
      return r.json();
    })
    .then(res => {
      document.getElementById('ketepatanGlobalLoading').style.display = 'none';
      document.getElementById('ketepatanGlobalPagination').innerHTML = '';
      if (!res.success) {
        document.getElementById('ketepatanGlobalContent').innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
        return;
      }
      if (res.data.length === 0) {
        document.getElementById('ketepatanGlobalContent').innerHTML = '<p class="text-muted text-center py-4">Tidak ada data.</p>';
        return;
      }

      // Render DataTable
      document.getElementById('ketepatanGlobalContent').innerHTML =
        '<table id="dtKetepatan" class="table table-sm modal-table align-middle" style="width:100%"></table>';

      const columns = [
        { title: 'No. Komplain', data: 'id_task',
          render: (d, type) => type === 'display' ? `<code style="font-size:11px">${d}</code>` : d },
        { title: 'Konsumen', data: 'konsumen',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Lokasi', data: 'lokasi',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Divisi', data: 'divisi',
          render: (d, type) => type === 'display' ? `<small><strong>${d||'-'}</strong></small>` : (d||'-') },
        { title: 'Kategori', data: 'jenis',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Due Date', data: 'due_date',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Done Date', data: 'done_date',
          render: (d, type) => type === 'display' ? `<small>${d||'-'}</small>` : (d||'-') },
        { title: 'Status', data: 'waktu_status',
          render: (d, type) => {
            if (type !== 'display') return d;
            const cls = d==='On Time'?'ontime':d==='Late'?'late':'working';
            return `<span class="badge-status badge-${cls}">${d}</span>`;
          }},
      ];

      _initDT('dtKetepatan', columns, res.data);
    })
    .catch(err => {
      console.error('Ketepatan Global Error:', err);
      document.getElementById('ketepatanGlobalLoading').style.display = 'none';
      document.getElementById('ketepatanGlobalContent').innerHTML = '<p class="text-danger">Koneksi error: ' + err.message + '</p>';
    });
}

// Init daterangepicker untuk modal Ketepatan Global
$(function() {
  var kgPickerOpts = {
    autoUpdateInput: false,
    locale: {
      format: 'DD/MM/YYYY',
      applyLabel: 'Terapkan',
      cancelLabel: 'Batal',
      customRangeLabel: 'Custom',
      daysOfWeek: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
      monthNames: ['Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'],
      firstDay: 1
    },
    ranges: {
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
      'Last 7 Days': [moment().subtract(6,'days'), moment()],
      'Last 30 Days': [moment().subtract(29,'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')]
    },
    alwaysShowCalendars: true,
    opens: 'right'
  };

  $('#kgDueDateRange').daterangepicker(kgPickerOpts);
  $('#kgDueDateRange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    _kgDueDateFrom = picker.startDate.format('YYYY-MM-DD');
    _kgDueDateTo   = picker.endDate.format('YYYY-MM-DD');
    document.getElementById('ketepatanGlobalContent').innerHTML = '';
    document.getElementById('ketepatanGlobalPagination').innerHTML = '';
    document.getElementById('ketepatanGlobalLoading').style.display = 'block';
    loadKetepatanGlobalPage(1);
  });

  $('#kgDoneDateRange').daterangepicker(kgPickerOpts);
  $('#kgDoneDateRange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    _kgDoneDateFrom = picker.startDate.format('YYYY-MM-DD');
    _kgDoneDateTo   = picker.endDate.format('YYYY-MM-DD');
    document.getElementById('ketepatanGlobalContent').innerHTML = '';
    document.getElementById('ketepatanGlobalPagination').innerHTML = '';
    document.getElementById('ketepatanGlobalLoading').style.display = 'block';
    loadKetepatanGlobalPage(1);
  });
});

function resetKetepatanDateFilter() {
  _kgDueDateFrom = _kgDueDateTo = _kgDoneDateFrom = _kgDoneDateTo = '';
  document.getElementById('kgDueDateRange').value  = '';
  document.getElementById('kgDoneDateRange').value = '';
  document.getElementById('ketepatanGlobalContent').innerHTML = '';
  document.getElementById('ketepatanGlobalPagination').innerHTML = '';
  document.getElementById('ketepatanGlobalLoading').style.display = 'block';
  loadKetepatanGlobalPage(1);
}

// renderKetepatanGlobalPagination — digantikan oleh DataTables pagination
function renderKetepatanGlobalPagination() { /* DataTables handles pagination */ }

// Export Excel — trigger DataTables Buttons (.xlsx native via JSZip)
if (document.getElementById('btnKetepatanExport')) {
  document.getElementById('btnKetepatanExport').addEventListener('click', () => {
    if (_dtInstances['dtKetepatan'] && $.fn.DataTable.isDataTable('#dtKetepatan')) {
      _dtInstances['dtKetepatan'].button('.btn-dt-excel').trigger();
    }
  });
}

// ============================================================
// DRILLDOWN RATING KONSUMEN
// ============================================================
const bsRatingModal = new bootstrap.Modal(document.getElementById('ratingDrilldownModal'));
// Fix: recalculate column widths setelah animasi modal selesai
document.getElementById('ratingDrilldownModal').addEventListener('shown.bs.modal', () => {
  if (_dtInstances['dtRating'] && $.fn.DataTable.isDataTable('#dtRating')) {
    _dtInstances['dtRating'].columns.adjust().draw(false);
  }
});
let _ratingBintangFilter = null; // null = semua, 1-5 = per bintang

// Export Excel — trigger DataTables Buttons (.xlsx native via JSZip)
if (document.getElementById('btnRatingExport')) {
  document.getElementById('btnRatingExport').addEventListener('click', () => {
    if (_dtInstances['dtRating'] && $.fn.DataTable.isDataTable('#dtRating')) {
      _dtInstances['dtRating'].button('.btn-dt-excel').trigger();
    }
  });
}

// starBadge — render nilai rating dengan warna dan ikon bintang
function starBadge(val) {
  if (val === null || val === undefined || val === '') return '<span class="text-muted">—</span>';
  const n = parseFloat(val);
  const color = n >= 4 ? '#0E9F6E' : n >= 3 ? '#D97706' : '#E02424';
  return `<span style="font-weight:700;color:${color};font-family:monospace">${n.toFixed(1)} ⭐</span>`;
}

function _renderRatingStarFilter() {
  let filterHtml = `<button class="btn btn-xs ${_ratingBintangFilter===null?'btn-warning':'btn-outline-secondary'}" style="font-size:11px;padding:2px 8px;border-radius:100px" onclick="openRatingDrilldown(null)">Semua ⭐</button>`;
  [5,4,3,2,1].forEach(b => {
    filterHtml += `<button class="btn btn-xs ${_ratingBintangFilter===b?'btn-warning':'btn-outline-secondary'}" style="font-size:11px;padding:2px 8px;border-radius:100px" onclick="openRatingDrilldown(${b})">${b} ⭐</button>`;
  });
  document.getElementById('ratingStarFilter').innerHTML = filterHtml;
}

function openRatingDrilldown(bintang) {
  _ratingBintangFilter = (bintang === null || bintang === undefined) ? null : parseInt(bintang);
  document.getElementById('ratingDrilldownContent').innerHTML = '';
  document.getElementById('ratingDrilldownPagination').innerHTML = '';
  document.getElementById('ratingDrilldownLoading').style.display = 'block';

  const label = _ratingBintangFilter !== null
    ? `Detail Rating Konsumen — ${_ratingBintangFilter} ⭐`
    : 'Detail Rating Konsumen — Semua';
  document.getElementById('ratingDrilldownTitle').textContent = label;

  // Render filter langsung di atas sebelum data dimuat
  _renderRatingStarFilter();

  bsRatingModal.show();
  loadRatingDrilldownPage(1);
}

function loadRatingDrilldownPage(page) {
  const params = new URLSearchParams({
    page:      1,
    per_page:  2000,
    date_from: filterGlobal.date_from,
    date_to:   filterGlobal.date_to,
    sumber:    filterGlobal.sumber,
    divisi:    filterGlobal.divisi,
  });
  if (_ratingBintangFilter !== null) params.set('bintang', _ratingBintangFilter);

  fetch(BASE_URL + 'dash_crm/rating_drilldown?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(res => {
      document.getElementById('ratingDrilldownLoading').style.display = 'none';
      document.getElementById('ratingDrilldownPagination').innerHTML = '';

      if (!res.success) {
        document.getElementById('ratingDrilldownContent').innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
        return;
      }

      // Update filter pills
      _renderRatingStarFilter();

      if (res.data.length === 0) {
        document.getElementById('ratingDrilldownContent').innerHTML = '<p class="text-muted text-center py-4">Tidak ada data rating.</p>';
        return;
      }

      // Render DataTable
      document.getElementById('ratingDrilldownContent').innerHTML =
        '<table id="dtRating" class="table table-sm modal-table align-middle" style="width:100%"></table>';

      const columns = [
        { title: 'No. Komplain', data: 'id_task',
          render: (d, type) => type === 'display' ? `<code style="font-size:11px">${d}</code>` : d },
        { title: 'Konsumen', data: 'konsumen', defaultContent: '-' },
        { title: 'Project', data: 'project', defaultContent: '-' },
        { title: 'Blok', data: 'blok', defaultContent: '-' },
        { title: 'Jenis', data: 'jenis', defaultContent: '-' },
        { title: '⭐ Avg', data: 'avg_rating',
          render: (d, type) => type === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Pelayanan', data: 'pelayanan',
          render: (d, type) => type === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Kualitas', data: 'kualitas',
          render: (d, type) => type === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Respons', data: 'respons',
          render: (d, type) => type === 'display' ? starBadge(d) : (d !== null && d !== '' ? parseFloat(d) : '') },
        { title: 'Feedback', data: 'feedback', defaultContent: '-' },
      ];

      _initDT('dtRating', columns, res.data);
    })
    .catch(err => {
      document.getElementById('ratingDrilldownLoading').style.display = 'none';
      document.getElementById('ratingDrilldownContent').innerHTML = `<p class="text-danger">Koneksi error: ${err.message}</p>`;
    });
}

// renderRatingDrilldownPagination — digantikan oleh DataTables pagination
function renderRatingDrilldownPagination() { /* DataTables handles pagination */ }

// ============================================================
// FILTER — submit via form GET
// ============================================================
// ---- Date Range Picker ----
$(function() {
  $('#dateRangePicker').daterangepicker({
    startDate: moment('<?php echo $filter['date_from']; ?>'),
    endDate: moment('<?php echo $filter['date_to']; ?>'),
    locale: {
      format: 'DD/MM/YYYY',
      applyLabel: 'Terapkan',
      cancelLabel: 'Batal',
      customRangeLabel: 'Custom',
      daysOfWeek: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
      monthNames: ['Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'],
      firstDay: 1
    },
    ranges: {
      'Year to Date': [moment().startOf('year'), moment()],
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
      'Last 7 Days': [moment().subtract(6,'days'), moment()],
      'Last 30 Days': [moment().subtract(29,'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')]
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
  var today = moment().format('YYYY-MM-DD');
  var jan1  = new Date().getFullYear() + '-01-01';
  window.location.href = BASE_URL + 'dash_crm?date_from=' + jan1 + '&date_to=' + today + '&sumber=all&divisi=all';
}
</script>
</body>
</html>