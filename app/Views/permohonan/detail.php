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

<!-- ═══════════════════════════════════════════════════
     C. RIWAYAT ALUR
     ═══════════════════════════════════════════════════ -->
<div class="card" style="margin-bottom:1rem;">
  <div class="card-header">
    <h3 style="font-size:.75rem;letter-spacing:.03em;">C. Riwayat Alur</h3>
  </div>
  <div class="card-body">

    <?php

    // ── Map status permohonan ke tahap stepper ──
    // Tahap: 0=Dikirim, 1=Verifikasi, 2=Diproses, 3=Selesai
    $statusLabels = [0=>'Draft',1=>'Menunggu Verifikasi',2=>'Diproses',3=>'Selesai',4=>'Ditolak'];
    $stepNames = ['Permohonan Dikirim', 'Verifikasi', 'Diproses PPID', 'Hasil Diterbitkan'];

    // Tentukan tahap selesai & tahap aktif berdasarkan status permohonan
    if ($st === 0) {
      $doneSteps = []; $activeStep = 0;
    } elseif ($st === 1) {
      $doneSteps = [0]; $activeStep = 1;
    } elseif ($st === 2) {
      $doneSteps = [0, 1]; $activeStep = 2;
    } elseif ($st === 3) {
      $doneSteps = [0, 1, 2, 3]; $activeStep = -1;
    } elseif ($st === 4) {
      // Ditolak — selesaikan tahap sesuai tahapan terakhir
      $lastDone = -1;
      foreach (($tahapan ?? []) as $ti => $tv) {
        if (($tv['status'] ?? '') === 'Selesai') $lastDone = $ti;
      }
      $doneSteps = range(0, max(0, $lastDone));
      $activeStep = min(3, $lastDone + 1);
    }

    // Tanggal per tahap dari tabel tahapan (jika ada)
    $tahapDates = [];
    foreach (($tahapan ?? []) as $ti => $tv) {
      $tahapDates[$ti] = $tv['tanggal_selesai'] ?? $tv['tanggal_mulai'] ?? null;
    }
    // Fallback: submitted_at untuk tahap 0
    if (empty($tahapDates[0]) && !empty($row['submitted_at'])) {
      $tahapDates[0] = $row['submitted_at'];
    }
    // Fallback: updated_at untuk tahap terakhir jika selesai
    if ($st === 3 && empty($tahapDates[3]) && !empty($row['updated_at'])) {
      $tahapDates[3] = $row['updated_at'];
    }

    $lastUpdate = $row['updated_at'] ?? $row['submitted_at'] ?? $row['created_at'] ?? null;
    ?>

    <!-- Stepper -->
    <?php if (empty($tahapan) && $st === 0): ?>
      <div style="text-align:center;padding:1.5rem 0;color:var(--n-400);font-size:.84rem;">
        <i class="bi bi-inbox" style="font-size:2rem;color:var(--n-200);display:block;margin-bottom:.5rem;"></i>
        Belum ada progres. Permohonan masih berupa draft.
      </div>
    <?php else: ?>
      <div class="stepper">
        <?php for ($i = 0; $i < 4; $i++):
          $isDone = in_array($i, $doneSteps, true);
          $isActive = ($i === $activeStep && $st !== 4);
          $isRejectedStep = ($st === 4 && $i === $activeStep);
          $isPending = !$isDone && !$isActive && !$isRejectedStep;
          $isLast = ($i === 3);
          $stateClass = $isDone ? 'stepper-done' : ($isActive ? 'stepper-active' : ($isRejectedStep ? 'stepper-rejected' : 'stepper-pending'));
          $stepDate = $tahapDates[$i] ?? null;
        ?>
          <div class="stepper-item <?= $stateClass ?>">
            <?php if (!$isLast): ?>
              <div class="stepper-line <?= $isDone ? 'stepper-line-done' : 'stepper-line-pending' ?>"></div>
            <?php endif; ?>

            <div class="stepper-dot">
              <?php if ($isDone): ?>
                <i class="bi bi-check-lg"></i>
              <?php elseif ($isRejectedStep): ?>
                <i class="bi bi-x-lg"></i>
              <?php elseif ($isActive): ?>
                <span class="stepper-dot-inner"></span>
              <?php endif; ?>
            </div>

            <div class="stepper-content">
              <div class="stepper-title-row">
                <span class="stepper-title"><?= esc($stepNames[$i]) ?></span>
                <?php if ($isActive): ?>
                  <span class="stepper-badge-active">Tahap saat ini</span>
                <?php endif; ?>
                <?php if ($isRejectedStep): ?>
                  <span class="stepper-badge-rejected">Ditolak</span>
                <?php endif; ?>
              </div>
              <div class="stepper-meta">
                <?php if ($isDone && $stepDate): ?>
                  <span><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($stepDate)) ?> &bull; <?= date('H:i', strtotime($stepDate)) ?> WIB</span>
                <?php elseif ($isActive && $stepDate): ?>
                  <span><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($stepDate)) ?> &bull; <?= date('H:i', strtotime($stepDate)) ?> WIB</span>
                <?php elseif ($isActive && !$stepDate && $row['submitted_at']): ?>
                  <span><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['submitted_at'])) ?> &bull; <?= date('H:i', strtotime($row['submitted_at'])) ?> WIB</span>
                <?php elseif ($isPending): ?>
                  <span class="stepper-pending-text">Menunggu</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <!-- Alasan Penolakan -->
    <?php if ($st === 4 && !empty($row['alasan_penolakan'])): ?>
      <div class="stepper-reject-note">
        <i class="bi bi-x-octagon-fill"></i>
        <div>
          <strong>Alasan Penolakan</strong>
          <p><?= esc($row['alasan_penolakan']) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Footer: Pembaruan terakhir -->
    <?php if ($lastUpdate): ?>
      <div class="progres-footer">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Pembaruan terakhir: <strong><?= date('d M Y', strtotime($lastUpdate)) ?> &bull; <?= date('H:i', strtotime($lastUpdate)) ?> WIB</strong></span>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════
     D. PESAN DARI ADMIN
     ═══════════════════════════════════════════════════ -->
