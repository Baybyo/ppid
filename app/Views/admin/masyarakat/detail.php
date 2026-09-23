<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php $isActive = (int) $user['is_active'] === 1; ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
  <div>
    <h1><i class="bi bi-person-gear" style="color:var(--blue-600);"></i> Kelola Akun Masyarakat</h1>
    <p><?= esc($user['nama']) ?></p>
  </div>
  <div style="display:flex;gap:.5rem;align-items:center;">
    <span class="badge <?= $isActive ? 'badge-selesai' : 'badge-ditolak' ?>" style="font-size:.82rem;padding:.4rem .85rem;">
      <?= $isActive ? 'Status: Aktif' : 'Status: Nonaktif' ?>
    </span>
    <a href="<?= site_url('admin/masyarakat') ?>" class="btn btn-sm btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;align-items:start;">
  <!-- Kolom Kiri: Form Edit Data & Password -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3><i class="bi bi-pencil-square" style="color:var(--blue-500);"></i> Edit Data & Kredensial</h3>
      </div>
      <div class="card-body">
        <form action="<?= site_url('admin/masyarakat/update/' . $user['id']) ?>" method="POST">
          <?= csrf_field() ?>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
            <div class="form-group">
              <label class="form-label">Nama Lengkap <span class="req">*</span></label>
              <input type="text" name="nama" class="form-control" required value="<?= esc(old('nama', $user['nama'])) ?>" placeholder="Nama lengkap">
            </div>

            <div class="form-group">
              <label class="form-label">No. Handphone (Login) <span class="req">*</span></label>
              <input type="text" name="no_hp" class="form-control" required value="<?= esc(old('no_hp', $user['no_hp'])) ?>" placeholder="08xxxxxxxxxx">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
            <div class="form-group">
              <label class="form-label">Alamat Email</label>
              <input type="email" name="email" class="form-control" value="<?= esc(old('email', $user['email'] ?? '')) ?>" placeholder="nama@email.com">
            </div>

            <div class="form-group">
              <label class="form-label">No. Identitas (KTP/Paspor/SIM)</label>
              <input type="text" name="nisn" class="form-control" value="<?= esc(old('nisn', $user['nisn'] ?? '')) ?>" placeholder="Nomor KTP / Paspor / SIM">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Status Akun</label>
            <select name="is_active" class="form-select">
              <option value="1" <?= old('is_active', $user['is_active']) == 1 ? 'selected' : '' ?>>Aktif (Dapat Login & Mengajukan)</option>
              <option value="0" <?= old('is_active', $user['is_active']) == 0 ? 'selected' : '' ?>>Nonaktif (Akses Diblokir)</option>
            </select>
          </div>

          <div class="section-divider"></div>

          <h4 style="font-size:.9rem;font-weight:700;color:var(--n-800);margin-bottom:.35rem;">
            <i class="bi bi-key-fill" style="color:var(--yellow-600);"></i> Reset Password (Opsional)
          </h4>
          <p style="font-size:.78rem;color:var(--n-400);margin-bottom:.85rem;">Kosongkan bila tidak ingin mengganti password user ini.</p>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <div class="input-password-wrapper">
                <input type="password" name="password_baru" id="password_baru" class="form-control" minlength="8" placeholder="Minimal 8 karakter">
                <button type="button" class="btn-toggle-pwd" onclick="togglePassword('password_baru', this)"><i class="bi bi-eye"></i></button>
              </div>
              <span style="font-size:11px;color:var(--n-400);">Huruf besar, kecil & angka</span>
            </div>

            <div class="form-group">
              <label class="form-label">Ulangi Password Baru</label>
              <div class="input-password-wrapper">
                <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="Ketik ulang password">
                <button type="button" class="btn-toggle-pwd" onclick="togglePassword('konfirmasi_password', this)"><i class="bi bi-eye"></i></button>
              </div>
            </div>
          </div>

          <div style="margin-top:1.25rem;display:flex;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Kolom Kanan: Status & Quick Actions -->
  <div>
    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header">
        <h3><i class="bi bi-clock-history" style="color:var(--blue-500);"></i> Info Sesi & Akun</h3>
      </div>
      <div class="card-body" style="font-size:.84rem;">
        <div style="display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--n-100);">
          <span style="color:var(--n-400);">ID User</span>
          <span style="font-weight:600;font-family:monospace;">#<?= $user['id'] ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--n-100);">
          <span style="color:var(--n-400);">Terdaftar</span>
          <span style="font-weight:600;"><?= !empty($user['created_at']) ? date('d M Y H:i', strtotime($user['created_at'])) : '-' ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.45rem 0;">
          <span style="color:var(--n-400);">Terakhir Diperbarui</span>
          <span style="font-weight:600;"><?= !empty($user['updated_at']) ? date('d M Y H:i', strtotime($user['updated_at'])) : '-' ?></span>
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom:1rem;">
      <div class="card-header">
        <h3><i class="bi bi-lightning-charge" style="color:var(--blue-500);"></i> Tindakan Cepat</h3>
      </div>
      <div class="card-body">
        <button onclick="toggleStatus()" class="btn <?= $isActive ? 'btn-outline' : 'btn-success' ?>" style="width:100%;margin-bottom:.65rem;" id="btnToggle">
          <i class="bi bi-<?= $isActive ? 'lock' : 'unlock' ?>"></i> <?= $isActive ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>
        </button>
      </div>
    </div>

    <div class="card" style="border:1px solid var(--red-200);background:var(--red-50);">
      <div class="card-header" style="border-bottom-color:var(--red-200);">
        <h3 style="color:var(--red-600);"><i class="bi bi-exclamation-triangle"></i> Hapus Akun</h3>
      </div>
      <div class="card-body" style="font-size:.82rem;">
        <p style="color:var(--n-600);margin-bottom:.75rem;">
          Menghapus akun ini akan menghapus data login masyarakat secara permanen. Riwayat permohonan yang pernah dibuat tetap tersimpan sebagai arsip.
        </p>
        <form action="<?= site_url('admin/masyarakat/delete/' . $user['id']) ?>" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun masyarakat ini secara permanen? Tindakan ini tidak dapat dibatalkan.');">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger" style="width:100%;">
            <i class="bi bi-trash3-fill"></i> Hapus Akun Permanen
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon = btn.querySelector('i');
  if (!input || !icon) return;
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}

async function toggleStatus() {
  if (!confirm('Yakin ingin mengubah status akun ini?')) return;
  const btn = document.getElementById('btnToggle');
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

  try {
    const fd = new FormData();
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    const res = await fetch('<?= site_url('admin/masyarakat/toggle-status/' . $user['id']) ?>', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const j = await res.json();
    if (j.status) {
      showToast(j.message);
      setTimeout(() => { window.location.reload(); }, 400);
    } else {
      showToast(j.message || 'Gagal', 'error');
      btn.disabled = false;
      btn.innerHTML = origHtml;
    }
  } catch(e) {
    showToast('Kesalahan jaringan', 'error');
    btn.disabled = false;
    btn.innerHTML = origHtml;
  }
}
</script>
<?= $this->endSection() ?>