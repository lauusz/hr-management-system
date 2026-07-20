<div id="projectImageViewer" class="project-image-viewer" role="dialog" aria-modal="true" aria-label="Preview gambar" hidden>
    <div class="project-image-viewer__backdrop" data-image-viewer-action="close"></div>

    <div class="project-image-viewer__toolbar" aria-label="Kontrol gambar">
        <button type="button" class="project-image-viewer__button" data-image-viewer-action="zoom-out" aria-label="Perkecil gambar">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/></svg>
        </button>
        <span class="project-image-viewer__percentage" data-image-viewer-percentage>100%</span>
        <button type="button" class="project-image-viewer__button" data-image-viewer-action="zoom-in" aria-label="Perbesar gambar">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        </button>
        <button type="button" class="project-image-viewer__button" data-image-viewer-action="rotate-left" aria-label="Putar gambar ke kiri">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7M3 4v8h8"/></svg>
        </button>
        <button type="button" class="project-image-viewer__button" data-image-viewer-action="rotate-right" aria-label="Putar gambar ke kanan">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7M21 4v8h-8"/></svg>
        </button>
        <button type="button" class="project-image-viewer__button" data-image-viewer-action="reset" aria-label="Reset tampilan">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
        </button>
    </div>

    <button type="button" class="project-image-viewer__close" data-image-viewer-action="close" aria-label="Tutup preview gambar">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>

    <div class="project-image-viewer__stage" data-image-viewer-stage>
        <img class="project-image-viewer__image" data-image-viewer-image src="" alt="" draggable="false">
        <p class="project-image-viewer__error" data-image-viewer-error hidden>Gambar tidak dapat ditampilkan.</p>
    </div>
</div>

