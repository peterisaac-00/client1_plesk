/**
 * نظام إدارة المستندات والتحقق عبر QR — JavaScript v2
 */
(function () {
    'use strict';

    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ===== الإشعارات (Toasts) =====
    var ICONS = {
        success: 'bi-check-circle-fill',
        danger: 'bi-exclamation-triangle-fill',
        warning: 'bi-exclamation-circle-fill',
        info: 'bi-info-circle-fill'
    };

    function showToast(message, type) {
        var container = document.getElementById('flashToasts');
        if (!container) return;
        type = type || 'success';
        var icon = ICONS[type] || ICONS.success;

        var el = document.createElement('div');
        el.className = 'toast-custom toast-' + type;
        el.setAttribute('role', 'status');
        el.innerHTML =
            '<span class="toast-icon"><i class="bi ' + icon + '"></i></span>' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="toast-close" aria-label="إغلاق"><i class="bi bi-x-lg"></i></button>';
        container.appendChild(el);

        var close = function () {
            if (prefersReduced) { el.remove(); return; }
            el.classList.add('leaving');
            setTimeout(function () { el.remove(); }, 150);
        };

        el.querySelector('.toast-close').addEventListener('click', close);
        setTimeout(close, 4200);
    }
    window.showToast = showToast;

    function initServerToasts() {
        document.querySelectorAll('#flashToasts .toast-server').forEach(function (el) {
            var type = el.dataset.type || 'success';
            var message = el.dataset.message || '';
            if (!message) return;
            setTimeout(function () { showToast(message, type); }, 80);
        });
    }

    // ===== الشريط الجانبي (Drawer) =====
    function initSidebar() {
        var sidebar = document.getElementById('adminSidebar');
        var backdrop = document.getElementById('sidebarBackdrop');
        var toggle = document.getElementById('sidebarToggle');
        if (!sidebar || !backdrop) return;

        function openSidebar() {
            sidebar.classList.add('open');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
        if (toggle) toggle.addEventListener('click', openSidebar);
        backdrop.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 991.98) closeSidebar();
        });
    }

    // ===== نوافذ التأكيد =====
    function initConfirmDialogs() {
        var modalEl = document.getElementById('confirmModal');
        if (!modalEl) return;

        var titleEl = modalEl.querySelector('.confirm-title');
        var messageEl = modalEl.querySelector('.confirm-message');
        var iconEl = modalEl.querySelector('.confirm-icon');
        var cancelBtn = modalEl.querySelector('[data-confirm-cancel]');
        var submitBtn = modalEl.querySelector('[data-confirm-submit]');
        var pendingForm = null;

        var modal = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('hidden.bs.modal', function () {
            pendingForm = null;
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        });

        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            var onSubmit = function (e) {
                e.preventDefault();
                pendingForm = form;
                var variant = form.dataset.confirmVariant || 'danger';
                var submitLabel = form.dataset.confirmSubmit || 'نعم، متأكد';

                titleEl.textContent = form.dataset.confirm || 'تأكيد العملية';
                messageEl.textContent = form.dataset.message || 'هل أنت متأكد من تنفيذ هذه العملية؟';

                iconEl.className = 'confirm-icon confirm-icon-' + (variant === 'success' ? 'success' : variant === 'warning' ? 'warning' : 'danger');
                iconEl.innerHTML = '<i class="bi ' +
                    (variant === 'success' ? 'bi-check-circle' : variant === 'warning' ? 'bi-exclamation-triangle' : 'bi-trash') + '"></i>';

                submitBtn.className = 'btn btn-' + (variant === 'success' ? 'primary' : 'danger') + ' px-4';
                submitBtn.innerHTML = '<span class="btn-label">' + submitLabel + '</span>';
                submitBtn.disabled = false;

                modal.show();
            };
            form.addEventListener('submit', onSubmit);
            form._confirmOnSubmit = onSubmit;
        });

        if (cancelBtn) cancelBtn.addEventListener('click', function () { modal.hide(); });

        submitBtn.addEventListener('click', function () {
            if (!pendingForm) return;
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            var label = submitBtn.querySelector('.btn-label');
            if (label) label.textContent = 'جاري التنفيذ...';
            if (pendingForm._confirmOnSubmit) {
                pendingForm.removeEventListener('submit', pendingForm._confirmOnSubmit);
            }
            pendingForm.submit();
        });
    }

    // ===== إظهار / إخفاء كلمة المرور =====
    function initPasswordToggles() {
        document.querySelectorAll('.password-wrap input[type="password"], .password-wrap input[type="text"].password-input').forEach(function (input) {
            var wrap = input.closest('.password-wrap');
            if (!wrap) return;
            var toggle = wrap.querySelector('.password-toggle');
            if (!toggle) return;
            toggle.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                toggle.innerHTML = '<i class="bi bi-' + (show ? 'eye-slash' : 'eye') + '"></i>';
                toggle.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
                input.focus();
            });
        });
    }

    // ===== نموذج الرفع: Drag & Drop + معاينة =====
    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return bytes + ' B';
    }

    function fileTypeLabel(file) {
        var ext = (file.name.split('.').pop() || '').toUpperCase();
        if (ext === 'PDF') return 'PDF';
        if (['JPG', 'JPEG'].indexOf(ext) > -1) return 'صورة JPG';
        if (ext === 'PNG') return 'صورة PNG';
        return file.type || ext || 'ملف';
    }

    function initUploadForm() {
        var fileInput = document.getElementById('document');
        var dropzone = document.getElementById('dropzone');
        var dropzoneInner = document.getElementById('dropzoneInner');
        var preview = document.getElementById('uploadPreview');
        var fileName = document.getElementById('fileName');
        var fileSize = document.getElementById('fileSize');
        var fileType = document.getElementById('fileType');
        var removeBtn = document.getElementById('uploadRemove');
        var maxSize = fileInput ? parseInt(fileInput.dataset.maxSize || '20971520', 10) : 20971520;

        if (!fileInput || !dropzone) return;

        function renderPreview() {
            if (fileInput.files.length === 0) {
                if (dropzoneInner) dropzoneInner.classList.remove('d-none');
                if (preview) preview.classList.add('d-none');
                return;
            }
            var file = fileInput.files[0];
            if (file.size > maxSize) {
                showToast('حجم الملف يتجاوز الحد المسموح (' + formatBytes(maxSize) + ').', 'danger');
                fileInput.value = '';
                renderPreview();
                return;
            }
            if (dropzoneInner) dropzoneInner.classList.add('d-none');
            if (preview) preview.classList.remove('d-none');
            if (fileName) fileName.textContent = file.name;
            if (fileSize) fileSize.textContent = formatBytes(file.size) + ' — ' + fileTypeLabel(file);
            if (fileType) fileType.textContent = fileTypeLabel(file);
        }

        fileInput.addEventListener('change', renderPreview);

        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                fileInput.value = '';
                renderPreview();
            });
        }

        ['dragenter', 'dragover'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });
        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                renderPreview();
            }
        });

        var form = document.getElementById('uploadForm');
        var btn = document.getElementById('uploadBtn');
        if (form && btn) {
            form.addEventListener('submit', function () {
                if (form.checkValidity() && fileInput.files.length > 0) {
                    btn.disabled = true;
                    btn.classList.add('loading');
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري رفع المستند...';
                }
            });
        }
    }

    // ===== نسخ رابط التحقق =====
    function initCopyButtons() {
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var text = btn.getAttribute('data-copy') || '';
                if (!text) {
                    var source = document.getElementById(btn.dataset.copySource || 'verifyLink');
                    if (source) text = source.value || source.textContent.trim() || '';
                }
                var done = function () {
                    showToast('تم نسخ رابط التحقق بنجاح.', 'success');
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
                } else {
                    fallbackCopy(text, done);
                }
            });
        });
    }

    function fallbackCopy(text, done) {
        var input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); done(); } catch (e) { showToast('تعذر النسخ، انسخ الرابط يدويًا.', 'warning'); }
        input.remove();
    }

    // ===== إخفاء شاشة تحميل عارض PDF =====
    function initViewerLoader() {
        var iframe = document.querySelector('.doc-iframe');
        var loader = document.getElementById('viewerLoading');
        if (!iframe || !loader) return;
        var hideLoader = function () {
            loader.style.display = 'none';
        };
        if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') {
            hideLoader();
        } else {
            iframe.addEventListener('load', hideLoader);
            setTimeout(hideLoader, 9000);
        }
    }

    // ===== طباعة رمز QR =====
    function initPrintButtons() {
        document.querySelectorAll('[data-print]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                window.print();
            });
        });
    }

    // ===== حالات تحميل النماذج العامة =====
    function initFormLoading() {
        document.querySelectorAll('form[data-loading]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) return;
                btn.disabled = true;
                btn.classList.add('loading');
                var label = btn.querySelector('.btn-label') || btn;
                var original = label.innerHTML;
                label.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري الحفظ...';
                btn.dataset.originalLabel = original;
            });
        });
    }

    // ===== استبدال ملف المستند (إرسال تلقائي عند اختيار ملف) =====
    function initReplaceForms() {
        document.querySelectorAll('form[data-replace-form] input[type="file"]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (input.files && input.files.length > 0) {
                    var form = input.closest('form');
                    if (!form) return;
                    var icon = form.querySelector('label.btn-icon i');
                    if (icon) { icon.className = 'bi bi-arrow-repeat'; }
                    form.submit();
                }
            });
        });
    }

    // ===== الرسوم البيانية (لوحة التحكم) =====
    function chartColors() {
        var root = getComputedStyle(document.documentElement);
        return {
            primary: root.getPropertyValue('--primary').trim() || '#2b5df0',
            success: root.getPropertyValue('--success').trim() || '#16a34a',
            danger: root.getPropertyValue('--danger').trim() || '#dc2626',
            muted: root.getPropertyValue('--muted').trim() || '#5b6b89',
            border: root.getPropertyValue('--border').trim() || '#e4e9f2'
        };
    }

    function initCharts() {
        if (typeof Chart === 'undefined') return;
        var colors = chartColors();

        // ---- رسم تحقق آخر 7 أيام ----
        var weekEl = document.getElementById('weekChart');
        if (weekEl && weekEl.dataset.labels && weekEl.dataset.values) {
            var labels = JSON.parse(weekEl.dataset.labels);
            var values = JSON.parse(weekEl.dataset.values);
            var hasData = values.some(function (v) { return parseInt(v, 10) > 0; });
            if (!hasData) {
                weekEl.closest('.chart-card').classList.add('chart-empty');
                weekEl.parentElement.innerHTML =
                    '<div class="empty-state py-4">' +
                    '<div class="empty-icon"><i class="bi bi-bar-chart-line"></i></div>' +
                    '<div class="empty-title">لا توجد بيانات تحقق</div>' +
                    '<div class="empty-hint">عند حدوث أول عملية تحقق خلال الأسبوع ستظهر هنا</div>' +
                    '</div>';
                return;
            }
            new Chart(weekEl, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'عمليات التحقق',
                        data: values,
                        backgroundColor: colors.primary + '26',
                        borderColor: colors.primary,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        hoverBackgroundColor: colors.primary + '3d'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            rtl: true,
                            titleFont: { family: 'Cairo', weight: 700 },
                            bodyFont: { family: 'Cairo' },
                            backgroundColor: '#17203a',
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Cairo', size: 11.5 }, color: colors.muted }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { family: 'Cairo', size: 11 }, color: colors.muted },
                            grid: { color: colors.border }
                        }
                    }
                }
            });
        }

        // ---- توزيع حالات المستندات ----
        var statusEl = document.getElementById('statusChart');
        if (statusEl && statusEl.dataset.data) {
            var statusData = JSON.parse(statusEl.dataset.data);
            var total = statusData.reduce(function (a, b) { return a + b; }, 0);
            if (total === 0) {
                statusEl.closest('.chart-card').classList.add('chart-empty');
                statusEl.parentElement.innerHTML =
                    '<div class="empty-state py-4">' +
                    '<div class="empty-icon"><i class="bi bi-pie-chart"></i></div>' +
                    '<div class="empty-title">لا توجد مستندات</div>' +
                    '<div class="empty-hint">ارفع مستنداتك الأولى لتظهر الحالة هنا</div>' +
                    '</div>';
                return;
            }
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: ['مفعل', 'غير مفعل', 'معطل'],
                    datasets: [{
                        data: statusData,
                        backgroundColor: [colors.success, colors.muted, colors.danger],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            rtl: true,
                            titleFont: { family: 'Cairo', weight: 700 },
                            bodyFont: { family: 'Cairo' },
                            backgroundColor: '#17203a',
                            padding: 12,
                            cornerRadius: 10
                        }
                    }
                }
            });
        }
    }

    // ===== لوحة التحميل (Skeleton) للرسوم =====
    function initChartSkeletons() {
        document.querySelectorAll('.chart-skeleton').forEach(function (sk) {
            var card = sk.closest('.chart-card');
            if (!card) return;
            setTimeout(function () { sk.remove(); }, 400);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initServerToasts();
        initSidebar();
        initConfirmDialogs();
        initPasswordToggles();
        initUploadForm();
        initCopyButtons();
        initViewerLoader();
        initFormLoading();
        initReplaceForms();
        initPrintButtons();
        initCharts();
        initChartSkeletons();
    });
})();
