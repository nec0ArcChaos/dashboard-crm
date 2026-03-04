<div class="container-fluid px-3 px-md-4 py-4" style="max-width:1600px">

  <!-- ===== FILTER BAR ===== -->
  <div class="filter-bar mb-4">
    <div class="d-flex flex-wrap align-items-center gap-3">
      <span class="filter-label">Filter</span>
      <div class="d-flex align-items-center gap-2">
        <small class="text-secondary">Dari</small>
        <input type="date" class="form-control form-control-sm" id="dateFrom"
               value="<?= htmlspecialchars($filter['date_from']) ?>" style="width:150px">
        <small class="text-secondary">s.d.</small>
        <input type="date" class="form-control form-control-sm" id="dateTo"
               value="<?= htmlspecialchars($filter['date_to']) ?>" style="width:150px">
      </div>
      <div class="vr d-none d-md-block"></div>
      <div class="d-flex align-items-center gap-2">
        <small class="text-secondary">Sumber</small>
        <select class="form-select form-select-sm" id="filterSumber" style="width:160px">
          <option value="all"      <?= $filter['sumber']==='all'?'selected':'' ?>>Semua Sumber</option>
          <option value="konsumen" <?= $filter['sumber']==='konsumen'?'selected':'' ?>>Dari Konsumen</option>
          <option value="sosmed"   <?= $filter['sumber']==='sosmed'?'selected':'' ?>>Dari Sosmed</option>
        </select>
      </div>
      <div class="vr d-none d-md-block"></div>
      <div class="d-flex align-items-center gap-2">
        <small class="text-secondary">Divisi</small>
        <select class="form-select form-select-sm" id="filterDivisi" style="width:160px">
          <option value="all" <?= $filter['divisi']==='all'?'selected':'' ?>>Semua Divisi</option>
          <?php foreach ($list_divisi as $div): ?>
          <option value="<?= htmlspecialchars($div['divisi']) ?>"
            <?= $filter['divisi']===$div['divisi']?'selected':'' ?>>
            <?= htmlspecialchars($div['label']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="ms-auto d-flex gap-2">
        <button class="btn btn-sm btn-primary px-3 fw-semibold" onclick="applyFilter()">Terapkan</button>
        <button class="btn btn-sm btn-outline-secondary px-3" onclick="resetFilter()">Reset</button>
      </div>
    </div>
  </div>

  <!-- ===== 01 VERIFIKASI ===== -->
  <div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="section-badge">01</span>
      <span class="section-title">Verifikasi Komplain</span>
      <span class="ms-auto section-period" id="period-label"><?= htmlspecialchars($period_label) ?></span>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3">
        <div class="kpi-card green" style="cursor:pointer" onclick="openModal('verif_total')">
          <div class="kpi-label">Total Komplain</div>
          <div class="kpi-value"><?= number_format($total_komplain, 0, ',', '.') ?></div>
          <div class="kpi-meta">Klik untuk drill-down →</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card <?= $pct_verif >= 80 ? 'green' : 'orange' ?>" style="cursor:pointer" onclick="openModal('verif_total')">
          <div class="kpi-label">Terverifikasi</div>
          <div class="kpi-value"><?= number_format($terverifikasi, 0, ',', '.') ?></div>
          <div class="kpi-meta">
            <span class="pill <?= $pct_verif >= 80 ? 'pill-green' : 'pill-orange' ?>">
              <?= $pct_verif >= 80 ? '✓' : '⚠' ?> <?= $pct_verif ?>%
            </span>
            <?= $pct_verif >= 80 ? '≥ 80% Target' : 'Di bawah target' ?>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card <?= $belum_verifikasi > 0 ? 'red' : 'green' ?>" style="cursor:pointer" onclick="openModal('verif_total')">
          <div class="kpi-label">Belum Terverifikasi</div>
          <div class="kpi-value"><?= number_format($belum_verifikasi, 0, ',', '.') ?></div>
          <div class="kpi-meta">
            <span class="pill <?= $belum_verifikasi > 0 ? 'pill-red' : 'pill-green' ?>">
              <?= $belum_verifikasi > 0 ? '⚠' : '✓' ?> <?= 100 - $pct_verif ?>%
            </span>
            <?= $belum_verifikasi > 0 ? 'Perlu Aksi' : 'Sudah Semua' ?>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <?php if ($filter['sumber'] === 'konsumen'): ?>
        <div class="kpi-card <?= $pct_konsumen_verif >= 80 ? 'green' : 'red' ?>">
          <div class="kpi-label">% Verifikasi Konsumen</div>
          <div class="kpi-value <?= $pct_konsumen_verif >= 80 ? 'text-success' : 'text-danger' ?>">
            <?= $pct_konsumen_verif ?>%
          </div>
          <div class="kpi-meta">
            <span class="pill <?= $pct_konsumen_verif >= 80 ? 'pill-green' : 'pill-red' ?>">
              <?= $pct_konsumen_verif >= 80 ? '✓ Baik' : '🚨 Di bawah target' ?>
            </span>
          </div>
        </div>
        <?php else: ?>
        <div class="kpi-card <?= $pct_verif_sosmed >= 80 ? 'green' : 'red' ?>">
          <div class="kpi-label">% Verifikasi Sosmed</div>
          <div class="kpi-value <?= $pct_verif_sosmed >= 80 ? 'text-success' : 'text-danger' ?>">
            <?= $pct_verif_sosmed ?>%
          </div>
          <div class="kpi-meta">
            <span class="pill <?= $pct_verif_sosmed >= 80 ? 'pill-green' : 'pill-red' ?>">
              <?= $pct_verif_sosmed >= 80 ? '✓ Baik' : '🚨 Di bawah target' ?>
            </span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="chart-card">
          <div class="chart-title">Verifikasi per Sumber</div>
          <div class="chart-sub">Klik bar atau legend untuk melihat semua data</div>
          <div style="height:220px"><canvas id="chartVerifSumber"></canvas></div>
          <div style="font-size:12px; color:#999; text-align:center; margin-top:8px; padding:0 8px;">
           Gunakan <strong>legend</strong> untuk melihat semua data (kecil atau besar)
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="chart-card">
          <div class="chart-title">Terverifikasi</div>
          <div class="chart-sub">&nbsp;</div>
          <div style="height:180px"><canvas id="chartVerifDonut"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="chart-card">
          <div class="chart-title">Ringkasan Verifikasi</div>
          <div class="chart-sub">Per sumber &amp; status</div>
          <div class="mt-2">
            <?php if ($filter['sumber'] !== 'sosmed'): ?>
            <div class="status-item" onclick="openModal('verif_konsumen')">
              <div class="status-dot-sm" style="background:#0E9F6E"></div>
              <div class="status-name">Konsumen — Terverifikasi</div>
              <div class="status-qty"><?= number_format($verif_per_sumber['konsumen']['terverifikasi'], 0, ',', '.') ?></div>
              <span class="pill <?= $pct_konsumen_verif >= 60 ? 'pill-green' : 'pill-red' ?>" style="font-size:10px"><?= $pct_konsumen_verif ?>%</span>
            </div>
            <div class="status-item" onclick="openModal('verif_konsumen_belum')">
              <div class="status-dot-sm" style="background:#E02424"></div>
              <div class="status-name">Konsumen — Belum Verf.</div>
              <div class="status-qty"><?= number_format($verif_per_sumber['konsumen']['belum'], 0, ',', '.') ?></div>
              <?php
                $pct_konsumen_belum = ($verif_per_sumber['konsumen']['terverifikasi'] + $verif_per_sumber['konsumen']['belum']) > 0
                  ? round(($verif_per_sumber['konsumen']['belum']/($verif_per_sumber['konsumen']['terverifikasi'] + $verif_per_sumber['konsumen']['belum']))*100)
                  : 0;
                if ($pct_konsumen_belum > 60) {
                  $pill_class = 'pill-red';
                } elseif ($pct_konsumen_belum >= 30) {
                  $pill_class = 'pill-orange';
                } else {
                  $pill_class = 'pill-green';
                }
              ?>
              <span class="pill <?= $pill_class ?>" style="font-size:10px"><?= $pct_konsumen_belum ?>%</span>
            </div>
            <?php endif; ?>
            <?php if ($filter['sumber'] !== 'konsumen'): ?>
            <div class="status-item" onclick="openModal('verif_sosmed_v')">
              <div class="status-dot-sm" style="background:#1A56DB"></div>
              <div class="status-name">Sosmed — Terverifikasi</div>
              <div class="status-qty"><?= number_format($verif_per_sumber['sosmed']['terverifikasi'], 0, ',', '.') ?></div>
              <span class="pill <?= $pct_verif_sosmed >= 60 ? 'pill-green' : 'pill-red' ?>" style="font-size:10px"><?= $pct_verif_sosmed ?>%</span>
            </div>
            <div class="status-item" onclick="openModal('verif_sosmed_b')">
              <div class="status-dot-sm" style="background:#E02424"></div>
              <div class="status-name">Sosmed — Belum Verf.</div>
              <div class="status-qty"><?= number_format($verif_per_sumber['sosmed']['belum'], 0, ',', '.') ?></div>
              <?php
                $pct_sosmed_belum = $total_sosmed > 0 ? round(($verif_per_sumber['sosmed']['belum']/$total_sosmed)*100) : 0;
                if ($pct_sosmed_belum > 60) {
                  $pill_class_sosmed = 'pill-red';
                } elseif ($pct_sosmed_belum >= 30) {
                  $pill_class_sosmed = 'pill-orange';
                } else {
                  $pill_class_sosmed = 'pill-green';
                }
              ?>
              <span class="pill <?= $pill_class_sosmed ?>" style="font-size:10px"><?= $pct_sosmed_belum ?>%</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="section-divider"></div>

  <!-- ===== 02 ESKALASI ===== -->
  <div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="section-badge">02</span>
      <span class="section-title">Eskalasi Komplain</span>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3">
        <div class="kpi-card green">
          <div class="kpi-label">Total Komplain</div>
          <div class="kpi-value"><?= number_format($total_komplain, 0, ',', '.') ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card <?= $rate_eskalasi >= 80 ? 'green' : 'orange' ?>">
          <div class="kpi-label">Sudah Eskalasi</div>
          <div class="kpi-value"><?= number_format($sudah_eskalasi, 0, ',', '.') ?></div>
          <div class="kpi-meta"><span class="pill pill-green">✓ <?= $rate_eskalasi ?>%</span></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card <?= $belum_eskalasi > 0 ? 'orange' : 'green' ?>">
          <div class="kpi-label">Belum Eskalasi</div>
          <div class="kpi-value"><?= number_format($belum_eskalasi, 0, ',', '.') ?></div>
          <div class="kpi-meta"><span class="pill pill-orange">⚠ <?= 100 - $rate_eskalasi ?>%</span></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card <?= $rate_eskalasi >= 80 ? 'green' : 'orange' ?>">
          <div class="kpi-label">Rate Eskalasi</div>
          <div class="kpi-value <?= $rate_eskalasi >= 80 ? 'text-success' : 'text-warning' ?>"><?= $rate_eskalasi ?>%</div>
          <div class="kpi-meta"><span class="pill <?= $rate_eskalasi >= 80 ? 'pill-green' : 'pill-orange' ?>"><?= $rate_eskalasi >= 80 ? '✓ Baik' : '⚠ Perlu Perhatian' ?></span></div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="chart-card">
          <div class="chart-title">Trend Eskalasi</div>
          <div class="chart-sub">Distribusi per bulan</div>
          <div style="height:200px"><canvas id="chartEskalasiTrend"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="chart-card">
          <div class="chart-title">Proporsi Eskalasi</div>
          <div style="height:160px"><canvas id="chartEskalasiDonut"></canvas></div>
          <div class="d-flex justify-content-center gap-3 mt-3">
            <span class="d-flex align-items-center gap-1">
              <span class="status-dot-sm" style="background:#0E9F6E"></span>
              <span style="font-size:12px">Sudah <?= $rate_eskalasi ?>%</span>
            </span>
            <span class="d-flex align-items-center gap-1">
              <span class="status-dot-sm" style="background:#D97706"></span>
              <span style="font-size:12px">Belum <?= 100-$rate_eskalasi ?>%</span>
            </span>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="chart-card">
          <div class="chart-title">Ringkasan Eskalasi</div>
          <div class="chart-sub">Per sumber &amp; status</div>
          <div class="mt-2">
            <?php if ($filter['sumber'] !== 'sosmed'): ?>
            <div class="status-item" onclick="openModal('esk_konsumen_sudah')">
              <div class="status-dot-sm" style="background:#0E9F6E"></div>
              <div class="status-name">Konsumen — Sudah Esk.</div>
              <div class="status-qty"><?= number_format($eskalasi_per_sumber['konsumen']['sudah'], 0, ',', '.') ?></div>
              <span class="pill pill-green" style="font-size:10px"><?= $pct_konsumen_eskalasi ?>%</span>
            </div>
            <div class="status-item" onclick="openModal('esk_konsumen_belum')">
              <div class="status-dot-sm" style="background:#E02424"></div>
              <div class="status-name">Konsumen — Belum Esk.</div>
              <div class="status-qty"><?= number_format($eskalasi_per_sumber['konsumen']['belum'], 0, ',', '.') ?></div>
              <span class="pill pill-red" style="font-size:10px"><?= ($eskalasi_per_sumber['konsumen']['sudah'] + $eskalasi_per_sumber['konsumen']['belum']) > 0 ? round(($eskalasi_per_sumber['konsumen']['belum']/($eskalasi_per_sumber['konsumen']['sudah'] + $eskalasi_per_sumber['konsumen']['belum']))*100) : 0 ?>%</span>
            </div>
            <?php endif; ?>
            <?php if ($filter['sumber'] !== 'konsumen'): ?>
            <div class="status-item" onclick="openModal('esk_sosmed_sudah')">
              <div class="status-dot-sm" style="background:#0E9F6E"></div>
              <div class="status-name">Sosmed — Sudah Esk.</div>
              <div class="status-qty"><?= number_format($eskalasi_per_sumber['sosmed']['sudah'], 0, ',', '.') ?></div>
              <span class="pill pill-green" style="font-size:10px"><?= $pct_sosmed_eskalasi ?>%</span>
            </div>
            <div class="status-item" onclick="openModal('esk_sosmed_belum')">
              <div class="status-dot-sm" style="background:#E02424"></div>
              <div class="status-name">Sosmed — Belum Esk.</div>
              <div class="status-qty"><?= number_format($eskalasi_per_sumber['sosmed']['belum'], 0, ',', '.') ?></div>
              <span class="pill pill-red" style="font-size:10px"><?= ($eskalasi_per_sumber['sosmed']['sudah'] + $eskalasi_per_sumber['sosmed']['belum']) > 0 ? round(($eskalasi_per_sumber['sosmed']['belum']/($eskalasi_per_sumber['sosmed']['sudah'] + $eskalasi_per_sumber['sosmed']['belum']))*100) : 0 ?>%</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="section-divider"></div>

  <!-- ===== 03 KETEPATAN WAKTU ===== -->
  <div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="section-badge">03</span>
      <span class="section-title">Ketepatan Waktu Pengerjaan</span>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-6 col-md">
        <div class="kpi-card" style="cursor: pointer;" onclick="openModal('ketepatan_total')">
          <div class="kpi-label">Total Data Done</div>
          <div class="kpi-value"><?= number_format($total_ketepatan, 0, ',', '.') ?></div>
          <div class="kpi-meta" style="font-size: 11px;">Klik untuk detail <span style="font-size: 12px;">→</span></div>
        </div>
      </div>
      <?php
        $pct_ontime = $total_ketepatan > 0 ? round(($total_ontime / $total_ketepatan) * 100) : 0;
        $pct_late   = $total_ketepatan > 0 ? round(($total_late   / $total_ketepatan) * 100) : 0;
      ?>
      <div class="col-6 col-md">
        <div class="kpi-card green" style="cursor:pointer" onclick="openKetepatanGlobal('ontime')">
          <div class="kpi-label">Total On Time</div>
          <div class="kpi-value text-success" style="font-size:1.4rem">
            <?= number_format($total_ontime, 0, ',', '.') ?>
            <span style="color:#CBD5E1;font-weight:300;margin:0 4px">|</span>
            <?= $pct_ontime ?>%
          </div>
          <div class="kpi-meta"><span style="font-size:11px">Klik untuk detail →</span></div>
        </div>
      </div>
      <div class="col-6 col-md">
        <div class="kpi-card <?= $total_late > 0 ? 'red' : 'green' ?>" style="cursor:pointer" onclick="openKetepatanGlobal('late')">
          <div class="kpi-label">Total Late</div>
          <div class="kpi-value <?= $total_late > 0 ? 'text-danger' : 'text-success' ?>" style="font-size:1.4rem">
            <?= number_format($total_late, 0, ',', '.') ?>
            <span style="color:#CBD5E1;font-weight:300;margin:0 4px">|</span>
            <?= $pct_late ?>%
          </div>
          <div class="kpi-meta"><span style="font-size:11px">Klik untuk detail →</span></div>
        </div>
      </div>
      <div class="col-6 col-md">
        <div class="kpi-card <?= $divisi_bawah_80 > 0 ? 'red' : 'green' ?>">
          <div class="kpi-label">Divisi &lt; 80%</div>
          <div class="kpi-value <?= $divisi_bawah_80 > 0 ? 'text-danger' : 'text-success' ?>"><?= $divisi_bawah_80 ?></div>
          <div class="kpi-meta">Perlu perhatian</div>
        </div>
      </div>
      <div class="col-6 col-md">
        <div class="kpi-card green">
          <div class="kpi-label">Divisi ≥ 80%</div>
          <div class="kpi-value text-success"><?= $divisi_atas_80 ?></div>
          <div class="kpi-meta">Performansi baik</div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-7">
        <div class="chart-card">
          <div class="chart-title">On Time vs Late per Divisi</div>
          <div class="chart-sub">Klik bar untuk lihat detail</div>
          <div style="height:<?= max(200, count($ketepatan) * 32 + 60) ?>px"><canvas id="chartKetepatan"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-lg-5">
        <div class="chart-card">
          <div class="chart-title">Ketepatan Waktu per Divisi</div>
          <div class="chart-sub">Klik baris untuk detail</div>
          <div id="ketepatanList" class="mt-2"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="section-divider"></div>

  <!-- ===== 04 RATING ===== -->
  <div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="section-badge">04</span>
      <span class="section-title">Rating Konsumen</span>
    </div>

    <?php if (!empty($rating_summary['total_responden']) && $rating_summary['total_responden'] > 0): ?>
    <div class="chart-card">
      <div class="row g-3 align-items-center">
        <div class="col-12 col-md-4 text-center" style="cursor:pointer" onclick="openRatingDrilldown(null)" title="Lihat semua data rating">
          <div style="font-size:48px">⭐</div>
          <div class="fs-1 fw-bold text-warning"><?= number_format($rating_summary['avg_all'] ?? 0, 1) ?></div>
          <div class="text-muted small">Rata-rata dari <?= number_format($rating_summary['total_responden'], 0, ',', '.') ?> responden</div>
          <div class="d-flex justify-content-center gap-1 mt-2">
            <?php for ($i=1; $i<=5; $i++): ?>
            <i class="bi bi-star<?= $i <= round($rating_summary['avg_all'] ?? 0) ? '-fill text-warning' : ' text-muted' ?>"></i>
            <?php endfor; ?>
          </div>
          <div class="mt-2"><small class="text-primary" style="font-size:11px">Klik untuk drill-down →</small></div>
        </div>
        <div class="col-12 col-md-8">
          <div class="row g-3 mb-3">
            <div class="col-4">
              <div class="bg-light rounded-3 p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase" style="font-size:10px">Pelayanan</div>
                <div class="fs-3 fw-bold text-primary"><?= number_format($rating_summary['avg_pelayanan'] ?? 0, 1) ?></div>
              </div>
            </div>
            <div class="col-4">
              <div class="bg-light rounded-3 p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase" style="font-size:10px">Kualitas</div>
                <div class="fs-3 fw-bold text-success"><?= number_format($rating_summary['avg_kualitas'] ?? 0, 1) ?></div>
              </div>
            </div>
            <div class="col-4">
              <div class="bg-light rounded-3 p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase" style="font-size:10px">Respons</div>
                <div class="fs-3 fw-bold text-info"><?= number_format($rating_summary['avg_respons'] ?? 0, 1) ?></div>
              </div>
            </div>
          </div>
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <small class="text-muted fw-semibold" style="font-size:11px">DISTRIBUSI RATING</small>
              <button class="btn btn-xs btn-outline-primary" style="font-size:11px;padding:2px 8px;border-radius:100px" onclick="openRatingDrilldown(null)">Lihat Semua →</button>
            </div>
            <?php
            $distribusi_map = [];
            foreach ($distribusi_rating as $r) { $distribusi_map[$r['bintang']] = $r['total']; }
            $max_rating = $rating_summary['total_responden'] ?: 1;
            for ($i=5; $i>=1; $i--):
              $cnt = isset($distribusi_map[$i]) ? $distribusi_map[$i] : 0;
              $pct = round(($cnt/$max_rating)*100);
            ?>
            <div class="d-flex align-items-center gap-2 mb-1" style="cursor:pointer" onclick="openRatingDrilldown(<?= $i ?>)" title="Lihat detail rating <?= $i ?> bintang">
              <span style="font-size:12px;width:30px"><?= $i ?>⭐</span>
              <div class="progress flex-grow-1" style="height:8px">
                <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
              </div>
              <span style="font-size:12px;width:36px;text-align:right"><?= $cnt ?></span>
            </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="rating-ph">
      <div style="font-size:40px">⭐</div>
      <h5 class="mt-3 fw-semibold text-secondary">Data Rating Belum Tersedia — Future-Ready Placeholder</h5>
      <p class="text-muted small mt-2">Struktur sudah disiapkan. Setelah sumber data rating konsumen tersedia, koneksikan ke endpoint ini.</p>
      <div class="d-flex justify-content-center gap-2 my-3">
        <div class="btn btn-outline-secondary btn-sm disabled">1⭐</div>
        <div class="btn btn-outline-secondary btn-sm disabled">2⭐</div>
        <div class="btn btn-outline-secondary btn-sm disabled">3⭐</div>
        <div class="btn btn-outline-secondary btn-sm disabled">4⭐</div>
        <div class="btn btn-outline-secondary btn-sm disabled">5⭐</div>
      </div>
      <div class="row justify-content-center g-3" style="max-width:480px;margin:0 auto">
        <div class="col-4"><div class="bg-light border rounded p-3 text-center"><div class="text-muted small fw-bold text-uppercase" style="font-size:10px">Avg Rating</div><div class="fs-3 fw-bold text-muted">—</div></div></div>
        <div class="col-4"><div class="bg-light border rounded p-3 text-center"><div class="text-muted small fw-bold text-uppercase" style="font-size:10px">Responden</div><div class="fs-3 fw-bold text-muted">—</div></div></div>
        <div class="col-4"><div class="bg-light border rounded p-3 text-center"><div class="text-muted small fw-bold text-uppercase" style="font-size:10px">Kepuasan</div><div class="fs-3 fw-bold text-muted">—</div></div></div>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <div class="section-divider"></div>

  <!-- ===== 05 STATUS ===== -->
  <div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="section-badge">05</span>
      <span class="section-title">Status Komplain</span>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3">
        <div class="kpi-card green">
          <div class="kpi-label">Total Eskalasi</div>
          <div class="kpi-value"><?= number_format($sudah_eskalasi, 0, ',', '.') ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card green">
          <div class="kpi-label">Done</div>
          <div class="kpi-value text-success"><?= number_format($total_done, 0, ',', '.') ?></div>
          <div class="kpi-meta">
            <span class="pill pill-green">✓ <?= $sudah_eskalasi > 0 ? round(($total_done/$sudah_eskalasi)*100) : 0 ?>%</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card red">
          <div class="kpi-label">Reject</div>
          <div class="kpi-value text-danger"><?= number_format($total_reject, 0, ',', '.') ?></div>
          <div class="kpi-meta">
            <span class="pill pill-red"><?= $sudah_eskalasi > 0 ? round(($total_reject/$sudah_eskalasi)*100) : 0 ?>%</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-card orange">
          <div class="kpi-label">In Progress</div>
          <div class="kpi-value text-warning"><?= number_format($total_inprog, 0, ',', '.') ?></div>
          <div class="kpi-meta">
            <span class="pill pill-orange"><?= $sudah_eskalasi > 0 ? round(($total_inprog/$sudah_eskalasi)*100) : 0 ?>%</span>
            Working/Reschedule
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div class="chart-card">
          <div class="chart-title">Distribusi Status</div>
          <div class="chart-sub">Klik status untuk lihat detail</div>
          <div style="height:240px"><canvas id="chartStatus"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="chart-card">
          <div class="chart-title">Breakdown Status</div>
          <div class="chart-sub">Klik baris untuk drill-down</div>
          <div id="statusList" class="mt-1"></div>
        </div>
      </div>
    </div>
  </div>

</div><!-- /container-fluid -->