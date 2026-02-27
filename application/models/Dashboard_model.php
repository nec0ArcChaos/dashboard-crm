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
     * Komplain terverifikasi (status != 1)
     */
    public function get_terverifikasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status !=', 1);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Komplain belum terverifikasi (status = 1 = waiting)
     */
    public function get_belum_verifikasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status', 1);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Data verifikasi per sumber (konsumen vs sosmed)
     * Belum verifikasi = status = 1 (Waiting) ONLY
     * Terverifikasi = status != 1 (sudah di-handle oleh CRM)
     * Return array: [ 'konsumen' => ['terverifikasi'=>n, 'belum'=>n], 'sosmed' => [...] ]
     */
    public function get_verifikasi_per_sumber($filter = []) {
        $result = [
            'konsumen' => ['terverifikasi' => 0, 'belum' => 0],
            'sosmed'   => ['terverifikasi' => 0, 'belum' => 0],
        ];

        // Konsumen terverifikasi (status != 1)
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('t.status !=', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['terverifikasi'] = $this->db->count_all_results();

        // Konsumen belum terverifikasi (status = 1 = waiting)
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('t.status', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['belum'] = $this->db->count_all_results();

        // Sosmed terverifikasi (status != 1)
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('t.status !=', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['terverifikasi'] = $this->db->count_all_results();

        // Sosmed belum terverifikasi (status = 1 = waiting)
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('t.status', 1);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['belum'] = $this->db->count_all_results();

        return $result;
    }

    // ============================================================
    // SECTION 02 — ESKALASI
    // ============================================================

    /**
     * Komplain sudah dieskalasi (escalation_at IS NOT NULL)
     * KECUALI status 1, 2, 3 (Waiting, Waiting Head Div, Reject Lv.1) tetap dihitung belum eskalasi
     * Filter berdasarkan tanggal eskalasi (escalation_at)
     */
    public function get_sudah_eskalasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        // Status 1,2,3 tidak dihitung sebagai sudah eskalasi
        $this->db->where('t.status NOT IN (1,2,3)', null, false);
        
        // Filter tanggal berdasarkan escalation_at
        if (!empty($filter['date_from'])) {
            $this->db->where('DATE(t.escalation_at) >=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('DATE(t.escalation_at) <=', $filter['date_to']);
        }
        
        // Filter sumber dan divisi
        if (!empty($filter['sumber']) && $filter['sumber'] === 'konsumen') {
            $this->db->where('t.status_konsumen', 1);
        } elseif (!empty($filter['sumber']) && $filter['sumber'] === 'sosmed') {
            $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        }
        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all') {
            $this->db->where('c.divisi', $filter['divisi']);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Komplain belum dieskalasi
     * Include: escalation_at IS NULL OR status IN (1,2,3)
     */
    public function get_belum_eskalasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        // Belum eskalasi = escalation_at NULL OR status 1,2,3 (meskipun escalation_at ada)
        $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
        $this->_apply_filters(
            @$filter['date_from'], @$filter['date_to'],
            @$filter['sumber'], @$filter['divisi']
        );
        return $this->db->count_all_results();
    }

    /**
     * Trend eskalasi per bulan (14 bulan terakhir)
     * KECUALI status 1,2,3 tetap dihitung belum eskalasi
     * Filter berdasarkan tanggal eskalasi (escalation_at)
     */
    public function get_trend_eskalasi($filter = []) {
        $this->db->select("DATE_FORMAT(t.escalation_at, '%Y-%m') as bulan, COUNT(*) as total");
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.escalation_at IS NOT NULL', null, false);
        // Status 1,2,3 tidak dihitung dalam trend
        $this->db->where('t.status NOT IN (1,2,3)', null, false);
        
        // Filter tanggal berdasarkan escalation_at
        if (!empty($filter['date_from'])) {
            $this->db->where('DATE(t.escalation_at) >=', $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $this->db->where('DATE(t.escalation_at) <=', $filter['date_to']);
        }
        
        // Filter sumber dan divisi
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
        $this->db->limit(14);
        return $this->db->get()->result_array();
    }

    /**
     * Data eskalasi per sumber (konsumen vs sosmed)
     * Sudah eskalasi = escalation_at IS NOT NULL & status NOT IN (1,2,3) dengan filter tanggal eskalasi
     * Belum eskalasi = escalation_at IS NULL OR status IN (1,2,3) dengan filter tanggal
     * Return array: [ 'konsumen' => ['sudah'=>n, 'belum'=>n], 'sosmed' => [...] ]
     */
    public function get_eskalasi_per_sumber($filter = []) {
        $result = [
            'konsumen' => ['sudah' => 0, 'belum' => 0],
            'sosmed'   => ['sudah' => 0, 'belum' => 0],
        ];

        // Konsumen sudah eskalasi (filter berdasarkan tanggal eskalasi, KECUALI status 1,2,3)
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
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

        // Konsumen belum eskalasi (escalation_at IS NULL OR status IN (1,2,3))
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('t.status_konsumen', 1);
        $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['konsumen']['belum'] = $this->db->count_all_results();

        // Sosmed sudah eskalasi (filter berdasarkan tanggal eskalasi, KECUALI status 1,2,3)
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
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

        // Sosmed belum eskalasi (escalation_at IS NULL OR status IN (1,2,3))
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
        $this->db->where('(t.escalation_at IS NULL OR t.status IN (1,2,3))', null, false);
        $this->_apply_filters(@$filter['date_from'], @$filter['date_to'], null, @$filter['divisi']);
        $result['sosmed']['belum'] = $this->db->count_all_results();

        return $result;
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
     * Menampilkan data dari SEMUA task (tidak hanya yang sudah dieskalasi)
     */
    public function get_status_komplain($filter = []) {
        // Gunakan raw query agar lebih akurat dengan kondisi kompleks
        $params = [];
        $sql = "
            SELECT s.id, s.status, s.color, COUNT(t.id_task) as total
            FROM cm_status s
            LEFT JOIN cm_task t ON t.status = s.id
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
                // Menampilkan semua data eskalasi (baik sudah maupun belum)
                // Filter sumber akan diaplikasikan dari modal_sumber
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
            case 'status':
                if (!empty($extra['status_id'])) {
                    $this->db->where('t.status', $extra['status_id']);
                }
                break;
            case 'divisi':
                if (!empty($extra['divisi'])) {
                    $this->db->where('c.divisi', $extra['divisi']);
                    $this->db->where('t.escalation_at IS NOT NULL', null, false);
                }
                break;
        }

        // Apply modal sumber filter untuk verifikasi modal
        if (!empty($extra['modal_sumber'])) {
            if ($extra['modal_sumber'] === 'konsumen') {
                $this->db->where('t.status_konsumen', 1);
            } elseif ($extra['modal_sumber'] === 'sosmed') {
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
            }
        }

        // Apply modal eskalasi filter untuk eskalasi gabungan modal
        if (!empty($extra['modal_eskalasi'])) {
            if ($extra['modal_eskalasi'] === 'sudah') {
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
            } elseif ($extra['modal_eskalasi'] === 'belum') {
                $this->db->where('t.escalation_at IS NULL', null, false);
            }
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
                $this->db->where('t.escalation_at IS NOT NULL', null, false); break;
            case 'esk_belum':
                $this->db->where('t.escalation_at IS NULL', null, false); break;
            case 'esk_gabungan':
                // Menampilkan semua data eskalasi (baik sudah maupun belum)
                // Filter sumber akan diaplikasikan dari modal_sumber
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
            case 'status':
                if (!empty($extra['status_id'])) {
                    $this->db->where('t.status', $extra['status_id']);
                }
                break;
            case 'divisi':
                if (!empty($extra['divisi'])) {
                    $this->db->where('c.divisi', $extra['divisi']);
                    $this->db->where('t.escalation_at IS NOT NULL', null, false);
                }
                break;
        }

        // Apply modal sumber filter untuk verifikasi modal
        if (!empty($extra['modal_sumber'])) {
            if ($extra['modal_sumber'] === 'konsumen') {
                $this->db->where('t.status_konsumen', 1);
            } elseif ($extra['modal_sumber'] === 'sosmed') {
                $this->db->where('(t.status_konsumen IS NULL OR t.status_konsumen = 0)', null, false);
            }
        }

        // Apply modal eskalasi filter untuk eskalasi gabungan modal
        if (!empty($extra['modal_eskalasi'])) {
            if ($extra['modal_eskalasi'] === 'sudah') {
                $this->db->where('t.escalation_at IS NOT NULL', null, false);
            } elseif ($extra['modal_eskalasi'] === 'belum') {
                $this->db->where('t.escalation_at IS NULL', null, false);
            }
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

    // ============================================================
    // DRILL-DOWN VERIFIKASI — Tabel detail komplain per status
    // ============================================================
    /**
     * Ambil detail komplain untuk drilldown verifikasi
     * Kolom: id_task, konsumen, project, category, status
     * Filter: Exclude status 1 (Waiting), 2 (Waiting Head Div), 8 (Rescheduled), 9 (Rescheduled 2)
     */
    public function get_drilldown_verifikasi($filter = [], $limit = 100, $offset = 0) {
        $this->db->select('
            t.id_task,
            t.konsumen,
            t.project as lokasi,
            t.id_project,
            t.id_category,
            c.category as jenis_kategori,
            t.status,
            s.status as status_label,
            t.verified_at,
            t.created_at
        ');
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');
        $this->db->join('cm_status s', 's.id = t.status', 'left');

        // Filter: Exclude status 1 (Waiting), 2 (Waiting Head Div), 8 (Rescheduled), 9 (Rescheduled 2)
        $this->db->where_not_in('t.status', [1, 2, 8, 9]);

        // Apply global filters
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

        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        $rows = $this->db->get()->result_array();

        // Format hasil dengan status label dari cm_status
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id_task'         => $row['id_task'],
                'konsumen'        => $row['konsumen'],
                'lokasi'          => $row['lokasi'],
                'id_project'      => $row['id_project'],
                'jenis'           => $row['jenis_kategori'],
                'id_category'     => $row['id_category'],
                'status'          => $row['status_label'] ?: 'Unknown',
                'status_id'       => (int)$row['status'],
                'verified_at'     => $row['verified_at'],
                'created_at'      => $row['created_at']
            ];
        }

        return $result;
    }

    /**
     * Count total komplain untuk drilldown verifikasi (pagination)
     * Filter: Exclude status 1, 2, 8, 9 (Waiting, Waiting Head Div, Rescheduled, Rescheduled 2)
     */
    public function count_drilldown_verifikasi($filter = []) {
        $this->db->from('cm_task t');
        $this->db->join('cm_category c', 'c.id = t.id_category', 'left');

        // Filter: Exclude status 1 (Waiting), 2 (Waiting Head Div), 8 (Rescheduled), 9 (Rescheduled 2)
        $this->db->where_not_in('t.status', [1, 2, 8, 9]);

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

        return $this->db->count_all_results();
    }
}
