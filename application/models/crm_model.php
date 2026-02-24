<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model    : Crm_model
 * File     : application/models/Crm_model.php
 *
 * Tabel utama:
 *   - cm_task          : tabel komplain/task
 *   - cm_category      : kategori komplain
 *   - cm_status        : status task
 *
 * Status mapping:
 *   1 = Waiting, 2 = Waiting Head Div, 3 = Reject Level 1, 4 = Working On,
 *   5 = Reject Level 2, 6 = Done, 7 = Unsolved, 8 = Rescheduled, 9 = Rescheduled 2
 */
class Crm_model extends CI_Model {

    private $tbl = 'cm_task';
    private $tbl_category = 'cm_category';
    private $tbl_status = 'cm_status';

    // ── FILTER BUILDER ────────────────────────────────────────
    private function _apply_filter(array $filter)
    {
        $this->db->where('created_at >=', $filter['date_from'] . ' 00:00:00')
                 ->where('created_at <=', $filter['date_to'] . ' 23:59:59');

        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all')
            $this->db->where('divisi', $filter['divisi']);
    }

    // ── 01 VERIFIKASI ─────────────────────────────────────────
    public function get_verifikasi(array $filter): array
    {
        $this->_apply_filter($filter);
        
        $rows = $this->db
            ->select('CASE WHEN verified_by IS NOT NULL THEN "terverifikasi" ELSE "belum" END as kategori, COUNT(*) as jumlah')
            ->from($this->tbl)
            ->group_by('kategori')
            ->get()->result_array();

        $v = [
            'total'               => 0,
            'terverifikasi'       => 0,
            'belum'               => 0,
            'pct_terverifikasi'   => 0,
            'pct_belum'           => 0,
        ];

        foreach ($rows as $r) {
            $j = (int)$r['jumlah'];
            $v['total'] += $j;
            if ($r['kategori'] === 'terverifikasi') {
                $v['terverifikasi'] += $j;
            } else {
                $v['belum'] += $j;
            }
        }

        if ($v['total'] > 0) {
            $v['pct_terverifikasi'] = round($v['terverifikasi'] / $v['total'] * 100);
            $v['pct_belum'] = 100 - $v['pct_terverifikasi'];
        }

        return $v;
    }

    // ── 02 ESKALASI ───────────────────────────────────────────
    public function get_eskalasi(array $filter): array
    {
        $this->_apply_filter($filter);
        
        // Jika status > 1 dianggap sudah eskalasi (not waiting)
        $rows = $this->db->select('CASE WHEN status > 1 THEN 1 ELSE 0 END as eskalasi, COUNT(*) as jumlah')
                         ->from($this->tbl)
                         ->group_by('eskalasi')
                         ->get()->result_array();

        $sudah = $belum = 0;
        foreach ($rows as $r) {
            if ((int)$r['eskalasi'] === 1) {
                $sudah = (int)$r['jumlah'];
            } else {
                $belum = (int)$r['jumlah'];
            }
        }
        $total = $sudah + $belum;
        return [
            'total'      => $total,
            'sudah'      => $sudah,
            'belum'      => $belum,
            'pct_sudah'  => $total > 0 ? round($sudah / $total * 100) : 0,
            'pct_belum'  => $total > 0 ? round($belum / $total * 100) : 0,
        ];
    }

    // ── 02 TREND ESKALASI (per bulan) ─────────────────────────
    public function get_eskalasi_trend(array $filter): array
    {
        $this->_apply_filter($filter);
        return $this->db
            ->select("DATE_FORMAT(created_at, '%b\\'%y') as bulan, MONTH(created_at) as m, YEAR(created_at) as y, COUNT(*) as jumlah")
            ->from($this->tbl)
            ->where('status >', 1)  // status > waiting
            ->group_by('YEAR(created_at), MONTH(created_at)')
            ->order_by('y ASC, m ASC')
            ->get()->result_array();
    }

    // ── 03 KETEPATAN WAKTU ────────────────────────────────────
    public function get_ketepatan(array $filter): array
    {
        $this->_apply_filter($filter);
        $rows = $this->db
            ->select('divisi, SUM(CASE WHEN done_date <= due_date THEN 1 ELSE 0 END) as ontime, SUM(CASE WHEN done_date > due_date THEN 1 ELSE 0 END) as late, COUNT(*) as total')
            ->from($this->tbl)
            ->where('status >', 1)  // status > waiting (processed)
            ->where('status', 6)    // only count Done (status 6)
            ->group_by('divisi')
            ->order_by('divisi')
            ->get()->result_array();

        return array_map(function($r) {
            return [
                'divisi' => $r['divisi'],
                'ontime' => (int)$r['ontime'],
                'late'   => (int)$r['late'],
                'total'  => (int)$r['total'],
            ];
        }, $rows);
    }

    // ── 05 STATUS LIST ────────────────────────────────────────
    public function get_status_list(array $filter): array
    {
        $this->_apply_filter($filter);
        
        // Join dengan cm_status untuk mendapat info status
        $rows = $this->db
            ->select('cs.status as label, cs.color as color, COUNT(ct.id_task) as qty')
            ->from($this->tbl . ' ct')
            ->join($this->tbl_status . ' cs', 'ct.status = cs.id', 'left')
            ->where('ct.status >', 1)  // exclude waiting (status > 1)
            ->group_by('ct.status, cs.status, cs.color')
            ->order_by('qty DESC')
            ->get()->result_array();

        return array_map(function($r) {
            return [
                'label' => $r['label'] ?? 'Unknown',
                'qty'   => (int)$r['qty'],
                'color' => $r['color'] ?? '#6b7280',
                'badge' => 'secondary',
            ];
        }, $rows);
    }

    // ── MASTER DIVISI ────────────────────────────────────────
    public function get_divisi_list(): array
    {
        $rows = $this->db->select('DISTINCT divisi')
                        ->from($this->tbl)
                        ->where('divisi IS NOT NULL', null, false)
                        ->where('divisi !=', '')
                        ->order_by('divisi')
                        ->get()->result_array();

        return array_column($rows, 'divisi');
    }

    // ── DETAIL KOMPLAIN (untuk modal AJAX) ───────────────────
    public function get_detail_komplain(array $filter, array $extra = [], int $limit = 100): array
    {
        $this->_apply_filter($filter);

        // Handle verified_by filters
        if (isset($extra['verified_by'])) {
            if ($extra['verified_by'] === 1 || $extra['verified_by'] === true) {
                $this->db->where('verified_by IS NOT NULL', null, false);
            } else {
                $this->db->where('verified_by IS NULL', null, false);
            }
        }

        // Handle status > 1 (sudah diproses/eskalasi)
        if (isset($extra['status >']) && $extra['status >'] === 1) {
            $this->db->where('status >', 1);
        }
        // Handle specific status
        elseif (isset($extra['status']) && !empty($extra['status'])) {
            $this->db->where('status', (int)$extra['status']);
        }

        // Handle divisi filter
        if (!empty($extra['divisi'])) {
            $this->db->where('divisi', $extra['divisi']);
        }

        return $this->db
            ->select('ct.id_task, ct.konsumen, ct.project, ct.blok, ct.task, ct.description, ct.created_at, ct.status, cs.status as status_label, cs.color')
            ->from($this->tbl . ' ct')
            ->join($this->tbl_status . ' cs', 'ct.status = cs.id', 'left')
            ->order_by('ct.created_at DESC')
            ->limit($limit)
            ->get()->result_array();
    }
}