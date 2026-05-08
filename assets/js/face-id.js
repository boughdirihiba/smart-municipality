(() => {
  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const cfg = window.__FACEID_CONFIG || {};

  const postJson = async (url, body) => {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const data = ct.includes('application/json') ? await res.json().catch(() => null) : null;
    if (!data) {
      throw new Error('Réponse serveur invalide (JSON attendu).');
    }
    if (!res.ok) {
      const msg = data && data.error ? String(data.error) : 'Request failed';
      throw new Error(msg);
    }
    return data;
  };

  const loadScriptOnce = (() => {
    const loaded = new Set();
    return (src) =>
      new Promise((resolve, reject) => {
        if (!src) return reject(new Error('Missing script src'));
        if (loaded.has(src)) return resolve();
        const s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.onload = () => {
          loaded.add(src);
          resolve();
        };
        s.onerror = () => reject(new Error(`Failed to load script: ${src}`));
        document.head.appendChild(s);
      });
  })();

  const ensureFaceApi = async () => {
    if (window.faceapi) return;

    // Try local first.
    const local = cfg.faceApiLocal;
    const cdn = cfg.faceApiCdn;

    try {
      if (local) await loadScriptOnce(local);
    } catch (_) {
      // ignore
    }

    if (!window.faceapi) {
      if (!cdn) {
        throw new Error(
          "Face recognition library missing. Place face-api.js in assets/vendor/face-api/ or enable internet for CDN."
        );
      }
      await loadScriptOnce(cdn);
    }

    if (!window.faceapi) {
      throw new Error('Face API failed to load');
    }
  };

  const loadModels = async () => {
    const modelUrl = cfg.modelUrl;
    if (!modelUrl) throw new Error('Missing modelUrl for face-api models');

    const { faceapi } = window;

    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl),
      faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
      faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
    ]);
  };

  const computeDescriptorFromVideo = async (videoEl) => {
    const { faceapi } = window;
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
    const result = await faceapi
      .detectSingleFace(videoEl, opts)
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!result || !result.descriptor) {
      throw new Error('Aucun visage détecté. Approchez-vous et regardez la caméra.');
    }

    return Array.from(result.descriptor);
  };

  const startCamera = async (videoEl) => {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      throw new Error("Webcam non supportée par ce navigateur.");
    }

    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
    videoEl.srcObject = stream;
    await videoEl.play();
    return stream;
  };

  const stopCamera = (stream) => {
    if (!stream) return;
    stream.getTracks().forEach((t) => t.stop());
  };

  const createModalController = (modalEl) => {
    const dialog = qs('.faceid-dialog', modalEl);
    const closeBtn = qs('[data-faceid-close]', modalEl);
    const backdrop = qs('.faceid-backdrop', modalEl);
    const video = qs('video', modalEl);
    const msg = qs('[data-faceid-msg]', modalEl);

    const btnStart = qs('[data-faceid-start]', modalEl);
    const btnCapture = qs('[data-faceid-capture]', modalEl);

    let stream = null;
    let ready = false;

    const setMsg = (text, kind = '') => {
      if (!msg) return;
      msg.textContent = text || '';
      msg.classList.toggle('is-error', kind === 'error');
      msg.classList.toggle('is-success', kind === 'success');
    };

    const open = async () => {
      modalEl.classList.add('is-open');
      setMsg('');

      try {
        setMsg('Chargement…');
        await ensureFaceApi();
        await loadModels();
        ready = true;
        setMsg('Caméra prête.');
      } catch (e) {
        ready = false;
        setMsg(String(e.message || e), 'error');
      }

      // Auto start camera if possible.
      try {
        if (video) stream = await startCamera(video);
      } catch (e) {
        setMsg(String(e.message || e), 'error');
      }
    };

    const close = () => {
      modalEl.classList.remove('is-open');
      stopCamera(stream);
      stream = null;
      ready = false;
    };

    const captureDescriptor = async () => {
      if (!ready) throw new Error('Face ID non prêt');
      if (!video) throw new Error('Video element missing');
      return await computeDescriptorFromVideo(video);
    };

    backdrop && backdrop.addEventListener('click', close);
    closeBtn && closeBtn.addEventListener('click', close);

    btnStart &&
      btnStart.addEventListener('click', async () => {
        try {
          setMsg('Ouverture de la caméra…');
          if (video) stream = await startCamera(video);
          setMsg('Caméra ouverte.');
        } catch (e) {
          setMsg(String(e.message || e), 'error');
        }
      });

    return { open, close, captureDescriptor, setMsg };
  };

  const initProfileEnroll = () => {
    const btn = qs('[data-faceid-enroll-btn]');
    const modal = qs('#faceIdEnrollModal');
    if (!btn || !modal) return;

    const ctl = createModalController(modal);
    const saveBtn = qs('[data-faceid-save]', modal);

    btn.addEventListener('click', () => ctl.open());

    saveBtn &&
      saveBtn.addEventListener('click', async () => {
        try {
          ctl.setMsg('Analyse du visage…');
          const descriptor = await ctl.captureDescriptor();
          ctl.setMsg('Enregistrement…');
          await postJson(cfg.enrollUrl, { descriptor });
          ctl.setMsg('Face ID enregistré avec succès.', 'success');
          setTimeout(() => window.location.reload(), 700);
        } catch (e) {
          ctl.setMsg(String(e.message || e), 'error');
        }
      });
  };

  const initLoginFace = () => {
    const btn = qs('[data-faceid-login-btn]');
    const modal = qs('#faceIdLoginModal');
    if (!btn || !modal) return;

    const ctl = createModalController(modal);
    const loginBtn = qs('[data-faceid-login]', modal);

    const mailInput = qs('#mail');

    btn.addEventListener('click', () => ctl.open());

    loginBtn &&
      loginBtn.addEventListener('click', async () => {
        try {
          const mail = mailInput ? String(mailInput.value || '').trim() : '';
          if (!mail) {
            ctl.setMsg('Veuillez saisir votre email dans le champ Email.', 'error');
            return;
          }

          ctl.setMsg('Analyse du visage…');
          const descriptor = await ctl.captureDescriptor();
          ctl.setMsg('Vérification…');
          const res = await postJson(cfg.loginUrl, { mail, descriptor });
          const redirectTo = res && res.redirect ? String(res.redirect) : 'index.php?route=profile';
          window.location.href = redirectTo;
        } catch (e) {
          ctl.setMsg(String(e.message || e), 'error');
        }
      });
  };

  initProfileEnroll();
  initLoginFace();
})();
