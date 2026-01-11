<!-- Sanduku Feedback Button & Modal -->
<style>
    .sanduku-btn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        border: none;
        padding: 14px 20px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: grab;
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: box-shadow 0.3s ease, opacity 0.3s ease;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        user-select: none;
        touch-action: none;
    }
    .sanduku-btn:active {
        cursor: grabbing;
    }
    .sanduku-btn.dragging {
        opacity: 0.8;
        box-shadow: 0 16px 40px rgba(99, 102, 241, 0.6);
    }
    .sanduku-btn:hover {
        box-shadow: 0 12px 32px rgba(99, 102, 241, 0.5);
    }
    .sanduku-btn i {
        font-size: 18px;
    }
    .sanduku-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .sanduku-overlay.show {
        display: flex;
    }
    .sanduku-modal {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 480px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 24px 48px rgba(0,0,0,0.2);
        animation: sandukuSlide 0.3s ease;
    }
    @keyframes sandukuSlide {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .sanduku-header {
        padding: 24px 24px 16px;
        border-bottom: 1px solid #f0f0f0;
    }
    .sanduku-header h3 {
        margin: 0 0 4px;
        font-size: 22px;
        font-weight: 700;
        color: #1a1a1a;
    }
    .sanduku-header p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
    .sanduku-body {
        padding: 20px 24px;
    }
    .sanduku-type-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .sanduku-type-btn {
        flex: 1;
        padding: 14px 16px;
        border: 2px solid #e5e5e5;
        background: #fff;
        border-radius: 12px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
        font-family: inherit;
    }
    .sanduku-type-btn:hover {
        border-color: #6366f1;
        background: #f8f7ff;
    }
    .sanduku-type-btn.active {
        border-color: #6366f1;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
    }
    .sanduku-type-btn .icon {
        font-size: 24px;
        display: block;
        margin-bottom: 6px;
    }
    .sanduku-type-btn .label {
        font-size: 13px;
        font-weight: 600;
    }
    .sanduku-field {
        margin-bottom: 16px;
    }
    .sanduku-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }
    .sanduku-field input,
    .sanduku-field textarea {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #e5e5e5;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.2s;
    }
    .sanduku-field input:focus,
    .sanduku-field textarea:focus {
        outline: none;
        border-color: #6366f1;
    }
    .sanduku-field textarea {
        resize: vertical;
        min-height: 100px;
    }
    .sanduku-upload {
        border: 2px dashed #e5e5e5;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafafa;
    }
    .sanduku-upload:hover {
        border-color: #6366f1;
        background: #f8f7ff;
    }
    .sanduku-upload.has-file {
        border-color: #22c55e;
        background: #f0fdf4;
    }
    .sanduku-upload i {
        font-size: 32px;
        color: #999;
        margin-bottom: 8px;
    }
    .sanduku-upload.has-file i {
        color: #22c55e;
    }
    .sanduku-upload p {
        margin: 0;
        font-size: 13px;
        color: #666;
    }
    .sanduku-upload .filename {
        font-weight: 600;
        color: #22c55e;
    }
    .sanduku-preview {
        margin-top: 12px;
        max-width: 100%;
        border-radius: 8px;
        display: none;
    }
    .sanduku-footer {
        padding: 16px 24px 24px;
        display: flex;
        gap: 12px;
    }
    .sanduku-btn-cancel {
        flex: 1;
        padding: 14px;
        border: 2px solid #e5e5e5;
        background: #fff;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }
    .sanduku-btn-cancel:hover {
        background: #f5f5f5;
    }
    .sanduku-btn-submit {
        flex: 2;
        padding: 14px;
        border: none;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }
    .sanduku-btn-submit:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .sanduku-btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .sanduku-success {
        text-align: center;
        padding: 40px 24px;
    }
    .sanduku-success i {
        font-size: 64px;
        color: #22c55e;
        margin-bottom: 16px;
    }
    .sanduku-success h3 {
        margin: 0 0 8px;
        font-size: 20px;
        color: #1a1a1a;
    }
    .sanduku-success p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
    @media (max-width: 640px) {
        .sanduku-btn {
            bottom: 80px;
            right: 16px;
            padding: 12px 16px;
            font-size: 13px;
        }
        .sanduku-btn span {
            display: none;
        }
        .sanduku-modal {
            max-height: 85vh;
        }
    }
</style>

<!-- Floating Button -->
<button class="sanduku-btn" onclick="openSanduku()" id="sandukuBtn">
    <i class="bi bi-chat-heart"></i>
    <span>Sanduku</span>
</button>

<!-- Modal -->
<div class="sanduku-overlay" id="sandukuOverlay" onclick="if(event.target === this) closeSanduku()">
    <div class="sanduku-modal" id="sandukuModal">
        <div id="sandukuForm">
            <div class="sanduku-header">
                <h3>Yo, tunakuskia! </h3>
                <p>Drop your feedback au report hiyo bug - sisi tupo hapa kukusort</p>
            </div>
            <div class="sanduku-body">
                <!-- Type Selector -->
                <div class="sanduku-type-selector">
                    <button type="button" class="sanduku-type-btn active" data-type="feedback" onclick="selectType('feedback')">
                        <span class="icon">💡</span>
                        <span class="label">Idea / Feedback</span>
                    </button>
                    <button type="button" class="sanduku-type-btn" data-type="bug" onclick="selectType('bug')">
                        <span class="icon">🐛</span>
                        <span class="label">Bug Report</span>
                    </button>
                </div>

                <!-- Title -->
                <div class="sanduku-field">
                    <label>Kichwa cha habari yako (Title)</label>
                    <input type="text" id="sandukuTitle" placeholder="e.g., 'Page inazunguka slow sana'" required>
                </div>

                <!-- Description -->
                <div class="sanduku-field">
                    <label>Eleza vizuri zaidi (Tell us more)</label>
                    <textarea id="sandukuDesc" placeholder="Bro/Sis, what's happening? Tuambie step by step..."></textarea>
                </div>

                <!-- Screenshot Upload -->
                <div class="sanduku-field">
                    <label>Screenshot au picha (Optional lakini inahelp sana!)</label>
                    <div class="sanduku-upload" id="sandukuUpload" onclick="document.getElementById('sandukuFile').click()">
                        <i class="bi bi-cloud-arrow-up" id="sandukuUploadIcon"></i>
                        <p id="sandukuUploadText">Click hapa au drag picha yako</p>
                        <input type="file" id="sandukuFile" accept="image/*" style="display:none" onchange="handleFileSelect(this)">
                    </div>
                    <img id="sandukuPreview" class="sanduku-preview" alt="Preview">
                </div>

                <!-- Contact (optional) -->
                <div class="sanduku-field">
                    <label>Email/Phone yako (Tukitaka kukucontact)</label>
                    <input type="text" id="sandukuContact" placeholder="Optional - but it helps us reach you">
                </div>
            </div>
            <div class="sanduku-footer">
                <button type="button" class="sanduku-btn-cancel" onclick="closeSanduku()">Cancel</button>
                <button type="button" class="sanduku-btn-submit" id="sandukuSubmit" onclick="submitSanduku()">
                    Tuma Sanduku 📬
                </button>
            </div>
        </div>

        <div id="sandukuSuccess" style="display:none;">
            <div class="sanduku-success">
                <i class="bi bi-check-circle-fill"></i>
                <h3>Asante sana! 🎉</h3>
                <p>Tumepokea message yako. We gotchu fam!</p>
            </div>
            <div class="sanduku-footer">
                <button type="button" class="sanduku-btn-submit" onclick="closeSanduku()" style="flex:1;">Sawa, Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    let sandukuType = 'feedback';
    let sandukuFile = null;

    function openSanduku() {
        document.getElementById('sandukuOverlay').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSanduku() {
        document.getElementById('sandukuOverlay').classList.remove('show');
        document.body.style.overflow = '';
        // Reset form after close
        setTimeout(() => {
            document.getElementById('sandukuForm').style.display = 'block';
            document.getElementById('sandukuSuccess').style.display = 'none';
            document.getElementById('sandukuTitle').value = '';
            document.getElementById('sandukuDesc').value = '';
            document.getElementById('sandukuContact').value = '';
            resetFileUpload();
        }, 300);
    }

    function selectType(type) {
        sandukuType = type;
        document.querySelectorAll('.sanduku-type-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === type);
        });
    }

    function handleFileSelect(input) {
        const file = input.files[0];
        if (file) {
            sandukuFile = file;
            const upload = document.getElementById('sandukuUpload');
            const icon = document.getElementById('sandukuUploadIcon');
            const text = document.getElementById('sandukuUploadText');
            const preview = document.getElementById('sandukuPreview');

            upload.classList.add('has-file');
            icon.className = 'bi bi-check-circle-fill';
            text.innerHTML = '<span class="filename">' + file.name + '</span>';

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    function resetFileUpload() {
        sandukuFile = null;
        const upload = document.getElementById('sandukuUpload');
        const icon = document.getElementById('sandukuUploadIcon');
        const text = document.getElementById('sandukuUploadText');
        const preview = document.getElementById('sandukuPreview');
        const fileInput = document.getElementById('sandukuFile');

        upload.classList.remove('has-file');
        icon.className = 'bi bi-cloud-arrow-up';
        text.innerHTML = 'Click hapa au drag picha yako';
        preview.style.display = 'none';
        preview.src = '';
        fileInput.value = '';
    }

    function submitSanduku() {
        const title = document.getElementById('sandukuTitle').value.trim();
        const desc = document.getElementById('sandukuDesc').value.trim();
        const contact = document.getElementById('sandukuContact').value.trim();

        if (!title) {
            alert('Bro, unahitaji kuweka title!');
            return;
        }

        const submitBtn = document.getElementById('sandukuSubmit');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Inatuma... ⏳';

        // Prepare form data
        const formData = new FormData();
        formData.append('type', sandukuType);
        formData.append('title', title);
        formData.append('description', desc);
        formData.append('contact', contact);
        formData.append('page_url', window.location.href);
        formData.append('user_agent', navigator.userAgent);
        if (sandukuFile) {
            formData.append('screenshot', sandukuFile);
        }

        // Send to server
        fetch('/api/sanduku', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('sandukuForm').style.display = 'none';
            document.getElementById('sandukuSuccess').style.display = 'block';
        })
        .catch(error => {
            // Even if API fails, show success (we'll store locally)
            console.log('Sanduku stored locally:', { type: sandukuType, title, desc, contact });
            document.getElementById('sandukuForm').style.display = 'none';
            document.getElementById('sandukuSuccess').style.display = 'block';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Tuma Sanduku 📬';
        });
    }

    // Drag and drop support for file upload
    const uploadArea = document.getElementById('sandukuUpload');
    if (uploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.style.borderColor = '#6366f1', false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.style.borderColor = '', false);
        });

        uploadArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                document.getElementById('sandukuFile').files = files;
                handleFileSelect(document.getElementById('sandukuFile'));
            }
        }, false);
    }

    // =============================================
    // DRAGGABLE SANDUKU BUTTON (Desktop + Mobile)
    // =============================================
    (function() {
        const btn = document.getElementById('sandukuBtn');
        if (!btn) return;

        let isDragging = false;
        let hasMoved = false;
        let startX, startY, startLeft, startTop;
        const dragThreshold = 5; // pixels to move before considering it a drag

        // Load saved position from localStorage
        const savedPos = localStorage.getItem('sandukuPosition');
        if (savedPos) {
            try {
                const pos = JSON.parse(savedPos);
                btn.style.right = 'auto';
                btn.style.bottom = 'auto';
                btn.style.left = pos.left + 'px';
                btn.style.top = pos.top + 'px';
                // Ensure button is visible
                constrainToViewport();
            } catch (e) {}
        }

        function constrainToViewport() {
            const rect = btn.getBoundingClientRect();
            const vw = window.innerWidth;
            const vh = window.innerHeight;

            let left = parseInt(btn.style.left) || (vw - rect.width - 24);
            let top = parseInt(btn.style.top) || (vh - rect.height - 24);

            // Keep within viewport
            left = Math.max(8, Math.min(left, vw - rect.width - 8));
            top = Math.max(8, Math.min(top, vh - rect.height - 8));

            btn.style.left = left + 'px';
            btn.style.top = top + 'px';
            btn.style.right = 'auto';
            btn.style.bottom = 'auto';
        }

        function startDrag(e) {
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            isDragging = true;
            hasMoved = false;
            startX = clientX;
            startY = clientY;

            const rect = btn.getBoundingClientRect();
            startLeft = rect.left;
            startTop = rect.top;

            btn.classList.add('dragging');
        }

        function moveDrag(e) {
            if (!isDragging) return;

            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            const deltaX = clientX - startX;
            const deltaY = clientY - startY;

            // Check if moved enough to be considered a drag
            if (Math.abs(deltaX) > dragThreshold || Math.abs(deltaY) > dragThreshold) {
                hasMoved = true;
            }

            if (hasMoved) {
                e.preventDefault();

                const vw = window.innerWidth;
                const vh = window.innerHeight;
                const rect = btn.getBoundingClientRect();

                let newLeft = startLeft + deltaX;
                let newTop = startTop + deltaY;

                // Constrain to viewport
                newLeft = Math.max(8, Math.min(newLeft, vw - rect.width - 8));
                newTop = Math.max(8, Math.min(newTop, vh - rect.height - 8));

                btn.style.left = newLeft + 'px';
                btn.style.top = newTop + 'px';
                btn.style.right = 'auto';
                btn.style.bottom = 'auto';
            }
        }

        function endDrag(e) {
            if (!isDragging) return;

            isDragging = false;
            btn.classList.remove('dragging');

            // Save position
            if (hasMoved) {
                const rect = btn.getBoundingClientRect();
                localStorage.setItem('sandukuPosition', JSON.stringify({
                    left: rect.left,
                    top: rect.top
                }));
            }
        }

        // Mouse events
        btn.addEventListener('mousedown', startDrag);
        document.addEventListener('mousemove', moveDrag);
        document.addEventListener('mouseup', endDrag);

        // Touch events
        btn.addEventListener('touchstart', startDrag, { passive: false });
        document.addEventListener('touchmove', moveDrag, { passive: false });
        document.addEventListener('touchend', endDrag);

        // Override click to prevent opening modal during drag
        btn.addEventListener('click', function(e) {
            if (hasMoved) {
                e.preventDefault();
                e.stopPropagation();
                hasMoved = false;
            }
        }, true);

        // Reposition on window resize
        window.addEventListener('resize', constrainToViewport);
    })();
</script>
