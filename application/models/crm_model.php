<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model    : Crm_model
 * File     : application/models/Crm_model.php
 *
 * Asumsi nama tabel (sesuaikan dengan skema DB Anda):
 *   - komplain          : tabel utama komplain
 *   - tb_divisi         : master divisi
 *
 * Kolom minimal tabel `komplain`:
 *   id, no_komplain, nama_konsumen, lokasi, divisi,
 *   jenis_komplain, tgl_masuk, sumber (konsumen|sosmed),
 *   status_verif (terverifikasi|belum), eskalasi (1|0),
 *   status_komplain (Done|Reject|Working On|…),
 *   tgl_target, tgl_selesai, on_time (1|0)
 */
class Crm_model extends CI_Model {

    private $tbl = 'komplain';

    // ── FILTER BUILDER ────────────────────────────────────────
    private function _apply_filter(array $filter)
    {
        $this->db->where('tgl_masuk >=', $filter['date_from'])
                 ->where('tgl_masuk <=', $filter['date_to']);

        if (!empty($filter['sumber']) && $filter['sumber'] !== 'all')
            $this->db->where('sumber', $filter['sumber']);

        if (!empty($filter['divisi']) && $filter['divisi'] !== 'all')
            $this->db->where('divisi', $filter['divisi']);
    }

    // ── 01 VERIFIKASI ─────────────────────────────────────────
    public function get_verifikasi(array $filter): array
    {
        $this->_apply_filter($filter);
        $rows = $this->db->select('sumber, status_verif, COUNT(*) as jumlah')
                         ->from($this->tbl)
                         ->group_by('sumber, status_verif')
                         ->get()->result_array();

        $v = [
            'total'               => 0,
            'terverifikasi'       => 0,
            'belum'               => 0,
            'konsumen_total'      => 0,
            'sosmed_terverifikasi'=> 0,
            'sosmed_belum'        => 0,
            'pct_terverifikasi'   => 0,
            'pct_belum'           => 0,
            'pct_sosmed'          => 0,
        ];

        foreach ($rows as $r) {
            $j = (int)$r['jumlah'];
            $v['total'] += $j;
            if ($r['status_verif'] === 'terverifikasi') $v['terverifikasi'] += $j;
            else                                        $v['belum']         += $j;
            if ($r['sumber'] === 'konsumen')            $v['konsumen_total'] += $j;
            if ($r['sumber'] === 'sosmed') {
                if ($r['status_verif'] === 'terverifikasi') $v['sosmed_terverifikasi'] += $j;
                else                                        $v['sosmed_belum']         += $j;
            }
        }

        if ($v['total'] > 0) {
            $v['pct_terverifikasi'] = round($v['terverifikasi']/$v['total']*100);
            $v['pct_belum']         = 100 - $v['pct_terverifikasi'];
        }
        $sosmed_total = $v['sosmed_terverifikasi'] + $v['sosmed_belum'];
        $v['pct_sosmed'] = $sosmed_total > 0 ? round($v['sosmed_terverifikasi']/$sosmed_total*100) : 0;

        return $v;
    }

    // ── 02 ESKALASI ───────────────────────────────────────────
    public function get_eskalasi(array $filter): array
    {
        $this->_apply_filter($filter);
        $rows = $this->db->select('eskalasi, COUNT(*) as jumlah')
                         ->from($this->tbl)
                         ->group_by('eskalasi')
                         ->get()->result_array();

        $sudah = $belum = 0;
        foreach ($rows as $r) {
            if ((int)$r['eskalasi'] === 1) $sudah = (int)$r['jumlah'];
            else                           $belum  = (int)$r['jumlah'];
        }
        $total = $sudah + $belum;
        return [
            'total'      => $total,
            'sudah'      => $sudah,
            'belum'      => $belum,
            'pct_sudah'  => $total > 0 ? round($sudah/$total*100) : 0,
            'pct_belum'  => $total > 0 ? round($belum/$total*100) : 0,
        ];
    }