<?php if (!empty($pesan)): ?>
<div class="card" style="margin-bottom:1rem;">
  <div class="card-header">
    <h3 style="font-size:.75rem;letter-spacing:.03em;">D. Pesan dari Admin PPID</h3>
    <span style="font-size:.72rem;font-weight:700;color:var(--n-500);background:var(--n-100);padding:.2rem .6rem;border-radius:20px;">
      <?= count($pesan) ?> pesan
    </span>
  </div>
  <div class="card-body">
    <?php foreach ($pesan as $p):
      $isTolak = ($p['tipe'] ?? '') === 'penolakan';
      $baru    = empty($p['is_read']);
    ?>
      <div style="border:1px solid <?= $isTolak ? '#fecaca' : 'var(--n-200)' ?>;border-left:4px solid <?= $isTolak ? '#dc2626' : 'var(--blue-500)' ?>;
                  background:<?= $isTolak ? '#fef2f2' : 'var(--white)' ?>;border-radius:8px;padding:.8rem .9rem;margin-bottom:.7rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.4rem;">
          <span style="font-size:.85rem;font-weight:700;color:<?= $isTolak ? '#991b1b' : 'var(--n-800)' ?>;display:flex;align-items:center;gap:.4rem;">
            <i class="bi bi-<?= $isTolak ? 'x-octagon-fill' : 'envelope-paper-fill' ?>" style="color:<?= $isTolak ? '#dc2626' : 'var(--blue-500)' ?>;"></i>
            <?= esc($p['judul']) ?>
          </span>
          <span style="display:flex;gap:.4rem;align-items:center;">
            <?php if ($baru): ?>
              <span style="font-size:.62rem;font-weight:800;padding:.1rem .5rem;border-radius:20px;background:var(--yellow-100);color:var(--yellow-600);">BARU</span>
            <?php endif; ?>
            <span style="font-size:.63rem;font-weight:700;padding:.1rem .5rem;border-radius:20px;
                  background:<?= $isTolak ? '#fee2e2' : 'var(--blue-50)' ?>;color:<?= $isTolak ? '#dc2626' : 'var(--blue-600)' ?>;">
              <?= $isTolak ? 'PENOLAKAN' : 'INFORMASI' ?>
            </span>
          </span>
        </div>
        <div style="font-size:.83rem;color:var(--n-700);line-height:1.55;white-space:pre-wrap;"><?= esc($p['isi']) ?></div>
        <div style="margin-top:.5rem;padding-top:.4rem;border-top:1px dashed <?= $isTolak ? '#fecaca' : 'var(--n-200)' ?>;font-size:.68rem;color:var(--n-400);display:flex;align-items:center;gap:.4rem;">
          <i class="bi bi-calendar3"></i>
          <?= !empty($p['created_at']) ? date('d M Y H:i', strtotime($p['created_at'])) : '-' ?> WIB
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
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
