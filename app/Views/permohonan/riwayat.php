<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
  <div>
    <h1>Riwayat Permohonan</h1>
    <p>Semua permohonan yang telah Anda ajukan.</p>
  </div>
  <a href="<?= site_url('permohonan/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat Baru</a>
</div>

<?php if (!empty($totalUnread)): ?>
  <div class="alert alert-danger" style="margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
    <i class="bi bi-envelope-exclamation-fill"></i>
    <span>Anda memiliki <strong><?= (int) $totalUnread ?> pesan baru</strong> dari admin PPID. Buka halaman detail permohonan untuk membacanya.</span>
  </div>
<?php endif; ?>

<?php
$statusMap = [0=>'Draft',1=>'Menunggu Verifikasi',2=>'Diproses',3=>'Selesai',4=>'Ditolak'];
$badgeClass = [0=>'badge-draft',1=>'badge-menunggu',2=>'badge-proses',3=>'badge-selesai',4=>'badge-ditolak'];
?>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>No. Registrasi</th>
          <th>Tanggal</th>
          <th>Rincian</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($list)): ?>
          <tr><td colspan="5" style="text-align:center;padding:2.5rem;color:var(--n-400);">
            <div style="font-size:2rem;color:var(--n-200);margin-bottom:.5rem;"><i class="bi bi-inbox"></i></div>
            Belum ada permohonan. <a href="<?= site_url('permohonan/create') ?>">Buat baru</a>
          </td></tr>
        <?php endif; ?>
        <?php foreach (($list ?? []) as $r):
          $st = (int) $r['status'];
          $jmlPesanBaru = (int) ($unread[$r['id']] ?? 0);
        ?>
          <tr>
            <td style="font-family:monospace;font-weight:600;color:var(--blue-600);font-size:.82rem;">
              <?= esc($r['no_registrasi'] ?? 'Draft #' . $r['id']) ?>
            </td>
            <td style="color:var(--n-400);font-size:.82rem;"><?= esc($r['submitted_at'] ?? $r['created_at']) ?></td>
            <td style="max-width:250px;"><?= esc(mb_strimwidth($r['rincian_informasi'] ?? '', 0, 50, '...')) ?></td>
            <td>
              <span class="badge <?= $badgeClass[$st] ?? 'badge-draft' ?>"><?= $statusMap[$st] ?? $st ?></span>
              <?php if ($jmlPesanBaru > 0): ?>
                <a href="<?= site_url('permohonan/detail/' . $r['id']) ?>" title="Lihat pesan dari admin"
                   style="display:inline-flex;align-items:center;gap:.25rem;margin-top:.3rem;font-size:.68rem;font-weight:700;
                          background:var(--red-100);color:var(--red-600);padding:.15rem .5rem;border-radius:20px;text-decoration:none;">
                  <i class="bi bi-envelope-exclamation-fill"></i> <?= $jmlPesanBaru ?> pesan baru
                </a>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= site_url('permohonan/detail/' . $r['id']) ?>" class="btn btn-sm btn-outline"><i class="bi bi-eye"></i> Detail</a>
              <?php if ($st === 0): ?>
                <a href="<?= site_url('permohonan/create') ?>?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline"><i class="bi bi-pencil"></i> Edit</a>
                <button class="btn btn-sm btn-outline" style="color:var(--red-600);border-color:var(--red-300);" onclick="deleteDraft(<?= $r['id'] ?>, this)"><i class="bi bi-trash"></i></button>
              <?php endif; ?>
              <?php if ($st !== 0): ?>
                <a href="<?= site_url('permohonan/cetak-bukti/' . $r['id']) ?>" class="btn btn-sm btn-outline"><i class="bi bi-download"></i> Bukti</a>
              <?php endif; ?>
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
function deleteDraft(id, btn) {
  if (!confirm('Hapus draft ini?')) return;
  btn.disabled = true;
  fetch('<?= site_url('permohonan/delete-draft/') ?>' + id, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_VALUE, 'X-Requested-With': 'XMLHttpRequest' }
  }).then(r => r.json()).then(d => {
    if (d.status) {
      btn.closest('tr').remove();
    } else {
      alert(d.message || 'Gagal menghapus');
      btn.disabled = false;
    }
  }).catch(() => { alert('Terjadi kesalahan'); btn.disabled = false; });
}
</script>
<?= $this->endSection() ?>
