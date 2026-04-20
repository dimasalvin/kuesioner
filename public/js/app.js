/* ============================================
   KUESIONER KLINIK - App JavaScript
   Mobile-first, sesederhana mungkin
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

    /* ================================================================
       STAR RATING
       Struktur HTML:
         .star-rating
           span.star-wrap
             input[type=radio] #id_1 value="1"
             label[for=id_1] ★
           span.star-wrap
             input[type=radio] #id_2 value="2"
             label[for=id_2] ★
           ... dst

       Logic: saat input berubah (change event), render ulang warna
       semua label dalam group berdasarkan nilai input yang checked.
       Tidak ada hover, tidak ada touchstart, tidak ada dataset.
    ================================================================ */

    document.querySelectorAll('.star-rating').forEach(function(group) {
        var inputs = group.querySelectorAll('input[type="radio"]');
        var labels = group.querySelectorAll('label');
        var qItem  = group.closest('.q-item');

        // Render warna bintang sesuai nilai checked saat ini
        function render() {
            var val = 0;
            inputs.forEach(function(inp) {
                if (inp.checked) val = parseInt(inp.value);
            });

            labels.forEach(function(lbl, i) {
                // index label ke-i cocok dengan nilai i+1
                lbl.style.color = (i < val) ? '#F4C842' : '#D0D8E0';
            });

            if (val > 0 && qItem) {
                qItem.classList.add('answered');
                qItem.style.borderColor = '';
                qItem.style.borderLeft  = '';
            }
        }

        // Render awal (untuk back navigation)
        render();

        // Pasang change event di setiap input radio
        inputs.forEach(function(inp) {
            inp.addEventListener('change', function() {
                render();
            });
        });

        // Pasang juga click di label untuk memastikan di iOS Safari
        labels.forEach(function(lbl) {
            lbl.addEventListener('click', function() {
                // Beri sedikit delay agar browser sempat update .checked
                setTimeout(function() { render(); }, 10);
            });
        });
    });


    /* ================================================================
       STEP PROGRESS DOTS
    ================================================================ */
    var progressContainer = document.querySelector('.step-progress');
    if (progressContainer) {
        var currentStep = parseInt(progressContainer.dataset.current || 1);
        var totalSteps  = parseInt(progressContainer.dataset.total   || 6);
        progressContainer.innerHTML = '';
        for (var i = 1; i <= totalSteps; i++) {
            var dot = document.createElement('div');
            dot.classList.add('step-dot');
            if      (i === currentStep) dot.classList.add('active');
            else if (i <  currentStep)  dot.classList.add('done');
            progressContainer.appendChild(dot);
        }
    }


    /* ================================================================
       COMPLAINT TOGGLE
    ================================================================ */
    var yesBtn       = document.getElementById('btn-yes');
    var noBtn        = document.getElementById('btn-no');
    var complaintBox = document.getElementById('complaint-box');
    var hasComplain  = document.getElementById('has_complain');

    if (yesBtn && noBtn) {
        yesBtn.addEventListener('click', function() {
            yesBtn.classList.add('selected');
            noBtn.classList.remove('selected');
            if (complaintBox) complaintBox.classList.add('show');
            if (hasComplain)  hasComplain.value = '1';
        });
        noBtn.addEventListener('click', function() {
            noBtn.classList.add('selected');
            yesBtn.classList.remove('selected');
            if (complaintBox) complaintBox.classList.remove('show');
            if (hasComplain)  hasComplain.value = '0';
        });
        if (hasComplain && hasComplain.value === '1') {
            yesBtn.classList.add('selected');
            if (complaintBox) complaintBox.classList.add('show');
        } else if (hasComplain && hasComplain.value === '0') {
            noBtn.classList.add('selected');
        }
    }


    /* ================================================================
       FORM VALIDATION
       Cek input[type=radio]:checked langsung dari DOM — paling reliable
    ================================================================ */
    var form = document.getElementById('kuesioner-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var valid = true;
            var firstError = null;

            // Cek text / select required
            form.querySelectorAll('input[required], select[required]').forEach(function(inp) {
                if (!inp.value.trim()) {
                    valid = false;
                    inp.style.borderColor = 'var(--coral)';
                    if (!firstError) firstError = inp;
                } else {
                    inp.style.borderColor = '';
                }
            });

            // Cek setiap grup bintang
            form.querySelectorAll('.star-rating[data-required]').forEach(function(group) {
                var checked = group.querySelector('input[type="radio"]:checked');
                var qi = group.closest('.q-item');
                if (!checked) {
                    valid = false;
                    if (qi) {
                        qi.style.borderColor = 'var(--coral)';
                        qi.style.borderLeft  = '4px solid var(--coral)';
                        if (!firstError) firstError = qi;
                    }
                }
            });

            if (!valid) {
                e.preventDefault();
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        form.querySelectorAll('input, select').forEach(function(inp) {
            inp.addEventListener('change', function() {
                inp.style.borderColor = '';
            });
        });
    }


    /* ================================================================
       PAGE LOAD ANIMATION
    ================================================================ */
    document.querySelectorAll('.q-item, .field-group').forEach(function(el, i) {
        el.style.opacity        = '0';
        el.style.transform      = 'translateY(12px)';
        el.style.transition     = 'opacity 0.25s ease, transform 0.25s ease';
        el.style.transitionDelay = (i * 30) + 'ms';
        setTimeout(function() {
            el.style.opacity   = '1';
            el.style.transform = 'translateY(0)';
        }, 50 + i * 30);
    });

});
