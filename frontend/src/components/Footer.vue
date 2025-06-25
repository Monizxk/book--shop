<template>
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-item">
        <i class="fas fa-phone-alt"></i> {{ contacts.phone }}
      </div>
      <div class="footer-item">
        <i class="fab fa-viber"></i> Viber: {{ contacts.viber }}
      </div>
      <div class="footer-item">
        <i class="fas fa-envelope"></i> {{ contacts.email }}
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const contacts = ref({
  phone: '+380 (63) 755-42-70',
  viber: '+380 (63) 755-42-70',
  email: 'bookseller.in.ua@gmail.com'
})

async function fetchContacts() {
  try {
    const response = await fetch('http://localhost:8000/api/settings/contacts')
    const data = await response.json()
    contacts.value = data
  } catch (error) {
    // fallback: дефолтные значения уже заданы
  }
}

onMounted(() => {
  fetchContacts()
})
</script>

<style>

.footer {
  background-color: #f5f5f5;
  padding: 20px 10px;
  text-align: center;
  color: #333;
  font-size: 14px;
  margin-top: 30px;
  border-top: 1px solid #ddd;
}

.footer-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.footer-item i {
  margin-right: 8px;
  color: #088178;
}

</style>