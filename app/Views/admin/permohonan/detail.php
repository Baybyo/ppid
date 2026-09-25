<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$statusMap = [0=>'Draft',1=>'Menunggu Verifikasi',2=>'Diproses',3=>'Selesai',4=>'Ditolak'];
$badgeClass = [0=>'badge-draft',1=>'badge-menunggu',2=>'badge-proses',3=>'badge-selesai',4=>'badge-ditolak'];
$st = (int) ($row['status'] ?? 0);

$transitions = [1=>[2=>'Verifikasi & Proses',4=>'Tolak'], 2=>[3=>'Selesaikan',4=>'Tolak'], 3=>[], 4=>[]];
$allowedNext = $transitions[$st] ?? [];
$isFinal = empty($allowedNext);

$isOverdue = !empty($row['sla_deadline']) && strtotime($row['sla_deadline']) < time() && $st < 3;
$daysLeft = '';
$diff = 0;
if (!empty($row['sla_deadline']) && $st < 3) {
  // Sisa dihitung dalam HARI KERJA (Sen–Jum), bukan hari kalender
  $sisa = (int) ($sisaKerja ?? 0);
  $diff = $sisa;
  $daysLeft = $sisa < 0 ? abs($sisa) . ' hari kerja lalu' : $sisa . ' hari kerja lagi';
}
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
  <div>
    <h1><i class="bi bi-file-earmark-text" style="color:var(--blue-600);"></i> Detail Permohonan</h1>
    <p><?= esc($row['no_registrasi'] ?? 'Draft #' . $row['id']) ?></p>
  </div>
  <div style="display:flex;gap:.5rem;align-items:center;">
    <?php if ($isOverdue): ?>
      <span style="background:var(--red-100);color:var(--red-600);padding:.3rem .7rem;border-radius:20px;font-size:.72rem;font-weight:700;display:flex;align-items:center;gap:.3rem;">
        <i class="bi bi-exclamation-triangle"></i> Melebihi SLA
      </span>
    <?php endif; ?>
    <span class="badge <?= $badgeClass[$st] ?>" style="font-size:.82rem;padding:.4rem .85rem;"><?= $statusMap[$st] ?? $st ?></span>
    <a href="<?= site_url('admin/permohonan') ?>" class="btn btn-sm btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>
</div>

<?php if ($isOverdue): ?>
  <div class="alert alert-danger" style="margin-bottom:1rem;">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Permohonan ini telah melebihi batas waktu SLA! Batas: <?= date('d M Y', strtotime($row['sla_deadline'])) ?> — <?= $daysLeft ?> (Sabtu/Minggu tidak dihitung).</span>
  </div>
<?php endif; ?>

<?php
$tahapanDefs = [
  'Diterima'  => ['icon' => 'bi-inbox',        'desc' => 'Permohonan diterima sistem & menunggu verifikasi petugas.', 'color' => '#0ea5e9', 'soft' => '#e0f2fe'],
  'Verifikasi'=> ['icon' => 'bi-check2-square','desc' => 'Petugas memeriksa kelengkapan identitas & rincian informasi.', 'color' => '#8b5cf6', 'soft' => '#ede9fe'],
  'Diproses'  => ['icon' => 'bi-gear',         'desc' => 'Informasi sedang dihimpun & disiapkan oleh unit terkait.', 'color' => '#f59e0b', 'soft' => '#fef3c7'],
  'Selesai'   => ['icon' => 'bi-check-circle', 'desc' => 'Jawaban telah diserahkan kepada pemohon.', 'color' => '#16a34a', 'soft' => '#dcfce7'],
];
$tahapanIcons = ['bi-inbox', 'bi-check2-square', 'bi-gear', 'bi-check-circle'];
$tahapanStatus = [];
$tahapanRow    = [];
foreach ($tahapan as $t) {
  $tahapanStatus[$t['tahap']] = $t['status'];
  $tahapanRow[$t['tahap']]    = $t;
}

$stTahap = ['Menunggu' => ['badge-menunggu', 'Menunggu'], 'Dalam Proses' => ['badge-proses', 'Dalam Proses'], 'Selesai' => ['badge-selesai', 'Selesai']];

