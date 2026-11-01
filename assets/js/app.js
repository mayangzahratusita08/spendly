/**
 * Spendly — Vanilla JavaScript Application Helper
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Mobile Navigation Toggle
  var navToggle = document.querySelector('.nav-toggle');
  var navMenu = document.querySelector('.nav-menu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      var isOpen = navMenu.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
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
});
