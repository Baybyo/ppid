<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<div style="max-width:640px;margin:0 auto;">
  <div class="page-header">
    <h1>Lacak Status Permohonan</h1>
    <p>Masukkan nomor registrasi untuk mengecek status. Tidak perlu login.</p>
  </div>

  <div class="card" style="margin-bottom:1.25rem;">
    <div class="card-body">
      <form id="formTracking">
        <?= csrf_field() ?>
        <div style="display:flex;gap:.5rem;">
          <input type="text" id="noRegistrasi" name="no_registrasi" class="form-control" style="font-family:monospace;font-weight:600;" placeholder="PPID-001,11,09,2026" required>
          <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
        </div>
      </form>
    </div>
  </div>

  <div id="hasil"></div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const STATUS_MAP = {0:'Draft',1:'Menunggu Verifikasi',2:'Diproses',3:'Selesai',4:'Ditolak'};
const STATUS_BADGE = {0:'badge-draft',1:'badge-menunggu',2:'badge-proses',3:'badge-selesai',4:'badge-ditolak'};

document.getElementById('formTracking').addEventListener('submit', async (e) => {
  e.preventDefault();
  const box = document.getElementById('hasil');
  box.innerHTML = '<div style="text-align:center;padding:2rem;"><div class="spinner-border" style="border-color:var(--blue-200);border-right-color:var(--blue-600);"></div></div>';

  const fd = new FormData(e.target);
  try {
    const res = await fetch('<?= site_url("permohonan/check-status") ?>', {
      method: 'POST', body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const j = await res.json();

    if (!j.found) {
      box.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i><span>' + j.message + '</span></div>';
      return;
    }

    const status = parseInt(j.row.status);
    const badge = '<span class="badge ' + (STATUS_BADGE[status]||'badge-draft') + '">' + (STATUS_MAP[status]||status) + '</span>';

    let logs = '';
    if (j.logs && j.logs.length > 0) {
      logs = '<div style="margin-top:1rem;"><h3 style="font-size:.85rem;font-weight:700;margin-bottom:.5rem;">Riwayat</h3><div class="timeline">';
      j.logs.forEach(l => {
        const dari = STATUS_MAP[l.status_dari] || l.status_dari || '-';
        const ke = STATUS_MAP[l.status_ke] || l.status_ke || '-';
        logs += '<div class="timeline-item done"><div class="timeline-dot"></div><div class="timeline-time">' + l.created_at + '</div><div class="timeline-text">' + dari + ' &rarr; <strong>' + ke + '</strong>';
        if (l.aktivitas) logs += '<br><span style="color:var(--n-400);font-size:.78rem;">' + l.aktivitas + '</span>';
        logs += '</div></div>';
      });
      logs += '</div></div>';
    }

    let alasan = '';
    if (status === 4 && j.row.alasan_penolakan) {
      alasan = '<div class="alert alert-danger" style="margin-top:1rem;"><i class="bi bi-x-circle-fill"></i><span><strong>Alasan Penolakan:</strong> ' + j.row.alasan_penolakan + '</span></div>';
    }

    box.innerHTML = '<div class="ticket-box">' +
      '<div class="ticket-header">' +
        '<div style="font-size:.72rem;opacity:.8;">Nomor Registrasi</div>' +
        '<div class="ticket-no">' + j.row.no_registrasi + '</div>' +
      '</div>' +
      '<div class="ticket-body">' +
        '<div class="ticket-row"><div class="ticket-label">Nama Pemohon</div><div class="ticket-value">' + j.row.nama_pemohon + '</div></div>' +
        '<div class="ticket-row"><div class="ticket-label">NIK</div><div class="ticket-value" style="font-family:monospace;">' + j.row.nik_masked + '</div></div>' +
        '<div class="ticket-row"><div class="ticket-label">Status</div><div class="ticket-value">' + badge + '</div></div>' +
        '<div class="ticket-row"><div class="ticket-label">Waktu Kirim</div><div class="ticket-value">' + (j.row.submitted_at || '-') + '</div></div>' +
        '<div class="ticket-row" style="border:none;"><div class="ticket-label">Rincian</div><div class="ticket-value">' + j.row.rincian_informasi + '</div></div>' +
      '</div>' +
    '</div>' + alasan + logs;
  } catch(e) {
    box.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i><span>Terjadi kesalahan jaringan.</span></div>';
  }
});
</script>
<?= $this->endSection() ?>
