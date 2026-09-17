<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — PPID Dinas Tenaga Kerja Prov. Jawa Timur</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=9.0') ?>">
</head>
<body>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <div class="login-brand">
          <img src="<?= base_url('assets/images/logo-jatim.png') ?>" alt="Logo Jatim" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="login-brand-fallback" style="display:none;"><i class="bi bi-shield-lock-fill"></i></div>
        </div>
        <h1>PPID Dinas Tenaga Kerja</h1>
        <p>Provinsi Jawa Timur</p>
        <div class="login-gov">
          <strong>Pelayanan Informasi Publik</strong>
       </div>
      </div>
      <div class="login-body">
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
          </div>
        <?php endif; ?>
        <form method="post" action="<?= site_url('login') ?>">
          <?= csrf_field() ?>
          <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
              <i class="bi bi-check-circle-fill"></i>
              <span><?= esc(session()->getFlashdata('success')) ?></span>
            </div>
          <?php endif; ?>
          <div class="form-group">
            <label class="form-label">No. HP</label>
            <input type="text" name="no_hp" class="form-control" value="<?= esc(old('no_hp')) ?>" required autofocus placeholder="08xxxxxxxxxx">
            <span class="form-hint">Nomor telepon yang didaftarkan</span>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
          </div>
          <input type="hidden" name="redirect_to" value="<?= esc(old('redirect_to', '')) ?>">
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:.5rem;">
            <i class="bi bi-box-arrow-in-right"></i> Masuk
          </button>
        </form>
      </div>
      <div class="login-footer">
        Belum punya akun? <a href="<?= site_url('register') ?>">Daftar di sini</a>
      </div>
    </div>
  </div>
</body>
</html>
