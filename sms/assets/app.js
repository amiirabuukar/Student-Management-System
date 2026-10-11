// Shared helpers: CSRF token, toast notifications, sidebar toggle.
const csrf = $('meta[name=csrf]').attr('content');
function toast(msg, ok = true) {
  const el = $(`<div class="toast align-items-center text-bg-${ok ? 'success' : 'danger'} border-0"><div class="d-flex"><div class="toast-body"></div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
  el.find('.toast-body').text(msg); $('#toasts').append(el); new bootstrap.Toast(el[0], { delay: 3500 }).show();
}
$('#toggleSide').on('click', () => $('body').toggleClass('side-collapsed'));
