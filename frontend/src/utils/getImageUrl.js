function getImageUrl(imagePath) {
    if (!imagePath) return ''
    return `http://localhost:8000/storage/${imagePath}`
}