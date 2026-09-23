<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1><i class="bi bi-file-earmark-text" style="color:var(--blue-600);"></i> Daftar Permohonan</h1>
  <p>Kelola seluruh permohonan informasi publik yang masuk</p>
</div>

<div class="card" style="margin-bottom:1rem;">
  <div class="card-body">
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Cari</label>
        <input type="text" name="q" value="<?= esc($q ?? '') ?>" class="form-control" style="max-width:220px;" placeholder="Nama/nomor/identitas">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" style="max-width:170px;">
          <option value="">Semua Status</option>
          <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
          <option value="2" <?= ($status ?? '') === '2' ? 'selected' : '' ?>>Diproses</option>
          <option value="3" <?= ($status ?? '') === '3' ? 'selected' : '' ?>>Selesai</option>
          <option value="4" <?= ($status ?? '') === '4' ? 'selected' : '' ?>>Ditolak</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Dari</label>
        <input type="date" name="dari" value="<?= esc($dari ?? '') ?>" class="form-control" style="max-width:155px;">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Sampai</label>
        <input type="date" name="sampai" value="<?= esc($sampai ?? '') ?>" class="form-control" style="max-width:155px;">
      </div>
      <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
      <a href="<?= site_url('admin/permohonan/export' . (!empty($q) ? '?q=' . urlencode($q) : '') . (!empty($status) ? ($q ? '&' : '?') . 'status=' . $status : '') . (!empty($dari) ? (($q || $status) ? '&' : '?') . 'dari=' . $dari : '') . (!empty($sampai) ? (($q || $status || $dari) ? '&' : '?') . 'sampai=' . $sampai : '')) ?>" class="btn btn-outline"><i class="bi bi-download"></i> Export CSV</a>
      <?php if (!empty($q) || !empty($status) || !empty($dari) || !empty($sampai)): ?>
        <a href="<?= site_url('admin/permohonan') ?>" class="btn btn-outline"><i class="bi bi-x-lg"></i> Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php
$statusMap = [0=>'Draft',1=>'Menunggu Verifikasi',2=>'Diproses',3=>'Selesai',4=>'Ditolak'];
$badgeClass = [0=>'badge-draft',1=>'badge-menunggu',2=>'badge-proses',3=>'badge-selesai',4=>'badge-ditolak'];
?>

<div class="card">
  <div class="table-wrap">
    <table id="tablePermohonan" style="width:100%;">
      <thead>
        <tr>
          <th style="width:180px;">No. Registrasi</th>
          <th>Nama Pemohon</th>
          <th style="width:110px;">Identitas</th>
          <th style="width:100px;">Tanggal</th>
          <th style="width:100px;">Deadline</th>
          <th style="width:140px;text-align:center;">Status</th>
          <th style="width:80px;text-align:center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($list ?? []) as $r):
          $st = (int) ($r['status'] ?? 0);
          $nik = $r['no_identitas'] ?? '';
          $isOverdue = !empty($r['sla_deadline']) && strtotime($r['sla_deadline']) < time() && $st < 3;
        ?>
          <tr style="vertical-align:middle;<?= $isOverdue ? 'background:var(--red-100);' : '' ?>">
            <td style="font-family:monospace;font-weight:600;color:var(--blue-600);font-size:.82rem;"><?= esc($r['no_registrasi'] ?? 'Draft #' . $r['id']) ?></td>
            <td style="font-weight:500;"><?= esc($r['nama_pemohon'] ?? '-') ?></td>
            <td style="font-family:monospace;font-size:.82rem;color:var(--n-500);"><?= esc(strlen($nik) > 4 ? str_repeat('*', strlen($nik) - 4) . substr($nik, -4) : $nik) ?></td>
            <td style="color:var(--n-400);font-size:.82rem;"><?= date('d M Y', strtotime($r['submitted_at'] ?? $r['created_at'])) ?></td>
            <td style="font-size:.82rem;<?= $isOverdue ? 'color:var(--red-600);font-weight:600;' : 'color:var(--n-400);' ?>">
              <?= !empty($r['sla_deadline']) ? date('d M Y', strtotime($r['sla_deadline'])) : '-' ?>
              <?= $isOverdue ? ' <i class="bi bi-exclamation-triangle" style="font-size:.7rem;"></i>' : '' ?>
            </td>
            <td style="text-align:center;"><span class="badge <?= $badgeClass[$st] ?? 'badge-draft' ?>"><?= $statusMap[$st] ?? $st ?></span></td>
            <td style="text-align:center;">
              <a href="<?= site_url('admin/permohonan/detail/' . $r['id']) ?>" class="btn-ghost" title="Lihat Detail"><i class="bi bi-eye"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
  $('#tablePermohonan').DataTable(
    $.extend({}, DATATABLE_DEFAULTS, {
      order: [],
      columnDefs: [{ orderable: false, targets: [6] }],
      language: { emptyTable: 'Tidak ada data permohonan.' }
    })
  );
});
</script>
<?= $this->endSection() ?>
