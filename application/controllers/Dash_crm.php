<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dash_crm
 *
 * Main controller for the CRM Complaint Monitoring Dashboard.
 * Serves the dashboard page, AJAX endpoints for charts/modals,
 * and Excel export endpoints.
 */
class Dash_crm extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('dash_crm/model_dash_crm', 'dashboard_m');
        $this->load->helper(['url', 'date']);
        $this->load->library('session');
    }

    // ---------------------------------------------------------------
    //  Dashboard Page
    // ---------------------------------------------------------------

    public function index() {
        $filter = $this->_get_filter();

        $total_komplain   = $this->dashboard_m->get_total_komplain($filter);
        $terverifikasi    = $this->dashboard_m->get_terverifikasi($filter);
        $belum_verifikasi = $this->dashboard_m->get_belum_verifikasi($filter);
        $verif_per_sumber = $this->dashboard_m->get_verifikasi_per_sumber($filter);

        $this->_zero_out_unselected_sumber($verif_per_sumber, $filter['sumber'],
            ['terverifikasi' => 0, 'belum' => 0]);

        $pct_verif = $total_komplain > 0
            ? round(($terverifikasi / $total_komplain) * 100)
            : 0;

        $total_sosmed      = $verif_per_sumber['sosmed']['terverifikasi'] + $verif_per_sumber['sosmed']['belum'];
        $pct_verif_sosmed  = $total_sosmed > 0
            ? round(($verif_per_sumber['sosmed']['terverifikasi'] / $total_sosmed) * 100)
            : 0;
        $total_konsumen_verif = $verif_per_sumber['konsumen']['terverifikasi'] + $verif_per_sumber['konsumen']['belum'];
        $pct_konsumen_verif = $total_konsumen_verif > 0
            ? round(($verif_per_sumber['konsumen']['terverifikasi'] / $total_konsumen_verif) * 100)
            : 0;

        $sudah_eskalasi = $this->dashboard_m->get_sudah_eskalasi($filter);
        $belum_eskalasi = $this->dashboard_m->get_belum_eskalasi($filter);
        $rate_eskalasi  = $total_komplain > 0
            ? round(($sudah_eskalasi / $total_komplain) * 100)
            : 0;

        $eskalasi_per_sumber = $this->dashboard_m->get_eskalasi_per_sumber($filter);

        $this->_zero_out_unselected_sumber($eskalasi_per_sumber, $filter['sumber'],
            ['sudah' => 0, 'belum' => 0]);

        $total_konsumen = $eskalasi_per_sumber['konsumen']['sudah'] + $eskalasi_per_sumber['konsumen']['belum'];
        $total_sosmed   = $eskalasi_per_sumber['sosmed']['sudah'] + $eskalasi_per_sumber['sosmed']['belum'];

        $pct_konsumen_eskalasi = $total_konsumen > 0
            ? round(($eskalasi_per_sumber['konsumen']['sudah'] / $total_konsumen) * 100)
            : 0;
        $pct_sosmed_eskalasi = $total_sosmed > 0
            ? round(($eskalasi_per_sumber['sosmed']['sudah'] / $total_sosmed) * 100)
            : 0;

        $trend_eskalasi_raw = $this->dashboard_m->get_trend_eskalasi($filter);
        $trend_labels = [];
        $trend_data   = [];
        foreach ($trend_eskalasi_raw as $row) {
            $ts = strtotime($row['bulan'] . '-01');
            $trend_labels[] = date('M', $ts) . "'" . date('y', $ts);
            $trend_data[]   = (int)$row['total'];
        }

        $chart_data     = $this->_build_chart_data($filter);
        $ketepatan      = $chart_data['ketepatan'];
        $max_ontime_pct = 0;
        $min_ontime_pct = 100;
        $max_divisi     = '';
        $min_divisi     = '';
        $total_ketepatan = 0;
        $total_ontime    = 0;
        $total_late      = 0;
        $divisi_bawah_80 = 0;
        $divisi_atas_80  = 0;

        foreach ($ketepatan as $row) {
            $total_ketepatan += $row['total'];
            $total_ontime    += (int)$row['ontime'];
            $total_late      += (int)$row['late'];
            $pct = $row['pct'];

            if ($row['total'] > 0) {
                if ($pct > $max_ontime_pct) { $max_ontime_pct = $pct; $max_divisi = $row['label']; }
                if ($pct < $min_ontime_pct) { $min_ontime_pct = $pct; $min_divisi = $row['label']; }
                if ($pct >= 80) $divisi_atas_80++; else $divisi_bawah_80++;
            }
        }

        $rating_summary    = $this->dashboard_m->get_rating_summary($filter);
        $distribusi_rating = $this->dashboard_m->get_distribusi_rating($filter);

        $status_list  = $chart_data['status'];
        $total_done   = 0;
        $total_reject = 0;
        $total_inprog = 0;

        foreach ($status_list as $s) {
            $id = $s['id'];
            if ($id == 6) $total_done = $s['qty'];
            if ($id == 3 || $id == 5 || $id == 7) $total_reject += $s['qty'];
            if ($id == 4 || $id == 8 || $id == 9) $total_inprog += $s['qty'];
        }

        $list_divisi = $this->dashboard_m->get_list_divisi();

        $period_label = date('d-m-Y', strtotime($filter['date_from']))
            . ' s.d '
            . date('d-m-Y', strtotime($filter['date_to']));

        $data = [
            'filter'              => $filter,
            'list_divisi'         => $list_divisi,
            'period_label'        => $period_label,

            'total_komplain'      => $total_komplain,
            'terverifikasi'       => $terverifikasi,
            'belum_verifikasi'    => $belum_verifikasi,
            'pct_verif'           => $pct_verif,
            'verif_per_sumber'    => $verif_per_sumber,
            'pct_verif_sosmed'    => $pct_verif_sosmed,
            'pct_konsumen_verif'  => $pct_konsumen_verif,
            'total_sosmed'        => $total_sosmed,

            'sudah_eskalasi'        => $sudah_eskalasi,
            'belum_eskalasi'        => $belum_eskalasi,
            'rate_eskalasi'         => $rate_eskalasi,
            'eskalasi_per_sumber'   => $eskalasi_per_sumber,
            'pct_konsumen_eskalasi' => $pct_konsumen_eskalasi,
            'pct_sosmed_eskalasi'   => $pct_sosmed_eskalasi,

            'ketepatan'       => $ketepatan,
            'max_ontime_pct'  => $max_ontime_pct,
            'max_divisi'      => $max_divisi,
            'min_ontime_pct'  => $min_ontime_pct,
            'min_divisi'      => $min_divisi,
            'total_ketepatan' => $total_ketepatan,
            'total_ontime'    => $total_ontime,
            'total_late'      => $total_late,
            'divisi_bawah_80' => $divisi_bawah_80,
            'divisi_atas_80'  => $divisi_atas_80,

            'rating_summary'    => $rating_summary,
            'distribusi_rating' => $distribusi_rating,

            'status_list'  => $status_list,
            'total_done'   => $total_done,
            'total_reject' => $total_reject,
            'total_inprog' => $total_inprog,
        ];

        $this->load->view('dashboard_crm/header', $data);
        $this->load->view('dashboard_crm/index', $data);
        $this->load->view('dashboard_crm/footer', $data);
    }

    // ---------------------------------------------------------------
    //  AJAX Endpoints
    // ---------------------------------------------------------------

    public function chart_data() {
        $this->_guard_ajax();
        $this->_no_cache();

        $filter = $this->_get_filter();
        $data   = $this->_build_chart_data($filter);

        $this->_json_response(['success' => true, 'data' => $data]);
    }

    public function modal_detail() {
        $this->_guard_ajax();
        $this->_no_cache();

        $type      = $this->input->get('type');
        $status_id = $this->input->get('status_id');
        $divisi    = $this->input->get('divisi');
        $page      = (int)$this->input->get('page') ?: 1;
        $per_page  = max(10, min(5000, (int)$this->input->get('per_page') ?: 10));
        $offset    = ($page - 1) * $per_page;
        $filter    = $this->_get_filter();

        $mf_sumber         = $this->input->get('mf_sumber')         ?: 'all';
        $mf_status         = $this->input->get('mf_status')         ?: 'all';
        $mf_due_date_from  = $this->input->get('mf_due_date_from')  ?: '';
        $mf_due_date_to    = $this->input->get('mf_due_date_to')    ?: '';
        $mf_done_date_from = $this->input->get('mf_done_date_from') ?: '';
        $mf_done_date_to   = $this->input->get('mf_done_date_to')   ?: '';

        $extra = [];
        if ($status_id) $extra['status_id'] = $status_id;
        if ($divisi)    $extra['divisi']     = $divisi;

        $this->_route_modal_filters($type, $extra, $mf_sumber, $mf_status,
            $mf_due_date_from, $mf_due_date_to, $mf_done_date_from, $mf_done_date_to);

        $rows  = $this->dashboard_m->get_detail_modal($type, $extra, $filter, $per_page, $offset);
        $total = $this->dashboard_m->count_detail_modal($type, $extra, $filter);

        $data = [];
        foreach ($rows as $row) {
            $waktu_status  = $this->_resolve_waktu_status($row);
            $due_date_fmt  = $this->_format_date($row['due_date'], '0000-00-00', 'd-m-Y');
            $done_date_fmt = $this->_format_date($row['done_date'], '0000-00-00 00:00:00', 'd-m-Y H:i');

            $data[] = [
                'id_task'      => $row['id_task'],
                'konsumen'     => $row['konsumen'],
                'lokasi'       => $row['lokasi'],
                'jenis'        => in_array($type, ['divisi', 'ketepatan_total']) ? $row['category'] : ($row['jenis'] ?: $row['category']),
                'status'       => $row['status_label'],
                'status_id'    => $row['status_id'],
                'divisi'       => $row['divisi'],
                'waktu_status' => $waktu_status,
                'created_at'   => $row['created_at'],
                'due_date'     => $due_date_fmt,
                'done_date'    => $done_date_fmt,
            ];
        }

        $this->_json_response([
            'success'  => true,
            'data'     => $data,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $per_page,
        ]);
    }

    public function ketepatan_global() {
        $this->_guard_ajax();
        $this->_no_cache();

        $page           = (int)$this->input->get('page') ?: 1;
        $per_page       = (int)$this->input->get('per_page') ?: 20;
        $ketepatan      = $this->input->get('ketepatan') ?: 'all';
        $filter         = $this->_get_filter();
        $due_date_from  = $this->input->get('due_date_from')  ?: '';
        $due_date_to    = $this->input->get('due_date_to')    ?: '';
        $done_date_from = $this->input->get('done_date_from') ?: '';
        $done_date_to   = $this->input->get('done_date_to')   ?: '';

        $all_rows = $this->dashboard_m->get_ketepatan_global_detail($filter);

        $formatted_data = $this->_filter_ketepatan_rows($all_rows, $ketepatan,
            $due_date_from, $due_date_to, $done_date_from, $done_date_to);

        $total  = count($formatted_data);
        $offset = ($page - 1) * $per_page;
        $paginated_data = array_slice($formatted_data, $offset, $per_page);

        $this->_json_response([
            'success'  => true,
            'data'     => $paginated_data,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $per_page,
        ]);
    }

    public function drilldown_verifikasi() {
        $this->_guard_ajax();

        try {
            $this->_no_cache();

            $page     = (int)$this->input->get('page') ?: 1;
            $per_page = (int)$this->input->get('per_page') ?: 20;
            $offset   = ($page - 1) * $per_page;
            $filter   = $this->_get_filter();

            $rows  = $this->dashboard_m->get_drilldown_verifikasi($filter, $per_page, $offset);
            $total = $this->dashboard_m->count_drilldown_verifikasi($filter);

            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id_task'  => $row['id_task'],
                    'konsumen' => $row['konsumen'],
                    'lokasi'   => $row['lokasi'] ?: 'N/A',
                    'jenis'    => $row['jenis'] ?: 'N/A',
                    'status'   => $row['status'],
                ];
            }

            $this->_json_response([
                'success'  => true,
                'data'     => $data,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $per_page,
            ]);
        } catch (Exception $e) {
            $this->_json_response(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function rating_drilldown() {
        $this->_guard_ajax();

        try {
            $this->_no_cache();

            $bintang  = $this->input->get('bintang');
            $page     = (int)$this->input->get('page') ?: 1;
            $per_page = max(10, min(5000, (int)$this->input->get('per_page') ?: 10));
            $offset   = ($page - 1) * $per_page;
            $filter   = $this->_get_filter();

            if ($bintang === 'null' || $bintang === '') $bintang = null;

            $rows  = $this->dashboard_m->get_rating_drilldown($bintang, $filter, $per_page, $offset);
            $total = $this->dashboard_m->count_rating_drilldown($bintang, $filter);

            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id_task'    => $row['id_task'],
                    'konsumen'   => $row['konsumen'] ?: '-',
                    'project'    => $row['project'] ?: '-',
                    'blok'       => $row['blok'] ?: '-',
                    'jenis'      => $row['jenis'] ?: '-',
                    'divisi'     => $row['divisi'] ?: '-',
                    'pelayanan'  => $row['pelayanan'],
                    'kualitas'   => $row['kualitas'],
                    'respons'    => $row['respons'],
                    'feedback'   => $row['feedback'] ?: '-',
                    'avg_rating' => $row['avg_rating'],
                    'created_at' => $row['created_at'] ? date('d-m-Y', strtotime($row['created_at'])) : '-',
                ];
            }

            $this->_json_response([
                'success'  => true,
                'data'     => $data,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $per_page,
                'bintang'  => $bintang,
            ]);
        } catch (Exception $e) {
            $this->_json_response(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ---------------------------------------------------------------
    //  Export Endpoints
    // ---------------------------------------------------------------

    public function export_ketepatan_data() {
        $this->_guard_referer();

        try {
            $ketepatan      = $this->input->get('ketepatan')      ?: 'all';
            $filter         = $this->_get_filter();
            $due_date_from  = $this->input->get('due_date_from')  ?: '';
            $due_date_to    = $this->input->get('due_date_to')    ?: '';
            $done_date_from = $this->input->get('done_date_from') ?: '';
            $done_date_to   = $this->input->get('done_date_to')   ?: '';

            $all_rows = $this->dashboard_m->get_ketepatan_global_detail($filter);

            $formatted_data = $this->_filter_ketepatan_rows($all_rows, $ketepatan,
                $due_date_from, $due_date_to, $done_date_from, $done_date_to, true);

            if (empty($formatted_data)) {
                $this->_json_response(['success' => false, 'error' => 'Tidak ada data untuk diexport']);
                return;
            }

            $headers = ['ID Komplain', 'Konsumen', 'Lokasi', 'Blok', 'Divisi', 'Kategori', 'Due Date', 'Done Date', 'Status Ketepatan'];

            $ketepatan_label = $ketepatan === 'ontime' ? 'On Time' : ($ketepatan === 'late' ? 'Late' : 'Semua');
            $ketepatan_file  = $ketepatan === 'ontime' ? 'on-time' : ($ketepatan === 'late' ? 'late' : 'semua');

            $excel_content = $this->_excel_header('Ketepatan ' . $ketepatan_label, $headers);

            $sanitize = $this->_sanitizer();
            $esc      = $this->_escaper();

            foreach ($formatted_data as $row) {
                $status = $sanitize($row['waktu_status'], '-');
                $bg = $status === 'On Time' ? '#D1FAE5' : ($status === 'Late' ? '#FEE2E2' : '#F3F4F6');
                $excel_content .= '<tr>';
                $excel_content .= '<td style="padding:5px 8px;font-family:Courier New">' . $esc($sanitize($row['id_task'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['konsumen'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['lokasi'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['blok'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['divisi'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['jenis'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($sanitize($row['due_date'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($sanitize($row['done_date'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px;background:' . $bg . ';font-weight:bold;text-align:center">' . $esc($status) . '</td>';
                $excel_content .= '</tr>';
            }

            $excel_content .= '</tbody></table></body></html>';

            $this->_send_excel("export_ketepatan_{$ketepatan_file}_" . date('Y-m-d_H-i-s') . ".xls", $excel_content);

        } catch (Exception $e) {
            log_message('error', 'Export Ketepatan Error: ' . $e->getMessage());
            $this->_json_response(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function export_rating_data() {
        $this->_guard_referer();

        try {
            $bintang = $this->input->get('bintang');
            $filter  = $this->_get_filter();

            if ($bintang === 'null' || $bintang === '') $bintang = null;

            $rows = $this->dashboard_m->get_rating_drilldown_export($bintang, $filter);

            if (empty($rows)) {
                $this->_json_response(['success' => false, 'error' => 'Tidak ada data untuk diexport']);
                return;
            }

            $sanitize = $this->_sanitizer();
            $esc      = $this->_escaper();

            $headers = ['ID Komplain', 'Konsumen', 'Lokasi', 'Blok', 'Jenis', 'Divisi',
                        'Avg Rating', 'Pelayanan', 'Kualitas', 'Respons', 'Feedback', 'Tanggal'];

            $bintang_label = $bintang !== null ? $bintang . ' Bintang' : 'Semua';
            $excel_content = $this->_excel_header('Rating ' . $bintang_label, $headers);

            $ratingCell = function($val) use ($sanitize, $esc) {
                $v = $sanitize($val, '-');
                if (!is_numeric($v)) return '<td style="padding:5px 8px;text-align:center">-</td>';
                $n = (float)$v;
                $c = $n >= 4 ? '#0E9F6E' : ($n >= 3 ? '#D97706' : '#E02424');
                return '<td style="padding:5px 8px;text-align:center;color:' . $c . ';font-weight:bold">' . number_format($n, 1) . '</td>';
            };

            foreach ($rows as $row) {
                $avg     = $sanitize($row['avg_rating'], '-');
                $avg_num = is_numeric($avg) ? (float)$avg : 0;
                $bg      = $avg === '-' ? '#F3F4F6' : ($avg_num >= 4 ? '#D1FAE5' : ($avg_num >= 3 ? '#FEF3C7' : '#FEE2E2'));

                $created  = ($row['created_at'] && $row['created_at'] !== '0000-00-00 00:00:00')
                    ? date('d-m-Y', strtotime($row['created_at'])) : '-';

                $excel_content .= '<tr>';
                $excel_content .= '<td style="padding:5px 8px;font-family:Courier New">' . $esc($sanitize($row['id_task'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['konsumen'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['project'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['blok'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['jenis'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['divisi'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px;text-align:center;background:' . $bg . ';font-weight:bold">' . $esc($avg !== '-' ? number_format($avg_num, 1) : '-') . '</td>';
                $excel_content .= $ratingCell($row['pelayanan']);
                $excel_content .= $ratingCell($row['kualitas']);
                $excel_content .= $ratingCell($row['respons']);
                $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['feedback'], '-')) . '</td>';
                $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($created) . '</td>';
                $excel_content .= '</tr>';
            }

            $excel_content .= '</tbody></table></body></html>';

            $bintang_file = $bintang !== null ? $bintang . '-bintang' : 'semua';
            $this->_send_excel("export_rating_{$bintang_file}_" . date('Y-m-d_H-i-s') . ".xls", $excel_content);

        } catch (Exception $e) {
            log_message('error', 'Export Rating Error: ' . $e->getMessage());
            $this->_json_response(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function export_modal_data() {
        $this->_guard_referer();

        try {
            $type              = $this->input->get('type');
            $status_id         = $this->input->get('status_id');
            $divisi            = $this->input->get('divisi');
            $mf_sumber         = $this->input->get('mf_sumber')         ?: 'all';
            $mf_status         = $this->input->get('mf_status')         ?: 'all';
            $mf_due_date_from  = $this->input->get('mf_due_date_from')  ?: '';
            $mf_due_date_to    = $this->input->get('mf_due_date_to')    ?: '';
            $mf_done_date_from = $this->input->get('mf_done_date_from') ?: '';
            $mf_done_date_to   = $this->input->get('mf_done_date_to')   ?: '';
            $filter            = $this->_get_filter();

            if ($type === 'drilldown_verifikasi') {
                $this->_export_drilldown_verifikasi($filter);
                return;
            }

            $extra = [];
            if ($status_id) $extra['status_id'] = $status_id;
            if ($divisi)    $extra['divisi']     = $divisi;
            if ($mf_sumber !== 'all')    $extra['mf_sumber']         = $mf_sumber;
            if ($mf_status !== 'all')    $extra['mf_status']         = $mf_status;
            if ($mf_due_date_from)       $extra['mf_due_date_from']  = $mf_due_date_from;
            if ($mf_due_date_to)         $extra['mf_due_date_to']    = $mf_due_date_to;
            if ($mf_done_date_from)      $extra['mf_done_date_from'] = $mf_done_date_from;
            if ($mf_done_date_to)        $extra['mf_done_date_to']   = $mf_done_date_to;

            $rows = $this->dashboard_m->get_detail_modal_export($type, $extra, $filter);

            if (empty($rows)) {
                $this->_json_response(['success' => false, 'error' => 'Tidak ada data untuk diexport']);
                return;
            }

            $sanitize    = $this->_sanitizer();
            $esc         = $this->_escaper();
            $isKetepatan = ($type === 'divisi' || strpos($type, 'ketepatan') !== false);

            if ($isKetepatan) {
                $headers    = ['ID Komplain', 'Konsumen', 'Lokasi', 'Blok', 'Divisi', 'Jenis', 'Due Date', 'Done Date', 'Status Ketepatan', 'Tanggal Dibuat'];
                $sheet_name = 'Ketepatan';
            } else {
                $headers    = ['ID Komplain', 'Konsumen', 'Lokasi', 'Blok', 'Jenis', 'Divisi', 'Status', 'Tanggal Dibuat', 'Tanggal Diverifikasi', 'Diverifikasi Oleh', 'Catatan Verifikasi'];
                $sheet_name = 'Detail Komplain';
            }

            $type_label    = str_replace(['verif_', 'esk_', 'ketepatan_'], '', $type);
            $excel_content = $this->_excel_header($sheet_name, $headers);

            foreach ($rows as $row) {
                if ($isKetepatan) {
                    $waktu_status = $this->_resolve_waktu_status($row);

                    if (!empty($modal_ketepatan) && $modal_ketepatan !== 'all') {
                        if ($modal_ketepatan === 'ontime' && $waktu_status !== 'On Time') continue;
                        if ($modal_ketepatan === 'late'   && $waktu_status !== 'Late')    continue;
                    }

                    $bg = $waktu_status === 'On Time' ? '#D1FAE5' : ($waktu_status === 'Late' ? '#FEE2E2' : '#F3F4F6');
                    $excel_content .= '<tr>';
                    $excel_content .= '<td style="padding:5px 8px;font-family:Courier New">' . $esc($sanitize($row['id_task'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['konsumen'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['lokasi'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['blok'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['divisi'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['jenis'] ?: $row['category'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($this->_format_date($row['due_date'], '0000-00-00', 'd-m-Y')) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($this->_format_date($row['done_date'], '0000-00-00 00:00:00', 'd-m-Y H:i')) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px;text-align:center;font-weight:bold;background:' . $bg . '">' . $esc($waktu_status) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($sanitize($row['created_at'])) . '</td>';
                    $excel_content .= '</tr>';
                } else {
                    $status    = $sanitize($row['status_label'], 'Unknown');
                    $status_bg = ['Waiting' => '#FEF3C7', 'Reject' => '#FEE2E2', 'Done' => '#D1FAE5'];
                    $bg = '';
                    foreach ($status_bg as $k => $v) { if (stripos($status, $k) !== false) { $bg = $v; break; } }

                    $excel_content .= '<tr>';
                    $excel_content .= '<td style="padding:5px 8px;font-family:Courier New">' . $esc($sanitize($row['id_task'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['konsumen'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['lokasi'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['blok'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['jenis'] ?: $row['category'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['divisi'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px;text-align:center;font-weight:bold' . ($bg ? ';background:' . $bg : '') . '">' . $esc($status) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($sanitize($row['created_at'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($sanitize($row['verified_at'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['verified_name'])) . '</td>';
                    $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['verified_note'])) . '</td>';
                    $excel_content .= '</tr>';
                }
            }

            $excel_content .= '</tbody></table></body></html>';
            $this->_send_excel("export_komplain_{$type_label}_" . date('Y-m-d_H-i-s') . ".xls", $excel_content);

        } catch (Exception $e) {
            log_message('error', 'Export error: ' . $e->getMessage());
            $this->_json_response(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ---------------------------------------------------------------
    //  Private — Chart Data Builder
    // ---------------------------------------------------------------

    private function _build_chart_data($filter) {
        $total_komplain   = $this->dashboard_m->get_total_komplain($filter);
        $terverifikasi    = $this->dashboard_m->get_terverifikasi($filter);
        $belum_verifikasi = $this->dashboard_m->get_belum_verifikasi($filter);
        $verif_per_sumber = $this->dashboard_m->get_verifikasi_per_sumber($filter);

        $this->_zero_out_unselected_sumber($verif_per_sumber, $filter['sumber'],
            ['terverifikasi' => 0, 'belum' => 0]);

        $sudah_eskalasi = $this->dashboard_m->get_sudah_eskalasi($filter);
        $belum_eskalasi = $this->dashboard_m->get_belum_eskalasi($filter);

        $trend_eskalasi_raw = $this->dashboard_m->get_trend_eskalasi($filter);
        $trend_labels = [];
        $trend_data   = [];
        foreach ($trend_eskalasi_raw as $row) {
            $ts = strtotime($row['bulan'] . '-01');
            $trend_labels[] = date('M', $ts) . "'" . date('y', $ts);
            $trend_data[]   = (int)$row['total'];
        }

        $ketepatan_raw = $this->dashboard_m->get_ketepatan_waktu($filter);
        $ketepatan = [];
        foreach ($ketepatan_raw as $row) {
            $pct = $row['total'] > 0 ? round(($row['ontime'] / $row['total']) * 100) : 0;
            $ketepatan[] = [
                'divisi' => $row['divisi'],
                'label'  => $row['label'],
                'total'  => $row['total'],
                'ontime' => $row['ontime'],
                'late'   => $row['late'],
                'pct'    => $pct,
            ];
        }

        $status_color_map = [
            1 => ['color' => '#6B7280', 'badge' => 'waiting'],
            2 => ['color' => '#D97706', 'badge' => 'waiting'],
            3 => ['color' => '#E02424', 'badge' => 'reject'],
            4 => ['color' => '#1A56DB', 'badge' => 'working'],
            5 => ['color' => '#F05252', 'badge' => 'reject'],
            6 => ['color' => '#0E9F6E', 'badge' => 'done'],
            7 => ['color' => '#E02424', 'badge' => 'reject'],
            8 => ['color' => '#9061F9', 'badge' => 'waiting'],
            9 => ['color' => '#F05252', 'badge' => 'waiting'],
        ];

        $status_raw  = $this->dashboard_m->get_status_komplain($filter);
        $status_list = [];
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
        }

        return [
            'ketepatan'       => $ketepatan,
            'status'          => $status_list,
            'verifChart'      => [
                'konsumen_terverifikasi' => $verif_per_sumber['konsumen']['terverifikasi'],
                'konsumen_belum'         => $verif_per_sumber['konsumen']['belum'],
                'sosmed_terverifikasi'   => $verif_per_sumber['sosmed']['terverifikasi'],
                'sosmed_belum'           => $verif_per_sumber['sosmed']['belum'],
            ],
            'eskalasiDonut'   => [
                'sudah' => $sudah_eskalasi,
                'belum' => $belum_eskalasi,
            ],
            'trendLabels'     => array_values($trend_labels),
            'trendData'       => array_values($trend_data),
            'totalEskalasi'   => $sudah_eskalasi,
            'terverifikasi'   => $terverifikasi,
            'belumVerifikasi' => $belum_verifikasi,
        ];
    }

    // ---------------------------------------------------------------
    //  Private — Guards & Request Helpers
    // ---------------------------------------------------------------

    private function _guard_ajax() {
        if ($this->input->is_ajax_request()) return;

        $this->output
            ->set_status_header(403)
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'Forbidden']));
        exit;
    }

    private function _guard_referer() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, base_url()) === 0) return;

        $this->output->set_status_header(403);
        exit;
    }

    private function _get_filter() {
        $default_from = date('Y-01-01');
        $default_to   = date('Y-m-d');
        $date_from = $this->input->get('date_from') ?: $default_from;
        $date_to   = $this->input->get('date_to')   ?: $default_to;
        $sumber    = $this->input->get('sumber')     ?: 'all';
        $divisi    = $this->input->get('divisi_filter') ?: $this->input->get('divisi') ?: 'all';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) $date_from = $default_from;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to))   $date_to   = $default_to;

        return [
            'date_from' => $date_from,
            'date_to'   => $date_to,
            'sumber'    => $sumber,
            'divisi'    => $divisi,
        ];
    }

    private function _no_cache() {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    private function _json_response($data) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    // ---------------------------------------------------------------
    //  Private — Data Formatting
    // ---------------------------------------------------------------

    /**
     * Determines on-time status from a row with done_date and due_date fields.
     */
    private function _resolve_waktu_status($row) {
        if (!$row['done_date'] || $row['done_date'] === '0000-00-00 00:00:00') {
            return '-';
        }
        if (!$row['due_date'] || $row['due_date'] === '0000-00-00') {
            return 'Done';
        }
        $done = strtotime($row['done_date']);
        $due  = strtotime($row['due_date'] . ' 23:59:59');
        return ($done <= $due) ? 'On Time' : 'Late';
    }

    private function _format_date($value, $zero_value, $format) {
        if (empty($value) || $value === $zero_value) return '-';
        return date($format, strtotime($value));
    }

    /**
     * Zeroes out the opposite sumber when a specific one is selected.
     */
    private function _zero_out_unselected_sumber(&$per_sumber, $sumber, $zero_values) {
        if ($sumber === 'konsumen') {
            $per_sumber['sosmed'] = $zero_values;
        } elseif ($sumber === 'sosmed') {
            $per_sumber['konsumen'] = $zero_values;
        }
    }

    /**
     * Filters and formats ketepatan global rows.
     * Pass $include_blok = true for export (adds blok field).
     */
    private function _filter_ketepatan_rows($all_rows, $ketepatan, $due_from, $due_to, $done_from, $done_to, $include_blok = false) {
        $formatted = [];

        foreach ($all_rows as $row) {
            $waktu_status = $this->_resolve_waktu_status($row);

            if ($ketepatan === 'ontime' && $waktu_status !== 'On Time') continue;
            if ($ketepatan === 'late'   && $waktu_status !== 'Late')    continue;

            if ($due_from !== '') {
                if (!$row['due_date'] || $row['due_date'] === '0000-00-00') continue;
                if (strtotime($row['due_date']) < strtotime($due_from)) continue;
            }
            if ($due_to !== '') {
                if (!$row['due_date'] || $row['due_date'] === '0000-00-00') continue;
                if (strtotime($row['due_date']) > strtotime($due_to)) continue;
            }
            if ($done_from !== '') {
                if (!$row['done_date'] || $row['done_date'] === '0000-00-00 00:00:00') continue;
                if (strtotime($row['done_date']) < strtotime($done_from)) continue;
            }
            if ($done_to !== '') {
                if (!$row['done_date'] || $row['done_date'] === '0000-00-00 00:00:00') continue;
                if (strtotime($row['done_date']) > strtotime($done_to . ' 23:59:59')) continue;
            }

            $item = [
                'id_task'      => $row['id_task'],
                'konsumen'     => $row['konsumen'],
                'lokasi'       => $row['project'],
                'divisi'       => $row['divisi'],
                'jenis'        => $row['category'],
                'due_date'     => $this->_format_date($row['due_date'], '0000-00-00', 'd-m-Y'),
                'done_date'    => $this->_format_date($row['done_date'], '0000-00-00 00:00:00', 'd-m-Y H:i'),
                'waktu_status' => $waktu_status,
            ];

            if ($include_blok) {
                $item['blok'] = $row['blok'] ?: '-';
            }

            $formatted[] = $item;
        }

        return $formatted;
    }

    // ---------------------------------------------------------------
    //  Private — Modal Filter Routing
    //  Maps unified mf_* params to the model's expected $extra keys
    //  based on modal type.
    // ---------------------------------------------------------------

    private function _route_modal_filters($type, &$extra, $mf_sumber, $mf_status,
        $mf_due_from, $mf_due_to, $mf_done_from, $mf_done_to) {

        // Types where sumber & status are already implicit — skip routing
        $ringkasan_types = [
            'verif_konsumen', 'verif_konsumen_belum', 'verif_sosmed_v', 'verif_sosmed_b',
            'esk_konsumen_sudah', 'esk_konsumen_belum', 'esk_sosmed_sudah', 'esk_sosmed_belum',
        ];

        if (!in_array($type, $ringkasan_types)) {
            if ($mf_sumber !== 'all') {
                if (strpos($type, 'verif') === 0) {
                    $extra['modal_verif_sumber'] = $mf_sumber;
                } elseif ($type === 'ketepatan_total' || $type === 'divisi') {
                    $extra['modal_ketepatan_sumber'] = $mf_sumber;
                } else {
                    $extra['modal_sumber'] = $mf_sumber;
                }
            }

            if ($mf_status !== 'all') {
                if (strpos($type, 'verif') === 0) {
                    $extra['modal_verif_status'] = $mf_status;
                } elseif (strpos($type, 'esk') === 0) {
                    $extra['modal_eskalasi'] = $mf_status;
                } elseif ($type === 'ketepatan_total' || $type === 'divisi') {
                    $extra['modal_ketepatan_status'] = $mf_status;
                }
            }
        }

        // Date keys vary by type — route to the correct model key
        $date_key_map = [
            'ketepatan_total' => ['ketepatan_total_due_date_', 'ketepatan_total_done_date_'],
            'divisi'          => ['divisi_due_date_', 'divisi_done_date_'],
        ];
        $due_prefix  = isset($date_key_map[$type]) ? $date_key_map[$type][0] : 'modal_due_date_';
        $done_prefix = isset($date_key_map[$type]) ? $date_key_map[$type][1] : 'modal_done_date_';

        if ($mf_due_from)  $extra[$due_prefix . 'from']  = $mf_due_from;
        if ($mf_due_to)    $extra[$due_prefix . 'to']    = $mf_due_to;
        if ($mf_done_from) $extra[$done_prefix . 'from'] = $mf_done_from;
        if ($mf_done_to)   $extra[$done_prefix . 'to']   = $mf_done_to;
    }

    // ---------------------------------------------------------------
    //  Private — Excel Export Helpers
    // ---------------------------------------------------------------

    private function _sanitizer() {
        return function($value, $default = '-') {
            if ($value === null || $value === false || $value === '') return $default;
            return trim(strip_tags((string)$value));
        };
    }

    private function _escaper() {
        return function($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        };
    }

    /**
     * Builds the opening HTML for Excel export (sheet name + header row).
     */
    private function _excel_header($sheet_name, $headers) {
        $esc = $this->_escaper();

        $html  = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>' . $esc($sheet_name) . '</x:Name>';
        $html .= '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body><table border="1" style="border-collapse:collapse"><thead><tr>';

        foreach ($headers as $h) {
            $html .= '<th style="background:#2563EB;color:#fff;font-weight:bold;padding:6px 10px;white-space:nowrap">' . $esc($h) . '</th>';
        }

        $html .= '</tr></thead><tbody>';
        return $html;
    }

    private function _send_excel($filename, $content) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        echo $content;
        exit;
    }

    /**
     * Handles drilldown_verifikasi export separately from generic modal export.
     */
    private function _export_drilldown_verifikasi($filter) {
        $drilldown_sumber = $this->input->get('modal_sumber');
        $rows = $this->dashboard_m->get_drilldown_verifikasi_export($filter, $drilldown_sumber);

        if (empty($rows)) {
            $this->_json_response(['success' => false, 'error' => 'Tidak ada data untuk diexport']);
            return;
        }

        $sanitize = $this->_sanitizer();
        $esc      = $this->_escaper();

        $headers = ['ID Komplain', 'Konsumen', 'Lokasi', 'Blok', 'Divisi', 'Jenis', 'Status', 'Tanggal Dibuat'];
        $excel_content = $this->_excel_header('Drilldown Verifikasi', $headers);

        $status_bg = ['Waiting' => '#FEF3C7', 'Reject' => '#FEE2E2', 'Done' => '#D1FAE5'];

        foreach ($rows as $row) {
            $status = $sanitize($row['status_label'], 'Unknown');
            $bg = '';
            foreach ($status_bg as $k => $v) { if (stripos($status, $k) !== false) { $bg = $v; break; } }

            $excel_content .= '<tr>';
            $excel_content .= '<td style="padding:5px 8px;font-family:Courier New">' . $esc($sanitize($row['id_task'])) . '</td>';
            $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['konsumen'])) . '</td>';
            $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['lokasi'])) . '</td>';
            $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['blok'])) . '</td>';
            $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['divisi'])) . '</td>';
            $excel_content .= '<td style="padding:5px 8px">' . $esc($sanitize($row['jenis_kategori'])) . '</td>';
            $excel_content .= '<td style="padding:5px 8px;text-align:center;font-weight:bold' . ($bg ? ';background:' . $bg : '') . '">' . $esc($status) . '</td>';
            $excel_content .= '<td style="padding:5px 8px;white-space:nowrap">' . $esc($sanitize($row['created_at'])) . '</td>';
            $excel_content .= '</tr>';
        }

        $excel_content .= '</tbody></table></body></html>';
        $this->_send_excel("export_drilldown_verifikasi_" . date('Y-m-d_H-i-s') . ".xls", $excel_content);
    }
}
