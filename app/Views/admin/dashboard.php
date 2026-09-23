<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="welcome-banner">
  <div>
    <h2>Selamat Datang, <?= esc(session()->get('adminNama') ?? 'Admin') ?> 👋</h2>
    <p>Kelola permohonan informasi publik Dinas Tenaga Kerja Provinsi Jawa Timur</p>
  </div>
  <div class="welcome-banner-icon">
    <i class="bi bi-clipboard2-pulse"></i>
  </div>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,var(--blue-600),var(--blue-400));"><i class="bi bi-file-earmark-text"></i></div>
    <div>
      <div class="stat-value"><?= esc($stats['total'] ?? 0) ?></div>
      <div class="stat-label">Total Dikirim</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,var(--yellow-600),#f59e0b);"><i class="bi bi-hourglass-split"></i></div>
    <div>
      <div class="stat-value"><?= esc($stats['menunggu'] ?? 0) ?></div>
      <div class="stat-label">Menunggu Verifikasi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,var(--blue-500),var(--blue-300));"><i class="bi bi-arrow-repeat"></i></div>
    <div>
      <div class="stat-value"><?= esc($stats['diproses'] ?? 0) ?></div>
      <div class="stat-label">Diproses</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,var(--green-600),#10b981);"><i class="bi bi-check2-circle"></i></div>
    <div>
      <div class="stat-value"><?= esc($stats['selesai'] ?? 0) ?></div>
      <div class="stat-label">Selesai</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,var(--red-600),#ef4444);"><i class="bi bi-x-circle"></i></div>
    <div>
      <div class="stat-value"><?= esc($stats['ditolak'] ?? 0) ?></div>
      <div class="stat-label">Ditolak</div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:1.25rem;">
  <div class="card-header">
    <h3><i class="bi bi-clock-history" style="color:var(--blue-500);"></i> Permohonan Terbaru</h3>
    <a href="<?= site_url('admin/permohonan') ?>" class="btn btn-sm btn-outline">Lihat Semua <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="table-wrap">
    <table id="tableDashboard">
      <thead>
        <tr>
          <th>No. Registrasi</th>
          <th>Nama Pemohon</th>
          <th>Tanggal Kirim</th>
          <th>Deadline</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $statusMap = [0=>'Draft',1=>'Menunggu Verifikasi',2=>'Diproses',3=>'Selesai',4=>'Ditolak'];
        $badgeClass = [0=>'badge-draft',1=>'badge-menunggu',2=>'badge-proses',3=>'badge-selesai',4=>'badge-ditolak'];
        foreach (($recent ?? []) as $row):
          $st = (int) ($row['status'] ?? 0);
        ?>
          <tr>
            <td style="font-family:monospace;font-weight:600;color:var(--blue-600);font-size:.82rem;"><?= esc($row['no_registrasi'] ?? 'Draft #' . $row['id']) ?></td>
            <td><?= esc($row['nama_pemohon'] ?? '-') ?></td>
            <td style="color:var(--n-400);font-size:.82rem;"><?= date('d M Y H:i', strtotime($row['submitted_at'] ?? $row['created_at'])) ?></td>
            <td style="font-size:.82rem;<?= (!empty($row['sla_deadline']) && strtotime($row['sla_deadline']) < time() && $st < 3) ? 'color:var(--red-600);font-weight:600;' : 'color:var(--n-400);' ?>"><?= !empty($row['sla_deadline']) ? date('d M Y', strtotime($row['sla_deadline'])) : '-' ?></td>
            <td><span class="badge <?= $badgeClass[$st] ?? 'badge-draft' ?>"><?= $statusMap[$st] ?? $st ?></span></td>
            <td><a href="<?= site_url('admin/permohonan/detail/' . $row['id']) ?>" class="btn btn-sm btn-outline"><i class="bi bi-eye"></i></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($recent)): ?>
          <tr><td colspan="6" class="empty-state">Belum ada data permohonan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
  if ($('#tableDashboard tbody tr').length > 0 && !$('#tableDashboard .empty-state').length) {
    $('#tableDashboard').DataTable(
      $.extend({}, DATATABLE_DEFAULTS, {
        order: [],
        paging: false,
        searching: false,
        info: false
      })
    );
  }
});
</script>
<?= $this->endSection() ?>
