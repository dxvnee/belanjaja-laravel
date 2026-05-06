import { reactive } from 'vue'

const state = reactive({
    loading: {
        show: false,
        message: "Memproses..."
    },
    dialog: {
        show: false,
        type: 'success',
        mode: 'alert',
        title: '',
        message: '',
        resolve: null,
        onClose: null
    }
})


export function useFeedback() {
    function showLoading(message = "Memproses") {
        state.loading.show = true
        state.loading.message = message
    }

    function hideLoading() {
        state.loading.show = false
        state.loading.message = ""
    }

    function openDialog(mode, type, title, message, resolveOrCallback = null) {
        state.dialog = { show: true, type, mode, title, message, resolve: null, onClose: null }

        if (mode === 'confirm') state.dialog.resolve = resolveOrCallback
        if (mode === 'alert') state.dialog.onClose = resolveOrCallback
    }

    function closeDialog(answer = null) {
        state.dialog.resolve?.(answer)
        state.dialog.onClose?.()
        state.dialog.show = false
    }

    const showSuccess = (title, message, onClose) => openDialog('alert', 'success', title, message, onClose)
    const showError = (title, message, onClose) => openDialog('alert', 'error', title, message, onClose)
    const showWarning = (title, message, onClose) => openDialog('alert', 'warning', title, message, onClose)
    const showInfo = (title, message, onClose) => openDialog('alert', 'info', title, message, onClose)

    function confirm(title, message) {
        return new Promise((resolve) => {
            openDialog('confirm', 'warning', title, message, resolve)
        })
    }

    return {
        state,
        showLoading,
        hideLoading,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        confirm,
        closeDialog,
    }
}
