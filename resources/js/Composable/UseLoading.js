import { ref } from 'vue'

const isLoading = ref(false)
const message = ref("")

export function useLoading() {
    const loading = async (msg, fn) => {
        isLoading.value = true
        message.value = msg

        try {
            await fn()
        } finally {
            isLoading.value = false
        }
    }

    return {
        loading,
        isLoading,
        message
    }
}
