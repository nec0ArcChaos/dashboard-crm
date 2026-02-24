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

</div><!-- /container-fluid -->

<script>
Chart.defaults.font.family = "system-ui, sans-serif";
Chart.defaults.plugins.legend.display = false;

// ============================================================
// CLOCK
// ============================================================
function updateClock() {
  document.getElementById('clock').textContent =
    new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
setInterval(updateClock,1000); updateClock();

// ============================================================
// DATA DARI PHP (diteruskan via JSON)
// ============================================================
const ketepatanData = <?= $ketepatan_json ?>;
const statusData    = <?= $status_json ?>;
const verifChart    = <?= $verif_chart_json ?>;
const eskalasiDonut = <?= $eskalasi_donut_json ?>;
const trendLabels   = <?= $trend_labels_json ?>;
const trendData     = <?= $trend_data_json ?>;
const totalEsk      = <?= $sudah_eskalasi ?>;
const BASE_URL      = '<?= base_url() ?>';
const filterGlobal  = {
  date_from: '<?= $filter['date_from'] ?>',
  date_to:   '<?= $filter['date_to'] ?>',
  sumber:    '<?= $filter['sumber'] ?>',
  divisi:    '<?= $filter['divisi'] ?>',
};

// ============================================================
// SECTION 01 — CHARTS VERIFIKASI
// ============================================================
new Chart('chartVerifSumber', {
  type: 'bar',
  data: {
    labels: ['Dari Konsumen','Dari Sosmed'],
    datasets: [
      { label:'Terverifikasi', data:[verifChart.konsumen_terverifikasi, verifChart.sosmed_terverifikasi], backgroundColor:'#0E9F6E', borderRadius:6 },
      { label:'Belum Verif.',  data:[verifChart.konsumen_belum,         verifChart.sosmed_belum],         backgroundColor:'#E02424', borderRadius:6 },
    ]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:11}}} },
    scales:{ x:{stacked:true,grid:{display:false}}, y:{stacked:true,grid:{color:'#E4E8F0'}} },
    onClick:(e,els)=>{ if(els.length) openModal(els[0].datasetIndex===0?'verif_terverifikasi':'verif_belum'); }
  }
});

new Chart('chartVerifDonut', {
  type: 'doughnut',
  data: {
    labels: ['Terverifikasi','Belum Terverifikasi'],
    datasets:[{ data:[<?= $terverifikasi ?>,<?= $belum_verifikasi ?>], backgroundColor:['#0E9F6E','#E02424'], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
  },
  options:{
    responsive:true, maintainAspectRatio:false, cutout:'68%',
    plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:11}}} },
    onClick:(e,els)=>{ if(els.length) openModal(els[0].index===0?'verif_terverifikasi':'verif_belum'); }
  }
});

