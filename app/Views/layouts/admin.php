<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Admin') ?> — Admin PPID</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=9.3') ?>">
</head>
<body>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('sidebar').classList.remove('open');this.classList.remove('open');"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-text">
        <div class="brand-title">Admin PPID</div>
        <div class="brand-sub">PPID Dinas Tenaga Kerja<br>Provinsi Jawa Timur</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">Menu Utama</div>
      <a href="<?= site_url('admin') ?>" class="sidebar-link <?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
      </a>

      <div class="sidebar-divider"></div>

      <div class="sidebar-section">Pengelolaan</div>
      <a href="<?= site_url('admin/permohonan') ?>" class="sidebar-link <?= ($active ?? '') === 'permohonan' ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i> Permohonan
        <?php if (($stats['menunggu'] ?? 0) > 0): ?>
          <span style="margin-left:auto;background:var(--yellow-500);color:#fff;font-size:.65rem;padding:.15rem .45rem;border-radius:10px;font-weight:700;"><?= $stats['menunggu'] ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= site_url('admin/masyarakat') ?>" class="sidebar-link <?= ($active ?? '') === 'masyarakat' ? 'active' : '' ?>">
        <i class="bi bi-people-fill"></i> Masyarakat
      </a>

      <div class="sidebar-divider"></div>

      <div class="sidebar-section">Lainnya</div>
      <a href="<?= site_url('admin/profil') ?>" class="sidebar-link <?= ($active ?? '') === 'profil' ? 'active' : '' ?>">
        <i class="bi bi-person-gear"></i> Profil Saya
      </a>

      <div class="sidebar-divider"></div>

      <div class="sidebar-section">Sesi</div>
      <a href="<?= site_url('admin/logout') ?>" class="sidebar-link">
        <i class="bi bi-box-arrow-right"></i> Keluar
      </a>
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
        <div class="user-dropdown" id="userDropdown">
          <button class="user-trigger" onclick="document.getElementById('userDropdown').classList.toggle('open')">
            <span class="user-avatar"><?= esc(strtoupper(substr(session()->get('adminNama') ?? 'AD', 0, 2))) ?></span>
            <div class="user-info">
              <div class="user-name"><?= esc(session()->get('adminNama') ?? 'Admin') ?></div>
              <div class="user-role">Super Admin</div>
            </div>
            <i class="bi bi-chevron-down user-chevron"></i>
          </button>
          <div class="user-menu">
            <a href="<?= site_url('admin/profil') ?>"><i class="bi bi-person-gear"></i> Akun Saya</a>
            <a href="<?= site_url('admin') ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <div class="user-menu-divider"></div>
            <a href="<?= site_url('admin/logout') ?>" class="text-danger"><i class="bi bi-box-arrow-right"></i> Keluar</a>
          </div>
        </div>
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
        <span>&copy; <?= date('Y') ?> Dinas Tenaga Kerja Provinsi Jawa Timur.</span>
      </div>
      <div class="app-footer-right">

        <span><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
      </div>
    </footer>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <div class="toast-container" id="toastContainer"></div>
  <script>
    const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
    const CSRF_TOKEN_VALUE = '<?= csrf_hash() ?>';
    $.ajaxSetup({
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN_VALUE);
      }
    });

    function showToast(msg, type) {
      const c = document.getElementById('toastContainer');
      const el = document.createElement('div');
      el.className = 'toast-msg ' + (type || 'success');
      el.innerHTML = '<i class="bi bi-' + (type === 'error' ? 'exclamation-circle' : type === 'info' ? 'info-circle' : 'check-circle') + '-fill"></i> ' + msg;
      c.appendChild(el);
      setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3500);
    }

    const DATATABLE_ID_LANG = {
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
      infoEmpty: 'Tidak ada data',
      infoFiltered: '(disaring dari _MAX_ total data)',
      paginate: { previous: '<i class="bi bi-chevron-left"></i>', next: '<i class="bi bi-chevron-right"></i>' },
      emptyTable: 'Belum ada data.',
      zeroRecords: 'Data tidak ditemukan.'
    };
    const DATATABLE_DEFAULTS = {
      dom: '<"dataTables_topControls"lf>rt<"dataTables_bottomControls"ip>',
      language: DATATABLE_ID_LANG,
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
      pageLength: 10
    };
    document.addEventListener('click', function(e) {
      const dd = document.getElementById('userDropdown');
      if (dd && !dd.contains(e.target)) dd.classList.remove('open');
    });
  </script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
