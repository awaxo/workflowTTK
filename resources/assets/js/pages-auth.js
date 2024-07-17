/**
 *  Pages Authentication
 */

'use strict';
const formAuthentication = document.querySelector('#formAuthentication');

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    // Form validation for Add new record
    if (formAuthentication) {
      const fv = FormValidation.formValidation(formAuthentication, {
        fields: {
          username: {
            validators: {
              notEmpty: {
                message: 'Kérjük, add meg a felhasználónevet'
              },
              stringLength: {
                min: 6,
                message: 'A felhasználónévnek több mint 6 karakterből kell állnia'
              }
            }
          },
          email: {
            validators: {
              notEmpty: {
                message: 'Kérjük, add meg az e-mail címet'
              },
              emailAddress: {
                message: 'Kérjük, adjon meg egy érvényes e-mail címet'
              }
            }
          },
          'email-username': {
            validators: {
              notEmpty: {
                message: 'Kérjük, add meg az e-mailt / felhasználónevet'
              },
              stringLength: {
                min: 6,
                message: 'A felhasználónévnek több mint 6 karakterből kell állnia'
              }
            }
          },
          password: {
            validators: {
              notEmpty: {
                message: 'Kérjük, add meg a jelszavad'
              },
              stringLength: {
                min: 6,
                message: 'A jelszónak több mint 6 karakterből kell állnia'
              }
            }
          },
          'confirm-password': {
            validators: {
              notEmpty: {
                message: 'Kérjük, erősítsd meg a jelszavad'
              },
              identical: {
                compare: function () {
                  return formAuthentication.querySelector('[name="password"]').value;
                },
                message: 'A jelszó és a megerősítése nem egyezik'
              },
              stringLength: {
                min: 6,
                message: 'A jelszónak több mint 6 karakterből kell állnia'
              }
            }
          },
          terms: {
            validators: {
              notEmpty: {
                message: 'Kérjük, fogadja el a felhasználási feltételeket'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.mb-3'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),

          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      });
    }

    //  Two Steps Verification
    const numeralMask = document.querySelectorAll('.numeral-mask');

    // Verification masking
    if (numeralMask.length) {
      numeralMask.forEach(e => {
        new Cleave(e, {
          numeral: true
        });
      });
    }
  })();
});
