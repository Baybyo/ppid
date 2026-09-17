<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<div style="max-width:560px;margin:0 auto;">
  <div class="card">
    <div class="card-body" style="text-align:center;padding:2.5rem 2rem;">
      <div class="success-icon"><i class="bi bi-check-lg"></i></div>
      <h1 style="font-size:1.15rem;font-weight:700;margin:0 0 .5rem;">Permohonan Berhasil Dikirim</h1>
      <p style="font-size:.84rem;color:var(--n-400);margin:0 0 1.5rem;">Nomor registrasi Anda tercatat di sistem. Simpan nomor ini untuk pelacakan status.</p>

      <div class="ticket-box" style="text-align:left;margin-bottom:1.5rem;">
        <div class="ticket-header">
          <div style="font-size:.72rem;opacity:.8;">Nomor Registrasi</div>
          <div style="display:flex;align-items:center;gap:.5rem;">
            <div class="ticket-no"><?= esc($row['no_registrasi']) ?></div>
            <button type="button" class="btn btn-sm btn-outline" style="padding:.2rem .5rem;font-size:.72rem;" onclick="copyToClipboard('<?= esc($row['no_registrasi']) ?>')" title="Salin Nomor"><i class="bi bi-clipboard"></i></button>
          </div>
        </div>
        <div class="ticket-body">
          <div class="ticket-row">
            <div class="ticket-label">Waktu Kirim</div>
            <div class="ticket-value"><?= esc($row['submitted_at'] ?? $row['created_at']) ?></div>
          </div>
          <div class="ticket-row">
            <div class="ticket-label">Status</div>
            <div class="ticket-value"><span class="badge badge-menunggu">Menunggu Verifikasi</span></div>
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:center;gap:.5rem;flex-wrap:wrap;">
        <a href="<?= site_url('permohonan/download-bukti/' . $row['id']) ?>" class="btn btn-primary">
          <i class="bi bi-file-earmark-pdf"></i> Unduh PDF
        </a>
        <a href="<?= site_url('permohonan/cetak-bukti/' . $row['id']) ?>" class="btn btn-outline">
          <i class="bi bi-printer"></i> Cetak
        </a>
        <a href="<?= site_url('permohonan/tracking') ?>" class="btn btn-outline">
          <i class="bi bi-search"></i> Lacak Status
        </a>
        <a href="<?= site_url('permohonan/riwayat') ?>" class="btn btn-outline">
          <i class="bi bi-clock-history"></i> Riwayat
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
