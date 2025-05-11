// api/cart.js
export const cart = {
  items: JSON.parse(localStorage.getItem('cart') || '[]'),

  add(product) {
    const item = this.items.find(p => p.id === product.id)
    if (item) {
      item.quantity++
    } else {
      this.items.push({ 
        id: product.id,
        title: product.title,
        price: product.price,
        images: product.images || [],
        quantity: 1 
      })
    }
    this.save()
  },

  remove(productId) {
    this.items = this.items.filter(p => p.id !== productId)
    this.save()
  },

  updateQuantity(productId, quantity) {
    const item = this.items.find(p => p.id === productId)
    if (item) {
      item.quantity = Math.max(1, quantity)
      this.save()
    }
  },

  clear() {
    this.items = []
    this.save()
  },

  save() {
    localStorage.setItem('cart', JSON.stringify(this.items))
  },

  get total() {
    return this.items.reduce((sum, p) => sum + p.price * p.quantity, 0)
  }
}