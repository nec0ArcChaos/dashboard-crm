<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model_dash_crm
 *
 * Data layer for the CRM Dashboard. All queries target the hris schema
 * and revolve around cm_task, cm_category, cm_status, and cm_rating.
 *
 * Status reference (cm_status):
 *   1 = Waiting | 2 = Waiting Head Div | 3 = Reject Lv.1
 *   4 = On Progress | 5 = Pending | 6 = Done
 *   7 = Reject Lv.2 | 8 = Rescheduled | 9 = Rescheduled 2
 *
 * Sumber (source) logic:
 *   status_konsumen = 1        -> dari konsumen
 *   status_konsumen IS NULL/0  -> dari sosmed/karyawan
 */
class Model_dash_crm extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // ---------------------------------------------------------------
    //  Shared Filters
    // ---------------------------------------------------------------

    private function _apply_filters($date_from = null, $date_to = null, $sumber = null, $divisi = null) {
        if ($date_from) {
            $this->db->where('t.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('t.created_at <=', $date_to . ' 23:59:59');
        }
        if ($sumber === 'konsumen') {
            $this->db->where('t.status_konsumen', 1);
        } elseif ($sumber === 'sosmed') {
            $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        }
        if ($divisi && $divisi !== 'all') {
            $this->db->where('c.divisi', $divisi);
        }
    }

    private function _apply_rating_filters($filter = []) {
        if (!empty($filter['date_from'])) {
            $this->db->where('r.created_at >=', $filter['date_from'] . ' 00:00:00');
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('r.created_at <=', $filter['date_to'] . ' 23:59:59');
        }
        if (!empty($filter['sumber']) && $filter['sumber'] === 'konsumen') {
            $this->db->where('t.status_konsumen', 1);
        } elseif (!empty($filter['sumber']) && $filter['sumber'] === 'sosmed') {
            $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $this->db->where('c.divisi', $filter['divisi']);
        }
    }

    // ---------------------------------------------------------------
    //  Verifikasi
    // ---------------------------------------------------------------

    public function get_total_komplain($filter = []) {
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    public function get_terverifikasi($filter = []) {
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status !=', 1);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    public function get_belum_verifikasi($filter = []) {
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status', 1);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    public function get_verifikasi_per_sumber($filter = []) {
        $result = [
            'konsumen' => ['terverifikasi' => 0, 'belum' => 0],
            'sosmed'   => ['terverifikasi' => 0, 'belum' => 0],
        ];

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('t.status !=', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['terverifikasi'] = $this->db->count_all_results();

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('t.status', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['belum'] = $this->db->count_all_results();

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('t.status !=', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['terverifikasi'] = $this->db->count_all_results();

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('t.status', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['belum'] = $this->db->count_all_results();

        return $result;
    }

    // ---------------------------------------------------------------
    //  Eskalasi
    //  Status 1,2,3 are always treated as "belum eskalasi"
    //  regardless of escalation_at value.
    // ---------------------------------------------------------------

    public function get_sudah_eskalasi($filter = []) {
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        $this->db->where('t.status NOT IN (1,2,3)', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    public function get_belum_eskalasi($filter = []) {
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Trend uses escalation_at for date filtering instead of created_at.
     */
    public function get_trend_eskalasi($filter = []) {
        $this->db->select("DATE_FORMAT(t.escalation_at, '%Y-%m') as bulan, COUNT(*) as total");
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        $this->db->where('t.status NOT IN (1,2,3)', null, false);

        if (!empty($filter['date_from'])) {
            $this->db->where('DATE(t.escalation_at) >=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('DATE(t.escalation_at) <=', $filter['date_to']);
        }
        if (!empty($filter['sumber']) && $filter['sumber'] === 'konsumen') {
            $this->db->where('t.status_konsumen', 1);
        } elseif (!empty($filter['sumber']) && $filter['sumber'] === 'sosmed') {
            $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $this->db->where('c.divisi', $filter['divisi']);
        }

        $this->db->group_by("DATE_FORMAT(t.escalation_at, '%Y-%m')");
        $this->db->order_by('bulan', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * "Sudah" uses escalation_at for date filtering;
     * "Belum" uses created_at via _apply_filters.
     */
    public function get_eskalasi_per_sumber($filter = []) {
        $result = [
            'konsumen' => ['sudah' => 0, 'belum' => 0],
            'sosmed'   => ['sudah' => 0, 'belum' => 0],
        ];

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        $this->db->where('t.status NOT IN (1,2,3)', null, false);
        if (!empty($filter['date_from'])) {
            $this->db->where('DATE(t.escalation_at) >=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('DATE(t.escalation_at) <=', $filter['date_to']);
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $this->db->where('c.divisi', $filter['divisi']);
        }
        $result['konsumen']['sudah'] = $this->db->count_all_results();

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['belum'] = $this->db->count_all_results();

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        $this->db->where('t.status NOT IN (1,2,3)', null, false);
        if (!empty($filter['date_from'])) {
            $this->db->where('DATE(t.escalation_at) >=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('DATE(t.escalation_at) <=', $filter['date_to']);
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $this->db->where('c.divisi', $filter['divisi']);
        }
        $result['sosmed']['sudah'] = $this->db->count_all_results();

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['belum'] = $this->db->count_all_results();

        return $result;
    }

    // ---------------------------------------------------------------
    //  Ketepatan Waktu (On-Time Performance)
    //  Only status = 6 (Done) tasks with valid due_date are evaluated.
    //  On time = CAST(done_date AS DATE) <= due_date
    // ---------------------------------------------------------------

    public function get_ketepatan_waktu($filter = []) {
        $divisi_sql = "
            SELECT DISTINCT c.divisi
            FROM hris.cm_category c
            WHERE c.divisi IS NOT NULL
            ORDER BY c.divisi
        ";
        $divisi_query = $this->db->query($divisi_sql);
        $all_divisi = $divisi_query->result_array();

        $sql = "
            SELECT
                c.divisi,
                COUNT(*) as total,
                SUM(CASE WHEN t.done_date IS NOT NULL
                         AND CAST(t.done_date AS DATE) <= t.due_date
                         AND t.due_date != '0000-00-00'
                         THEN 1 ELSE 0 END) as ontime,
                SUM(CASE WHEN t.done_date IS NOT NULL
                         AND CAST(t.done_date AS DATE) > t.due_date
                         AND t.due_date != '0000-00-00'
                         THEN 1 ELSE 0 END) as late
            FROM hris.cm_task t
            LEFT JOIN hris.cm_category c ON c.id = t.id_category
            WHERE t.status = 6
              AND t.done_date IS NOT NULL
              AND c.divisi IS NOT NULL
              AND c.divisi != 'Other'
        ";

        $params = [];
        if (!empty($filter['date_from'])) {
            $sql .= " AND t.created_at >= ?";
            $params[] = $filter['date_from'] . ' 00:00:00';
        }
        if (!empty($filter['date_to'])) {
            $sql .= " AND t.created_at <= ?";
            $params[] = $filter['date_to'] . ' 23:59:59';
        }
        if (!empty($filter['sumber']) && $filter['sumber'] === 'konsumen') {
            $sql .= " AND t.status_konsumen = 1";
        } elseif (!empty($filter['sumber']) && $filter['sumber'] === 'sosmed') {
            $sql .= " AND (t.status_konsumen IS NULL OR t.status_konsumen = 0)";
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $sql .= " AND c.divisi = ?";
            $params[] = $filter['divisi'];
        }

        $sql .= " GROUP BY c.divisi ORDER BY c.divisi";

        $query = $this->db->query($sql, $params);
        $rows  = $query->result_array();

        $ketepatan_map = [];
        foreach ($rows as $row) {
            $ketepatan_map[$row['divisi']] = [
                'total'  => (int) $row['total'],
                'ontime' => (int) $row['ontime'],
                'late'   => (int) $row['late'],
            ];
        }

        $valid_divisi = [
            'Aftersales', 'Buspro', 'Estate', 'Finance', 'Legal',
            'Logistik', 'MEP', 'Project', 'Purchasing', 'Sales',
            'Serah Terima Kunci', 'Sosmed',
        ];

        $divisi_label_map = [
            'Buspro' => 'Buspro (Berkas)',
            'Legal'  => 'Legal (Perizinan/Sertifikat)',
            'Sales'  => 'Sales/Mkt',
        ];

        $result = [];
        foreach ($valid_divisi as $divisi_name) {
            $data = isset($ketepatan_map[$divisi_name]) ? $ketepatan_map[$divisi_name] : [
                'total'  => 0,
                'ontime' => 0,
                'late'   => 0,
            ];

            if (empty($filter['divisi']) || $filter['divisi'] === 'all' || $filter['divisi'] === $divisi_name) {
                $result[] = [
                    'divisi' => $divisi_name,
                    'label'  => $divisi_label_map[$divisi_name] ?? $divisi_name,
                    'total'  => $data['total'],
                    'ontime' => $data['ontime'],
                    'late'   => $data['late'],
                ];
            }
        }

        return $result;
    }

    // ---------------------------------------------------------------
    //  Rating Konsumen
    // ---------------------------------------------------------------

    public function get_rating_summary() {
        $this->db->select('AVG(avg_rating) as avg_all, COUNT(*) as total_responden,
            AVG(pelayanan) as avg_pelayanan,
            AVG(kualitas) as avg_kualitas,
            AVG(respons) as avg_respons');
        $this->db->from('hris.cm_rating');
        $row = $this->db->get()->row_array();
        return $row;
    }

    public function get_distribusi_rating() {
        $this->db->select('ROUND(avg_rating) as bintang, COUNT(*) as total');
        $this->db->from('hris.cm_rating');
        $this->db->where('avg_rating IS NOT NULL', null, false);
        $this->db->group_by('ROUND(avg_rating)');
        $this->db->order_by('bintang', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * @param int|string|null $bintang  ROUND(avg_rating) filter, null = all
     */
    public function get_rating_drilldown($bintang = null, $filter = [], $limit = 10, $offset = 0) {
        $this->db->select('r.id_task, t.konsumen, t.project, t.blok,
            c.category as jenis, c.divisi,
            r.pelayanan, r.kualitas, r.respons, r.feedback, r.avg_rating, r.created_at');
        $this->db->from('hris.cm_rating r');
        $this->db->join('hris.cm_task t',     't.id_task = r.id_task COLLATE utf8mb4_unicode_ci', 'left', false);
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        if ($bintang !== null && $bintang !== '' && $bintang !== 'null') {
            $this->db->where('ROUND(r.avg_rating)', (int)$bintang);
        }
        $this->_apply_rating_filters($filter);
        $this->db->order_by('r.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function count_rating_drilldown($bintang = null, $filter = []) {
        $this->db->from('hris.cm_rating r');
        $this->db->join('hris.cm_task t',     't.id_task = r.id_task COLLATE utf8mb4_unicode_ci', 'left', false);
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        if ($bintang !== null && $bintang !== '' && $bintang !== 'null') {
            $this->db->where('ROUND(r.avg_rating)', (int)$bintang);
        }
        $this->_apply_rating_filters($filter);
        return $this->db->count_all_results();
    }

    public function get_rating_drilldown_export($bintang = null, $filter = []) {
        $this->db->select('r.id_task, t.konsumen, t.project, t.blok,
            c.category as jenis, c.divisi,
            r.pelayanan, r.kualitas, r.respons, r.feedback, r.avg_rating, r.created_at');
        $this->db->from('hris.cm_rating r');
        $this->db->join('hris.cm_task t',     't.id_task = r.id_task COLLATE utf8mb4_unicode_ci', 'left', false);
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        if ($bintang !== null && $bintang !== '' && $bintang !== 'null') {
            $this->db->where('ROUND(r.avg_rating)', (int)$bintang);
        }
        $this->_apply_rating_filters($filter);
        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // ---------------------------------------------------------------
    //  Status Komplain
    //  Uses LEFT JOIN to cm_status so every status row appears
    //  even when no tasks match (total = 0).
    // ---------------------------------------------------------------

    public function get_status_komplain($filter = []) {
        $params = [];
        $sql = "
            SELECT s.id, s.status, s.color,
                COUNT(CASE
                    WHEN s.id != 6 THEN t.id_task
                    WHEN s.id = 6
                         AND t.done_date IS NOT NULL
                         AND c.divisi IS NOT NULL
                         AND c.divisi != 'Other' THEN t.id_task
                    ELSE NULL
                END) as total
            FROM hris.cm_status s
            LEFT JOIN hris.cm_task t ON t.status = s.id
            LEFT JOIN hris.cm_category c ON c.id = t.id_category
        ";

        $wheres = [];
        if (!empty($filter['date_from'])) {
            $wheres[] = "(t.created_at IS NULL OR t.created_at >= ?)";
            $params[]  = $filter['date_from'] . ' 00:00:00';
        }
        if (!empty($filter['date_to'])) {
            $wheres[] = "(t.created_at IS NULL OR t.created_at <= ?)";
            $params[]  = $filter['date_to'] . ' 23:59:59';
        }
        if (!empty($filter['sumber']) && $filter['sumber'] === 'konsumen') {
            $wheres[] = "(t.status_konsumen IS NULL OR t.status_konsumen = 1)";
        } elseif (!empty($filter['sumber']) && $filter['sumber'] === 'sosmed') {
            $wheres[] = "(t.status_konsumen IS NULL OR t.status_konsumen = 0)";
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $wheres[] = "c.divisi = ?";
            $params[] = $filter['divisi'];
        }

        if ($wheres) {
            $sql .= " WHERE " . implode(' AND ', $wheres);
        }
        $sql .= " GROUP BY s.id, s.status, s.color ORDER BY s.id ASC";

        $this->db->reset_query();
        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    // ---------------------------------------------------------------
    //  Modal Drill-Down
    //  Shared by get_detail_modal, count_detail_modal, and
    //  get_detail_modal_export. The $type param determines which
    //  WHERE clauses are applied; $extra carries modal-specific
    //  filters on top of the global $filter.
    // ---------------------------------------------------------------

    public function get_detail_modal($type, $extra = [], $filter = [], $limit = 10, $offset = 0) {
        $divisi_category_ids = $this->_resolve_divisi_category_ids($type, $extra);

        $this->db->select('t.id_task, t.konsumen, t.project as lokasi, t.task as jenis,
            t.status as status_id, s.status as status_label, s.color as status_color,
            t.due_date, t.done_date, t.created_at,
            t.verified_at, t.escalation_at,
            c.divisi, c.category');
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->join('hris.cm_status s', 's.id = t.status', 'left');

        $this->_apply_type_conditions($type, $extra, $divisi_category_ids);
        $this->_apply_modal_extra_filters($extra);
        $this->_apply_global_filters_raw($filter);

        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result_array();
    }

    public function count_detail_modal($type, $extra = [], $filter = []) {
        $divisi_category_ids = $this->_resolve_divisi_category_ids($type, $extra);

        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');

        $this->_apply_type_conditions($type, $extra, $divisi_category_ids);
        $this->_apply_modal_extra_filters($extra);
        $this->_apply_global_filters_raw($filter);

        return $this->db->count_all_results();
    }

    // ---------------------------------------------------------------
    //  Divisi Dropdown
    // ---------------------------------------------------------------

    public function get_list_divisi() {
        $label_map = [
            'Buspro' => 'Buspro (Berkas)',
            'Legal'  => 'Legal (Perizinan/Sertifikat)',
            'Sales'  => 'Sales/Mkt',
        ];

        $rows = $this->db->query("
            SELECT divisi
            FROM hris.cm_category
            WHERE divisi IS NOT NULL
              AND divisi != ''
              AND divisi != 'Other'
              AND is_show = 1
            GROUP BY divisi
            ORDER BY divisi ASC
        ")->result_array();

        return array_map(function($row) use ($label_map) {
            $row['label'] = $label_map[$row['divisi']] ?? $row['divisi'];
            return $row;
        }, $rows);
    }

    // ---------------------------------------------------------------
    //  Ketepatan Waktu — Global Detail
    //  Returns ALL matching rows; pagination is handled by controller.
    // ---------------------------------------------------------------

    public function get_ketepatan_global_detail($filter = []) {
        $this->db->select('
            t.id_task, t.konsumen, t.project, t.blok,
            t.due_date, t.done_date,
            c.divisi, c.category, t.created_at
        ');
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');

        $this->db->where('t.status', 6);
        $this->db->where('t.due_date IS NOT NULL', null, false);
        $this->db->where('t.due_date !=', '0000-00-00');
        $this->db->where('t.done_date IS NOT NULL', null, false);
        $this->db->where('t.done_date !=', '0000-00-00 00:00:00');
        $this->db->where('c.divisi !=', 'Other');
        $this->db->where('c.divisi IS NOT NULL', null, false);

        $this->_apply_global_filters_raw($filter);

        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // ---------------------------------------------------------------
    //  Drilldown Verifikasi
    //  Excludes status 1 (Waiting), 2 (Waiting Head Div),
    //  8 (Rescheduled), 9 (Rescheduled 2).
    // ---------------------------------------------------------------

    public function get_drilldown_verifikasi($filter = [], $limit = 100, $offset = 0) {
        $this->db->select('
            t.id_task, t.konsumen, t.project as lokasi,
            t.id_project, t.id_category,
            c.category as jenis_kategori,
            t.status, s.status as status_label,
            t.verified_at, t.created_at
        ');
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->join('hris.cm_status s', 's.id = t.status', 'left');
        $this->db->where_not_in('t.status', [1, 2, 8, 9]);

        $this->_apply_global_filters_raw($filter);

        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        $rows = $this->db->get()->result_array();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id_task'     => $row['id_task'],
                'konsumen'    => $row['konsumen'],
                'lokasi'      => $row['lokasi'],
                'id_project'  => $row['id_project'],
                'jenis'       => $row['jenis_kategori'],
                'id_category' => $row['id_category'],
                'status'      => $row['status_label'] ?: 'Unknown',
                'status_id'   => (int)$row['status'],
                'verified_at' => $row['verified_at'],
                'created_at'  => $row['created_at'],
            ];
        }

        return $result;
    }

    public function count_drilldown_verifikasi($filter = []) {
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where_not_in('t.status', [1, 2, 8, 9]);

        $this->_apply_global_filters_raw($filter);

        return $this->db->count_all_results();
    }

    public function get_drilldown_verifikasi_export($filter = [], $drilldown_sumber = null) {
        $this->db->select('
            t.id_task, t.konsumen, t.project as lokasi, t.blok,
            t.id_project, t.id_category,
            c.divisi, c.category as jenis_kategori,
            t.status, s.status as status_label, t.created_at
        ');
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->join('hris.cm_status s', 's.id = t.status', 'left');
        $this->db->where_not_in('t.status', [1, 2, 8, 9]);

        $this->_apply_global_filters_raw($filter);

        if (!empty($drilldown_sumber) && $drilldown_sumber !== 'all') {
            if ($drilldown_sumber === 'konsumen') {
                $this->db->where('t.status_konsumen', 1);
            } elseif ($drilldown_sumber === 'sosmed') {
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
            }
        }

        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // ---------------------------------------------------------------
    //  Export — Full dataset (no pagination)
    //  Same logic as get_detail_modal but without limit/offset.
    // ---------------------------------------------------------------

    public function get_detail_modal_export($type, $extra = [], $filter = []) {
        $this->db->select('t.id_task, t.konsumen, t.project as lokasi, t.blok, t.task as jenis,
            t.status as status_id, s.status as status_label, s.color as status_color,
            t.due_date, t.done_date, t.created_at, t.updated_at,
            t.verified_at, t.verified_by, t.verified_name, t.verified_note,
            t.escalation_at, t.escalation_by, t.escalation_name,
            t.status_konsumen,
            c.divisi, c.category, c.id as id_category');
        $this->db->from('hris.cm_task t');
        $this->db->join('hris.cm_category c', 'c.id = t.id_category', 'left');
        $this->db->join('hris.cm_status s', 's.id = t.status', 'left');

        switch ($type) {
            case 'verif_total':
                break;
            case 'verif_terverifikasi':
                $this->db->where('t.status !=', 1);
                break;
            case 'verif_belum':
                $this->db->where('t.status', 1);
                break;
            case 'verif_konsumen':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.status !=', 1);
                break;
            case 'verif_konsumen_belum':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.status', 1);
                break;
            case 'verif_sosmed_v':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.status !=', 1);
                break;
            case 'verif_sosmed_b':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.status', 1);
                break;
            case 'esk_sudah':
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                break;
            case 'esk_belum':
                $this->db->where('t.escalation_at IS NULL', null, false);
                break;
            case 'esk_gabungan':
                break;
            case 'esk_konsumen_sudah':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                break;
            case 'esk_konsumen_belum':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.escalation_at IS NULL', null, false);
                break;
            case 'esk_sosmed_sudah':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                break;
            case 'esk_sosmed_belum':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.escalation_at IS NULL', null, false);
                break;
            case 'ketepatan_total':
                if (!empty($extra['ketepatan_total_due_date_from'])) {
                    $this->db->where('t.due_date >=', $extra['ketepatan_total_due_date_from']);
                }
                if (!empty($extra['ketepatan_total_due_date_to'])) {
                    $this->db->where('t.due_date <=', $extra['ketepatan_total_due_date_to']);
                }
                if (!empty($extra['ketepatan_total_done_date_from'])) {
                    $this->db->where('t.done_date >=', $extra['ketepatan_total_done_date_from'] . ' 00:00:00');
                }
                if (!empty($extra['ketepatan_total_done_date_to'])) {
                    $this->db->where('t.done_date <=', $extra['ketepatan_total_done_date_to'] . ' 23:59:59');
                }
                break;
            case 'status':
                if (!empty($extra['status_id'])) {
                    $this->db->where('t.status', $extra['status_id']);
                }
                break;
            case 'divisi':
                if (!empty($extra['divisi'])) {
                    $display_to_db = [
                        'Buspro (Berkas)'              => 'Buspro',
                        'Legal (Perizinan/Sertifikat)' => 'Legal',
                        'Sales/Mkt'                    => 'Sales',
                    ];
                    $db_divisi = $display_to_db[$extra['divisi']] ?? $extra['divisi'];

                    $divisi_category_ids = [];
                    $cat_result = $this->db->query(
                        "SELECT id FROM hris.cm_category WHERE divisi = ?",
                        [$db_divisi]
                    )->result_array();

                    $divisi_category_ids = array_column($cat_result, 'id');

                    if (!empty($divisi_category_ids)) {
                        $this->db->where_in('t.id_category', $divisi_category_ids);
                        $this->db->where('t.done_date IS NOT NULL', null, false);
                        $this->db->where('t.escalation_at IS NOT NULL', null, false);
                    } else {
                        $this->db->where('1', '0');
                    }
                }
                break;
        }

        $this->_apply_modal_extra_filters($extra);
        $this->_apply_global_filters_raw($filter);

        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // ---------------------------------------------------------------
    //  Private Helpers — Modal query building
    // ---------------------------------------------------------------

    /**
     * Resolves display divisi name to cm_category IDs for divisi drilldown.
     */
    private function _resolve_divisi_category_ids($type, $extra) {
        if ($type !== 'divisi' || empty($extra['divisi'])) {
            return [];
        }

        $display_to_db = [
            'Buspro (Berkas)'              => 'Buspro',
            'Legal (Perizinan/Sertifikat)' => 'Legal',
            'Sales/Mkt'                    => 'Sales',
        ];
        $db_divisi = $display_to_db[$extra['divisi']] ?? $extra['divisi'];

        $cat_result = $this->db->query(
            "SELECT id FROM hris.cm_category WHERE divisi = ?",
            [$db_divisi]
        )->result_array();

        return array_column($cat_result, 'id');
    }

    /**
     * Applies type-specific WHERE conditions for modal queries.
     */
    private function _apply_type_conditions($type, $extra, $divisi_category_ids) {
        switch ($type) {
            case 'verif_total':
                break;
            case 'verif_terverifikasi':
                $this->db->where('t.status !=', 1);
                break;
            case 'verif_belum':
                $this->db->where('t.status', 1);
                break;
            case 'verif_konsumen':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.status !=', 1);
                break;
            case 'verif_konsumen_belum':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.status', 1);
                break;
            case 'verif_sosmed_v':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.status !=', 1);
                break;
            case 'verif_sosmed_b':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.status', 1);
                break;
            case 'esk_sudah':
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                $this->db->where('t.status NOT IN (1,2,3)', null, false);
                break;
            case 'esk_belum':
                $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
                break;
            case 'esk_gabungan':
                break;
            case 'esk_konsumen_sudah':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                $this->db->where('t.status NOT IN (1,2,3)', null, false);
                break;
            case 'esk_konsumen_belum':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
                break;
            case 'esk_sosmed_sudah':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                $this->db->where('t.status NOT IN (1,2,3)', null, false);
                break;
            case 'esk_sosmed_belum':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
                break;
            case 'ketepatan_total':
                $this->db->where('t.status', 6);
                $this->db->where('t.done_date IS NOT NULL', null, false);
                $this->db->where('c.divisi IS NOT NULL', null, false);
                $this->db->where('c.divisi !=', 'Other');
                if (!empty($extra['ketepatan_total_due_date_from'])) {
                    $this->db->where('t.due_date >=', $extra['ketepatan_total_due_date_from']);
                }
                if (!empty($extra['ketepatan_total_due_date_to'])) {
                    $this->db->where('t.due_date <=', $extra['ketepatan_total_due_date_to']);
                }
                if (!empty($extra['ketepatan_total_done_date_from'])) {
                    $this->db->where('t.done_date >=', $extra['ketepatan_total_done_date_from'] . ' 00:00:00');
                }
                if (!empty($extra['ketepatan_total_done_date_to'])) {
                    $this->db->where('t.done_date <=', $extra['ketepatan_total_done_date_to'] . ' 23:59:59');
                }
                break;
            case 'status':
                if (!empty($extra['status_id'])) {
                    $this->db->where('t.status', $extra['status_id']);
                }
                break;
            case 'divisi':
                if (!empty($divisi_category_ids)) {
                    $this->db->where_in('t.id_category', $divisi_category_ids);
                    $this->db->where('t.done_date IS NOT NULL', null, false);
                    $this->db->where('t.escalation_at IS NOT NULL', null, false);
                    if (!empty($extra['divisi_due_date_from'])) {
                        $this->db->where('t.due_date >=', $extra['divisi_due_date_from']);
                    }
                    if (!empty($extra['divisi_due_date_to'])) {
                        $this->db->where('t.due_date <=', $extra['divisi_due_date_to']);
                    }
                    if (!empty($extra['divisi_done_date_from'])) {
                        $this->db->where('t.done_date >=', $extra['divisi_done_date_from'] . ' 00:00:00');
                    }
                    if (!empty($extra['divisi_done_date_to'])) {
                        $this->db->where('t.done_date <=', $extra['divisi_done_date_to'] . ' 23:59:59');
                    }
                } else {
                    $this->db->where('1', '0');
                }
                break;
        }
    }

    /**
     * Applies modal-level $extra filters (sumber, verif status, eskalasi, ketepatan).
     */
    private function _apply_modal_extra_filters($extra) {
        if (!empty($extra['modal_sumber'])) {
            if ($extra['modal_sumber'] === 'konsumen') {
                $this->db->where('t.status_konsumen', 1);
            } elseif ($extra['modal_sumber'] === 'sosmed') {
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
            }
        }

        if (!empty($extra['modal_verif_sumber'])) {
            if ($extra['modal_verif_sumber'] === 'konsumen') {
                $this->db->where('t.status_konsumen', 1);
            } elseif ($extra['modal_verif_sumber'] === 'sosmed') {
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
            }
        }

        if (!empty($extra['modal_verif_status'])) {
            if ($extra['modal_verif_status'] === 'verified') {
                $this->db->where('t.status !=', 1);
            } elseif ($extra['modal_verif_status'] === 'unverified') {
                $this->db->where('t.status', 1);
            }
        }

        if (!empty($extra['modal_eskalasi'])) {
            if ($extra['modal_eskalasi'] === 'sudah') {
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                $this->db->where('t.status NOT IN (1,2,3)', null, false);
            } elseif ($extra['modal_eskalasi'] === 'belum') {
                $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
            }
        }

        if (!empty($extra['modal_ketepatan_sumber'])) {
            if ($extra['modal_ketepatan_sumber'] === 'konsumen') {
                $this->db->where('t.status_konsumen', 1);
            } elseif ($extra['modal_ketepatan_sumber'] === 'sosmed') {
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
            }
        }

        if (!empty($extra['modal_ketepatan_status'])) {
            if ($extra['modal_ketepatan_status'] === 'ontime') {
                $this->db->where('t.done_date IS NOT NULL', null, false);
                $this->db->where('t.due_date IS NOT NULL AND t.due_date != "0000-00-00"', null, false);
                $this->db->where('DATE(t.done_date) <= t.due_date', null, false);
            } elseif ($extra['modal_ketepatan_status'] === 'late') {
                $this->db->where('t.done_date IS NOT NULL', null, false);
                $this->db->where('t.due_date IS NOT NULL AND t.due_date != "0000-00-00"', null, false);
                $this->db->where('DATE(t.done_date) > t.due_date', null, false);
            }
        }

        // Also used by export
        if (!empty($extra['modal_ketepatan'])) {
            if ($extra['modal_ketepatan'] === 'ontime') {
                $this->db->where('t.done_date IS NOT NULL', null, false);
                $this->db->where('t.due_date IS NOT NULL AND t.due_date != "0000-00-00"', null, false);
                $this->db->where('DATE(t.done_date) <= t.due_date', null, false);
            } elseif ($extra['modal_ketepatan'] === 'late') {
                $this->db->where('t.done_date IS NOT NULL', null, false);
                $this->db->where('t.due_date IS NOT NULL AND t.due_date != "0000-00-00"', null, false);
                $this->db->where('DATE(t.done_date) > t.due_date', null, false);
            }
        }

        if (!empty($extra['modal_due_date_from'])) {
            $this->db->where('t.created_at >=', $extra['modal_due_date_from'] . ' 00:00:00');
        }
        if (!empty($extra['modal_due_date_to'])) {
            $this->db->where('t.created_at <=', $extra['modal_due_date_to'] . ' 23:59:59');
        }
    }

    /**
     * Applies the global filter bar (date range, sumber, divisi) directly via Query Builder.
     */
    private function _apply_global_filters_raw($filter) {
        if (!empty($filter['date_from'])) {
            $this->db->where('t.created_at >=', $filter['date_from'] . ' 00:00:00');
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('t.created_at <=', $filter['date_to'] . ' 23:59:59');
        }
        if (!empty($filter['sumber']) && $filter['sumber'] === 'konsumen') {
            $this->db->where('t.status_konsumen', 1);
        } elseif (!empty($filter['sumber']) && $filter['sumber'] === 'sosmed') {
            $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $this->db->where('c.divisi', $filter['divisi']);
        }
    }
}