<style>
    .project-image-viewer[hidden] { display: none; }
    .project-image-viewer { position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; }
    .project-image-viewer__backdrop { position: absolute; inset: 0; background: rgba(0, 0, 0, 0.9); backdrop-filter: blur(4px); }
    .project-image-viewer__toolbar { position: fixed; top: 12px; left: 50%; z-index: 2; display: flex; align-items: center; gap: 2px; padding: 4px 6px; transform: translateX(-50%); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 999px; background: rgba(28, 28, 32, 0.88); color: #fff; }
    .project-image-viewer__button, .project-image-viewer__close { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border: 0; border-radius: 50%; background: transparent; color: #fff; cursor: pointer; }
    .project-image-viewer__button:hover, .project-image-viewer__button:focus-visible, .project-image-viewer__close:hover, .project-image-viewer__close:focus-visible { background: rgba(255, 255, 255, 0.16); outline: none; }
    .project-image-viewer__button:focus-visible, .project-image-viewer__close:focus-visible { box-shadow: 0 0 0 2px #fff; }
    .project-image-viewer__button svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .project-image-viewer__percentage { min-width: 44px; text-align: center; font-size: 12px; font-weight: 700; }
    .project-image-viewer__close { position: fixed; top: 12px; right: 12px; z-index: 2; border: 1px solid rgba(255, 255, 255, 0.18); background: rgba(255, 255, 255, 0.1); }
    .project-image-viewer__close svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }
    .project-image-viewer__stage { position: absolute; inset: 0; z-index: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; touch-action: none; }
    .project-image-viewer__stage.is-grab { cursor: grab; }
    .project-image-viewer__stage.is-grabbing { cursor: grabbing; }
    .project-image-viewer__image { max-width: 92vw; max-height: 88vh; object-fit: contain; transform-origin: center; transition: transform 0.15s ease-out; user-select: none; pointer-events: none; }
    .project-image-viewer__stage.is-grabbing .project-image-viewer__image { transition: none; }
    .project-image-viewer__error { position: relative; z-index: 1; margin: 0; padding: 14px 18px; border-radius: 10px; background: rgba(17, 24, 39, 0.9); color: #fff; font-size: 14px; font-weight: 600; }

    @media (max-width: 479px) {
        .project-image-viewer__toolbar { max-width: calc(100vw - 64px); overflow-x: auto; }
        .project-image-viewer__button { flex: 0 0 44px; }
        .project-image-viewer__image { max-width: 96vw; max-height: 82vh; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewer = document.getElementById('projectImageViewer');
        if (!viewer) return;

        const stage = viewer.querySelector('[data-image-viewer-stage]');
        const image = viewer.querySelector('[data-image-viewer-image]');
        const error = viewer.querySelector('[data-image-viewer-error]');
        const percentage = viewer.querySelector('[data-image-viewer-percentage]');
        const closeButton = viewer.querySelector('[data-image-viewer-action="close"]:not(.project-image-viewer__backdrop)');
        const minZoom = 0.5;
        const maxZoom = 4;
        const zoomStep = 0.25;
        let zoom = 1;
        let panX = 0;
        let panY = 0;
        let rotation = 0;
        let dragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let panStartX = 0;
        let panStartY = 0;
        let lastTrigger = null;

        function updateTransform() {
            image.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom}) rotate(${rotation}deg)`;
            percentage.textContent = `${Math.round(zoom * 100)}%`;
            stage.classList.toggle('is-grab', zoom > 1 && !dragging);
            stage.classList.toggle('is-grabbing', dragging);
        }

        function reset() {
            zoom = 1;
            panX = 0;
            panY = 0;
            rotation = 0;
            dragging = false;
            updateTransform();
        }

        function changeZoom(delta) {
            zoom = Math.max(minZoom, Math.min(maxZoom, Math.round((zoom + delta) / zoomStep) * zoomStep));
            if (zoom <= 1) {
                panX = 0;
                panY = 0;
            }
            updateTransform();
        }

        function open(trigger) {
            const src = trigger.getAttribute('data-image-viewer-src');
            if (!src) return;

            lastTrigger = trigger;
            reset();
            error.hidden = true;
            image.hidden = false;
            image.alt = trigger.getAttribute('data-image-viewer-alt') || 'Preview gambar';
            image.src = src;
            viewer.hidden = false;
            document.body.style.overflow = 'hidden';
            closeButton.focus();
        }

        function close() {
            if (viewer.hidden) return;
            viewer.hidden = true;
            image.src = '';
            image.alt = '';
            error.hidden = true;
            document.body.style.overflow = '';
            reset();
            if (lastTrigger) lastTrigger.focus();
            lastTrigger = null;
        }

        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('[data-image-viewer-src]');
            if (trigger) {
                event.preventDefault();
                open(trigger);
                return;
            }

            const actionButton = event.target.closest('[data-image-viewer-action]');
            if (!actionButton || !viewer.contains(actionButton)) return;

            const action = actionButton.getAttribute('data-image-viewer-action');
            if (action === 'close') close();
            if (action === 'zoom-out') changeZoom(-zoomStep);
            if (action === 'zoom-in') changeZoom(zoomStep);
            if (action === 'rotate-left') { rotation = (rotation + 270) % 360; updateTransform(); }
            if (action === 'rotate-right') { rotation = (rotation + 90) % 360; updateTransform(); }
            if (action === 'reset') reset();
        });

        image.addEventListener('error', function() {
            image.hidden = true;
            error.hidden = false;
        });

        image.addEventListener('load', function() {
            image.hidden = false;
            error.hidden = true;
        });

        stage.addEventListener('wheel', function(event) {
            if (viewer.hidden) return;
            event.preventDefault();
            changeZoom(event.deltaY < 0 ? zoomStep : -zoomStep);
        }, { passive: false });

        stage.addEventListener('dblclick', function(event) {
            event.preventDefault();
            if (zoom === 1) changeZoom(1);
            else reset();
        });

        function startDrag(clientX, clientY) {
            if (zoom <= 1) return;
            dragging = true;
            dragStartX = clientX;
            dragStartY = clientY;
            panStartX = panX;
            panStartY = panY;
            updateTransform();
        }

        function moveDrag(clientX, clientY) {
            if (!dragging) return;
            panX = panStartX + clientX - dragStartX;
            panY = panStartY + clientY - dragStartY;
            updateTransform();
        }

        function stopDrag() {
            if (!dragging) return;
            dragging = false;
            updateTransform();
        }

        stage.addEventListener('mousedown', function(event) { if (event.button === 0) startDrag(event.clientX, event.clientY); });
        document.addEventListener('mousemove', function(event) { moveDrag(event.clientX, event.clientY); });
        document.addEventListener('mouseup', stopDrag);
        stage.addEventListener('touchstart', function(event) {
            if (event.touches.length === 1) startDrag(event.touches[0].clientX, event.touches[0].clientY);
        }, { passive: true });
        stage.addEventListener('touchmove', function(event) {
            if (!dragging || event.touches.length !== 1) return;
            event.preventDefault();
            moveDrag(event.touches[0].clientX, event.touches[0].clientY);
        }, { passive: false });
        stage.addEventListener('touchend', stopDrag);

        document.addEventListener('keydown', function(event) {
            if (viewer.hidden) return;
            if (event.key === 'Escape') close();
            if (event.key === '+' || event.key === '=') { event.preventDefault(); changeZoom(zoomStep); }
            if (event.key === '-') { event.preventDefault(); changeZoom(-zoomStep); }
            if (event.key === '0') { event.preventDefault(); reset(); }
        });
    });
</script>
