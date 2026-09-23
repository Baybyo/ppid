<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1><i class="bi bi-people-fill" style="color:var(--blue-600);"></i> Kelola Masyarakat</h1>
  <p>Daftar seluruh akun masyarakat terdaftar</p>
</div>

<div class="card">
  <div class="table-wrap">
    <table id="tableMasyarakat" style="width:100%;">
      <thead>
        <tr>
          <th style="width:50px;text-align:center;">No.</th>
          <th>Nama</th>
          <th>No. HP</th>
          <th>Email</th>
          <th style="width:90px;text-align:center;">Status</th>
          <th style="width:100px;">Terdaftar</th>
          <th style="width:130px;text-align:center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach (($list ?? []) as $u): ?>
          <tr id="row-<?= $u['id'] ?>" style="vertical-align:middle;">
            <td style="text-align:center;color:var(--n-400);font-size:.82rem;"><?= $no++ ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:.6rem;">
                <div class="user-avatar" style="width:32px;height:32px;font-size:.65rem;flex-shrink:0;"><?= esc(strtoupper(substr($u['nama'], 0, 2))) ?></div>
                <div>
                  <a href="<?= site_url('admin/masyarakat/detail/' . $u['id']) ?>" style="font-weight:600;font-size:.84rem;text-decoration:none;color:var(--blue-600);"><?= esc($u['nama']) ?></a>
                  <?php if (!empty($u['nisn'])): ?>
                    <div style="font-size:.72rem;color:var(--n-400);">No ID: <?= esc($u['nisn']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td style="font-family:monospace;font-size:.82rem;"><?= esc($u['no_hp']) ?></td>
            <td style="font-size:.82rem;"><?= esc($u['email'] ?? '-') ?></td>
            <td style="text-align:center;">
              <span class="badge <?= (int)$u['is_active'] === 1 ? 'badge-selesai' : 'badge-ditolak' ?>" id="status-<?= $u['id'] ?>">
                <?= (int)$u['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td style="color:var(--n-400);font-size:.82rem;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td style="text-align:center;">
              <div style="display:flex;gap:.25rem;justify-content:center;">
                <a href="<?= site_url('admin/masyarakat/detail/' . $u['id']) ?>" class="btn btn-sm btn-outline" title="Kelola / Edit"><i class="bi bi-pencil-square"></i></a>
                <button onclick="toggleStatus(<?= $u['id'] ?>)" class="btn btn-sm <?= (int)$u['is_active'] === 1 ? 'btn-danger' : 'btn-success' ?>" id="btn-<?= $u['id'] ?>" title="<?= (int)$u['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                  <i class="bi bi-<?= (int)$u['is_active'] === 1 ? 'lock' : 'unlock' ?>"></i>
                </button>
                <form action="<?= site_url('admin/masyarakat/delete/' . $u['id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Hapus akun <?= esc($u['nama'], 'js') ?> permanen?');">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline" style="color:var(--red-600);border-color:var(--red-200);" title="Hapus"><i class="bi bi-trash3"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
  $('#tableMasyarakat').DataTable(
    $.extend({}, DATATABLE_DEFAULTS, {
      order: [],
      columnDefs: [{ orderable: false, targets: [6] }],
      language: { emptyTable: 'Tidak ada data masyarakat.' }
    })
  );
});

async function toggleStatus(id) {
  if (!confirm('Yakin ingin mengubah status akun ini?')) return;
  const btn = document.getElementById('btn-' + id);
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

  try {
    const fd = new FormData();
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    const res = await fetch('<?= site_url('admin/masyarakat/toggle-status/') ?>' + id, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const j = await res.json();
    if (j.status) {
      const badge = document.getElementById('status-' + id);
      if (j.is_active === 1) {
        badge.className = 'badge badge-selesai';
        badge.textContent = 'Aktif';
        btn.className = 'btn btn-sm btn-danger';
        btn.innerHTML = '<i class="bi bi-lock"></i>';
      } else {
        badge.className = 'badge badge-ditolak';
        badge.textContent = 'Nonaktif';
        btn.className = 'btn btn-sm btn-success';
        btn.innerHTML = '<i class="bi bi-unlock"></i>';
      }
    } else {
      alert(j.message || 'Gagal');
      btn.disabled = false;
      btn.innerHTML = origHtml;
    }
  } catch(e) {
    alert('Kesalahan jaringan');
    btn.disabled = false;
    btn.innerHTML = origHtml;
  }
}
</script>
<?= $this->endSection() ?>
