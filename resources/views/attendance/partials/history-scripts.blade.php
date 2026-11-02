<link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
<script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
<script>
    (function () {
        const toggleButton = document.getElementById('attendanceHistoryFilterToggle');
        const filterPanel = document.getElementById('attendanceHistoryFilterPanel');
        const rangeInput = document.getElementById('attendance_date_range');
        const startInput = document.getElementById('attendance_date_start');
        const endInput = document.getElementById('attendance_date_end');

        // Toggle Filter Panel
        if (toggleButton && filterPanel) {
            toggleButton.addEventListener('click', function () {
                const isHidden = filterPanel.style.display === 'none';
                filterPanel.style.display = isHidden ? '' : 'none';
                toggleButton.classList.toggle('active', isHidden);
                toggleButton.setAttribute('aria-expanded', String(isHidden));
            });
        }

        // Flatpickr Kalender Bahasa Indonesia
        if (rangeInput && typeof flatpickr === 'function') {
            const indonesianLocale = {
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                },
                months: {
                    shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                },
                firstDayOfWeek: 1,
                rangeSeparator: ' sampai ',
            };

            function syncHiddenDates(selectedDates, dateStr, instance) {
                if (selectedDates.length === 0) {
                    return;
                }
                const first = selectedDates[0];
                const last = selectedDates[selectedDates.length - 1];
                startInput.value = instance.formatDate(first, 'Y-m-d');
                endInput.value = instance.formatDate(last, 'Y-m-d');
            }

            flatpickr(rangeInput, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'j M Y',
                locale: indonesianLocale,
                maxDate: rangeInput.dataset.maxDate,
                defaultDate: [rangeInput.dataset.dateStart, rangeInput.dataset.dateEnd],
                disableMobile: true,
                onChange: syncHiddenDates,
                onClose: syncHiddenDates,
            });
        }

        // Modal Bukti Kehadiran (Sesuai Modal /hr/attendances)
        const modal = document.getElementById('attendance-evidence-modal');
        const modalDate = document.getElementById('attendance-evidence-date');
        const modalBody = document.getElementById('attendance-evidence-modal-body');

        if (!modal || !modalBody) {
            return;
        }

        function openEvidenceModal(key, focusSide) {
            const template = document.getElementById(`evidence-template-${key}`);
            if (!template) {
                return;
            }

            if (modalDate) {
                modalDate.textContent = template.dataset.date || '';
            }

            modalBody.replaceChildren(template.content.cloneNode(true));

            // Load Google Maps on demand
            modalBody.querySelectorAll('iframe[data-src]').forEach(function (iframe) {
                if (!iframe.src) {
                    iframe.src = iframe.dataset.src;
                }
            });

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Jika user menekan Clock Out di mobile, scroll ke panel Clock Out
            if (focusSide === 'out') {
                const outPanel = modalBody.querySelector(`#evidence-panel-out-${key}`);
                if (outPanel) {
                    setTimeout(function () {
                        outPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 120);
                }
            }
        }

        function closeEvidenceModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-open-evidence]');
            if (trigger) {
                event.preventDefault();
                openEvidenceModal(trigger.dataset.openEvidence, trigger.dataset.focusSide || 'in');
                return;
            }

            // Close modal via data-modal-close or clicking backdrop
            if (modal.style.display === 'flex') {
                if (event.target === modal || event.target.closest('#attendance-evidence-modal [data-modal-close="true"]')) {
                    closeEvidenceModal();
                }
            }
        });

        // Close via ESC key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.style.display === 'flex') {
                closeEvidenceModal();
            }
        });
    })();
</script>