// Normalisasi: status per tahap harus berurutan. Tahap sebelum tahap aktif
// otomatis dianggap Selesai, tahap sesudahnya Menunggu.
$stageOrder = array_keys($tahapanDefs);
$activeIdx  = -1;
foreach ($stageOrder as $idx => $stageName) {
  if (($tahapanStatus[$stageName] ?? 'Menunggu') !== 'Menunggu') $activeIdx = $idx;
}
foreach ($stageOrder as $idx => $stageName) {
  $cur = $tahapanStatus[$stageName] ?? 'Menunggu';
  if ($activeIdx < 0) {
    $tahapanStatus[$stageName] = 'Menunggu';
  } elseif ($idx < $activeIdx) {
    $tahapanStatus[$stageName] = 'Selesai';          // otomatis dianggap selesai
  } elseif ($idx === $activeIdx) {
    $tahapanStatus[$stageName] = $cur;
  } else {
    $tahapanStatus[$stageName] = 'Menunggu';
  }
}

$doneCount = count(array_filter($tahapanStatus, fn($s) => $s === 'Selesai'));
$activeCount = count(array_filter($tahapanStatus, fn($s) => $s === 'Dalam Proses'));
// Tahap aktif dihitung setengah poin agar progres naik sejak tahap mulai berjalan
$pct = (int) round((($doneCount + $activeCount * 0.5) / max(count($tahapanDefs), 1)) * 100);
?>

