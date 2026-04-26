(() => {
  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phoneRegex = /^[0-9+()\s.-]{6,20}$/;

  const byName = (form, name) => form.querySelector(`[name="${CSS.escape(name)}"]`);
  const errorBox = (name) => document.getElementById(`error-${name}`);

  const findInlineError = (input, name) => {
    if (!input || !(input instanceof HTMLElement)) return null;

    const parent = input.parentElement;
    if (!parent) return null;

    const selector = `[data-js-error-for="${CSS.escape(name)}"]`;
    const existing = parent.querySelector(selector);
    return existing && existing instanceof HTMLElement ? existing : null;
  };

  const getOrCreateInlineError = (input, name) => {
    const existing = findInlineError(input, name);
    if (existing) return existing;
    if (!input || !(input instanceof HTMLElement)) return null;

    const parent = input.parentElement;
    if (!parent) return null;

    const el = document.createElement('div');
    el.className = 'js-field-error muted';
    el.dataset.jsErrorFor = name;
    el.style.color = '#ef4444';
    el.style.fontWeight = '800';
    el.style.marginTop = '6px';
    el.style.fontSize = '12px';
    parent.appendChild(el);
    return el;
  };

  const setError = (form, name, message) => {
    const input = byName(form, name);
    const box = errorBox(name);

    if (input) {
      input.classList.add('is-invalid');
      input.setAttribute('aria-invalid', 'true');
    }

    if (box) {
      box.textContent = message;
      return;
    }

    const inline = input ? getOrCreateInlineError(input, name) : null;
    if (inline) inline.textContent = message;
  };

  const clearError = (form, name) => {
    const input = byName(form, name);
    const box = errorBox(name);

    if (input) {
      input.classList.remove('is-invalid');
      input.removeAttribute('aria-invalid');
    }

    if (box) {
      box.textContent = '';
      return;
    }

    const inline = input ? findInlineError(input, name) : null;
    if (inline) inline.textContent = '';
  };

  const clearAll = (form, names) => {
    names.forEach((n) => clearError(form, n));
  };

  const trimValue = (form, name) => {
    const input = byName(form, name);
    return input ? String(input.value ?? '').trim() : '';
  };

  const rawValue = (form, name) => {
    const input = byName(form, name);
    return input ? String(input.value ?? '') : '';
  };

  const validateLogin = (form) => {
    const names = ['mail', 'motdepasse'];
    clearAll(form, names);

    const mail = trimValue(form, 'mail');
    const password = rawValue(form, 'motdepasse');

    let ok = true;

    if (mail === '') {
      setError(form, 'mail', "L'email est obligatoire.");
      ok = false;
    } else if (!emailRegex.test(mail)) {
      setError(form, 'mail', "Format d'email invalide.");
      ok = false;
    }

    if (password === '') {
      setError(form, 'motdepasse', 'Le mot de passe est obligatoire.');
      ok = false;
    }

    return ok;
  };

  const validateSignup = (form) => {
    const names = ['prenom', 'nom', 'email', 'motdepasse', 'confirmMotdepasse'];
    clearAll(form, names);

    const prenom = trimValue(form, 'prenom');
    const nom = trimValue(form, 'nom');
    const mail = trimValue(form, 'email');
    const password = rawValue(form, 'motdepasse');
    const confirm = rawValue(form, 'confirmMotdepasse');

    let ok = true;

    if (prenom === '') {
      setError(form, 'prenom', 'Le prénom est obligatoire.');
      ok = false;
    }

    if (nom === '') {
      setError(form, 'nom', 'Le nom est obligatoire.');
      ok = false;
    }

    if (mail === '') {
      setError(form, 'email', "L'email est obligatoire.");
      ok = false;
    } else if (!emailRegex.test(mail)) {
      setError(form, 'email', "Format d'email invalide.");
      ok = false;
    }

    if (password === '') {
      setError(form, 'motdepasse', 'Le mot de passe est obligatoire.');
      ok = false;
    } else if (password.length < 6) {
      setError(form, 'motdepasse', 'Le mot de passe doit contenir au moins 6 caractères.');
      ok = false;
    }

    if (confirm === '') {
      setError(form, 'confirmMotdepasse', 'Veuillez confirmer le mot de passe.');
      ok = false;
    } else if (password !== '' && confirm !== password) {
      setError(form, 'confirmMotdepasse', 'Les mots de passe ne correspondent pas.');
      ok = false;
    }

    return ok;
  };

  const validateForgot = (form) => {
    const names = ['mail'];
    clearAll(form, names);

    const mail = trimValue(form, 'mail');

    let ok = true;

    if (mail === '') {
      setError(form, 'mail', "L'email est obligatoire.");
      ok = false;
    } else if (!emailRegex.test(mail)) {
      setError(form, 'mail', "Format d'email invalide.");
      ok = false;
    }

    return ok;
  };

  const validateAdminMemberCreate = (form) => {
    const names = ['prenom', 'nom', 'mail', 'telephone', 'password', 'confirm_password'];
    clearAll(form, names);

    const prenom = trimValue(form, 'prenom');
    const nom = trimValue(form, 'nom');
    const mail = trimValue(form, 'mail');
    const telephone = trimValue(form, 'telephone');
    const password = rawValue(form, 'password');
    const confirm = rawValue(form, 'confirm_password');

    let ok = true;

    if (prenom === '') {
      setError(form, 'prenom', 'Le prénom est obligatoire.');
      ok = false;
    }
    if (nom === '') {
      setError(form, 'nom', 'Le nom est obligatoire.');
      ok = false;
    }

    if (mail === '') {
      setError(form, 'mail', "L'email est obligatoire.");
      ok = false;
    } else if (!emailRegex.test(mail)) {
      setError(form, 'mail', "Format d'email invalide.");
      ok = false;
    }

    if (telephone !== '' && !phoneRegex.test(telephone)) {
      setError(form, 'telephone', 'Format de téléphone invalide.');
      ok = false;
    }

    if (password === '') {
      setError(form, 'password', 'Mot de passe requis.');
      ok = false;
    } else if (password.length < 6) {
      setError(form, 'password', 'Le mot de passe doit contenir au moins 6 caractères.');
      ok = false;
    }

    if (confirm === '') {
      setError(form, 'confirm_password', 'Veuillez confirmer le mot de passe.');
      ok = false;
    } else if (password !== '' && confirm !== password) {
      setError(form, 'confirm_password', 'Les mots de passe ne correspondent pas.');
      ok = false;
    }

    return ok;
  };

  const validateAdminMemberUpdate = (form) => {
    const names = ['prenom', 'nom', 'mail', 'telephone', 'password', 'confirm_password'];
    clearAll(form, names);

    const prenom = trimValue(form, 'prenom');
    const nom = trimValue(form, 'nom');
    const mail = trimValue(form, 'mail');
    const telephone = trimValue(form, 'telephone');
    const password = rawValue(form, 'password');
    const confirm = rawValue(form, 'confirm_password');

    let ok = true;

    if (prenom === '') {
      setError(form, 'prenom', 'Le prénom est obligatoire.');
      ok = false;
    }
    if (nom === '') {
      setError(form, 'nom', 'Le nom est obligatoire.');
      ok = false;
    }

    if (mail === '') {
      setError(form, 'mail', "L'email est obligatoire.");
      ok = false;
    } else if (!emailRegex.test(mail)) {
      setError(form, 'mail', "Format d'email invalide.");
      ok = false;
    }

    if (telephone !== '' && !phoneRegex.test(telephone)) {
      setError(form, 'telephone', 'Format de téléphone invalide.');
      ok = false;
    }

    if (password !== '' || confirm !== '') {
      if (password === '') {
        setError(form, 'password', 'Mot de passe requis.');
        ok = false;
      } else if (password.length < 6) {
        setError(form, 'password', 'Le mot de passe doit contenir au moins 6 caractères.');
        ok = false;
      }

      if (confirm === '') {
        setError(form, 'confirm_password', 'Veuillez confirmer le mot de passe.');
        ok = false;
      } else if (password !== '' && confirm !== password) {
        setError(form, 'confirm_password', 'Les mots de passe ne correspondent pas.');
        ok = false;
      }
    }

    return ok;
  };

  const validateProfileInfo = (form) => {
    const names = ['prenom', 'nom', 'mail', 'telephone'];
    clearAll(form, names);

    const prenom = trimValue(form, 'prenom');
    const nom = trimValue(form, 'nom');
    const mail = trimValue(form, 'mail');
    const telephone = trimValue(form, 'telephone');

    let ok = true;

    if (prenom === '') {
      setError(form, 'prenom', 'Le prénom est obligatoire.');
      ok = false;
    }
    if (nom === '') {
      setError(form, 'nom', 'Le nom est obligatoire.');
      ok = false;
    }
    if (mail === '') {
      setError(form, 'mail', "L'email est obligatoire.");
      ok = false;
    } else if (!emailRegex.test(mail)) {
      setError(form, 'mail', "Format d'email invalide.");
      ok = false;
    }
    if (telephone !== '' && !phoneRegex.test(telephone)) {
      setError(form, 'telephone', 'Format de téléphone invalide.');
      ok = false;
    }

    return ok;
  };

  const validateProfilePassword = (form) => {
    const names = ['current_password', 'new_password', 'confirm_password'];
    clearAll(form, names);

    const current = rawValue(form, 'current_password');
    const next = rawValue(form, 'new_password');
    const confirm = rawValue(form, 'confirm_password');

    let ok = true;

    if (current === '') {
      setError(form, 'current_password', 'Mot de passe actuel requis.');
      ok = false;
    }
    if (next === '') {
      setError(form, 'new_password', 'Nouveau mot de passe requis.');
      ok = false;
    } else if (next.length < 6) {
      setError(form, 'new_password', 'Le mot de passe doit contenir au moins 6 caractères.');
      ok = false;
    }
    if (confirm === '') {
      setError(form, 'confirm_password', 'Veuillez confirmer le mot de passe.');
      ok = false;
    } else if (next !== '' && confirm !== next) {
      setError(form, 'confirm_password', 'Les mots de passe ne correspondent pas.');
      ok = false;
    }

    return ok;
  };

  const attach = (form, validateFn, fields) => {
    const onSubmit = (e) => {
      if (!validateFn(form)) {
        e.preventDefault();

        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) {
          firstInvalid.focus({ preventScroll: true });
          if (!reduceMotion) {
            firstInvalid.scrollIntoView({ block: 'center', behavior: 'smooth' });
          } else {
            firstInvalid.scrollIntoView({ block: 'center' });
          }
        }
      }
    };

    form.addEventListener('submit', onSubmit);

    fields.forEach((name) => {
      const input = byName(form, name);
      if (!input) return;

      input.addEventListener('input', () => {
        if (input.classList.contains('is-invalid')) {
          validateFn(form);
        }
      });

      input.addEventListener('blur', () => {
        validateFn(form);
      });
    });
  };

  const loginForm = document.getElementById('loginForm');
  if (loginForm) attach(loginForm, validateLogin, ['mail', 'motdepasse']);

  const signupForm = document.getElementById('signupForm');
  if (signupForm) attach(signupForm, validateSignup, ['prenom', 'nom', 'email', 'motdepasse', 'confirmMotdepasse']);

  const forgotForm = document.getElementById('forgotForm');
  if (forgotForm) attach(forgotForm, validateForgot, ['mail']);

  document
    .querySelectorAll('form[action*="route=admin-users-create"]')
    .forEach((form) => attach(form, validateAdminMemberCreate, ['prenom', 'nom', 'mail', 'telephone', 'password', 'confirm_password']));

  document
    .querySelectorAll('form[action*="route=admin-users-update"]')
    .forEach((form) => attach(form, validateAdminMemberUpdate, ['prenom', 'nom', 'mail', 'telephone', 'password', 'confirm_password']));

  document
    .querySelectorAll('form[action*="route=profile"]')
    .forEach((form) => {
      const actionInput = byName(form, 'action');
      const action = actionInput ? String(actionInput.value ?? '') : '';
      if (action === 'info') {
        attach(form, validateProfileInfo, ['prenom', 'nom', 'mail', 'telephone']);
      }
      if (action === 'password') {
        attach(form, validateProfilePassword, ['current_password', 'new_password', 'confirm_password']);
      }
    });
})();