    // ── 02 TREND ESKALASI (per bulan) ─────────────────────────
    public function get_eskalasi_trend(array $filter): array
    {
        $this->_apply_filter($filter);
        return $this->db
            ->select("DATE_FORMAT(tgl_masuk,'%b\\'%y') as bulan, MONTH(tgl_masuk) as m, YEAR(tgl_masuk) as y, COUNT(*) as jumlah")
            ->from($this->tbl)
            ->where('eskalasi', 1)
            ->group_by('YEAR(tgl_masuk), MONTH(tgl_masuk)')
            ->order_by('y ASC, m ASC')
            ->get()->result_array();
    }

    // ── 03 KETEPATAN WAKTU ────────────────────────────────────
    public function get_ketepatan(array $filter): array
    {
        $this->_apply_filter($filter);
        $rows = $this->db
            ->select('divisi, SUM(on_time=1) as ontime, SUM(on_time=0) as late, COUNT(*) as total')
            ->from($this->tbl)
            ->where('eskalasi', 1)
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
        // Palet warna per status
        $palette = [
            'Done'             => ['color'=>'#10b981','badge'=>'success'],
            'Reject'           => ['color'=>'#ef4444','badge'=>'danger' ],
            'Working On'       => ['color'=>'#1a56db','badge'=>'primary'],
            'Waiting Head Div' => ['color'=>'#f59e0b','badge'=>'warning'],
            'Reschedule'       => ['color'=>'#8b5cf6','badge'=>'warning'],
            'Reschedule 2'     => ['color'=>'#f97316','badge'=>'warning'],
            'Waiting'          => ['color'=>'#6b7280','badge'=>'secondary'],
        ];

        $this->_apply_filter($filter);
        $rows = $this->db
            ->select('status_komplain as label, COUNT(*) as qty')
            ->from($this->tbl)
            ->where('eskalasi', 1)
            ->group_by('status_komplain')
            ->order_by('qty DESC')
            ->get()->result_array();

        return array_map(function($r) use ($palette) {
            $p = $palette[$r['label']] ?? ['color'=>'#6b7280','badge'=>'secondary'];
            return [
                'label' => $r['label'],
                'qty'   => (int)$r['qty'],
                'color' => $p['color'],
                'badge' => $p['badge'],
            ];
        }, $rows);
    }

    // ── MASTER DIVISI ────────────────────────────────────────
    public function get_divisi_list(): array
    {
        return $this->db->select('nama_divisi')->from('tb_divisi')
                        ->order_by('nama_divisi')->get()
                        ->list_fields() // ganti dengan:
                        ;
        // Cara yang benar:
        // return array_column(
        //     $this->db->select('nama_divisi')->from('tb_divisi')->order_by('nama_divisi')->get()->result_array(),
        //     'nama_divisi'
        // );

        /* Jika belum ada tabel master, gunakan data statis:
        return [
            'Aftersales','Buspro (Berkas)','Estate','Finance',
            'Legal','MEP','Project','Purchasing','Sales/Mkt',
            'Serah Terima Kunci','Sosmed',
        ];
        */
    }

    // ── DETAIL KOMPLAIN (untuk modal AJAX) ───────────────────
    public function get_detail_komplain(array $filter, array $extra = [], int $limit = 100): array
    {
        $this->_apply_filter($filter);

        if (!empty($extra['sumber']))          $this->db->where('sumber',          $extra['sumber']);
        if (!empty($extra['status_verif']))    $this->db->where('status_verif',    $extra['status_verif']);
        if (isset($extra['eskalasi']))         $this->db->where('eskalasi',        (int)$extra['eskalasi']);
        if (!empty($extra['divisi']))          $this->db->where('divisi',          $extra['divisi']);
        if (!empty($extra['status_komplain'])) $this->db->where('status_komplain', $extra['status_komplain']);

        return $this->db
            ->select('no_komplain, nama_konsumen, lokasi, divisi, jenis_komplain, tgl_masuk, status_komplain as status')
            ->from($this->tbl)
            ->order_by('tgl_masuk DESC')
            ->limit($limit)
            ->get()->result_array();
    }
}