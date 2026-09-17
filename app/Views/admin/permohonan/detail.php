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
  $diff = (int) ceil((strtotime($row['sla_deadline']) - time()) / 86400);
  $daysLeft = $diff < 0 ? abs($diff) . ' hari lalu' : $diff . ' hari lagi';
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
    <span>Permohonan ini telah melebihi batas waktu SLA! Deadline: <?= date('d M Y', strtotime($row['sla_deadline'])) ?> (<?= $daysLeft ?>).</span>
  </div>
<?php endif; ?>

<?php
$tahapanNames = ['Diterima', 'Verifikasi', 'Diproses', 'Selesai'];
$tahapanIcons = ['bi-inbox', 'bi-check2-square', 'bi-gear', 'bi-check-circle'];
$tahapanStatus = [];
foreach ($tahapan as $t) {
  $tahapanStatus[$t['tahap']] = $t['status'];
}
?>

<div class="card" style="margin-bottom:1rem;">
  <div class="card-header"><h3><i class="bi bi-kanban" style="color:var(--blue-500);"></i> Tahapan Proses</h3></div>
  <div class="card-body">
    <div style="display:flex;gap:0;position:relative;">
      <?php foreach ($tahapanNames as $i => $tn):
        $ts = $tahapanStatus[$tn] ?? 'Menunggu';
        $isActive = $ts === 'Dalam Proses';
        $isDone = $ts === 'Selesai';
      ?>
        <div style="flex:1;text-align:center;position:relative;z-index:1;">
          <div style="width:42px;height:42px;border-radius:50;margin:0 auto .4rem;display:flex;align-items:center;justify-content:center;font-size:1rem;
            background:<?= $isDone ? 'var(--green-600)' : ($isActive ? 'var(--blue-600)' : 'var(--n-200)') ?>;
            color:<?= ($isDone || $isActive) ? '#fff' : 'var(--n-400)' ?>;border-radius:50%;">
            <i class="bi <?= $isDone ? 'bi-check-lg' : $tahapanIcons[$i] ?>"></i>
          </div>
          <div style="font-size:.75rem;font-weight:600;color:<?= $isDone ? 'var(--green-600)' : ($isActive ? 'var(--blue-600)' : 'var(--n-400)') ?>;"><?= $tn ?></div>
          <div style="font-size:.65rem;color:var(--n-400);"><?= $ts ?></div>
        </div>
        <?php if ($i < count($tahapanNames) - 1): ?>
          <div style="flex:0 0 auto;width:40px;height:2px;margin-top:20px;background:<?= $isDone ? 'var(--green-600)' : 'var(--n-200)' ?>;"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
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
      <div class="card-header"><h3><i class="bi bi-clock-history" style="color:var(--blue-500);"></i> Riwayat Log</h3></div>
      <div class="card-body">
        <?php if (!empty($logs)): ?>
          <div class="timeline">
            <?php foreach ($logs as $l): ?>
              <div class="timeline-item done">
                <div class="timeline-dot"></div>
                <div class="timeline-time"><?= esc($l['created_at']) ?></div>
                <div class="timeline-text">
                  <?= esc($l['aktivitas'] ?? '-') ?>
                  <?php if (!empty($l['status_dari']) || !empty($l['status_ke'])): ?>
                    <br><span style="color:var(--n-400);font-size:.75rem;"><?= esc($l['status_dari'] ?? '-') ?> &rarr; <strong><?= esc($l['status_ke'] ?? '-') ?></strong></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="empty-state">Belum ada riwayat.</p>
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
            </div>
            <div class="form-group">
              <label class="form-label">Catatan (opsional)</label>
              <textarea name="keterangan" rows="2" class="form-control" placeholder="Catatan internal"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="bi bi-check-lg"></i> Simpan Status</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i><span>Status final. Tidak dapat diubah.</span></div>
    <?php endif; ?>

    <?php if ($st >= 2): ?>
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
          <span style="color:var(--n-400);">Deadline SLA</span>
          <span style="font-weight:600;<?= $isOverdue ? 'color:var(--red-600);' : '' ?>"><?= !empty($row['sla_deadline']) ? date('d M Y', strtotime($row['sla_deadline'])) : '-' ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;">
          <span style="color:var(--n-400);">Sisa Waktu</span>
          <span style="font-weight:600;<?= $isOverdue ? 'color:var(--red-600);' : ($diff <= 2 ? 'color:var(--yellow-600);' : '') ?>"><?= $daysLeft ?: '-' ?></span>
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
