<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<?php
$statusMap = [0=>'Draft',1=>'Menunggu Verifikasi',2=>'Diproses',3=>'Selesai',4=>'Ditolak'];
$badgeClass = [0=>'badge-draft',1=>'badge-menunggu',2=>'badge-proses',3=>'badge-selesai',4=>'badge-ditolak'];
$st = (int) $row['status'];
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
  <div>
    <h1>Detail Permohonan</h1>
    <p><?= esc($row['no_registrasi'] ?? 'Draft #' . $row['id']) ?></p>
  </div>
  <span class="badge <?= $badgeClass[$st] ?>" style="font-size:.82rem;padding:.4rem .85rem;"><?= $statusMap[$st] ?? $st ?></span>
</div>

<div class="card" style="margin-bottom:1rem;">
  <div class="card-header"><h3>A. Identitas Pemohon</h3></div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem;font-size:.86rem;">
      <div><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">Nama Lengkap</strong><?= esc($row['nama_pemohon']) ?></div>
      <div><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">Jenis Identitas</strong><?= esc($row['jenis_identitas']) ?></div>
      <div><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">No. Identitas</strong><span style="font-family:monospace;"><?= esc($row['no_identitas']) ?></span></div>
      <div><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">Pekerjaan</strong><?= esc($row['pekerjaan']) ?></div>
      <div><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">No. Telepon</strong><?= esc($row['no_telp']) ?></div>
      <div><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">Email</strong><?= esc($row['email']) ?></div>
      <div style="grid-column:1/-1;"><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">Alamat</strong><?= nl2br(esc($row['alamat'])) ?></div>
    </div>
  </div>
</div>

<div class="card" style="margin-bottom:1rem;">
  <div class="card-header"><h3>B. Rincian Permohonan</h3></div>
  <div class="card-body">
    <div style="font-size:.86rem;">
      <div style="margin-bottom:.75rem;"><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">Rincian Informasi</strong><?= nl2br(esc($row['rincian_informasi'])) ?></div>
      <div><strong style="color:var(--n-400);font-size:.75rem;display:block;margin-bottom:.15rem;">Tujuan Penggunaan</strong><?= nl2br(esc($row['tujuan_penggunaan'])) ?></div>
    </div>
  </div>
</div>

<div class="card" style="margin-bottom:1rem;">
  <div class="card-header"><h3>C. Riwayat</h3></div>
  <div class="card-body">
    <?php if (empty($logs)): ?>
      <p style="color:var(--n-400);font-size:.84rem;margin:0;">Belum ada riwayat.</p>
    <?php else: ?>
      <div class="timeline">
        <?php foreach ($logs as $l): ?>
          <div class="timeline-item done">
            <div class="timeline-dot"></div>
            <div class="timeline-time"><?= esc($l['created_at']) ?></div>
            <div class="timeline-text">
              <?= esc($l['status_dari'] ?? '-') ?> &rarr; <strong><?= esc($l['status_ke']) ?></strong>
              <?php if (!empty($l['aktivitas'])): ?>
                <br><span style="color:var(--n-400);font-size:.78rem;"><?= esc($l['aktivitas']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($row['alasan_penolakan'])): ?>
  <div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i><span><strong>Alasan Penolakan:</strong> <?= esc($row['alasan_penolakan']) ?></span></div>
<?php endif; ?>

<?php if (!empty($lampiran)): ?>
<div class="card" style="margin-bottom:1rem;">
  <div class="card-header"><h3><i class="bi bi-paperclip" style="color:var(--blue-500);"></i> Lampiran</h3></div>
  <div class="card-body">
    <?php foreach ($lampiran as $lamp):
      $isImage = in_array($lamp['mime_type'] ?? '', ['image/jpeg', 'image/png']);
      $isJawaban = ($lamp['tipe'] ?? '') === 'jawaban';
    ?>
      <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--n-100);font-size:.84rem;">
        <i class="bi bi-file-earmark<?= $isImage ? '-image' : '' ?>" style="color:var(--blue-500);font-size:1.15rem;"></i>
        <div style="flex:1;">
          <a href="<?= site_url('files/' . $lamp['id']) ?>" target="_blank" style="font-weight:500;color:var(--blue-600);"><?= esc($lamp['nama_file']) ?></a>
          <div style="font-size:.72rem;color:var(--n-400);">
            <?= $isJawaban ? 'Jawaban Admin' : 'Identitas' ?> &middot; <?= esc($lamp['ukuran_kb']) ?> KB
          </div>
        </div>
        <a href="<?= site_url('files/' . $lamp['id']) ?>" target="_blank" class="btn btn-sm btn-outline" style="font-size:.72rem;"><i class="bi bi-eye"></i> Lihat</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div style="display:flex;gap:.5rem;">
  <a href="<?= site_url('permohonan/riwayat') ?>" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
  <?php if ($st !== 0): ?>
    <a href="<?= site_url('permohonan/cetak-bukti/' . $row['id']) ?>" class="btn btn-primary"><i class="bi bi-download"></i> Unduh Bukti</a>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
