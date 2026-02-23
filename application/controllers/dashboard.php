<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller  : Dashboard
 * File        : application/controllers/Dashboard.php
 * URL         : /dashboard/crm
 * Requires    : application/models/Crm_model.php
 */
class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Crm_model');
        $this->load->helper(['url', 'form']);
        $this->load->library(['session', 'form_validation']);

        // Proteksi login — sesuaikan dengan sistem auth Anda
        // if (!$this->session->userdata('logged_in')) redirect('auth/login');
    }

    // ─── HALAMAN UTAMA ──────────────────────────────────────
    public function crm()
    {
        // Ambil filter dari POST (saat form submit) atau default
        $filter = [
            'date_from' => $this->input->post('date_from') ?: '2025-01-01',
            'date_to'   => $this->input->post('date_to')   ?: date('Y-m-d'),
            'sumber'    => $this->input->post('sumber')    ?: 'all',
            'divisi'    => $this->input->post('divisi')    ?: 'all',
        ];

        // Ambil semua data dari model
        $verifikasi = $this->Crm_model->get_verifikasi($filter);
        $eskalasi   = $this->Crm_model->get_eskalasi($filter);
        $ketepatan  = $this->Crm_model->get_ketepatan($filter);
        $status_list = $this->Crm_model->get_status_list($filter);
        $eskalasi_trend = $this->Crm_model->get_eskalasi_trend($filter);

        // Hitung summary ketepatan
        $max = $min = null;
        $below = $above = $total_ket = 0;
        foreach ($ketepatan as $d) {
            $pct = $d['total'] > 0 ? round($d['ontime']/$d['total']*100) : 0;
            $total_ket += $d['total'];
            if ($max === null || $pct > $max['pct']) $max = ['pct'=>$pct,'divisi'=>$d['divisi']];
            if ($min === null || $pct < $min['pct']) $min = ['pct'=>$pct,'divisi'=>$d['divisi']];
            $pct >= 80 ? $above++ : $below++;
        }

        // Hitung summary status
        $total_esk = array_sum(array_column($status_list,'qty'));
        $done = $reject = $in_progress = 0;
        foreach ($status_list as $s) {
            if ($s['label'] === 'Done')   $done    += $s['qty'];
            elseif ($s['label'] === 'Reject') $reject += $s['qty'];
            else    $in_progress += $s['qty'];
        }

        $data = [
            'filter'       => $filter,
            'divisi_list'  => $this->Crm_model->get_divisi_list(),

            'verifikasi'   => $verifikasi,
            'eskalasi'     => $eskalasi,
            'ketepatan'    => $ketepatan,
            'status_list'  => $status_list,
            'eskalasi_trend' => $eskalasi_trend,

            'ketepatan_summary' => [
                'max_pct'      => $max['pct']    ?? 0,
                'max_divisi'   => $max['divisi'] ?? '-',
                'min_pct'      => $min['pct']    ?? 0,
                'min_divisi'   => $min['divisi'] ?? '-',
                'below_target' => $below,
                'above_target' => $above,
                'total'        => $total_ket,
            ],

            'status_summary' => [
                'total'          => $total_esk,
                'done'           => $done,
                'reject'         => $reject,
                'in_progress'    => $in_progress,
                'pct_done'       => $total_esk > 0 ? round($done/$total_esk*100)        : 0,
                'pct_reject'     => $total_esk > 0 ? round($reject/$total_esk*100)      : 0,
                'pct_in_progress'=> $total_esk > 0 ? round($in_progress/$total_esk*100) : 0,
            ],
        ];

        $this->load->view('dashboard/crm', $data);
    }

    // ─── AJAX DETAIL MODAL ──────────────────────────────────
    public function crm_detail()
    {
        // Hanya terima AJAX POST
        if (!$this->input->is_ajax_request()) show_404();

        $type  = $this->input->post('type');
        $extra = json_decode($this->input->post('extra'), true) ?: [];
        $filter = [
            'date_from' => $this->input->post('date_from') ?: '2025-01-01',
            'date_to'   => $this->input->post('date_to')   ?: date('Y-m-d'),
            'sumber'    => 'all',
            'divisi'    => $extra['divisi'] ?? 'all',
        ];

        $rows  = [];
        $title = 'Detail Komplain';

        switch ($type) {
            case 'verifTerverifikasi':
                $title = 'Komplain Terverifikasi';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['status_verif'=>'terverifikasi']);
                break;
            case 'verifBelum':
                $title = 'Belum Terverifikasi';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['status_verif'=>'belum']);
                break;
            case 'verifKonsumen':
                $title = 'Komplain Konsumen — Terverifikasi';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['sumber'=>'konsumen']);
                break;
            case 'verifSosmedV':
                $title = 'Sosmed Terverifikasi';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['sumber'=>'sosmed','status_verif'=>'terverifikasi']);
                break;
            case 'verifSosmedB':
                $title = 'Sosmed Belum Verifikasi';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['sumber'=>'sosmed','status_verif'=>'belum']);
                break;
            case 'eskSudah':
                $title = 'Sudah Eskalasi';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['eskalasi'=>1]);
                break;
            case 'eskBelum':
                $title = 'Belum Eskalasi';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['eskalasi'=>0]);
                break;
            case 'divisi':
                $title = ($extra['divisi'] ?? '-') . ' — Ketepatan Waktu';
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['divisi'=>$extra['divisi']??'']);
                break;
            case 'status':
                $title = 'Status: ' . ($extra['label'] ?? '-');
                $rows  = $this->Crm_model->get_detail_komplain($filter, ['status_komplain'=>$extra['label']??'']);
                break;
        }

        // Render tabel HTML (bisa juga pakai view partial)
        $html  = $this->_render_modal_table($rows);

        $response = [
            'title'     => $title . ' (' . number_format(count($rows)) . ')',
            'html'      => $html,
            'csrf_hash' => $this->security->get_csrf_hash(), // refresh token
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ─── EXPORT EXCEL (opsional, butuh PHPSpreadsheet / PhpExcel) ──
    public function crm_export()
    {
        $type   = $this->input->get('type');
        $divisi = $this->input->get('divisi');
        // Implementasikan export sesuai library Excel yang dipakai
        // Contoh menggunakan PhpSpreadsheet:
        // $this->load->library('excel');
        // ...
        echo 'Export ' . htmlspecialchars($type) . ' — implementasikan sesuai kebutuhan';
    }

    // ─── HELPER: render tabel modal ─────────────────────────
    private function _render_modal_table(array $rows): string
    {
        if (empty($rows)) {
            return '<div class="alert alert-info">Tidak ada data ditemukan.</div>';
        }

        $badge_map = [
            'Done'       => 'success',
            'Reject'     => 'danger',
            'Working On' => 'primary',
            'Menunggu'   => 'warning',
            'On Time'    => 'success',
            'Late'       => 'danger',
        ];

        $th = '<thead class="table-light">
            <tr>
                <th>No. Komplain</th>
                <th>Konsumen</th>
                <th>Lokasi</th>
                <th>Divisi</th>
                <th>Jenis</th>
                <th>Tgl Masuk</th>
                <th>Status</th>
            </tr>
        </thead>';

        $tbody = '';
        foreach ($rows as $r) {
            $status  = htmlspecialchars($r['status'] ?? '-');
            $badge   = $badge_map[$status] ?? 'secondary';
            $tbody  .= '<tr>
                <td style="font-family:monospace;font-size:11px;color:#9ca3af">' . htmlspecialchars($r['no_komplain'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['nama_konsumen'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['lokasi'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['divisi'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['jenis_komplain'] ?? '-') . '</td>
                <td style="font-family:monospace;font-size:11px">' . htmlspecialchars($r['tgl_masuk'] ?? '-') . '</td>
                <td><span class="badge bg-' . $badge . '-subtle text-' . $badge . '">' . $status . '</span></td>
            </tr>';
        }

        return '<div class="table-responsive">
            <table class="table table-hover table-sm">' . $th . '<tbody>' . $tbody . '</tbody></table>
        </div>';
    }
}