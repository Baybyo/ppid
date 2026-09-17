<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<div class="page-header">
  <h1 class="page-title"><i class="bi bi-person-gear"></i> Profil Saya</h1>
  <p class="page-subtitle">Kelola data akun dan keamanan akun Anda</p>
</div>

<div class="profile-grid">
  <!-- Kolom Kiri: Info Akun -->
  <div class="profile-sidebar">
    <div class="profile-card">
      <div class="profile-avatar-lg">
        <?= esc(strtoupper(substr($user['nama'] ?? 'U', 0, 2))) ?>
      </div>
      <h3 class="profile-name"><?= esc($user['nama'] ?? '-') ?></h3>
      <p class="profile-email"><?= esc($user['email'] ?? 'Belum ada email') ?></p>
      <div class="profile-badge">
        <i class="bi bi-shield-check"></i> Akun Terverifikasi
      </div>
      <div class="profile-meta">
        <div class="meta-item">
          <i class="bi bi-calendar-check"></i>
          <span>Bergabung: <?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?></span>
        </div>
        <div class="meta-item">
          <i class="bi bi-phone"></i>
          <span><?= esc($user['no_hp'] ?? '-') ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Kolom Kanan: Form Edit -->
  <div class="profile-main">
    <!-- Tab Navigation -->
    <div class="profile-tabs">
      <button class="profile-tab active" onclick="switchTab('profil')">
        <i class="bi bi-person"></i> Data Diri
      </button>
      <button class="profile-tab" onclick="switchTab('password')">
        <i class="bi bi-lock"></i> Keamanan
      </button>
    </div>

    <!-- Tab: Data Diri -->
    <div class="tab-content active" id="tab-profil">
      <div class="profile-section">
        <div class="section-header">
          <h4><i class="bi bi-person-lines-fill"></i> Informasi Pribadi</h4>
          <p>Perbarui data diri Anda yang terdaftar di sistem</p>
        </div>
        <form action="<?= site_url('profil/update') ?>" method="post" class="profile-form">
          <?= csrf_field() ?>
          <div class="form-row">
            <div class="form-group">
              <label for="nama">Nama Lengkap <span class="required">*</span></label>
              <div class="input-icon">
                <i class="bi bi-person"></i>
                <input type="text" name="nama" id="nama" value="<?= esc($user['nama'] ?? '') ?>" required minlength="3" placeholder="Masukkan nama lengkap">
              </div>
            </div>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label for="email">Alamat Email</label>
              <div class="input-icon">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" id="email" value="<?= esc($user['email'] ?? '') ?>" placeholder="email@contoh.com">
              </div>
              <span class="form-hint">Digunakan untuk notifikasi informasi</span>
            </div>
            <div class="form-group">
              <label for="no_hp">Nomor Telepon <span class="required">*</span></label>
              <div class="input-icon">
                <i class="bi bi-phone"></i>
                <input type="tel" name="no_hp" id="no_hp" value="<?= esc($user['no_hp'] ?? '') ?>" required placeholder="08xxxxxxxxxx">
              </div>
              <span class="form-hint">Digunakan untuk login</span>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab: Keamanan -->
    <div class="tab-content" id="tab-password">
      <div class="profile-section">
        <div class="section-header">
          <h4><i class="bi bi-shield-lock"></i> Ganti Password</h4>
          <p>Gunakan minimal 6 karakter untuk keamanan akun</p>
        </div>
        <form action="<?= site_url('profil/change-password') ?>" method="post" class="profile-form">
          <?= csrf_field() ?>
          <div class="form-row">
            <div class="form-group">
              <label for="password_lama">Password Saat Ini <span class="required">*</span></label>
              <div class="input-icon">
                <i class="bi bi-lock"></i>
                <input type="password" name="password_lama" id="password_lama" required placeholder="Masukkan password saat ini">
              </div>
            </div>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label for="password_baru">Password Baru <span class="required">*</span></label>
              <div class="input-icon">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password_baru" id="password_baru" required minlength="6" placeholder="Minimal 6 karakter">
              </div>
            </div>
            <div class="form-group">
              <label for="konfirmasi_password">Konfirmasi Password <span class="required">*</span></label>
              <div class="input-icon">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="konfirmasi_password" id="konfirmasi_password" required minlength="6" placeholder="Ulangi password baru">
              </div>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin mengganti password?')">
              <i class="bi bi-shield-check"></i> Perbarui Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .page-header { margin-bottom: 1.5rem; }
  .page-title { font-size: 1.25rem; font-weight: 700; color: var(--n-900); display: flex; align-items: center; gap: .5rem; }
  .page-title i { color: var(--blue-600); }
  .page-subtitle { font-size: .82rem; color: var(--n-400); margin-top: .2rem; }

  .profile-grid { display: grid; grid-template-columns: 280px 1fr; gap: 1.25rem; align-items: start; }

  /* ── Profile Sidebar ── */
  .profile-card {
    background: var(--white);
    border: 1px solid var(--n-200);
    border-radius: var(--r);
    padding: 1.75rem 1.25rem;
    text-align: center;
    box-shadow: var(--shadow-sm);
  }

  .profile-avatar-lg {
    width: 80px; height: 80px; border-radius: 50%;
    background: var(--blue-600); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 800; margin: 0 auto .75rem;
    box-shadow: 0 0 0 4px var(--blue-100);
  }

  .profile-name { font-size: 1.05rem; font-weight: 700; color: var(--n-900); margin: 0 0 .2rem; }
  .profile-email { font-size: .78rem; color: var(--n-400); margin: 0 0 .75rem; word-break: break-all; }

  .profile-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    background: var(--green-100); color: var(--green-700);
    padding: .3rem .7rem; border-radius: 20px;
    font-size: .7rem; font-weight: 600;
  }

  .profile-meta { margin-top: 1rem; border-top: 1px solid var(--n-100); padding-top: .75rem; }
  .meta-item { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: var(--n-500); padding: .25rem 0; }
  .meta-item i { color: var(--n-400); width: 16px; text-align: center; }

  /* ── Tabs ── */
  .profile-tabs {
    display: flex; gap: 0; border-bottom: 2px solid var(--n-100);
    margin-bottom: 1.25rem;
  }

  .profile-tab {
    padding: .65rem 1.25rem; font-size: .82rem; font-weight: 600;
    color: var(--n-400); background: none; border: none;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    cursor: pointer; transition: all .15s var(--ease);
    display: flex; align-items: center; gap: .4rem;
  }

  .profile-tab:hover { color: var(--n-600); }
  .profile-tab.active { color: var(--blue-600); border-bottom-color: var(--blue-600); }

  .tab-content { display: none; }
  .tab-content.active { display: block; }

  /* ── Profile Section ── */
  .profile-section {
    background: var(--white);
    border: 1px solid var(--n-200);
    border-radius: var(--r);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
  }

  .section-header { margin-bottom: 1.25rem; padding-bottom: .75rem; border-bottom: 1px solid var(--n-100); }
  .section-header h4 { font-size: .95rem; font-weight: 700; color: var(--n-800); margin: 0; display: flex; align-items: center; gap: .4rem; }
  .section-header h4 i { color: var(--blue-500); }
  .section-header p { font-size: .78rem; color: var(--n-400); margin: .25rem 0 0; }

  .profile-form .form-row { display: grid; gap: 1rem; }
  .profile-form .form-row-2 { grid-template-columns: 1fr 1fr; }

  .profile-form label {
    display: block; font-size: .78rem; font-weight: 600;
    color: var(--n-600); margin-bottom: .35rem;
  }

  .required { color: var(--red-500); }

  .input-icon {
    position: relative; display: flex; align-items: center;
  }

  .input-icon i {
    position: absolute; left: .75rem; color: var(--n-400);
    font-size: .9rem; pointer-events: none;
  }

  .input-icon input {
    width: 100%; padding: .55rem .75rem .55rem 2.25rem;
    border: 1px solid var(--n-200); border-radius: 8px;
    font-size: .82rem; color: var(--n-800);
    transition: border-color .15s, box-shadow .15s;
    background: var(--white);
  }

  .input-icon input:focus {
    outline: none; border-color: var(--blue-400);
    box-shadow: 0 0 0 3px var(--blue-100);
  }

  .input-icon input::placeholder { color: var(--n-300); }

  .form-hint { display: block; font-size: .7rem; color: var(--n-400); margin-top: .25rem; }

  .form-actions { margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--n-100); }

  .profile-form .btn { padding: .55rem 1.5rem; }

  @media (max-width: 768px) {
    .profile-grid { grid-template-columns: 1fr; }
    .profile-form .form-row-2 { grid-template-columns: 1fr; }
  }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function switchTab(tab) {
  document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

  document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
  document.getElementById(`tab-${tab}`).classList.add('active');
}
</script>
<?= $this->endSection() ?>
