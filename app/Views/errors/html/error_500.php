<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>500 — Kesalahan Server</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#f1f5f9; color:#1e293b; min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .error-box { text-align:center; padding:3rem; max-width:480px; }
    .error-code { font-size:6rem; font-weight:800; color:#ef4444; line-height:1; margin-bottom:.5rem; }
    .error-icon { font-size:3rem; color:#94a3b8; margin-bottom:1rem; }
    .error-title { font-size:1.4rem; font-weight:700; margin-bottom:.5rem; }
    .error-desc { color:#64748b; font-size:.9rem; margin-bottom:1.5rem; line-height:1.6; }
    .btn { display:inline-flex; align-items:center; gap:.4rem; padding:.6rem 1.2rem; border-radius:8px; text-decoration:none; font-weight:600; font-size:.85rem; transition:all .2s; }
    .btn-primary { background:#3b82f6; color:#fff; }
    .btn-primary:hover { background:#2563eb; }
    .btn-outline { border:1px solid #e2e8f0; color:#475569; background:#fff; }
    .btn-outline:hover { background:#f8fafc; border-color:#cbd5e1; }
  </style>
</head>
<body>
  <div class="error-box">
    <div class="error-icon"><i class="bi bi-exclamation-octagon"></i></div>
    <div class="error-code">500</div>
    <div class="error-title">Kesalahan Server</div>
    <div class="error-desc">Terjadi kesalahan pada server. Tim teknis telah diberitahu. Silakan coba lagi nanti.</div>
    <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;">
      <a href="javascript:location.reload()" class="btn btn-outline"><i class="bi bi-arrow-clockwise"></i> Muat Ulang</a>
      <a href="<?= base_url('/') ?>" class="btn btn-primary"><i class="bi bi-house"></i> Beranda</a>
    </div>
  </div>
</body>
</html>
