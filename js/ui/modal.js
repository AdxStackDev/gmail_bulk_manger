/**
 * UI: modal dialogs (alert / confirm).
 *
 * Provides showAlert() and showConfirm() backed by a single dynamically
 * created modal element. Loaded as a classic script; functions are global.
 */

function showModal(title, message, onConfirm, isConfirm = false) {
    // Check if modal container exists, if not create it
    let modal = document.getElementById('customModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'customModal';
        modal.className = 'fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 hidden';
        modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-96 transform transition-all scale-100">
                <h3 id="customModalTitle" class="text-lg font-bold text-gray-900 dark:text-white mb-2"></h3>
                <p id="customModalMessage" class="text-gray-600 dark:text-gray-300 mb-6"></p>
                <div class="flex justify-end gap-3">
                    <button id="customModalCancel" class="px-4 py-2 rounded text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                    <button id="customModalConfirm" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">Okay</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const titleEl = document.getElementById('customModalTitle');
    const msgEl = document.getElementById('customModalMessage');
    const confirmBtn = document.getElementById('customModalConfirm');
    const cancelBtn = document.getElementById('customModalCancel');

    titleEl.textContent = title;
    msgEl.textContent = message;

    if (!isConfirm) {
        cancelBtn.classList.add('hidden');
        confirmBtn.textContent = 'Okay';
    } else {
        cancelBtn.classList.remove('hidden');
        confirmBtn.textContent = 'Confirm';
    }

    modal.classList.remove('hidden');

    // Clean up old listeners
    const newConfirm = confirmBtn.cloneNode(true);
    const newCancel = cancelBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirm, confirmBtn);
    cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);

    newConfirm.onclick = () => {
        modal.classList.add('hidden');
        if (onConfirm) onConfirm();
    };

    newCancel.onclick = () => {
        modal.classList.add('hidden');
    };
}

function showAlert(title, message) {
    showModal(title, message, null, false);
}

function showConfirm(title, message, onConfirm) {
    showModal(title, message, onConfirm, true);
}
