// Generic AJAX CRUD table (list, search, filter, paginate, add/edit modal, delete with confirmation).
$(function () {
  const M = window.MOD; let page = 1, timer;
  const modalEl = document.getElementById('formModal'), modal = modalEl ? new bootstrap.Modal(modalEl) : null;
  function load() {
    const p = { m: M.m, a: 'list', q: $('#q').val(), page };
    $('.flt').each(function () { p[this.name] = $(this).val(); });
    $('#tbody').html('<tr><td colspan="99" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Loading…</td></tr>');
    $.getJSON('api.php', p).done(d => {
      const b = $('#tbody').empty();
      if (!d.rows.length) b.html('<tr><td colspan="99" class="text-center text-muted py-4"><i class="fa fa-folder-open fa-2x d-block mb-2"></i>No records found.</td></tr>');
      d.rows.forEach(r => {
        const tr = $('<tr>');
        Object.keys(M.columns).forEach(k => {
          const td = $('<td>');
          if (k === 'name' && r.photo) td.append($('<img class="thumb">').attr('src', 'uploads/' + encodeURIComponent(r.photo)));
          if (k === 'status') td.append($('<span class="badge">').addClass(r.status === 'Active' ? 'bg-success' : 'bg-secondary').text(r.status));
          else td.append(document.createTextNode(r[k] ?? '—'));
          tr.append(td);
        });
        const act = $('<td class="text-end">');
        if (M.write) {
          act.append($('<button class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="fa fa-pen"></i></button>').on('click', () => edit(r.id)),
                     $('<button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></button>').on('click', () => del(r.id)));
        }
        b.append(tr.append(act));
      });
      const pg = $('#pager').empty();
      for (let i = 1; i <= d.pages; i++) pg.append($('<li class="page-item">').toggleClass('active', i === d.page).append($('<a class="page-link" href="#">').text(i).on('click', e => { e.preventDefault(); page = i; load(); })));
    }).fail(() => toast('Failed to load data.', false));
  }
  function reset(title) { $('#recForm')[0].reset(); $('#recForm').removeClass('was-validated'); $('#rec_id').val(''); $('#formErr').addClass('d-none'); $('#formTitle').text(title); }
  function edit(id) {
    $.getJSON('api.php', { m: M.m, a: 'get', id }).done(d => {
      reset('Edit record'); $('#rec_id').val(id);
      Object.entries(d.row).forEach(([k, v]) => { const f = $('#recForm [name="' + k + '"]'); if (f.length && f.attr('type') !== 'file') f.val(v ?? ''); });
      modal.show();
    }).fail(x => toast(x.responseJSON?.error || 'Error', false));
  }
  function del(id) {
    if (!confirm('Delete this record? This cannot be undone.')) return;
    $.post('api.php?m=' + M.m + '&a=delete', { csrf, id }).done(d => { toast(d.message); load(); }).fail(x => toast(x.responseJSON?.error || 'Delete failed', false));
  }
  $('#addBtn').on('click', () => { reset('Add record'); modal.show(); });
  $('#recForm').on('submit', function (e) {
    e.preventDefault(); if (!this.checkValidity()) { $(this).addClass('was-validated'); return; }
    const fd = new FormData(this); fd.append('csrf', csrf);
    $.ajax({ url: 'api.php?m=' + M.m + '&a=save', type: 'POST', data: fd, processData: false, contentType: false })
      .done(d => { modal.hide(); toast(d.message); load(); })
      .fail(x => $('#formErr').removeClass('d-none').text(x.responseJSON?.error || 'Save failed'));
  });
  $('#q').on('input', () => { clearTimeout(timer); timer = setTimeout(() => { page = 1; load(); }, 300); });
  $('.flt').on('change', () => { page = 1; load(); });
  load();
});
