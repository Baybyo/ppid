<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'PPID') ?> — Dinas Tenaga Kerja Provinsi Jawa Timur</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=9.0') ?>">
</head>
<body>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('sidebar').classList.remove('open');this.classList.remove('open');"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-logo">
        <img src="<?= base_url('assets/images/logo-jatim.png') ?>" alt="Logo Jatim" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="brand-fallback" style="display:none;"><i class="bi bi-shield-lock-fill"></i></div>
      </div>
      <div class="brand-text">
        <div class="brand-title">PPID</div>
        <div class="brand-sub">Dinas Tenaga Kerja<br>Provinsi Jawa Timur</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">Layanan</div>
      <?php if (session()->get('isMasyarakatLoggedIn')): ?>
        <a href="<?= site_url('permohonan/create') ?>" class="sidebar-link <?= uri_string() === 'permohonan/create' ? 'active' : '' ?>">
          <i class="bi bi-file-earmark-plus"></i> Buat Permohonan
        </a>
        <a href="<?= site_url('permohonan/riwayat') ?>" class="sidebar-link <?= str_starts_with(uri_string(), 'permohonan/riwayat') ? 'active' : '' ?>">
          <i class="bi bi-clock-history"></i> Riwayat
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="sidebar-link <?= uri_string() === 'login' ? 'active' : '' ?>">
          <i class="bi bi-file-earmark-plus"></i> Buat Permohonan
        </a>
      <?php endif; ?>
      <a href="<?= site_url('permohonan/tracking') ?>" class="sidebar-link <?= str_starts_with(uri_string(), 'permohonan/tracking') ? 'active' : '' ?>">
        <i class="bi bi-search"></i> Lacak Status
      </a>

      <div class="sidebar-divider"></div>

      <div class="sidebar-section">Akun</div>
      <?php if (session()->get('isMasyarakatLoggedIn')): ?>
        <a href="<?= site_url('profil') ?>" class="sidebar-link <?= str_starts_with(uri_string(), 'profil') ? 'active' : '' ?>">
          <i class="bi bi-person-gear"></i> Profil Saya
        </a>
        <a href="<?= site_url('permohonan/riwayat') ?>" class="sidebar-link">
          <i class="bi bi-clock-history"></i> Riwayat
        </a>
        <div class="sidebar-divider"></div>
        <a href="<?= site_url('logout') ?>" class="sidebar-link">
          <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="sidebar-link <?= uri_string() === 'login' ? 'active' : '' ?>">
          <i class="bi bi-box-arrow-in-right"></i> Masuk
        </a>
        <a href="<?= site_url('register') ?>" class="sidebar-link <?= uri_string() === 'register' ? 'active' : '' ?>">
          <i class="bi bi-person-plus-fill"></i> Daftar Akun
        </a>
      <?php endif; ?>
    </nav>
  </aside>

  <div class="app-shell">
    <div class="app-topbar">
      <div class="topbar-left">
        <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('open');">
          <i class="bi bi-list"></i>
        </button>
        <div class="topbar-logo">
          <img src="<?= base_url('assets/images/logo-jatim.png') ?>" alt="Logo Jatim" onerror="this.style.display='none'">
        </div>
        <div class="topbar-info">
          <h1>Pemerintah Provinsi Jawa Timur</h1>
          <div class="topbar-sub">Layanan Permohonan Informasi Publik (PPID)</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if (session()->get('isMasyarakatLoggedIn')): ?>
          <div class="user-dropdown" id="userDropdown">
            <button class="user-trigger" onclick="document.getElementById('userDropdown').classList.toggle('open')">
              <span class="user-avatar"><?= esc(strtoupper(substr(session()->get('masyarakatNama'), 0, 2))) ?></span>
              <div class="user-info">
                <div class="user-name"><?= esc(session()->get('masyarakatNama')) ?></div>
                <div class="user-role">PPID Prov. Jawa Timur</div>
              </div>
              <i class="bi bi-chevron-down user-chevron"></i>
            </button>
            <div class="user-menu">
              <a href="<?= site_url('profil') ?>"><i class="bi bi-person-gear"></i> Akun Saya</a>
              <a href="<?= site_url('permohonan/riwayat') ?>"><i class="bi bi-clock-history"></i> Riwayat</a>
              <div class="user-menu-divider"></div>
              <a href="<?= site_url('logout') ?>" class="text-danger"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="btn btn-sm" style="color:var(--blue-600);border:1px solid var(--blue-200);margin-right:.5rem;">Masuk</a>
          <a href="<?= site_url('register') ?>" class="btn btn-primary btn-sm">Daftar</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="app-body">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle-fill"></i>
          <span><?= esc(session()->getFlashdata('success')) ?></span>
        </div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span><?= esc(session()->getFlashdata('error')) ?></span>
        </div>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </div>

    <footer class="app-footer">
      <div class="app-footer-left">
        <i class="bi bi-shield-lock-fill"></i>
        <span>&copy; <?= date('Y') ?> Dinas Tenaga Kerja Provinsi Jawa Timur.</span>
      </div>
      <div class="app-footer-right">
        <span><i class="bi bi-info-circle"></i> PPID v1.0</span>
        <span><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
      </div>
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <div class="toast-container" id="toastContainer"></div>
  <script>
    const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
    const CSRF_TOKEN_VALUE = '<?= csrf_hash() ?>';

    function showToast(msg, type) {
      const c = document.getElementById('toastContainer');
      const el = document.createElement('div');
      el.className = 'toast-msg ' + (type || 'success');
      el.innerHTML = '<i class="bi bi-' + (type === 'error' ? 'exclamation-circle' : type === 'info' ? 'info-circle' : 'check-circle') + '-fill"></i> ' + msg;
      c.appendChild(el);
      setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3500);
    }

    function showFieldError(field, msg) {
      clearFieldError(field);
      field.classList.add('is-invalid');
      const span = document.createElement('span');
      span.className = 'field-error';
      span.textContent = msg;
      field.parentNode.appendChild(span);
    }

    function clearFieldError(field) {
      field.classList.remove('is-invalid');
      const err = field.parentNode.querySelector('.field-error');
      if (err) err.remove();
    }

    function clearAllErrors(form) {
      form.querySelectorAll('.is-invalid').forEach(f => { f.classList.remove('is-invalid'); });
      form.querySelectorAll('.field-error').forEach(e => e.remove());
    }

    function showToastErrors(errors) {
      const msgs = Object.values(errors);
      if (msgs.length) showToast(msgs[0], 'error');
    }

    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => showToast('Berhasil disalin!', 'info'));
    }

    document.addEventListener('click', function(e) {
      const dd = document.getElementById('userDropdown');
      if (dd && !dd.contains(e.target)) dd.classList.remove('open');
    });
  </script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
