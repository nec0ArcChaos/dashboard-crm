<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard_model
 * Model untuk mengambil data dashboard CRM dari database db_crm
 */
class Dashboard_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // ============================================================
    // HELPER: buat WHERE clause filter tanggal + sumber + divisi
    // ============================================================
    private function _apply_filters($date_from = null, $date_to = null, $sumber = null, $divisi = null) {
        if ($date_from) {
            $this->db->where('t.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('t.created_at <=', $date_to . ' 23:59:59');
        }
        // Sumber: status_konsumen = 1 (dari konsumen), NULL/0 (dari sosmed/karyawan)
        if ($sumber === 'konsumen') {
            $this->db->where('t.status_konsumen', 1);
        } elseif ($sumber === 'sosmed') {
            $this->db->where('t.status_konsumen IS NULL OR t.status_konsumen = 0', null, false);
        }
        // Filter divisi dari cm_category
        if ($divisi && $divisi !== 'all') {
            $this->db->where('c.divisi', $divisi);
        }
    }

    // ============================================================
    // SECTION 01 — VERIFIKASI
    // ============================================================

    /**
     * Total semua komplain
     */
    public function get_total_komplain($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Komplain terverifikasi (verified_at IS NOT NULL)
     */
    public function get_terverifikasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.verified_at IS NOT NULL', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Komplain belum terverifikasi
     */
    public function get_belum_verifikasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.verified_at IS NULL', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Data verifikasi per sumber (konsumen vs sosmed)
     * Return array: [ 'konsumen' => ['terverifikasi'=>n, 'belum'=>n], 'sosmed' => [...] ]
     */
    public function get_verifikasi_per_sumber($filter = []) {
        $result = [
            'konsumen' => ['terverifikasi' => 0, 'belum' => 0],
            'sosmed'   => ['terverifikasi' => 0, 'belum' => 0],
        ];

        // Konsumen terverifikasi
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('t.verified_at IS NOT NULL', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['terverifikasi'] = $this->db->count_all_results();

        // Konsumen belum terverifikasi
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('t.verified_at IS NULL', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['belum'] = $this->db->count_all_results();

        // Sosmed terverifikasi
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('t.verified_at IS NOT NULL', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['terverifikasi'] = $this->db->count_all_results();

        // Sosmed belum terverifikasi
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('t.verified_at IS NULL', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['belum'] = $this->db->count_all_results();

        return $result;
    }

    // ============================================================
    // SECTION 02 — ESKALASI
    // ============================================================

    /**
     * Komplain sudah dieskalasi (escalation_at IS NOT NULL)
     */
    public function get_sudah_eskalasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Komplain belum dieskalasi
     */
    public function get_belum_eskalasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.escalation_at IS NULL', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Trend eskalasi per bulan (14 bulan terakhir)
     */
    public function get_trend_eskalasi($filter = []) {
        $this->db->select("DATE_FORMAT(t.escalation_at, '%Y-%m') as bulan, COUNT(*) as total");
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        $this->db->group_by("DATE_FORMAT(t.escalation_at, '%Y-%m')");
        $this->db->order_by('bulan', 'ASC');
        $this->db->limit(14);
        return $this->db->get()->result_array();
    }

    // ============================================================
    // SECTION 03 — KETEPATAN WAKTU
    // ============================================================

    /**
     * Ketepatan waktu per divisi (on time = done_date <= due_date)
     */
    public function get_ketepatan_waktu($filter = []) {
        $sql = "
            SELECT
                c.divisi,
                COUNT(*) as total,
                SUM(CASE WHEN t.done_date IS NOT NULL AND t.done_date <= CONCAT(t.due_date, ' 23:59:59')
                         AND t.due_date != '0000-00-00' THEN 1 ELSE 0 END) as ontime,
                SUM(CASE WHEN t.done_date IS NOT NULL AND t.done_date > CONCAT(t.due_date, ' 23:59:59')
                         AND t.due_date != '0000-00-00' THEN 1 ELSE 0 END) as late
            FROM cm_task t
            LEFT JOIN cm_category c ON c.id = t.id_category
            WHERE t.escalation_at IS NOT NULL
              AND c.divisi IS NOT NULL
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

        $sql .= " GROUP BY c.divisi HAVING total > 0 ORDER BY c.divisi";

        $query = $this->db->query($sql, $params);
        $rows  = $query->result_array();

        // Normalisasi divisi label agar konsisten dengan frontend
        $divisi_map = [
            'Project'  => 'Project',
            'Buspro'   => 'Buspro (Berkas)',
            'Estate'   => 'Estate',
            'Finance'  => 'Finance',
            'Legal'    => 'Legal',
            'MEP'      => 'MEP',
            'Sales'    => 'Sales/Mkt',
            'CRM'      => 'Sosmed',
            'Aftersales' => 'Aftersales',
        ];

        $result = [];
        foreach ($rows as $row) {
            $label = isset($divisi_map[$row['divisi']]) ? $divisi_map[$row['divisi']] : $row['divisi'];
            // Cari divisi yang sama di hasil sebelumnya (Serah Terima)
            $serah = ($row['divisi'] === 'Project' && strpos($row['divisi'], 'Serah') !== false);

            $result[] = [
                'divisi' => $label,
                'total'  => (int) $row['total'],
                'ontime' => (int) $row['ontime'],
                'late'   => (int) $row['late'],
            ];
        }
        return $result;
    }

    // ============================================================
    // SECTION 04 — RATING KONSUMEN
    // ============================================================

    /**
     * Rata-rata rating konsumen dari cm_rating
     */
    public function get_rating_summary() {
        $this->db->select('AVG(avg_rating) as avg_all, COUNT(*) as total_responden,
            AVG(pelayanan) as avg_pelayanan,
            AVG(kualitas) as avg_kualitas,
            AVG(respons) as avg_respons');
        $this->db->from('cm_rating');
        $row = $this->db->get()->row_array();
        return $row;
    }

    /**
     * Distribusi rating (bintang 1-5)
     */
    public function get_distribusi_rating() {
        $this->db->select('ROUND(avg_rating) as bintang, COUNT(*) as total');
        $this->db->from('cm_rating');
        $this->db->where('avg_rating IS NOT NULL', null, false);
        $this->db->group_by('ROUND(avg_rating)');
        $this->db->order_by('bintang', 'ASC');
        return $this->db->get()->result_array();
    }

    // ============================================================
    // SECTION 05 — STATUS KOMPLAIN
    // ============================================================

    /**
     * Distribusi status komplain berdasarkan cm_status
     */
    public function get_status_komplain($filter = []) {
        $this->db->select('s.id, s.status, s.color, COUNT(t.id_task) as total');
        $this->db->from('cm_status s');
        $this->db->join('cm_task t', 't.status = s.id AND t.escalation_at IS NOT NULL', 'left');
        if (!empty($filter['date_from'])) {
            $this->db->where('(t.created_at IS NULL OR t.created_at >=', $filter['date_from'] . ' 00:00:00');
            $this->db->where('t.created_at IS NULL OR t.created_at >=', null, false); // handled in raw query instead
        }
        $this->db->group_by('s.id, s.status, s.color');
        $this->db->order_by('s.id', 'ASC');

        // Gunakan raw query agar lebih akurat dengan kondisi kompleks
        $params = [];
        $sql = "
            SELECT s.id, s.status, s.color, COUNT(t.id_task) as total
            FROM cm_status s
            LEFT JOIN cm_task t ON t.status = s.id AND t.escalation_at IS NOT NULL
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
        if ($wheres) {
            $sql .= " WHERE " . implode(' AND ', $wheres);
        }
        $sql .= " GROUP BY s.id, s.status, s.color ORDER BY s.id ASC";

        $this->db->reset_query();
        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    // ============================================================
    // MODAL DRILL-DOWN — Detail Komplain
    // ============================================================

    /**
     * Detail komplain untuk modal, dengan berbagai filter type
     *
     * @param string $type   : verif_terverifikasi | verif_belum | verif_konsumen |
     *                         verif_sosmed_v | verif_sosmed_b | esk_sudah | esk_belum | status | divisi
     * @param array  $extra  : parameter tambahan (status_id, divisi name, dll)
     * @param array  $filter : filter global
     * @param int    $limit
     * @param int    $offset
     */
    public function get_detail_modal($type, $extra = [], $filter = [], $limit = 10, $offset = 0) {
        $this->db->select('t.id_task, t.konsumen, t.project as lokasi, t.task as jenis,
            t.status as status_id, s.status as status_label, s.color as status_color,
            t.due_date, t.done_date, t.created_at,
            t.verified_at, t.escalation_at,
            c.divisi, c.category');
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->join('cm_status s', 's.id = t.status', 'left');

        switch ($type) {
            case 'verif_terverifikasi':
                $this->db->where('t.verified_at IS NOT NULL', null, false);
                break;
            case 'verif_belum':
                $this->db->where('t.verified_at IS NULL', null, false);
                break;
            case 'verif_konsumen':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.verified_at IS NOT NULL', null, false);
                break;
            case 'verif_sosmed_v':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.verified_at IS NOT NULL', null, false);
                break;
            case 'verif_sosmed_b':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.verified_at IS NULL', null, false);
                break;
            case 'esk_sudah':
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
                break;
            case 'esk_belum':
                $this->db->where('t.escalation_at IS NULL', null, false);
                break;
            case 'status':
                if (!empty($extra['status_id'])) {
                    $this->db->where('t.status', $extra['status_id']);
                    $this->db->where('t.escalation_at IS NOT NULL', null, false);
                }
                break;
            case 'divisi':
                if (!empty($extra['divisi'])) {
                    $this->db->where('c.divisi', $extra['divisi']);
                    $this->db->where('t.escalation_at IS NOT NULL', null, false);
                }
                break;
        }

        // Apply global filter
        if (!empty($filter['date_from'])) {
            $this->db->where('t.created_at >=', $filter['date_from'] . ' 00:00:00');
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('t.created_at <=', $filter['date_to'] . ' 23:59:59');
        }

        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result_array();
    }

    /**
     * Count total baris untuk modal (untuk paginasi)
     */
    public function count_detail_modal($type, $extra = [], $filter = []) {
        // Reuse query sama seperti get_detail_modal tapi pakai count
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');

        switch ($type) {
            case 'verif_terverifikasi':
                $this->db->where('t.verified_at IS NOT NULL', null, false); break;
            case 'verif_belum':
                $this->db->where('t.verified_at IS NULL', null, false); break;
            case 'verif_konsumen':
                $this->db->where('t.status_konsumen', 1);
                $this->db->where('t.verified_at IS NOT NULL', null, false); break;
            case 'verif_sosmed_v':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.verified_at IS NOT NULL', null, false); break;
            case 'verif_sosmed_b':
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
                $this->db->where('t.verified_at IS NULL', null, false); break;
            case 'esk_sudah':
                $this->db->where('t.escalation_at IS NOT NULL', null, false); break;
            case 'esk_belum':
                $this->db->where('t.escalation_at IS NULL', null, false); break;
            case 'status':
                if (!empty($extra['status_id'])) {
                    $this->db->where('t.status', $extra['status_id']);
                    $this->db->where('t.escalation_at IS NOT NULL', null, false);
                }
                break;
            case 'divisi':
                if (!empty($extra['divisi'])) {
                    $this->db->where('c.divisi', $extra['divisi']);
                    $this->db->where('t.escalation_at IS NOT NULL', null, false);
                }
                break;
        }

        if (!empty($filter['date_from'])) {
            $this->db->where('t.created_at >=', $filter['date_from'] . ' 00:00:00');
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('t.created_at <=', $filter['date_to'] . ' 23:59:59');
        }

        return $this->db->count_all_results();
    }

    // ============================================================
    // HELPER — Daftar divisi untuk filter dropdown
    // Mengambil nilai unik kolom `divisi` dari cm_category
    // CI3 tidak punya select_distinct(), gunakan raw query + GROUP BY
    // ============================================================
    public function get_list_divisi() {
        $query = $this->db->query("
            SELECT divisi
            FROM cm_category
            WHERE divisi IS NOT NULL
              AND divisi != ''
              AND is_show = 1
            GROUP BY divisi
            ORDER BY divisi ASC
        ");
        return $query->result_array();
    }
}
