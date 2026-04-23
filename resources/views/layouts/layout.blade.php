<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SyndicPro — @yield('title', 'Dashboard')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

<div class="erp-wrapper">
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <div class="erp-main" id="mainContent">
        <!-- Navbar -->
        @include('components.navbar')

        <!-- Page Content -->
        <div class="erp-content">
            
            @yield('content')
        </div>
    </div>
</div>
<div id="toastStack" class="toast-stack"></div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<script>
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}

/* Fermer modal sur click overlay */
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});

/* ESC key */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
  }
});

function submitForm(modalId, toastTitle, toastMsg) {
    
    const btn = document.querySelector(`#${modalId} .btn-primary-erp`);
    if (btn) {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement…';
        btn.disabled = true;
        setTimeout(() => {
        btn.innerHTML = orig;
        btn.disabled  = false;
        closeModal(modalId);
        showToast('success', toastTitle, toastMsg);
        }, 1200);
    }
}

/* ── 5. TOAST ──────────────────────────────────────────────────── */
const T_CFG = {
  success: { cls:'green',  icon:'fa-circle-check' },
  warning: { cls:'orange', icon:'fa-triangle-exclamation' },
  error:   { cls:'red',    icon:'fa-circle-xmark' },
};

function showToast(type, title, msg) {
  const stack = document.getElementById('toastStack');
  const cfg   = T_CFG[type] || T_CFG.success;
  const el    = document.createElement('div');
  el.className = 'toast-item';
  el.innerHTML = `
    <div class="clause-icon ${cfg.cls}"><i class="fa-solid ${cfg.icon}"></i></div>
    <div class="toast-body">
      <div class="toast-title">${title}</div>
      <div class="toast-msg">${msg}</div>
    </div>
    <button class="toast-close" onclick="removeToast(this.closest('.toast-item'))">
      <i class="fa-solid fa-xmark"></i>
    </button>`;
  stack.appendChild(el);
  setTimeout(() => removeToast(el), 4200);
}

function removeToast(el) {
  if (!el || !el.parentNode) return;
  el.classList.add('is-out');
  setTimeout(() => el.remove(), 280);
}

</script> 
@stack('scripts')
</body>
</html>