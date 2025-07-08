import { ref } from 'vue'

const expandedKeys = ref({})

export const useCategories = () => {
    const collapseCategories = () => {
        expandedKeys.value = {}
    }

    return {
        expandedKeys,
        collapseCategories
    }
}