export const INSTALL_DELAY_MS = 7 * 24 * 60 * 60 * 1000;

const DEFERRED_UNTIL_KEY = 'hrd-pwa-install-deferred-until';
const INSTALLED_KEY = 'hrd-pwa-installed';
let installPrompt = null;
let installer = null;

export function isStandalone(view) {
  return view.navigator.standalone === true ||
    view.matchMedia('(display-mode: standalone)').matches;
}

export function supportedPlatform(navigator) {
  const ua = navigator.userAgent;
  const isIOS = /iPad|iPhone|iPod/.test(ua) ||
    (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

  if (isIOS) {
    return /Safari/.test(ua) && !/(CriOS|FxiOS|EdgiOS|OPiOS)/.test(ua)
      ? 'ios'
      : null;
  }

  return /Android/.test(ua) ? 'android' : null;
}

export function isInstallDeferred(storage, now = Date.now()) {
  return Number(storage.getItem(DEFERRED_UNTIL_KEY) || 0) > now;
}

function createInstaller(modal) {
  const installButton = modal.querySelector('[data-pwa-install]');
  const laterButton = modal.querySelector('[data-pwa-later]');
  const instructions = modal.querySelector('[data-pwa-instructions]');
  let previousFocus = null;

  const close = () => {
    modal.hidden = true;
    document.body.style.overflow = '';
    previousFocus?.focus();
  };

  const postpone = () => {
    localStorage.setItem(DEFERRED_UNTIL_KEY, String(Date.now() + INSTALL_DELAY_MS));
    close();
  };

  const show = (platform) => {
    instructions.textContent = platform === 'ios'
      ? 'Di Safari, tekan Share, pilih Add to Home Screen, lalu konfirmasi.'
      : 'Pasang HRD System agar mudah dibuka dari layar utama perangkat Anda.';
    installButton.hidden = platform !== 'android';
    previousFocus = document.activeElement;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    (platform === 'android' ? installButton : laterButton).focus();
  };

  const consider = () => {
    if (modal.dataset.offer !== '1' ||
        modal.dataset.shown === '1' ||
        isStandalone(window) ||
        localStorage.getItem(INSTALLED_KEY) === '1' ||
        isInstallDeferred(localStorage)) {
      return;
    }

    const platform = supportedPlatform(navigator);
    if (platform === 'ios' || (platform === 'android' && installPrompt)) {
      modal.dataset.shown = '1';
      show(platform);
    }
  };

  installButton.addEventListener('click', async () => {
    if (!installPrompt) return;

    const prompt = installPrompt;
    installPrompt = null;
    prompt.prompt();
    const choice = await prompt.userChoice;

    if (choice.outcome === 'dismissed') {
      localStorage.setItem(DEFERRED_UNTIL_KEY, String(Date.now() + INSTALL_DELAY_MS));
    }
    close();
  });

  laterButton.addEventListener('click', postpone);
  modal.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      postpone();
      return;
    }

    if (event.key !== 'Tab') return;
    const buttons = [...modal.querySelectorAll('button:not([hidden])')];
    const first = buttons[0];
    const last = buttons.at(-1);

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  return { close, consider };
}

if (typeof window !== 'undefined') {
  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    installPrompt = event;
    installer?.consider();
  });

  window.addEventListener('appinstalled', () => {
    localStorage.setItem(INSTALLED_KEY, '1');
    localStorage.removeItem(DEFERRED_UNTIL_KEY);
    installPrompt = null;
    installer?.close();
  });

  const initialize = () => {
    const modal = document.getElementById('pwa-install-modal');
    if (!modal) return;
    installer = createInstaller(modal);
    installer.consider();
  };

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', initialize)
    : initialize();
}