// ============================================================
// SECTION 02 — CHARTS ESKALASI
// ============================================================
new Chart('chartEskalasiTrend', {
  type: 'line',
  data: {
    labels: trendLabels.length ? trendLabels : ['Jan\'25','Feb\'25','Mar\'25','Apr\'25','Mei\'25','Jun\'25','Jul\'25','Agt\'25','Sep\'25','Okt\'25','Nov\'25','Des\'25','Jan\'26','Feb\'26'],
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

new Chart('chartEskalasiDonut', {
  type: 'doughnut',
  data: {
    labels:['Sudah Eskalasi','Belum Eskalasi'],
    datasets:[{ data:[eskalasiDonut.sudah, eskalasiDonut.belum], backgroundColor:['#0E9F6E','#D97706'], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
  },
  options:{
    responsive:true, maintainAspectRatio:false, cutout:'68%',
    plugins:{ legend:{display:false} },
    onClick:(e,els)=>{ if(els.length) openModal(els[0].index===0?'esk_sudah':'esk_belum'); }
  }
});

// ============================================================
// SECTION 03 — KETEPATAN WAKTU
// ============================================================
new Chart('chartKetepatan', {
  type:'bar',
  data:{
    labels: ketepatanData.map(d=>d.divisi),
    datasets:[
      { label:'On Time', data:ketepatanData.map(d=>d.ontime), backgroundColor:ketepatanData.map(d=>d.pct>=80?'#0E9F6E':'#E02424'), borderRadius:4 },
      { label:'Late',    data:ketepatanData.map(d=>d.late),  backgroundColor:ketepatanData.map(d=>d.pct>=80?'rgba(14,159,110,.25)':'rgba(224,36,36,.25)'), borderRadius:4 },
    ]
  },
  options:{
    responsive:true, maintainAspectRatio:false, indexAxis:'y',
    plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:11}}} },
    scales:{
      x:{grid:{color:'#E4E8F0'},ticks:{font:{size:10}}},
      y:{grid:{display:false},ticks:{font:{size:10}}}
    },
    onClick:(e,els)=>{ if(els.length) openModal('divisi', {divisi: ketepatanData[els[0].index].divisi}); }
  }
});

// Render ketepatan list
const klEl = document.getElementById('ketepatanList');
if (klEl) {
  ketepatanData.forEach(d => {
    const pct = d.pct, isGreen = pct >= 80;
    const div = document.createElement('div');
    div.className = 'status-item';
    div.onclick = () => openModal('divisi', {divisi: d.divisi});
    div.innerHTML = `
      <div class="status-dot-sm" style="background:${isGreen?'#0E9F6E':'#E02424'}"></div>
      <div class="status-name" style="font-size:12px">${d.divisi}</div>
      <div class="flex-grow-1 mx-2">
        <div class="progress" style="height:6px">
          <div class="progress-bar ${isGreen?'bg-success':'bg-danger'}" style="width:${pct}%"></div>
        </div>
      </div>
      <div class="prog-pct" style="color:${isGreen?'#0E9F6E':'#E02424'}">${pct}%</div>`;
    klEl.appendChild(div);
  });
}

// ============================================================
// SECTION 05 — STATUS KOMPLAIN
// ============================================================
new Chart('chartStatus', {
  type:'doughnut',
  data:{
    labels: statusData.map(d=>d.label),
    datasets:[{ data:statusData.map(d=>d.qty), backgroundColor:statusData.map(d=>d.color), borderWidth:2, borderColor:'#fff', hoverOffset:8 }]
  },
  options:{
    responsive:true, maintainAspectRatio:false, cutout:'55%',
    plugins:{ legend:{display:true,position:'bottom',labels:{boxWidth:10,font:{size:10},padding:10}} },
    onClick:(e,els)=>{ if(els.length) openModal('status', {status_id: statusData[els[0].index].id}); }
  }
});

// Render status list
const slEl = document.getElementById('statusList');
if (slEl) {
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

// ============================================================
// MODAL LOGIC — AJAX ke controller
// ============================================================
const bsModal = new bootstrap.Modal(document.getElementById('detailModal'));
let _currentModal = {};

function openModal(type, extra = {}) {
  _currentModal = { type, extra };
  document.getElementById('modalTitle').textContent = 'Memuat data...';
  document.getElementById('modalContent').innerHTML = '';
  document.getElementById('modalPagination').innerHTML = '';
  document.getElementById('modalLoading').style.display = 'block';
  bsModal.show();
  loadModalPage(1);
}

function loadModalPage(page) {
  const params = new URLSearchParams({
    type:      _currentModal.type,
    status_id: _currentModal.extra?.status_id || '',
    divisi:    _currentModal.extra?.divisi    || '',
    page:      page,
    date_from: filterGlobal.date_from,
    date_to:   filterGlobal.date_to,
    sumber:    filterGlobal.sumber,
    divisi_filter: filterGlobal.divisi,
  });

  fetch(BASE_URL + 'dashboard/modal_detail?' + params.toString())
    .then(r => r.json())
    .then(res => {
      document.getElementById('modalLoading').style.display = 'none';
      if (!res.success) { document.getElementById('modalContent').innerHTML = '<p class="text-danger">Gagal memuat data.</p>'; return; }

      // Set judul modal
      const titleMap = {
        verif_terverifikasi: `Komplain Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_belum:         `Belum Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_konsumen:      `Konsumen Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_sosmed_v:      `Sosmed Terverifikasi (${res.total.toLocaleString('id')})`,
        verif_sosmed_b:      `Sosmed Belum Verifikasi (${res.total.toLocaleString('id')})`,
        esk_sudah:           `Sudah Eskalasi (${res.total.toLocaleString('id')})`,
        esk_belum:           `Belum Eskalasi (${res.total.toLocaleString('id')})`,
        status:              `Status Komplain (${res.total.toLocaleString('id')})`,
        divisi:              `Divisi ${_currentModal.extra?.divisi || ''} (${res.total.toLocaleString('id')})`,
      };
      document.getElementById('modalTitle').textContent = titleMap[_currentModal.type] || 'Detail Komplain';

      if (res.data.length === 0) {
        document.getElementById('modalContent').innerHTML = '<p class="text-muted text-center py-4">Tidak ada data.</p>';
        return;
      }

      // Render tabel
      let html = `<p class="text-muted small">Menampilkan ${((page-1)*res.per_page)+1}–${Math.min(page*res.per_page, res.total)} dari ${res.total.toLocaleString('id')} data.</p>
        <div class="table-responsive">
        <table class="table table-sm modal-table align-middle">
          <thead><tr>
            <th>No. Komplain</th><th>Konsumen</th><th>Lokasi</th><th>Jenis</th><th>Status</th><th>Waktu</th>
          </tr></thead><tbody>`;

      res.data.forEach(row => {
        const waktuClass = row.waktu_status === 'On Time' ? 'ontime' : row.waktu_status === 'Late' ? 'late' : 'working';
        html += `<tr>
          <td><code style="font-size:11px">${row.id_task}</code></td>
          <td>${row.konsumen || '-'}</td>
          <td><small>${row.lokasi || '-'}</small></td>
          <td><small>${row.jenis || '-'}</small></td>
          <td><span class="badge-status badge-${getBadgeClass(row.status_id)}">${row.status || '-'}</span></td>
          <td><span class="badge-status badge-${waktuClass}">${row.waktu_status}</span></td>
        </tr>`;
      });
      html += '</tbody></table></div>';
      document.getElementById('modalContent').innerHTML = html;

      // Paginasi
      renderPagination(res.total, res.per_page, page);
    })
    .catch(() => {
      document.getElementById('modalLoading').style.display = 'none';
      document.getElementById('modalContent').innerHTML = '<p class="text-danger">Koneksi error.</p>';
    });
}

function getBadgeClass(status_id) {
  const map = {1:'waiting',2:'waiting',3:'reject',4:'working',5:'reject',6:'done',7:'reject',8:'waiting',9:'waiting'};
  return map[status_id] || 'working';
}

function renderPagination(total, per_page, current_page) {
  const total_pages = Math.ceil(total / per_page);
  if (total_pages <= 1) { document.getElementById('modalPagination').innerHTML = ''; return; }

  let html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';
  if (current_page > 1) {
    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadModalPage(${current_page-1});return false;">‹</a></li>`;
  }
  const start = Math.max(1, current_page-2), end = Math.min(total_pages, current_page+2);
  if (start > 1) html += `<li class="page-item"><a class="page-link" href="#" onclick="loadModalPage(1);return false;">1</a></li><li class="page-item disabled"><span class="page-link">…</span></li>`;
  for (let p = start; p <= end; p++) {
    html += `<li class="page-item ${p===current_page?'active':''}"><a class="page-link" href="#" onclick="loadModalPage(${p});return false;">${p}</a></li>`;
  }
  if (end < total_pages) html += `<li class="page-item disabled"><span class="page-link">…</span></li><li class="page-item"><a class="page-link" href="#" onclick="loadModalPage(${total_pages});return false;">${total_pages}</a></li>`;
  if (current_page < total_pages) {
    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadModalPage(${current_page+1});return false;">›</a></li>`;
  }
  html += '</ul></nav>';
  document.getElementById('modalPagination').innerHTML = html;
}

// Export Excel — buka URL download
document.getElementById('btnExport').addEventListener('click', () => {
  const params = new URLSearchParams({
    type:      _currentModal.type,
    status_id: _currentModal.extra?.status_id || '',
    divisi:    _currentModal.extra?.divisi    || '',
    export:    'excel',
    date_from: filterGlobal.date_from,
    date_to:   filterGlobal.date_to,
  });
  window.open(BASE_URL + 'dashboard/modal_detail?' + params.toString(), '_blank');
});

// ============================================================
// FILTER — submit via form GET
// ============================================================
function applyFilter() {
  const f = document.getElementById('dateFrom').value;
  const t = document.getElementById('dateTo').value;
  const s = document.getElementById('filterSumber').value;
  const d = document.getElementById('filterDivisi').value;
  window.location.href = BASE_URL + 'dashboard?date_from=' + f + '&date_to=' + t + '&sumber=' + s + '&divisi=' + d;
}

function resetFilter() {
  window.location.href = BASE_URL + 'dashboard?date_from=2025-01-01&date_to=' + new Date().toISOString().split('T')[0] + '&sumber=all&divisi=all';
}
</script>
</body>
</html>
