<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller
 * MVC CodeIgniter 3 — CRM Monitoring Komplain Konsumen
 *
 * Accessible at: (base_url)/dash_crm/
 */
class Dash_crm extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('dash_crm/model_dash_crm', 'dashboard_m');
        $this->load->helper(['url', 'date']);
        $this->load->library('session');
    }

    // ============================================================
    // Halaman Utama Dashboard
    // ============================================================
    public function index() {
        $filter = $this->_get_filter();

        // VERIFIKASI
        $total_komplain   = $this->dashboard_m->get_total_komplain($filter);
        $terverifikasi    = $this->dashboard_m->get_terverifikasi($filter);
        $belum_verifikasi = $this->dashboard_m->get_belum_verifikasi($filter);
        $verif_per_sumber = $this->dashboard_m->get_verifikasi_per_sumber($filter);

        $pct_verif = $total_komplain > 0
            ? round(($terverifikasi / $total_komplain) * 100)
            : 0;

        $total_sosmed      = $verif_per_sumber['sosmed']['terverifikasi'] + $verif_per_sumber['sosmed']['belum'];
        $pct_verif_sosmed  = $total_sosmed > 0
            ? round(($verif_per_sumber['sosmed']['terverifikasi'] / $total_sosmed) * 100)
            : 0;
        $pct_konsumen_verif = ($verif_per_sumber['konsumen']['terverifikasi'] + $verif_per_sumber['konsumen']['belum']) > 0
            ? round(($verif_per_sumber['konsumen']['terverifikasi'] /
              ($verif_per_sumber['konsumen']['terverifikasi'] + $verif_per_sumber['konsumen']['belum'])) * 100)
            : 0;

        // ESKALASI
        $sudah_eskalasi = $this->dashboard_m->get_sudah_eskalasi($filter);
        $belum_eskalasi = $this->dashboard_m->get_belum_eskalasi($filter);
        $rate_eskalasi  = $total_komplain > 0
            ? round(($sudah_eskalasi / $total_komplain) * 100)
            : 0;

        $eskalasi_per_sumber = $this->dashboard_m->get_eskalasi_per_sumber($filter);

        // Hitung persentase eskalasi per sumber
        $total_konsumen = $eskalasi_per_sumber['konsumen']['sudah'] + $eskalasi_per_sumber['konsumen']['belum'];
        $total_sosmed   = $eskalasi_per_sumber['sosmed']['sudah'] + $eskalasi_per_sumber['sosmed']['belum'];

        $pct_konsumen_eskalasi = $total_konsumen > 0
            ? round(($eskalasi_per_sumber['konsumen']['sudah'] / $total_konsumen) * 100)
            : 0;

        $pct_sosmed_eskalasi = $total_sosmed > 0
            ? round(($eskalasi_per_sumber['sosmed']['sudah'] / $total_sosmed) * 100)
            : 0;

        $trend_eskalasi_raw = $this->dashboard_m->get_trend_eskalasi($filter);

        // Indeks hasil DB: ['YYYY-MM' => total]
        $db_trend = [];
        foreach ($trend_eskalasi_raw as $row) {
            $db_trend[$row['bulan']] = (int)$row['total'];
        }

        // Bangun skeleton bulan lengkap dari date_from s.d date_to (isi 0 untuk bulan tanpa data)
        $trend_labels = [];
        $trend_data   = [];
        $cur = strtotime(date('Y-m-01', strtotime($filter['date_from'])));
        $end = strtotime(date('Y-m-01', strtotime($filter['date_to'])));
        while ($cur <= $end) {
            $key            = date('Y-m', $cur);
            $trend_labels[] = date('M', $cur)."'".(date('y', $cur));
            $trend_data[]   = isset($db_trend[$key]) ? $db_trend[$key] : 0;
            $cur            = strtotime('+1 month', $cur);
        }

        // KETEPATAN WAKTU
        $ketepatan_raw = $this->dashboard_m->get_ketepatan_waktu($filter);
        $ketepatan     = [];
        $max_ontime_pct = 0;
        $min_ontime_pct = 100;
        $max_divisi = '';
        $min_divisi = '';
        $total_ketepatan = 0;
        $total_ontime    = 0;
        $total_late      = 0;
        $divisi_bawah_80 = 0;
        $divisi_atas_80  = 0;

        foreach ($ketepatan_raw as $row) {
            $pct = $row['total'] > 0 ? round(($row['ontime'] / $row['total']) * 100) : 0;
            $ketepatan[] = [
                'divisi' => $row['divisi'],
                'total'  => $row['total'],
                'ontime' => $row['ontime'],
                'late'   => $row['late'],
                'pct'    => $pct,
            ];
            $total_ketepatan += $row['total'];
            $total_ontime    += (int)$row['ontime'];
            $total_late      += (int)$row['late'];

            // Hanya hitung statistik untuk divisi yang memiliki data (total > 0)
            if ($row['total'] > 0) {
                if ($pct > $max_ontime_pct) { $max_ontime_pct = $pct; $max_divisi = $row['divisi']; }
                if ($pct < $min_ontime_pct) { $min_ontime_pct = $pct; $min_divisi = $row['divisi']; }
                if ($pct >= 80) $divisi_atas_80++; else $divisi_bawah_80++;
            }
        }

        // RATING
        $rating_summary    = $this->dashboard_m->get_rating_summary();
        $distribusi_rating = $this->dashboard_m->get_distribusi_rating();

        // STATUS
        $status_raw = $this->dashboard_m->get_status_komplain($filter);

        // Peta warna dan badge per status_id
        $status_color_map = [
            1 => ['color' => '#6B7280', 'badge' => 'waiting'],   // Waiting
            2 => ['color' => '#D97706', 'badge' => 'waiting'],   // Waiting Head Div
            3 => ['color' => '#E02424', 'badge' => 'reject'],    // Reject Level 1
            4 => ['color' => '#1A56DB', 'badge' => 'working'],   // Working On
            5 => ['color' => '#F05252', 'badge' => 'reject'],    // Reject Level 2
            6 => ['color' => '#0E9F6E', 'badge' => 'done'],      // Done
            7 => ['color' => '#E02424', 'badge' => 'reject'],    // Unsolved
            8 => ['color' => '#9061F9', 'badge' => 'waiting'],   // Rescheduled
            9 => ['color' => '#F05252', 'badge' => 'waiting'],   // Rescheduled 2
        ];

        $status_list  = [];
        $total_done   = 0;
        $total_reject = 0;
        $total_inprog = 0;

        foreach ($status_raw as $row) {
            $id    = (int)$row['id'];
            $color = isset($status_color_map[$id]) ? $status_color_map[$id]['color'] : '#6B7280';
            $badge = isset($status_color_map[$id]) ? $status_color_map[$id]['badge'] : 'waiting';
            $status_list[] = [
                'id'    => $id,
                'label' => $row['status'],
                'qty'   => (int)$row['total'],
                'color' => $color,
                'badge' => $badge,
            ];
            if ($id == 6) $total_done   = (int)$row['total'];
            if ($id == 3 || $id == 5 || $id == 7) $total_reject += (int)$row['total'];
            if ($id == 4 || $id == 8 || $id == 9) $total_inprog += (int)$row['total'];
        }

        // Daftar divisi untuk dropdown filter
        $list_divisi = $this->dashboard_m->get_list_divisi();

        // Period label
        $period_label = date('d-m-Y', strtotime($filter['date_from']))
            . ' s.d ' .
            date('d-m-Y', strtotime($filter['date_to']));

        $data = [
            // Filter
            'filter'              => $filter,
            'list_divisi'         => $list_divisi,
            'period_label'        => $period_label,

            // Section 01 Verifikasi
            'total_komplain'      => $total_komplain,
            'terverifikasi'       => $terverifikasi,
            'belum_verifikasi'    => $belum_verifikasi,
            'pct_verif'           => $pct_verif,
            'verif_per_sumber'    => $verif_per_sumber,
            'pct_verif_sosmed'    => $pct_verif_sosmed,
            'pct_konsumen_verif'  => $pct_konsumen_verif,
            'total_sosmed'        => $total_sosmed,

            // Section 02 Eskalasi
            'sudah_eskalasi'      => $sudah_eskalasi,
            'belum_eskalasi'      => $belum_eskalasi,
            'rate_eskalasi'       => $rate_eskalasi,
            'eskalasi_per_sumber' => $eskalasi_per_sumber,
            'pct_konsumen_eskalasi' => $pct_konsumen_eskalasi,
            'pct_sosmed_eskalasi'   => $pct_sosmed_eskalasi,
            'trend_labels_json'   => json_encode(array_values($trend_labels)),
            'trend_data_json'     => json_encode(array_values($trend_data)),

            // Section 03 Ketepatan Waktu
            'ketepatan'           => $ketepatan,
            'ketepatan_json'      => json_encode($ketepatan),
            'max_ontime_pct'      => $max_ontime_pct,
            'max_divisi'          => $max_divisi,
            'min_ontime_pct'      => $min_ontime_pct,
            'min_divisi'          => $min_divisi,
            'total_ketepatan'     => $total_ketepatan,
            'total_ontime'        => $total_ontime,
            'total_late'          => $total_late,
            'divisi_bawah_80'     => $divisi_bawah_80,
            'divisi_atas_80'      => $divisi_atas_80,

            // Section 04 Rating
            'rating_summary'      => $rating_summary,
            'distribusi_rating'   => $distribusi_rating,

            // Section 05 Status
            'status_list'         => $status_list,
            'status_json'         => json_encode($status_list),
            'total_done'          => $total_done,
            'total_reject'        => $total_reject,
            'total_inprog'        => $total_inprog,

            // Verifikasi chart JSON
            'verif_chart_json' => json_encode([
                'konsumen_terverifikasi' => $verif_per_sumber['konsumen']['terverifikasi'],
                'konsumen_belum'         => $verif_per_sumber['konsumen']['belum'],
                'sosmed_terverifikasi'   => $verif_per_sumber['sosmed']['terverifikasi'],
                'sosmed_belum'           => $verif_per_sumber['sosmed']['belum'],
            ]),
            'eskalasi_donut_json' => json_encode([
                'sudah' => $sudah_eskalasi,
                'belum' => $belum_eskalasi,
            ]),
        ];

        $this->load->view('dashboard_crm/header', $data);
        $this->load->view('dashboard_crm/index', $data);
        $this->load->view('dashboard_crm/footer', $data);
    }

    // ============================================================
    // AJAX — Modal Detail Komplain
    // ============================================================
    public function modal_detail() {
        // Set no-cache headers
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $type      = $this->input->get('type');
        $status_id = $this->input->get('status_id');
        $divisi    = $this->input->get('divisi');
        $page      = (int)$this->input->get('page') ?: 1;
        $per_page  = 10;
        $offset    = ($page - 1) * $per_page;
        $filter    = $this->_get_filter();

        // Get modal_sumber filter untuk verifikasi modal
        $modal_sumber = $this->input->get('modal_sumber');

        // Get modal_eskalasi filter untuk eskalasi gabungan modal
        $modal_eskalasi = $this->input->get('modal_eskalasi');

        // Get modal_ketepatan filter untuk ketepatan waktu modal
        $modal_ketepatan = $this->input->get('modal_ketepatan');

        // Get modal_verif_sumber dan modal_verif_status untuk verif_total
        $modal_verif_sumber = $this->input->get('modal_verif_sumber');
        $modal_verif_status = $this->input->get('modal_verif_status');

        // Get modal_ketepatan_sumber dan modal_ketepatan_status untuk ketepatan_total
        $modal_ketepatan_sumber = $this->input->get('modal_ketepatan_sumber');
        $modal_ketepatan_status = $this->input->get('modal_ketepatan_status');

        $extra = [];
        if ($status_id) $extra['status_id'] = $status_id;
        if ($divisi)    $extra['divisi']     = $divisi;
        if ($modal_sumber && $modal_sumber !== 'all') {
            $extra['modal_sumber'] = $modal_sumber;
        }
        if ($modal_eskalasi && $modal_eskalasi !== 'all') {
            $extra['modal_eskalasi'] = $modal_eskalasi;
        }
        if ($modal_ketepatan && $modal_ketepatan !== 'all') {
            $extra['modal_ketepatan'] = $modal_ketepatan;
        }
        if ($modal_verif_sumber && $modal_verif_sumber !== 'all') {
            $extra['modal_verif_sumber'] = $modal_verif_sumber;
        }
        if ($modal_verif_status && $modal_verif_status !== 'all') {
            $extra['modal_verif_status'] = $modal_verif_status;
        }
        if ($modal_ketepatan_sumber && $modal_ketepatan_sumber !== 'all') {
            $extra['modal_ketepatan_sumber'] = $modal_ketepatan_sumber;
        }
        if ($modal_ketepatan_status && $modal_ketepatan_status !== 'all') {
            $extra['modal_ketepatan_status'] = $modal_ketepatan_status;
        }

        $rows  = $this->dashboard_m->get_detail_modal($type, $extra, $filter, $per_page, $offset);
        $total = $this->dashboard_m->count_detail_modal($type, $extra, $filter);

        // Format data untuk response JSON
        $data = [];
        foreach ($rows as $row) {
            // Tentukan ketepatan waktu
            $waktu_status = '-';
            if ($row['done_date'] && $row['done_date'] !== '0000-00-00 00:00:00') {
                if ($row['due_date'] && $row['due_date'] !== '0000-00-00') {
                    $done = strtotime($row['done_date']);
                    $due  = strtotime($row['due_date'] . ' 23:59:59');
                    $waktu_status = ($done <= $due) ? 'On Time' : 'Late';
                } else {
                    $waktu_status = 'Done';
                }
            }

            $data[] = [
                'id_task'      => $row['id_task'],
                'konsumen'     => $row['konsumen'],
                'lokasi'       => $row['lokasi'],
                'jenis'        => $row['jenis'] ?: $row['category'],
                'status'       => $row['status_label'],
                'status_id'    => $row['status_id'],
                'divisi'       => $row['divisi'],
                'waktu_status' => $waktu_status,
                'created_at'   => $row['created_at'],
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data'    => $data,
                'total'   => $total,
                'page'    => $page,
                'per_page'=> $per_page,
            ]));
    }

    // ============================================================
    // AJAX — Ketepatan Waktu Global (detail semua divisi)
    // ============================================================
    public function ketepatan_global() {
        // Set no-cache headers
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $page           = (int)$this->input->get('page') ?: 1;
        $per_page       = (int)$this->input->get('per_page') ?: 20;
        $ketepatan      = $this->input->get('ketepatan') ?: 'all';
        $export         = $this->input->get('export');
        $filter         = $this->_get_filter();
        $due_date_from  = $this->input->get('due_date_from')  ?: '';
        $due_date_to    = $this->input->get('due_date_to')    ?: '';
        $done_date_from = $this->input->get('done_date_from') ?: '';
        $done_date_to   = $this->input->get('done_date_to')   ?: '';

        // Ambil SEMUA data ketepatan global (tanpa limit/offset di database)
        $all_rows = $this->dashboard_m->get_ketepatan_global_detail($filter);

        // Format dan filter data berdasarkan ketepatan
        $formatted_data = [];
        foreach ($all_rows as $row) {
            // Tentukan ketepatan waktu
            $waktu_status = '-';
            if ($row['done_date'] && $row['done_date'] !== '0000-00-00 00:00:00') {
                if ($row['due_date'] && $row['due_date'] !== '0000-00-00') {
                    $done = strtotime($row['done_date']);
                    $due  = strtotime($row['due_date'] . ' 23:59:59');
                    $waktu_status = ($done <= $due) ? 'On Time' : 'Late';
                } else {
                    $waktu_status = 'Done';
                }
            }

            // Filter berdasarkan ketepatan yang dipilih
            if ($ketepatan === 'ontime' && $waktu_status !== 'On Time') { continue; }
            if ($ketepatan === 'late'   && $waktu_status !== 'Late')    { continue; }

            // Filter berdasarkan due_date range
            if ($due_date_from !== '') {
                if (!$row['due_date'] || $row['due_date'] === '0000-00-00') { continue; }
                if (strtotime($row['due_date']) < strtotime($due_date_from)) { continue; }
            }
            if ($due_date_to !== '') {
                if (!$row['due_date'] || $row['due_date'] === '0000-00-00') { continue; }
                if (strtotime($row['due_date']) > strtotime($due_date_to)) { continue; }
            }

            // Filter berdasarkan done_date range
            if ($done_date_from !== '') {
                if (!$row['done_date'] || $row['done_date'] === '0000-00-00 00:00:00') { continue; }
                if (strtotime($row['done_date']) < strtotime($done_date_from)) { continue; }
            }
            if ($done_date_to !== '') {
                if (!$row['done_date'] || $row['done_date'] === '0000-00-00 00:00:00') { continue; }
                if (strtotime($row['done_date']) > strtotime($done_date_to . ' 23:59:59')) { continue; }
            }

            $formatted_data[] = [
                'id_task'      => $row['id_task'],
                'konsumen'     => $row['konsumen'],
                'lokasi'       => $row['project'],
                'divisi'       => $row['divisi'],
                'jenis'        => $row['jenis'] ?: $row['category'],
                'due_date'     => $row['due_date'] && $row['due_date'] !== '0000-00-00' ? date('d-m-Y', strtotime($row['due_date'])) : '-',
                'done_date'    => $row['done_date'] && $row['done_date'] !== '0000-00-00 00:00:00' ? date('d-m-Y H:i', strtotime($row['done_date'])) : '-',
                'waktu_status' => $waktu_status,
            ];
        }

        // Hitung total filtered data
        $total = count($formatted_data);

        // Implementasikan pagination secara manual
        $offset = ($page - 1) * $per_page;
        $paginated_data = array_slice($formatted_data, $offset, $per_page);

        // Jangan export di sini, gunakan endpoint export_ketepatan_data() untuk file download
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data'    => $paginated_data, // Per-page data saja
                'total'   => $total,
                'page'    => $page,
                'per_page'=> $per_page,
            ]));
    }

    // ============================================================
    // EXPORT — Export ketepatan waktu ke CSV
    // ============================================================
    public function export_ketepatan_data() {
        try {
            $ketepatan      = $this->input->get('ketepatan')      ?: 'all';
            $filter         = $this->_get_filter();
            $due_date_from  = $this->input->get('due_date_from')  ?: '';
            $due_date_to    = $this->input->get('due_date_to')    ?: '';
            $done_date_from = $this->input->get('done_date_from') ?: '';
            $done_date_to   = $this->input->get('done_date_to')   ?: '';

            // Ambil SEMUA data ketepatan global
            $all_rows = $this->dashboard_m->get_ketepatan_global_detail($filter);

            // Format dan filter data berdasarkan ketepatan
            $formatted_data = [];
            foreach ($all_rows as $row) {
                // Tentukan ketepatan waktu
                $waktu_status = '-';
                if ($row['done_date'] && $row['done_date'] !== '0000-00-00 00:00:00') {
                    if ($row['due_date'] && $row['due_date'] !== '0000-00-00') {
                        $done = strtotime($row['done_date']);
                        $due  = strtotime($row['due_date'] . ' 23:59:59');
                        $waktu_status = ($done <= $due) ? 'On Time' : 'Late';
                    } else {
                        $waktu_status = 'Done';
                    }
                }

                // Filter berdasarkan ketepatan yang dipilih
                if ($ketepatan === 'ontime' && $waktu_status !== 'On Time') { continue; }
                if ($ketepatan === 'late'   && $waktu_status !== 'Late')    { continue; }

                // Filter berdasarkan due_date range
                if ($due_date_from !== '') {
                    if (!$row['due_date'] || $row['due_date'] === '0000-00-00') { continue; }
                    if (strtotime($row['due_date']) < strtotime($due_date_from)) { continue; }
                }
                if ($due_date_to !== '') {
                    if (!$row['due_date'] || $row['due_date'] === '0000-00-00') { continue; }
                    if (strtotime($row['due_date']) > strtotime($due_date_to)) { continue; }
                }

                // Filter berdasarkan done_date range
                if ($done_date_from !== '') {
                    if (!$row['done_date'] || $row['done_date'] === '0000-00-00 00:00:00') { continue; }
                    if (strtotime($row['done_date']) < strtotime($done_date_from)) { continue; }
                }
                if ($done_date_to !== '') {
                    if (!$row['done_date'] || $row['done_date'] === '0000-00-00 00:00:00') { continue; }
                    if (strtotime($row['done_date']) > strtotime($done_date_to . ' 23:59:59')) { continue; }
                }

                $formatted_data[] = [
                    'id_task'      => $row['id_task'],
                    'konsumen'     => $row['konsumen'],
                    'lokasi'       => $row['project'],
                    'blok'         => $row['blok'] ?: '-',
                    'divisi'       => $row['divisi'],
                    'jenis'        => $row['jenis'] ?: $row['category'],
                    'due_date'     => $row['due_date'] && $row['due_date'] !== '0000-00-00' ? date('d-m-Y', strtotime($row['due_date'])) : '-',
                    'done_date'    => $row['done_date'] && $row['done_date'] !== '0000-00-00 00:00:00' ? date('d-m-Y H:i', strtotime($row['done_date'])) : '-',
                    'waktu_status' => $waktu_status,
                ];
            }

            if (empty($formatted_data)) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'error'   => 'Tidak ada data untuk diexport',
                    ]));
                return;
            }

            // Helper sanitize function
            $sanitize = function($value, $default = '') {
                if ($value === null || $value === false) {
                    return $default;
                }
                return trim(strip_tags((string)$value));
            };

            // Prepare CSV headers
            $headers = [
                'ID Komplain',
                'Konsumen',
                'Lokasi',
                'Blok',
                'Divisi',
                'Jenis',
                'Due Date',
                'Done Date',
                'Status Ketepatan',
            ];

            // Build CSV content
            $csv_content = implode(',', array_map(function($h) { return '"' . str_replace('"', '""', $h) . '"'; }, $headers)) . "\n";

            foreach ($formatted_data as $row) {
                $line = [
                    $sanitize($row['id_task'], '-'),
                    $sanitize($row['konsumen'], '-'),
                    $sanitize($row['lokasi'], '-'),
                    $sanitize($row['blok'], '-'),
                    $sanitize($row['divisi'], '-'),
                    $sanitize($row['jenis'], '-'),
                    $sanitize($row['due_date'], '-'),
                    $sanitize($row['done_date'], '-'),
                    $sanitize($row['waktu_status'], '-'),
                ];
                $csv_content .= implode(',', array_map(function($v) { return '"' . str_replace('"', '""', $v) . '"'; }, $line)) . "\n";
            }

            // Generate filename dengan timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $ketepatan_label = $ketepatan === 'ontime' ? 'on-time' : ($ketepatan === 'late' ? 'late' : 'semua');
            $filename = "export_ketepatan_{$ketepatan_label}_{$timestamp}.csv";

            // Output CSV file
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: no-store, no-cache, must-revalidate');

            echo "\xEF\xBB\xBF"; // BOM untuk UTF-8
            echo $csv_content;
            exit;

        } catch (Exception $e) {
            log_message('error', 'Export Ketepatan Error: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error'   => $e->getMessage(),
                ]));
        }
    }

    // ============================================================
    // AJAX — Drilldown Verifikasi (tabel detail komplain)
    // ============================================================
    public function drilldown_verifikasi() {
        try {
            // Set no-cache headers
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');

            $page     = (int)$this->input->get('page') ?: 1;
            $per_page = (int)$this->input->get('per_page') ?: 20;
            $offset   = ($page - 1) * $per_page;
            $filter   = $this->_get_filter();

            $rows  = $this->dashboard_m->get_drilldown_verifikasi($filter, $per_page, $offset);
            $total = $this->dashboard_m->count_drilldown_verifikasi($filter);

            // Format untuk response JSON
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id_task'     => $row['id_task'],
                    'konsumen'    => $row['konsumen'],
                    'lokasi'      => $row['lokasi'] ?: 'N/A',
                    'jenis'       => $row['jenis'] ?: 'N/A',
                    'status'      => $row['status'],
                ];
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'data'    => $data,
                    'total'   => $total,
                    'page'    => $page,
                    'per_page'=> $per_page,
                ]));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error'   => $e->getMessage(),
                ]));
        }
    }

    // ============================================================
    // EXPORT — Export modal detail ke CSV
    // ============================================================
    public function export_modal_data() {
        try {
            $type              = $this->input->get('type');
            $status_id         = $this->input->get('status_id');
            $divisi            = $this->input->get('divisi');
            $modal_sumber      = $this->input->get('modal_sumber');
            $modal_eskalasi    = $this->input->get('modal_eskalasi');
            $modal_ketepatan   = $this->input->get('modal_ketepatan');
            $modal_verif_sumber= $this->input->get('modal_verif_sumber');
            $modal_verif_status= $this->input->get('modal_verif_status');
            $filter            = $this->_get_filter();

            // Jika tipe drilldown_verifikasi, gunakan fungsi khusus yang konsisten
            if ($type === 'drilldown_verifikasi') {
                // Support filter drilldown_sumber yang di-pass dari JavaScript
                $drilldown_sumber = $this->input->get('modal_sumber');
                $rows = $this->dashboard_m->get_drilldown_verifikasi_export($filter, $drilldown_sumber);

                if (empty($rows)) {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'success' => false,
                            'error'   => 'Tidak ada data untuk diexport',
                        ]));
                    return;
                }

                // Helper sanitize function
                $sanitize = function($value, $default = '') {
                    if ($value === null || $value === false) {
                        return $default;
                    }
                    return trim(strip_tags((string)$value));
                };

                // Prepare CSV headers untuk drilldown
                $headers = [
                    'ID Komplain',
                    'Konsumen',
                    'Lokasi',
                    'Blok',
                    'Divisi',
                    'Jenis',
                    'Status',
                    'Tanggal Dibuat',
                ];

                // Build CSV content
                $csv_content = implode(',', array_map(function($h) { return '"' . str_replace('"', '""', $h) . '"'; }, $headers)) . "\n";

                foreach ($rows as $row) {
                    $line = [
                        $sanitize($row['id_task'], '-'),
                        $sanitize($row['konsumen'], '-'),
                        $sanitize($row['lokasi'], '-'),
                        $sanitize($row['blok'], '-'),
                        $sanitize($row['divisi'], '-'),
                        $sanitize($row['jenis_kategori'], '-'),
                        $sanitize($row['status_label'], 'Unknown'),
                        $sanitize($row['created_at'], '-'),
                    ];
                    $csv_content .= implode(',', array_map(function($v) { return '"' . str_replace('"', '""', $v) . '"'; }, $line)) . "\n";
                }

                // Generate filename dengan timestamp
                $timestamp = date('Y-m-d_H-i-s');
                $filename = "export_drilldown_verifikasi_{$timestamp}.csv";

                // Output CSV file
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Pragma: no-cache');
                header('Expires: 0');
                header('Cache-Control: no-store, no-cache, must-revalidate');

                echo "\xEF\xBB\xBF"; // BOM untuk UTF-8
                echo $csv_content;
                exit;
            }

            // Untuk tipe lainnya, gunakan get_detail_modal_export
            $extra = [];
            if ($status_id) $extra['status_id'] = $status_id;
            if ($divisi)    $extra['divisi']     = $divisi;
            if ($modal_sumber && $modal_sumber !== 'all') {
                $extra['modal_sumber'] = $modal_sumber;
            }
            if ($modal_eskalasi && $modal_eskalasi !== 'all') {
                $extra['modal_eskalasi'] = $modal_eskalasi;
            }
            if ($modal_verif_sumber && $modal_verif_sumber !== 'all') {
                $extra['modal_verif_sumber'] = $modal_verif_sumber;
            }
            if ($modal_verif_status && $modal_verif_status !== 'all') {
                $extra['modal_verif_status'] = $modal_verif_status;
            }

            // Ambil data untuk export (tanpa pagination)
            $rows = $this->dashboard_m->get_detail_modal_export($type, $extra, $filter);

            if (empty($rows)) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'error'   => 'Tidak ada data untuk diexport',
                    ]));
                return;
            }

            // Helper function untuk konversi null ke string
            $sanitize = function($value, $default = '') {
                if ($value === null || $value === false) {
                    return $default;
                }
                // Hapus HTML tags dan trim
                return trim(strip_tags((string)$value));
            };

            // Tentukan header berdasarkan tipe export
            $headers = [
                'ID Komplain',
                'Konsumen',
                'Lokasi',
                'Blok',
                'Jenis',
                'Divisi',
                'Status',
                'Tanggal Dibuat',
                'Tanggal Diverifikasi',
                'Diverifikasi Oleh',
                'Catatan Verifikasi',
            ];

            // Jika tipe divisi (ketepatan), tambahkan kolom ketepatan
            if ($type === 'divisi' || strpos($type, 'ketepatan') !== false) {
                $headers = [
                    'ID Komplain',
                    'Konsumen',
                    'Lokasi',
                    'Blok',
                    'Divisi',
                    'Jenis',
                    'Due Date',
                    'Done Date',
                    'Status Ketepatan',
                    'Tanggal Dibuat',
                ];
            }

            // Build CSV headers
            $csv_content = implode(',', array_map(function($h) { return '"' . str_replace('"', '""', $h) . '"'; }, $headers)) . "\n";

            // Build CSV rows
            foreach ($rows as $row) {
                if ($type === 'divisi' || strpos($type, 'ketepatan') !== false) {
                    // Format untuk ketepatan
                    $waktu_status = '-';
                    if ($row['done_date'] && $row['done_date'] !== '0000-00-00 00:00:00') {
                        if ($row['due_date'] && $row['due_date'] !== '0000-00-00') {
                            $done = strtotime($row['done_date']);
                            $due  = strtotime($row['due_date'] . ' 23:59:59');
                            $waktu_status = ($done <= $due) ? 'On Time' : 'Late';
                        } else {
                            $waktu_status = 'Done';
                        }
                    }

                    // Filter ketepatan jika modal_ketepatan diberikan
                    if (!empty($modal_ketepatan) && $modal_ketepatan !== 'all') {
                        if ($modal_ketepatan === 'ontime' && $waktu_status !== 'On Time') {
                            continue;
                        }
                        if ($modal_ketepatan === 'late' && $waktu_status !== 'Late') {
                            continue;
                        }
                    }

                    $line = [
                        $sanitize($row['id_task'], '-'),
                        $sanitize($row['konsumen'], '-'),
                        $sanitize($row['lokasi'], '-'),
                        $sanitize($row['blok'], '-'),
                        $sanitize($row['divisi'], '-'),
                        $sanitize($row['jenis'] ?: $row['category'], '-'),
                        $row['due_date'] && $row['due_date'] !== '0000-00-00' ? date('d-m-Y', strtotime($row['due_date'])) : '-',
                        $row['done_date'] && $row['done_date'] !== '0000-00-00 00:00:00' ? date('d-m-Y H:i', strtotime($row['done_date'])) : '-',
                        $waktu_status,
                        $sanitize($row['created_at'], '-'),
                    ];
                } else {
                    // Format untuk verifikasi dan eskalasi
                    $line = [
                        $sanitize($row['id_task'], '-'),
                        $sanitize($row['konsumen'], '-'),
                        $sanitize($row['lokasi'], '-'),
                        $sanitize($row['blok'], '-'),
                        $sanitize($row['jenis'] ?: $row['category'], '-'),
                        $sanitize($row['divisi'], '-'),
                        $sanitize($row['status_label'], 'Unknown'),
                        $sanitize($row['created_at'], '-'),
                        $sanitize($row['verified_at'], '-'),
                        $sanitize($row['verified_name'], '-'),
                        $sanitize($row['verified_note'], '-'),
                    ];
                }

                $csv_content .= implode(',', array_map(function($v) { return '"' . str_replace('"', '""', $v) . '"'; }, $line)) . "\n";
            }

            // Generate filename dengan timestamp dan tipe
            $timestamp = date('Y-m-d_H-i-s');
            $type_label = str_replace(['verif_', 'esk_', 'ketepatan_'], '', $type);
            $filename = "export_komplain_{$type_label}_{$timestamp}.csv";

            // Output CSV file
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: no-store, no-cache, must-revalidate');

            echo "\xEF\xBB\xBF"; // BOM untuk UTF-8
            echo $csv_content;
            exit;

        } catch (Exception $e) {
            log_message('error', 'Export error: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error'   => $e->getMessage(),
                ]));
        }
    }

    // ============================================================
    // Helper: baca filter dari GET request
    // ============================================================
    private function _get_filter() {
        $date_from = $this->input->get('date_from') ?: '2025-01-01';
        $date_to   = $this->input->get('date_to')   ?: date('Y-m-d');
        $sumber    = $this->input->get('sumber')    ?: 'all';
        $divisi    = $this->input->get('divisi')    ?: 'all';

        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) $date_from = '2025-01-01';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to))   $date_to   = date('Y-m-d');

        return [
            'date_from' => $date_from,
            'date_to'   => $date_to,
            'sumber'    => $sumber,
            'divisi'    => $divisi,
        ];
    }
}
