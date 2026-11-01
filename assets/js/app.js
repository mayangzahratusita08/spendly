/**
 * Spendly — Vanilla JavaScript Application Helper
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Responsive Sidebar Drawer
  var sidebarToggle = document.querySelector('.sidebar-toggle');
  var sidebar = document.getElementById('app-sidebar');
  var sidebarBackdrop = document.querySelector('.sidebar-backdrop');

  if (sidebarToggle && sidebar && sidebarBackdrop) {
    var setSidebarOpen = function (isOpen) {
      document.body.classList.toggle('sidebar-open', isOpen);
      sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      sidebarToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
      sidebarBackdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

      if (isOpen) {
        var firstLink = sidebar.querySelector('.sidebar-link');
        if (firstLink && window.matchMedia('(max-width: 980px)').matches) {
          firstLink.focus();
        }
      } else if (window.matchMedia('(max-width: 980px)').matches) {
        sidebarToggle.focus();
      }
    };

    sidebarToggle.addEventListener('click', function () {
      setSidebarOpen(!document.body.classList.contains('sidebar-open'));
    });

    sidebarBackdrop.addEventListener('click', function () {
      setSidebarOpen(false);
    });

    sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.matchMedia('(max-width: 980px)').matches) {
          setSidebarOpen(false);
        }
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        setSidebarOpen(false);
      }
    });

    window.addEventListener('resize', function () {
      if (!window.matchMedia('(max-width: 980px)').matches) {
        document.body.classList.remove('sidebar-open');
        sidebarToggle.setAttribute('aria-expanded', 'false');
        sidebarToggle.setAttribute('aria-label', 'Open navigation menu');
        sidebarBackdrop.setAttribute('aria-hidden', 'true');
      }
    });
  }

  // 2. Alert Dismissal
  var alertCloses = document.querySelectorAll('.alert-close');
  alertCloses.forEach(function (button) {
    button.addEventListener('click', function () {
      var alert = button.closest('.alert');
      if (alert) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.2s ease';
        setTimeout(function () {
          alert.remove();
        }, 200);
      }
    });
  });

  // 3. Client-side registration password confirmation helper (usability aid)
  var regForm = document.getElementById('register-form');
  var passwordInput = document.getElementById('password');
  var confirmInput = document.getElementById('password_confirmation');

  if (regForm && passwordInput && confirmInput) {
    var checkPasswords = function () {
      if (confirmInput.value && passwordInput.value !== confirmInput.value) {
        confirmInput.setCustomValidity('Passwords do not match');
      } else {
        confirmInput.setCustomValidity('');
      }
    };

    passwordInput.addEventListener('input', checkPasswords);
    confirmInput.addEventListener('input', checkPasswords);
  }

  // 4. Dynamic category filtering based on transaction type (create & edit)
  var txForm = document.getElementById('transaction-form');
  var typeRadios = document.querySelectorAll('input[name="type"]');
  var categorySelect = document.getElementById('category_id');

  if (txForm && typeRadios.length && categorySelect) {
    var syncCategories = function () {
      var selectedType = 'expense';
      typeRadios.forEach(function (radio) {
        if (radio.checked) selectedType = radio.value;
      });

      document.querySelectorAll('.type-pill').forEach(function (pill) {
        var radio = pill.querySelector('input');
        if (radio) {
          pill.classList.remove('active-income', 'active-expense');
          if (radio.checked) {
            pill.classList.add(radio.value === 'income' ? 'active-income' : 'active-expense');
          }
        }
      });

      var currentValid = false;
      var options = categorySelect.querySelectorAll('option');
      options.forEach(function (opt) {
        var catType = opt.getAttribute('data-type');
        if (!catType) {
          opt.style.display = '';
        } else if (catType === selectedType) {
          opt.style.display = '';
          if (opt.selected) currentValid = true;
        } else {
          opt.style.display = 'none';
          if (opt.selected) opt.selected = false;
        }
      });

      if (!currentValid && categorySelect.value !== '') {
        categorySelect.value = '';
      }
    };

    typeRadios.forEach(function (radio) {
      radio.addEventListener('change', syncCategories);
    });

    syncCategories();
  }
});
