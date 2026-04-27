import { ref } from 'vue'

const show = ref(false)
const options = ref({})
let resolvePromise

export function useConfirm() {
    const confirm = (opts) => {
        options.value = opts
        show.value = true

        return new Promise((resolve) => {
            resolvePromise = resolve
        })
    }

    const confirmAction = () => {
        show.value = false
        resolvePromise(true)
    }

    const cancelAction = () => {
        show.value = false
        resolvePromise(false)
    }

    return {
        show,
        options,
        confirm,
        confirmAction,
        cancelAction
    }
}
