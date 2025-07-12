import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export function useBreadcrumbs() {
    const route = useRoute()
    const router = useRouter()

    const categories = ref([])
    const breadcrumbItems = ref([])

    // Знаходження категорії за ID
    const findCategoryById = (categories, id) => {
        return categories.find(cat => cat.id === parseInt(id))
    }

    // Побудова шляху від кореня до поточної категорії
    const buildCategoryPath = (category, allCategories) => {
        const path = []
        let current = category

        while (current) {
            path.unshift({
                id: current.id,
                name: current.name,
                slug: current.slug || current.id
            })

            if (current.parent_id) {
                current = findCategoryById(allCategories, current.parent_id)
            } else {
                current = null
            }
        }

        return path
    }

    // Оновлення breadcrumbs
    const updateBreadcrumbs = (category) => {
        if (!category) {
            breadcrumbItems.value = [
                { title: 'Головна', disabled: false, href: '/' },
                { title: 'Каталог', disabled: true, href: '/category' }
            ]
            return
        }

        const path = buildCategoryPath(category, categories.value)

        const pathItems = path.map((item, index) => {
            const isLast = index === path.length - 1

            const href = isLast
                ? ''
                : '/category/' + path.slice(0, index + 1).map(p => p.slug || p.id).join('/')

            return {
                title: item.name,
                disabled: isLast,
                href,
                categoryId: item.id
            }
        })

        breadcrumbItems.value = [
            { title: 'Головна', disabled: false, href: '/' },
            { title: 'Каталог', disabled: false, href: '/category' },
            ...pathItems
        ]
    }

    // Оновлення breadcrumbs на основі поточного маршруту
    const updateBreadcrumbsFromRoute = () => {
        const categoryId = route.params.categoryId || route.params.id

        if (categoryId) {
            const category = findCategoryById(categories.value, categoryId)
            updateBreadcrumbs(category)
        } else {
            updateBreadcrumbs(null)
        }
    }

    // Computed для поточної категорії
    const currentCategory = computed(() => {
        const categoryId = route.params.categoryId || route.params.id
        return categoryId ? findCategoryById(categories.value, categoryId) : null
    })

    return {
        categories,
        breadcrumbItems,
        currentCategory,
        updateBreadcrumbs,
        updateBreadcrumbsFromRoute,
        findCategoryById,
        buildCategoryPath
    }
}