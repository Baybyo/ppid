<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin — PPID</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=9.0') ?>">
</head>
<body>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <div class="login-brand">
          <img src="<?= base_url('assets/images/logo-jatim.png') ?>" alt="Logo Jatim" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="login-brand-fallback" style="display:none;"><i class="bi bi-person-gear"></i></div>
        </div>
        <h1>Admin PPID</h1>
        <p>Dinas Tenaga Kerja Prov. Jawa Timur</p>
        <div class="login-gov">
          <strong>Administrasi Sistem PPID</strong>
          Akses khusus petugas yang ditunjuk
        </div>
      </div>
      <div class="login-body">
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
          </div>
        <?php endif; ?>
        <form method="post" action="<?= site_url('admin/login') ?>">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autofocus placeholder="Masukkan username">
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-password-wrapper">
              <input type="password" name="password" id="password" class="form-control" required placeholder="Masukkan password">
              <button type="button" class="btn-toggle-pwd" onclick="togglePassword('password', this)"><i class="bi bi-eye"></i></button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:.5rem;">
            <i class="bi bi-box-arrow-in-right"></i> Masuk
          </button>
        </form>
      </div>

    </div>
  </div>
<script>
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon = btn.querySelector('i');
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
</script>
</body>
</html>
