function isBlank(value) {
	return !value || value.trim().length === 0;
}

function isValidEmail(email) {
	return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function setFieldError(inputEl, message) {
	if (!inputEl) return;

	inputEl.classList.add('is-invalid');
	const errorEl = document.getElementById(`error-${inputEl.id}`);
	if (errorEl) errorEl.textContent = message;
}

function clearFieldError(inputEl) {
	if (!inputEl) return;

	inputEl.classList.remove('is-invalid');
	const errorEl = document.getElementById(`error-${inputEl.id}`);
	if (errorEl) errorEl.textContent = '';
}

function validateLogin(formEl) {
	const identifiantEl = formEl.querySelector('#mail');
	const motDePasseEl = formEl.querySelector('#motdepasse');
	let isValid = true;

	clearFieldError(identifiantEl);
	clearFieldError(motDePasseEl);

	if (isBlank(identifiantEl?.value)) {
		setFieldError(identifiantEl, "L'email est obligatoire.");
		isValid = false;
	} else if (!isValidEmail(identifiantEl.value.trim())) {
		setFieldError(identifiantEl, "Format d'email invalide.");
		isValid = false;
	}

	if (isBlank(motDePasseEl?.value)) {
		setFieldError(motDePasseEl, 'Le mot de passe est obligatoire.');
		isValid = false;
	}

	return isValid;
}

function validateSignup(formEl) {
	const prenomEl = formEl.querySelector('#prenom');
	const nomEl = formEl.querySelector('#nom');
	const emailEl = formEl.querySelector('#email');
	const motDePasseEl = formEl.querySelector('#motdepasse');
	const confirmEl = formEl.querySelector('#confirmMotdepasse');
	let isValid = true;

	[prenomEl, nomEl, emailEl, motDePasseEl, confirmEl].forEach(clearFieldError);

	if (isBlank(prenomEl?.value)) {
		setFieldError(prenomEl, 'Le prénom est obligatoire.');
		isValid = false;
	}

	if (isBlank(nomEl?.value)) {
		setFieldError(nomEl, 'Le nom est obligatoire.');
		isValid = false;
	}

	if (isBlank(emailEl?.value)) {
		setFieldError(emailEl, "L'email est obligatoire.");
		isValid = false;
	} else if (!isValidEmail(emailEl.value.trim())) {
		setFieldError(emailEl, "Format d'email invalide.");
		isValid = false;
	}

	const passwordValue = motDePasseEl?.value ?? '';
	if (isBlank(passwordValue)) {
		setFieldError(motDePasseEl, 'Le mot de passe est obligatoire.');
		isValid = false;
	} else if (passwordValue.length < 6) {
		setFieldError(motDePasseEl, 'Le mot de passe doit contenir au moins 6 caractères.');
		isValid = false;
	}

	const confirmValue = confirmEl?.value ?? '';
	if (isBlank(confirmValue)) {
		setFieldError(confirmEl, 'Veuillez confirmer le mot de passe.');
		isValid = false;
	} else if (passwordValue !== confirmValue) {
		setFieldError(confirmEl, 'Les mots de passe ne correspondent pas.');
		isValid = false;
	}

	return isValid;
}

function validateForgotPassword(formEl) {
	const mailEl = formEl.querySelector('#mail');
	let isValid = true;

	clearFieldError(mailEl);

	const mailValue = (mailEl?.value ?? '').trim();
	if (isBlank(mailValue)) {
		setFieldError(mailEl, "L'email est obligatoire.");
		isValid = false;
	} else if (!isValidEmail(mailValue)) {
		setFieldError(mailEl, "Format d'email invalide.");
		isValid = false;
	}

	return isValid;
}

function focusFirstInvalid(formEl) {
	const firstInvalid = formEl.querySelector('.is-invalid');
	if (firstInvalid && typeof firstInvalid.focus === 'function') {
		firstInvalid.focus();
	}
}

document.addEventListener('DOMContentLoaded', () => {
	const loginForm = document.getElementById('loginForm');
	if (loginForm) {
		loginForm.addEventListener('submit', (e) => {
			const ok = validateLogin(loginForm);
			if (!ok) {
				e.preventDefault();
				focusFirstInvalid(loginForm);
			}
		});

		loginForm.querySelectorAll('input').forEach((inputEl) => {
			inputEl.addEventListener('input', () => clearFieldError(inputEl));
			inputEl.addEventListener('blur', () => {
				if (inputEl.classList.contains('is-invalid') && !isBlank(inputEl.value)) {
					clearFieldError(inputEl);
				}
			});
		});
	}

	const forgotForm = document.getElementById('forgotForm');
	if (forgotForm) {
		forgotForm.addEventListener('submit', (e) => {
			const ok = validateForgotPassword(forgotForm);
			if (!ok) {
				e.preventDefault();
				focusFirstInvalid(forgotForm);
				return;
			}

			// MVP: pas de backend de reset implémenté ici.
			e.preventDefault();
			alert('Si un compte existe, un email de réinitialisation sera envoyé.');
		});

		forgotForm.querySelectorAll('input').forEach((inputEl) => {
			inputEl.addEventListener('input', () => clearFieldError(inputEl));
		});
	}

	const signupForm = document.getElementById('signupForm');
	if (signupForm) {
		signupForm.addEventListener('submit', (e) => {
			const ok = validateSignup(signupForm);
			if (!ok) {
				e.preventDefault();
				focusFirstInvalid(signupForm);
			}
		});

		signupForm.querySelectorAll('input').forEach((inputEl) => {
			inputEl.addEventListener('input', () => clearFieldError(inputEl));
		});
	}
});