<div class="card" style="margin-bottom:1rem;">
  <div class="card-header">
    <h3><i class="bi bi-kanban" style="color:var(--blue-500);"></i> Tahapan Proses</h3>
    <span style="font-size:.72rem;font-weight:700;color:var(--n-500);background:var(--n-100);padding:.2rem .6rem;border-radius:20px;">
      <?= $doneCount ?>/<?= count($tahapanDefs) ?> tahap selesai
    </span>
  </div>
  <div class="card-body">

    <!-- Progress bar keseluruhan -->
    <div style="margin-bottom:1.25rem;">
      <div style="display:flex;justify-content:space-between;font-size:.74rem;font-weight:600;color:var(--n-500);margin-bottom:.35rem;">
        <span>Progres Keseluruhan</span>
        <span><?= $pct ?>%</span>
      </div>
      <div style="height:8px;background:var(--n-100);border-radius:99px;overflow:hidden;">
        <div style="height:100%;width:<?= $pct ?>%;border-radius:99px;background:linear-gradient(90deg,var(--blue-600),var(--green-500));transition:width .4s ease;"></div>
      </div>
    </div>

    <div style="display:flex;gap:.75rem;position:relative;flex-wrap:wrap;">
      <?php $i = -1; foreach ($tahapanDefs as $tn => $def): $i++;
        $ts   = $tahapanStatus[$tn] ?? 'Menunggu';
        $rowT = $tahapanRow[$tn] ?? [];
        $isActive = $ts === 'Dalam Proses';
        $isDone   = $ts === 'Selesai';
        $state = $isDone ? 'done' : ($isActive ? 'active' : 'wait');
        [$bc, $bl] = $stTahap[$ts] ?? ['badge-draft', $ts];
      ?>
        <div style="flex:1;min-width:180px;position:relative;border:1px solid <?= $isDone ? $def['color'] : ($isActive ? $def['color'] : 'var(--n-200)') ?>;
             border-radius:var(--r-lg);padding:.9rem;background:<?= $isDone || $isActive ? $def['soft'] : 'var(--white)' ?>;
             box-shadow:<?= $isActive ? '0 2px 10px rgba(0,0,0,.06)' : 'none' ?>;transition:all .2s ease;">

          <!-- Nomor urut -->
          <div style="position:absolute;top:.6rem;right:.7rem;font-size:.65rem;font-weight:800;color:<?= $isDone || $isActive ? $def['color'] : 'var(--n-300)' ?>;opacity:.7;">
            0<?= $i + 1 ?>
          </div>

          <!-- Ikon -->
          <div style="width:38px;height:38px;border-radius:10px;margin-bottom:.55rem;display:flex;align-items:center;justify-content:center;font-size:1.05rem;
               background:<?= $isDone || $isActive ? $def['color'] : 'var(--n-100)' ?>;
               color:<?= $isDone || $isActive ? '#fff' : 'var(--n-400)' ?>;">
            <i class="bi <?= $isDone ? 'bi-check-lg' : ($isActive ? 'bi-arrow-repeat' : $tahapanIcons[$i]) ?>"></i>
          </div>

          <!-- Judul + badge -->
          <div style="font-size:.84rem;font-weight:700;color:<?= $isDone || $isActive ? 'var(--n-800)' : 'var(--n-500)' ?>;margin-bottom:.3rem;">
            <?= $tn ?>
          </div>
          <span class="badge <?= $bc ?>" style="font-size:.65rem;padding:.15rem .5rem;margin-bottom:.5rem;display:inline-block;"><?= $bl ?></span>

          <!-- Keterangan -->
          <div style="font-size:.72rem;color:var(--n-500);line-height:1.45;margin-bottom:.55rem;">
            <?= $def['desc'] ?>
          </div>

          <!-- Tanggal -->
          <?php if (!empty($rowT['tanggal_selesai'])): ?>
            <div style="font-size:.68rem;color:var(--n-400);display:flex;align-items:center;gap:.3rem;padding-top:.45rem;border-top:1px dashed var(--n-200);">
              <i class="bi bi-calendar-check" style="color:<?= $def['color'] ?>;"></i>
              Selesai <?= date('d M Y H:i', strtotime($rowT['tanggal_selesai'])) ?>
            </div>
          <?php elseif (!empty($rowT['tanggal_mulai'])): ?>
            <div style="font-size:.68rem;color:var(--n-400);display:flex;align-items:center;gap:.3rem;padding-top:.45rem;border-top:1px dashed var(--n-200);">
              <i class="bi bi-play-circle" style="color:<?= $def['color'] ?>;"></i>
              Mulai <?= date('d M Y H:i', strtotime($rowT['tanggal_mulai'])) ?>
            </div>
          <?php elseif ($isDone): ?>
            <div style="font-size:.68rem;color:var(--n-400);display:flex;align-items:center;gap:.3rem;padding-top:.45rem;border-top:1px dashed var(--n-200);">
              <i class="bi bi-check2-all" style="color:<?= $def['color'] ?>;"></i>
              Selesai &middot; tanggal tidak tercatat
            </div>
          <?php elseif ($isActive): ?>
            <div style="font-size:.68rem;color:var(--n-400);display:flex;align-items:center;gap:.3rem;padding-top:.45rem;border-top:1px dashed var(--n-200);">
              <i class="bi bi-arrow-repeat" style="color:<?= $def['color'] ?>;"></i>
              Sedang berjalan
            </div>
          <?php else: ?>
            <div style="font-size:.68rem;color:var(--n-300);display:flex;align-items:center;gap:.3rem;padding-top:.45rem;border-top:1px dashed var(--n-200);">
              <i class="bi bi-clock"></i> Belum dimulai
            </div>
          <?php endif; ?>

          <!-- Keterangan tambahan -->
          <?php if (!empty($rowT['keterangan'])): ?>
            <div style="font-size:.68rem;color:var(--n-500);margin-top:.4rem;font-style:italic;">
              "<?= esc($rowT['keterangan']) ?>"
            </div>
          <?php endif; ?>
        </div>

        <!-- Penghubung antar kartu -->
        <?php if ($i < count($tahapanDefs) - 1): ?>
          <div style="position:absolute;top:50%;right:-.65rem;width:.6rem;height:2px;background:<?= $isDone ? 'var(--green-500)' : 'var(--n-200)' ?>;z-index:2;"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php if ($st === 4 && !empty($row['alasan_penolakan'])): ?>
      <div style="margin-top:1rem;padding:.75rem 1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.8rem;color:#991b1b;display:flex;gap:.5rem;align-items:flex-start;">
        <i class="bi bi-x-octagon-fill" style="color:#dc2626;font-size:1.05rem;flex-shrink:0;margin-top:.1rem;"></i>
        <div><strong>Ditolak:</strong> <?= esc($row['alasan_penolakan']) ?></div>
      </div>
    <?php endif; ?>

  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:1rem;align-items:start;">
  <div>
    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header"><h3><i class="bi bi-person" style="color:var(--blue-500);"></i> Data Pemohon</h3></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem;font-size:.86rem;">
          <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Nama Pemohon</strong><?= esc($row['nama_pemohon']) ?></div>
          <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Jenis Identitas</strong><?= esc($row['jenis_identitas']) ?></div>
          <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">No. Identitas</strong><span style="font-family:monospace;"><?= esc($row['no_identitas']) ?></span></div>
          <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Pekerjaan</strong><?= esc($row['pekerjaan']) ?></div>
          <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">No. Telepon</strong><?= esc($row['no_telp']) ?></div>
          <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Email</strong><?= esc($row['email']) ?></div>
          <div style="grid-column:1/-1;"><strong style="color:var(--n-400);font-size:.75rem;display:block;">Alamat</strong><?= nl2br(esc($row['alamat'])) ?></div>
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header"><h3><i class="bi bi-info-circle" style="color:var(--blue-500);"></i> Rincian Permohonan</h3></div>
      <div class="card-body">
        <div style="font-size:.86rem;">
          <div style="margin-bottom:.75rem;"><strong style="color:var(--n-400);font-size:.75rem;display:block;">Rincian Informasi</strong><?= nl2br(esc($row['rincian_informasi'])) ?></div>
          <div style="margin-bottom:.75rem;"><strong style="color:var(--n-400);font-size:.75rem;display:block;">Tujuan Penggunaan</strong><?= nl2br(esc($row['tujuan_penggunaan'])) ?></div>
          <div style="margin-bottom:.75rem;"><strong style="color:var(--n-400);font-size:.75rem;display:block;">Cara Memperoleh</strong><?php $cm = json_decode($row['cara_memperoleh'] ?? '[]', true) ?: []; echo esc(implode(', ', $cm)); ?></div>
          <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Cara Salinan</strong><?php $cs = json_decode($row['cara_salinan'] ?? '[]', true) ?: []; echo esc(implode(', ', $cs)); if (!empty($row['cara_salinan_lainnya'])) echo ' — ' . esc($row['cara_salinan_lainnya']); ?></div>
        </div>
      </div>
    </div>

    <?php if (!empty($lampiran)): ?>
    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header"><h3><i class="bi bi-paperclip" style="color:var(--blue-500);"></i> Lampiran</h3></div>
      <div class="card-body">
        <?php foreach ($lampiran as $lamp):
          $isImage = in_array($lamp['mime_type'], ['image/jpeg', 'image/png']);
        ?>
          <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem 0;border-bottom:1px solid var(--n-100);font-size:.82rem;">
            <i class="bi bi-file-earmark<?= $isImage ? '-image' : '' ?>" style="color:var(--blue-500);font-size:1.1rem;"></i>
            <div style="flex:1;">
              <a href="<?= site_url('files/' . $lamp['id']) ?>" target="_blank" style="font-weight:500;"><?= esc($lamp['nama_file']) ?></a>
              <div style="font-size:.72rem;color:var(--n-400);"><?= esc($lamp['tipe']) ?> &middot; <?= esc($lamp['ukuran_kb']) ?> KB</div>
            </div>
            <?php if ($isImage): ?>
              <a href="<?= site_url('files/' . $lamp['id']) ?>" target="_blank" class="btn btn-sm btn-outline" style="font-size:.72rem;"><i class="bi bi-eye"></i> Lihat</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header">
        <h3><i class="bi bi-envelope-paper" style="color:var(--blue-500);"></i> Pesan ke Pemohon</h3>
        <span style="font-size:.72rem;font-weight:700;color:var(--n-500);background:var(--n-100);padding:.2rem .6rem;border-radius:20px;">
          <?= count($pesan ?? []) ?> pesan
        </span>
      </div>
      <div class="card-body">
        <?php if (empty($pesan)): ?>
          <div class="empty-state" style="padding:1.5rem 1rem;text-align:center;">
            <div style="font-size:2rem;color:var(--n-200);margin-bottom:.5rem;"><i class="bi bi-envelope-paper"></i></div>
            <p style="color:var(--n-400);font-size:.84rem;margin:0;">Belum ada pesan yang dikirim ke pemohon untuk permohonan ini.</p>
          </div>
        <?php else: ?>
          <?php foreach ($pesan as $p):
            $isTolak = ($p['tipe'] ?? '') === 'penolakan';
          ?>
            <div style="border:1px solid <?= $isTolak ? '#fecaca' : 'var(--n-200)' ?>;border-left:3px solid <?= $isTolak ? '#dc2626' : 'var(--blue-500)' ?>;
                        background:<?= $isTolak ? '#fef2f2' : 'var(--white)' ?>;border-radius:8px;padding:.7rem .85rem;margin-bottom:.7rem;">
              <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.35rem;">
                <span style="font-size:.8rem;font-weight:700;color:<?= $isTolak ? '#991b1b' : 'var(--n-800)' ?>;">
                  <?= esc($p['judul']) ?>
                </span>
                <span style="font-size:.63rem;font-weight:700;padding:.1rem .5rem;border-radius:20px;
                      background:<?= $isTolak ? '#fee2e2' : 'var(--blue-50)' ?>;color:<?= $isTolak ? '#dc2626' : 'var(--blue-600)' ?>;">
                  <?= $isTolak ? 'PENOLAKAN' : 'UMUM' ?>
                </span>
              </div>
              <div style="font-size:.78rem;color:var(--n-600);line-height:1.5;white-space:pre-wrap;"><?= esc($p['isi']) ?></div>
              <div style="display:flex;align-items:center;gap:.75rem;margin-top:.5rem;padding-top:.4rem;border-top:1px dashed <?= $isTolak ? '#fecaca' : 'var(--n-200)' ?>;font-size:.66rem;color:var(--n-400);flex-wrap:wrap;">
                <span><i class="bi bi-calendar3"></i> <?= !empty($p['created_at']) ? date('d M Y H:i', strtotime($p['created_at'])) : '-' ?></span>
                <span><i class="bi bi-person-circle"></i> <?= esc($adminNames[(int) ($p['admin_id'] ?? 0)] ?? 'Admin') ?></span>
                <span style="margin-left:auto;font-weight:700;color:<?= !empty($p['is_read']) ? 'var(--green-600)' : 'var(--yellow-600)' ?>;">
                  <i class="bi bi-<?= !empty($p['is_read']) ? 'check2-all' : 'circle' ?>"></i>
                  <?= !empty($p['is_read']) ? 'Dibaca pemohon' : 'Belum dibaca' ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header">
        <h3><i class="bi bi-clock-history" style="color:var(--blue-500);"></i> Riwayat Log</h3>
        <span style="font-size:.72rem;font-weight:700;color:var(--n-500);background:var(--n-100);padding:.2rem .6rem;border-radius:20px;">
          <?= count($logs) ?> aktivitas
        </span>
      </div>
      <div class="card-body">
        <?php if (!empty($logs)): ?>
          <?php
          // Klasifikasi jenis aktivitas → ikon & warna
          $kls = function (?string $akt, ?string $dari, ?string $ke) {
            $akt = strtolower((string) $akt);
            if ($ke === '4')                       return ['bi-x-octagon-fill', '#dc2626', '#fef2f2', '#fecaca', 'Ditolak'];
            if ($ke === '3')                       return ['bi-check2-circle-fill', '#16a34a', '#f0fdf4', '#bbf7d0', 'Selesai'];
            if (str_contains($akt, 'upload'))      return ['bi-cloud-arrow-up-fill', '#2563b5', '#eff6ff', '#bfdbfe', 'Unggahan'];
            if (str_contains($akt, 'draft'))       return ['bi-pencil-square', '#8b5cf6', '#f5f3ff', '#ddd6fe', 'Draft'];
            if ($ke === '2')                       return ['bi-gear-fill', '#f59e0b', '#fffbeb', '#fde68a', 'Diproses'];
            if ($ke === '1')                       return ['bi-inbox-fill', '#0ea5e9', '#f0f9ff', '#bae6fd', 'Masuk'];
            if (!empty($dari) && !empty($ke))      return ['bi-arrow-left-right', '#6b7280', '#f9fafb', '#e5e7eb', 'Transisi'];
            return ['bi-info-circle-fill', '#6b7280', '#f9fafb', '#e5e7eb', 'Aktivitas'];
          };
          ?>
          <div class="timeline" style="padding-left:1.9rem;">
            <?php $logReversed = array_reverse($logs); foreach ($logReversed as $idx => $l):
              [$ic, $clr, $bgc, $brc, $label] = $kls($l['aktivitas'] ?? '', $l['status_dari'] ?? null, $l['status_ke'] ?? null);
              $isLast = ($idx === 0);
              $pelaku = !empty($adminNames[(int)($l['admin_id'] ?? 0)]) ? $adminNames[(int)($l['admin_id'] ?? 0)] : 'Sistem';
              $t = strtotime($l['created_at']);
            ?>
              <div class="timeline-item <?= $isLast ? 'active' : 'done' ?>" style="padding-bottom:1.15rem;">
                <!-- Ikon log -->
                <div style="position:absolute;left:-1.9rem;top:1px;width:22px;height:22px;border-radius:50%;
                     background:<?= $bgc ?>;border:2px solid <?= $brc ?>;display:flex;align-items:center;justify-content:center;z-index:2;">
                  <i class="bi <?= $ic ?>" style="font-size:.62rem;color:<?= $clr ?>;"></i>
                </div>

                <div style="background:<?= $isLast ? 'var(--blue-50)' : 'var(--white)' ?>;
                     border:1px solid <?= $isLast ? 'var(--blue-200)' : 'var(--n-200)' ?>;
                     border-left:3px solid <?= $clr ?>;border-radius:8px;padding:.6rem .75rem;">

                  <!-- Baris judul -->
                  <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.3rem;">
                    <span style="font-size:.72rem;font-weight:700;color:<?= $clr ?>;display:flex;align-items:center;gap:.3rem;">
                      <span style="background:<?= $bgc ?>;border:1px solid <?= $brc ?>;border-radius:5px;padding:.05rem .4rem;font-size:.62rem;text-transform:uppercase;letter-spacing:.03em;">
                        <?= $label ?>
                      </span>
                    </span>
                    <span style="font-size:.66rem;color:var(--n-400);white-space:nowrap;">
                      <i class="bi bi-calendar3"></i> <?= date('d M Y', $t) ?>
                      &middot; <i class="bi bi-clock"></i> <?= date('H:i', $t) ?> WIB
                    </span>
                  </div>

                  <!-- Isi aktivitas -->
                  <div style="font-size:.8rem;color:var(--n-700);line-height:1.45;">
                    <?= esc($l['aktivitas'] ?? '-') ?>
                  </div>

                  <!-- Transisi status -->
                  <?php if (!empty($l['status_dari']) && !empty($l['status_ke']) && $l['status_dari'] !== $l['status_ke']): ?>
                    <div style="display:flex;align-items:center;gap:.4rem;margin-top:.4rem;flex-wrap:wrap;">
                      <span class="badge badge-draft" style="font-size:.63rem;padding:.1rem .45rem;"><?= esc($statusMap[(int)$l['status_dari']] ?? $l['status_dari']) ?></span>
                      <i class="bi bi-arrow-right" style="color:var(--n-400);font-size:.7rem;"></i>
                      <span class="badge <?= $badgeClass[(int)($l['status_ke'] ?? 0)] ?? 'badge-draft' ?>" style="font-size:.63rem;padding:.1rem .45rem;"><?= esc($statusMap[(int)($l['status_ke'] ?? 0)] ?? $l['status_ke']) ?></span>
                    </div>
                  <?php endif; ?>

                  <!-- Pelaku + IP -->
                  <div style="display:flex;align-items:center;gap:.75rem;margin-top:.45rem;padding-top:.4rem;border-top:1px dashed var(--n-200);font-size:.66rem;color:var(--n-400);flex-wrap:wrap;">
                    <span style="display:flex;align-items:center;gap:.25rem;">
                      <i class="bi bi-person-circle" style="color:<?= $clr ?>;"></i>
                      <strong style="color:var(--n-500);"><?= esc($pelaku) ?></strong>
                    </span>
                    <?php if (!empty($l['ip_address'])): ?>
                      <span style="display:flex;align-items:center;gap:.25rem;font-family:monospace;">
                        <i class="bi bi-hdd-network"></i> <?= esc($l['ip_address']) ?>
                      </span>
                    <?php endif; ?>
                    <span style="margin-left:auto;font-weight:600;color:var(--n-300);">#<?= count($logs) - $idx ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state" style="padding:2rem 1rem;text-align:center;">
            <div style="font-size:2rem;color:var(--n-200);margin-bottom:.5rem;"><i class="bi bi-clock-history"></i></div>
            <p style="color:var(--n-400);font-size:.84rem;margin:0;">Belum ada aktivitas tercatat untuk permohonan ini.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div>
    <?php if (!$isFinal && $st >= 1 && $st < 3): ?>
      <div class="card" style="margin-bottom:1rem;">
        <div class="card-header"><h3><i class="bi bi-arrow-repeat" style="color:var(--blue-500);"></i> Ubah Status</h3></div>
        <div class="card-body">
          <form id="formUpdateStatus">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label">Status Baru <span class="req">*</span></label>
              <select name="status" id="statusSelect" class="form-select" required>
                <option value="">— Pilih —</option>
                <?php foreach ($allowedNext as $val => $label): ?>
                  <option value="<?= $val ?>"><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" id="alasanWrap" style="display:none;">
              <label class="form-label">Alasan Penolakan <span class="req">*</span></label>
              <textarea name="alasan_penolakan" id="alasanInput" rows="3" class="form-control" placeholder="Wajib diisi jika menolak"></textarea>
              <div class="form-hint">Alasan ini akan otomatis dikirim sebagai pesan ke pemohon agar pemohon mengetahui alasan penolakan.</div>
            </div>
            <div class="form-group">
              <label class="form-label">Keterangan / Catatan Internal Admin (opsional)</label>
              <textarea name="keterangan" rows="2" class="form-control" placeholder="Hanya terlihat oleh tim admin internal"></textarea>
              <div class="form-hint">Catatan ini tersimpan di riwayat aktivitas internal dan tidak ditampilkan ke pemohon.</div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="bi bi-check-lg"></i> Simpan Status</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i><span>Status final. Tidak dapat diubah.</span></div>
    <?php endif; ?>

    <?php if ($st >= 1): ?>
      <div class="card" style="margin-bottom:1rem;">
        <div class="card-header"><h3><i class="bi bi-send" style="color:var(--blue-500);"></i> Kirim Pesan ke Pemohon</h3></div>
        <div class="card-body">
          <form id="formKirimPesan">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label">Jenis Pesan</label>
              <select name="tipe" id="tipePesan" class="form-select">
                <option value="umum">Umum</option>
                <option value="penolakan" <?= $st === 4 ? 'selected' : '' ?>>Penolakan</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Judul <span class="req">*</span></label>
              <input type="text" name="judul" id="judulPesan" class="form-control" maxlength="150"
                     placeholder="Contoh: Permohonan Ditolak - <?= esc($row['no_registrasi'] ?? '') ?>"
                     value="<?= $st === 4 && !empty($row['alasan_penolakan']) ? 'Permohonan Ditolak - ' . esc($row['no_registrasi'] ?? '') : '' ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Isi Pesan <span class="req">*</span></label>
              <textarea name="isi" id="isiPesan" rows="5" class="form-control"
                        placeholder="Tulis pesan untuk pemohon..." required><?= $st === 4 && !empty($row['alasan_penolakan']) ? "Yth. " . esc($row['nama_pemohon']) . ",&#10;&#10;Permohonan Anda dengan nomor registrasi " . esc($row['no_registrasi'] ?? '') . " telah DITOLAK.&#10;&#10;Alasan penolakan:&#10;" . esc($row['alasan_penolakan']) : '' ?></textarea>
              <div class="form-hint">Pesan akan tampil di halaman detail permohonan pemohon.</div>
            </div>
            <?php if ($st === 4 && !empty($row['alasan_penolakan'])): ?>
              <div class="form-hint" style="margin-bottom:.6rem;color:var(--red-600);">
                <i class="bi bi-info-circle-fill"></i> Alasan penolakan sudah terisi otomatis. Klik kirim untuk mengirimkannya ke pemohon.
              </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="bi bi-send"></i> Kirim Pesan</button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($st >= 2 && $st !== 4): ?>
    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header"><h3><i class="bi bi-upload" style="color:var(--blue-500);"></i> Upload Jawaban</h3></div>
      <div class="card-body">
        <form id="formUploadJawaban" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="form-group">
            <input type="file" name="file_jawaban" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="form-hint">PDF/JPG/PNG, maks 10MB.</div>
          </div>
          <button type="submit" class="btn btn-outline" style="width:100%;"><i class="bi bi-upload"></i> Upload Jawaban</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <?php if ((int)$row['status'] >= 1): ?>
    <div class="card">
      <div class="card-header"><h3><i class="bi bi-info-circle" style="color:var(--blue-500);"></i> Informasi SLA</h3></div>
      <div class="card-body" style="font-size:.82rem;">
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid var(--n-100);">
          <span style="color:var(--n-400);">Tanggal Kirim</span>
          <span style="font-weight:600;"><?= date('d M Y', strtotime($row['submitted_at'])) ?></span>
        </div>
          <div style="display:flex;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid var(--n-100);">
            <span style="color:var(--n-400);">Batas Waktu (10 hari kerja)</span>
            <span style="font-weight:600;"><?= !empty($row['sla_deadline']) ? date('d M Y', strtotime($row['sla_deadline'])) : '-' ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid var(--n-100);">
            <span style="color:var(--n-400);">Hari Kerja Digunakan</span>
            <span style="font-weight:600;<?= ($usedKerja ?? 0) >= ($totalKerja ?? 0) ? 'color:var(--red-600);' : '' ?>">
              <?= ($usedKerja ?? 0) ?> dari <?= ($totalKerja ?? 10) ?> hari
            </span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:.3rem 0;">
            <span style="color:var(--n-400);">Sisa Hari Kerja</span>
            <span style="font-weight:600;<?= $isOverdue ? 'color:var(--red-600);' : (($sisaKerja ?? 0) <= 2 ? 'color:var(--yellow-600);' : 'color:var(--green-600);') ?>">
              <?= $isOverdue ? 'Lewat ' . abs($sisaKerja ?? 0) . ' hari' : (($sisaKerja ?? 0) . ' hari') ?>
            </span>
          </div>
          <div style="margin-top:.6rem;padding:.5rem .6rem;background:var(--blue-50);border:1px dashed var(--blue-200);border-radius:6px;font-size:.7rem;color:var(--blue-600);display:flex;gap:.4rem;align-items:flex-start;">
            <i class="bi bi-calendar-week" style="flex-shrink:0;margin-top:.1rem;"></i>
            <span>Perhitungan hanya <strong>Senin–Jumat</strong>. Sabtu & Minggu tidak dihitung sebagai hari kerja.</span>
          </div>
        </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const statusSelect = document.getElementById('statusSelect');
  const alasanWrap = document.getElementById('alasanWrap');
  if (statusSelect) {
    statusSelect.addEventListener('change', function() {
      alasanWrap.style.display = this.value === '4' ? 'block' : 'none';
      document.getElementById('alasanInput').required = (this.value === '4');
    });
  }

  const formUpdate = document.getElementById('formUpdateStatus');
  if (formUpdate) {
    formUpdate.addEventListener('submit', async function(e) {
      e.preventDefault();
      const fd = new FormData(this);
      const status = fd.get('status');
      if (!status) { showToast('Pilih status baru.', 'error'); return; }
      if (status === '<?= $st ?>') { showToast('Status sudah sama, pilih status lain.', 'error'); return; }
      if (status === '4' && !fd.get('alasan_penolakan')) { showToast('Alasan penolakan wajib diisi.', 'error'); return; }

      const btn = this.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

      try {
        const res = await fetch('<?= site_url("admin/permohonan/update-status/" . $row["id"]) ?>', {
          method: 'POST', body: fd,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const j = await res.json();
        if (j.status) {
          showToast('Status berhasil diperbarui!');
          setTimeout(() => { window.location.replace(window.location.href); }, 500);
        } else {
          showToast(j.message || 'Gagal', 'error');
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan Status';
        }
      } catch(err) {
        showToast('Kesalahan jaringan', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan Status';
      }
    });
  }

  const formPesan = document.getElementById('formKirimPesan');
  if (formPesan) {
    formPesan.addEventListener('submit', async function(e) {
      e.preventDefault();
      const fd = new FormData(this);
      if (!fd.get('judul') || !fd.get('isi')) { showToast('Judul dan isi pesan wajib diisi.', 'error'); return; }

      const btn = this.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';

      try {
        const res = await fetch('<?= site_url("admin/permohonan/kirim-pesan/" . $row["id"]) ?>', {
          method: 'POST', body: fd,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const j = await res.json();
        if (j.status) {
          showToast('Pesan terkirim ke pemohon!');
          setTimeout(() => { window.location.replace(window.location.href); }, 500);
        } else {
          showToast(j.message || 'Gagal mengirim pesan', 'error');
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-send"></i> Kirim Pesan';
        }
      } catch(err) {
        showToast('Kesalahan jaringan', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send"></i> Kirim Pesan';
      }
    });
  }

  const formUpload = document.getElementById('formUploadJawaban');
  if (formUpload) {
    formUpload.addEventListener('submit', async function(e) {
      e.preventDefault();
      const fd = new FormData(this);

      const btn = this.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengunggah...';

      try {
        const res = await fetch('<?= site_url("admin/permohonan/upload-jawaban/" . $row["id"]) ?>', {
          method: 'POST', body: fd,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const j = await res.json();
        if (j.status) {
          showToast('Jawaban berhasil diunggah!');
          setTimeout(() => { window.location.replace(window.location.href); }, 500);
        } else {
          showToast(j.message || 'Gagal upload', 'error');
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-upload"></i> Upload Jawaban';
        }
      } catch(err) {
        showToast('Kesalahan jaringan', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-upload"></i> Upload Jawaban';
      }
    });
  }
});
</script>
<?= $this->endSection() ?>
