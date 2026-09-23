<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bukti Permohonan <?= esc($row['no_registrasi'] ?? '') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=6.0') ?>">
  <style>
    body { background: var(--n-100); padding: 1.5rem; }
    .print-container { max-width: 680px; margin: 0 auto; }
    @media print {
      body { background: #fff; padding: 0; }
      .no-print { display: none !important; }
      .card { box-shadow: none; border: 1px solid #e2e6ec; }
    }
  </style>
</head>
<body>
<div class="print-container">
  <div class="card" style="overflow:hidden;">
    <div style="background:linear-gradient(135deg,var(--blue-700),var(--blue-500));color:#fff;padding:1.5rem;text-align:center;">
      <h1 style="font-size:1rem;font-weight:800;letter-spacing:.03em;margin:0;">BUKTI PERMOHONAN INFORMASI PUBLIK</h1>
      <p style="font-size:.76rem;color:#a8cce8;margin:.3rem 0 0;">PPID — Dinas Tenaga Kerja Provinsi Jawa Timur</p>
    </div>
    <div class="card-body">
      <?php
      $statusMap = [0=>'Draft',1=>'Menunggu Verifikasi',2=>'Diproses',3=>'Selesai',4=>'Ditolak'];
      $st = (int) $row['status'];
      $nik = $row['no_identitas'] ?? '';
      $nikMasked = strlen($nik) > 4 ? str_repeat('*', strlen($nik) - 4) . substr($nik, -4) : '****';
      ?>
      <div class="success-no" style="text-align:center;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:2px solid var(--blue-100);"><?= esc($row['no_registrasi'] ?? '-') ?></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem;font-size:.84rem;">
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Nama Pemohon</strong><?= esc($row['nama_pemohon']) ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Jenis Identitas</strong><?= esc($row['jenis_identitas']) ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">No. Identitas</strong><span style="font-family:monospace;"><?= esc($nikMasked) ?></span></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Pekerjaan</strong><?= esc($row['pekerjaan']) ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">No. Telepon</strong><?= esc($row['no_telp']) ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Email</strong><?= esc($row['email']) ?></div>
        <div style="grid-column:1/-1;"><strong style="color:var(--n-400);font-size:.75rem;display:block;">Alamat</strong><?= nl2br(esc($row['alamat'])) ?></div>
        <div style="grid-column:1/-1;border-top:1px solid var(--n-100);padding-top:.75rem;margin-top:.25rem;"><strong style="color:var(--n-400);font-size:.75rem;display:block;">Rincian Informasi</strong><?= nl2br(esc($row['rincian_informasi'])) ?></div>
        <div style="grid-column:1/-1;"><strong style="color:var(--n-400);font-size:.75rem;display:block;">Tujuan Penggunaan</strong><?= nl2br(esc($row['tujuan_penggunaan'])) ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Cara Memperoleh</strong><?php $cm = json_decode($row['cara_memperoleh'] ?? '[]', true) ?: []; echo esc(implode(', ', $cm)); ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Cara Salinan</strong><?php $cs = json_decode($row['cara_salinan'] ?? '[]', true) ?: []; echo esc(implode(', ', $cs)); if (!empty($row['cara_salinan_lainnya'])) echo ' — ' . esc($row['cara_salinan_lainnya']); ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Waktu Pengajuan</strong><?= esc($row['submitted_at'] ?? $row['created_at']) ?></div>
        <div><strong style="color:var(--n-400);font-size:.75rem;display:block;">Status</strong><span class="badge badge-<?= $st == 1 ? 'menunggu' : ($st == 2 ? 'proses' : ($st == 3 ? 'selesai' : ($st == 4 ? 'ditolak' : 'draft'))) ?>" style="margin-top:.2rem;"><?= $statusMap[$st] ?? $st ?></span></div>
      </div>
    </div>
    <div style="text-align:center;padding:1rem;border-top:1px solid var(--n-100);color:var(--n-400);font-size:.75rem;">
      <p style="margin:0;">Dokumen ini dicetak sistem sebagai bukti pengajuan permohonan informasi publik.</p>
      <p style="margin:.25rem 0 0;">Nomor registrasi bersifat unik dan tidak dapat diganti.</p>
    </div>
  </div>
  <div class="no-print" style="text-align:center;padding:1rem;">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Cetak / PDF</button>
    <a href="<?= site_url('permohonan/riwayat') ?>" class="btn btn-outline" style="margin-left:.5rem;">Kembali</a>
  </div>
</div>
</body>
</html>
