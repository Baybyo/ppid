<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1>Formulir Permohonan Informasi Publik</h1>
  <p>Isi data secara lengkap. Setelah dikirim, Anda akan menerima Nomor Registrasi.</p>
</div>

<div class="card">
  <div class="card-body">
    <form id="formPermohonan" action="<?= site_url('permohonan/submit') ?>" method="post" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="draftId" value="<?= esc($existing['id'] ?? '') ?>">

      <div class="section-block">
        <div class="section-block-title">A. Identitas Pemohon</div>
        <?php $ex = $existing ?? []; $val = function($k, $d='') use ($ex) { return esc($ex[$k] ?? old($k) ?? $d); }; ?>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Nama Lengkap <span class="req">*</span></label>
              <input type="text" name="nama_pemohon" class="form-control" value="<?= $val('nama_pemohon', session()->get('masyarakatNama')) ?>" required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label class="form-label">Jenis Identitas <span class="req">*</span></label>
              <select name="jenis_identitas" class="form-select" required>
                <?php foreach (['KTP','SIM','Paspor'] as $j): ?>
                  <option value="<?= $j ?>" <?= $val('jenis_identitas') === $j ? 'selected' : '' ?>><?= $j ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label class="form-label">No. Identitas <span class="req">*</span></label>
              <input type="text" name="no_identitas" class="form-control" value="<?= $val('no_identitas') ?>" required>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Pekerjaan <span class="req">*</span></label>
              <input type="text" name="pekerjaan" class="form-control" value="<?= $val('pekerjaan') ?>" required>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">No. Telepon <span class="req">*</span></label>
              <input type="text" name="no_telp" class="form-control" value="<?= $val('no_telp', session()->get('masyarakatNoHp')) ?>" required>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Email <span class="req">*</span></label>
              <input type="email" name="email" class="form-control" value="<?= $val('email', session()->get('masyarakatEmail')) ?>" required>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group">
              <label class="form-label">Alamat <span class="req">*</span></label>
              <textarea name="alamat" rows="2" class="form-control" maxlength="500" required><?= $val('alamat') ?></textarea>
              <span class="char-counter" data-max="500"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="section-block">
        <div class="section-block-title">B. Rincian Permohonan</div>
        <div class="row g-3">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label">Rincian Informasi yang Dibutuhkan <span class="req">*</span></label>
              <textarea name="rincian_informasi" id="rincianInput" rows="4" class="form-control" maxlength="2000" required><?= $val('rincian_informasi') ?></textarea>
              <span class="char-counter" data-max="2000"></span>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group">
              <label class="form-label">Tujuan Penggunaan Informasi <span class="req">*</span></label>
              <textarea name="tujuan_penggunaan" id="tujuanInput" rows="3" class="form-control" maxlength="1000" required><?= $val('tujuan_penggunaan') ?></textarea>
              <span class="char-counter" data-max="1000"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="section-block">
        <div class="section-block-title">C. Cara Memperoleh & Salinan</div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Cara Memperoleh Informasi <span class="req">*</span></label>
              <?php $cm = json_decode($ex['cara_memperoleh'] ?? '[]', true) ?: []; ?>
              <?php foreach (['Melihat/membaca/mendengarkan/mencatat','Mendapatkan salinan hardcopy/softcopy'] as $o): ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="cara_memperoleh[]" value="<?= esc($o) ?>" <?= in_array($o, $cm, true) ? 'checked' : '' ?>>
                  <label class="form-check-label"><?= esc($o) ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Cara Mendapatkan Salinan <span class="req">*</span></label>
              <?php $cs = json_decode($ex['cara_salinan'] ?? '[]', true) ?: []; ?>
              <?php foreach (['Mengambil langsung','Kurir','Pos','Email','Yang Lain'] as $o): ?>
                <div class="form-check">
                  <input class="form-check-input cara-salinan" type="checkbox" name="cara_salinan[]" value="<?= esc($o) ?>" <?= in_array($o, $cs, true) ? 'checked' : '' ?>>
                  <label class="form-check-label"><?= esc($o) ?></label>
                </div>
              <?php endforeach; ?>
              <div id="caraLainWrap" class="mt-2" style="display:none;">
                <input type="text" name="cara_salinan_lainnya" class="form-control" placeholder="Sebutkan cara lainnya" value="<?= $val('cara_salinan_lainnya') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="section-block">
        <div class="section-block-title">D. Dokumen & Persetujuan</div>
        <div class="row g-3">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label">Upload Identitas (KTP/SIM/Paspor) <span class="req">*</span></label>
              <input type="file" name="file_identitas" id="fileIdentitas" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
              <div class="form-hint">Format: JPG, JPEG, PNG, atau PDF. Maksimal 10MB.</div>
              <div id="fileInfo" class="mt-1" style="font-size:.8rem;color:var(--n-400);"></div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="persetujuan" id="persetujuan" value="1">
              <label class="form-check-label" for="persetujuan">
                Saya menyatakan data yang diisi benar dan menyetujui ketentuan layanan PPID. <span class="req">*</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:.5rem;padding-top:.75rem;border-top:1px solid var(--n-200);margin-top:.5rem;">
        <button type="button" id="btnDraft" class="btn btn-outline">
          <i class="bi bi-save2"></i> Simpan Draft
        </button>
        <button type="submit" id="btnSubmit" class="btn btn-primary">
          <i class="bi bi-send"></i> Kirim Permohonan
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function(){
  const boxes = [...document.querySelectorAll('.cara-salinan')];
  const wrap = document.getElementById('caraLainWrap');
  const tog = () => { wrap.style.display = boxes.some(b => b.checked && b.value === 'Yang Lain') ? 'block' : 'none'; };
  boxes.forEach(b => b.addEventListener('change', tog));
  tog();

  const fileInput = document.getElementById('fileIdentitas');
  const fileInfo = document.getElementById('fileInfo');
  fileInput.addEventListener('change', () => {
    const f = fileInput.files[0];
    if(f) {
      const sizeMB = (f.size / 1024 / 1024).toFixed(2);
      fileInfo.innerHTML = '<strong>' + f.name + '</strong> (' + sizeMB + ' MB)';
    } else {
      fileInfo.innerHTML = '';
    }
  });

  document.getElementById('btnDraft').addEventListener('click', async () => {
    const f = document.getElementById('formPermohonan');
    const fd = new FormData(f);
    fd.delete('persetujuan');
    fd.delete('file_identitas');

    const btn = document.getElementById('btnDraft');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

    try {
      const res = await fetch('<?= site_url("permohonan/store-draft") ?>', {
        method: 'POST', body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const j = await res.json();
      if(j.status) {
        document.getElementById('draftId').value = j.draft_id;
        showToast('Draft berhasil tersimpan!');
      } else {
        showToast(j.message || 'Gagal menyimpan draft', 'error');
      }
    } catch(e) {
      showToast('Terjadi kesalahan jaringan', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-save2"></i> Simpan Draft';
    }
  });

  document.getElementById('formPermohonan').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const fd = new FormData(form);
    clearAllErrors(form);

    const required = ['nama_pemohon','no_identitas','jenis_identitas','pekerjaan','alamat','no_telp','email','rincian_informasi','tujuan_penggunaan'];
    for(const name of required) {
      if(!fd.get(name) || fd.get(name).trim() === '') {
        const field = form.querySelector('[name="' + name + '"]');
        if(field) showFieldError(field, 'Field ini wajib diisi.');
      }
    }
    if(!fd.get('persetujuan')) {
      showToast('Centang persetujuan terlebih dahulu.', 'error');
      return;
    }
    const cm = fd.getAll('cara_memperoleh[]');
    if(cm.length === 0) {
      showToast('Pilih minimal 1 cara memperoleh informasi.', 'error');
      return;
    }
    const cs = fd.getAll('cara_salinan[]');
    if(cs.length === 0) {
      showToast('Pilih minimal 1 cara mendapatkan salinan.', 'error');
      return;
    }
    if(cs.includes('Yang Lain') && (!fd.get('cara_salinan_lainnya') || fd.get('cara_salinan_lainnya').trim().length < 3)) {
      showToast('Detail "Yang Lain" wajib diisi minimal 3 karakter.', 'error');
      return;
    }
    if(!fd.get('file_identitas') || fd.get('file_identitas').size === 0) {
      showToast('File identitas wajib diunggah.', 'error');
      return;
    }

    const hasErrors = form.querySelectorAll('.field-error').length > 0;
    if(hasErrors) {
      showToast('Periksa field yang bermasalah.', 'error');
      return;
    }

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';

    try {
      const res = await fetch(form.action, {
        method: 'POST', body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const j = await res.json();
      if(j.status) {
        window.location.href = j.redirect;
      } else {
        if(j.errors) {
          for(const [field, msg] of Object.entries(j.errors)) {
            const el = form.querySelector('[name="' + field + '"]');
            if(el) showFieldError(el, msg);
          }
          showToastErrors(j.errors);
        } else {
          showToast(j.message || 'Gagal mengirim', 'error');
        }
      }
    } catch(e) {
      showToast('Terjadi kesalahan jaringan', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-send"></i> Kirim Permohonan';
    }
  });

  document.querySelectorAll('.char-counter').forEach(function(el) {
    const max = parseInt(el.dataset.max);
    const ta = el.previousElementSibling;
    if (!ta || ta.tagName !== 'TEXTAREA') return;
    function update() {
      const len = ta.value.length;
      el.textContent = len + ' / ' + max;
      el.className = 'char-counter' + (len > max ? ' over' : len > max * 0.9 ? ' warn' : '');
    }
    ta.addEventListener('input', update);
    update();
  });
})();
</script>
<?= $this->endSection() ?>
