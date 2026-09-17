<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun — PPID Dinas Tenaga Kerja Prov. Jawa Timur</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=9.0') ?>">
</head>
<body>
  <div class="login-page">
    <div class="login-card" style="max-width:460px;">
      <div class="login-header">
        <div class="login-brand">
          <img src="<?= base_url('assets/images/logo-jatim.png') ?>" alt="Logo Jatim" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="login-brand-fallback" style="display:none;"><i class="bi bi-person-plus-fill"></i></div>
        </div>
        <h1>Daftar Akun Masyarakat</h1>
        <p>Dinas Tenaga Kerja Prov. Jawa Timur</p>
        <div class="login-gov">
          <strong>Ajukan Permohonan Informasi Publik</strong>
          Isi data dengan benar untuk keperluan verifikasi
        </div>
      </div>
      <div class="login-body">
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
          </div>
        <?php endif; ?>
        <form method="post" action="<?= site_url('register') ?>" novalidate>
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Nama Lengkap <span class="req">*</span></label>
            <input type="text" name="nama" class="form-control" value="<?= esc(old('nama')) ?>" required minlength="3" placeholder="Masukkan nama lengkap">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" placeholder="email@contoh.com">
            <span class="form-hint">Opsional — untuk notifikasi informasi</span>
          </div>
          <div class="form-group">
            <label class="form-label">No. HP <span class="req">*</span></label>
            <input type="text" name="no_hp" class="form-control" value="<?= esc(old('no_hp')) ?>" required placeholder="08xxxxxxxxxx">
            <span class="form-hint">Digunakan untuk login</span>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group">
              <label class="form-label">Password <span class="req">*</span></label>
              <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
            </div>
            <div class="form-group">
              <label class="form-label">Ulangi Password <span class="req">*</span></label>
              <input type="password" name="konfirmasi_password" class="form-control" required placeholder="Ketik ulang password">
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:.5rem;">
            <i class="bi bi-check2-circle"></i> Daftar Sekarang
          </button>
        </form>
      </div>
      <div class="login-footer">
        Sudah punya akun? <a href="<?= site_url('login') ?>">Masuk di sini</a>
      </div>
    </div>
  </div>
</body>
</html>
