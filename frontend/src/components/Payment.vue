<script setup>
import Footer from "./Footer.vue"

</script>

<template>
  <div>
    <div class="delivery-page">
      <section class="product-spacer">
        <div class="delivery-header">
          <h1 class="main-title">Оплата в інтернет-магазині «BookSeller»</h1>
        </div>

        <div class="delivery-options">
          <div class="delivery-option">
            <div class="option-header">
              <div class="option-icon">📦</div>
              <h3>Нова Пошта (відділення або поштомат)</h3>
            </div>
            <div class="option-content">
              <div class="option-features">
              </div>
              <h4>Оплата карткою</h4>
              <p class="option-description">
                Оплата платіжною карткою Visa / Mastercard (Без комісії)
              </p>
              <p class="option-description">
                Надсилаємо посилання для оплати, після чого Ви отримуєте чек
              </p>
              <h4>Банківський переказ</h4>
              <p class="option-description">
                Безготівковий переказ (за IBAN) згідно рахунку.
              </p>
              <p class="option-description">
                Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.
              </p>
              <h4>Оплата при отриманні</h4>
              <p class="option-description">
                Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми
              </p>
              <p class="option-description">
                Зверніть увагу, що доставка при накладеному платежі є платною!
              </p>
              <p class="pricing-info">
                <strong>Для оплати при отриманні потрібно вести 20% від суми</strong>
              </p>
            </div>
          </div>

          <div class="delivery-options">
            <div class="delivery-option">
              <div class="option-header">
                <div class="option-icon">📦</div>
                <h3>Нова Пошта (відділення або поштомат)</h3>
              </div>
              <div class="option-content">
                <div class="option-features">
                </div>
                <h4>Оплата карткою</h4>
                <p class="option-description">
                  Оплата платіжною карткою Visa / Mastercard (Без комісії)
                </p>
                <p class="option-description">
                  Надсилаємо посилання для оплати, після чого Ви отримуєте чек
                </p>
                <h4>Банківський переказ</h4>
                <p class="option-description">
                  Безготівковий переказ (за IBAN) згідно рахунку.
                </p>
                <p class="option-description">
                  Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.
                </p>
                <h4>Оплата при отриманні</h4>
                <p class="option-description">
                  Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми
                </p>
                <p class="option-description">
                  Зверніть увагу, що доставка при накладеному платежі є платною!
                </p>
                <p class="pricing-info">
                  <strong>Для оплати при отриманні потрібно вести 20% від суми</strong>
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="contact-info">
          <p>
            <strong>Маєте питання?</strong> Зв'яжіться з нами, і ми з радістю допоможемо!
          </p>
        </div>
      </section>
    </div>
    <Footer/>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');


.product-spacer {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 16px;
  padding: 20px;
  font-family: 'Inter', sans-serif;
  color: #2d3748;
}

/* Header Section */
.delivery-header {
  text-align: center;
  margin-bottom: 40px;
}

.main-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 5px;
  background: black;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.main-description {
  font-size: 1.2rem;
  color: #4a5568;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}

/* Free Delivery Banner */
.free-delivery-banner {
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
}

.banner-icon {
  font-size: 3rem;
  margin-right: 20px;
}

.banner-content h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 8px;
}

.banner-content p {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* Delivery Options */
.delivery-options {
  display: grid;
  gap: 24px;
  margin-bottom: 20px;
}

.delivery-option {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  background: #fafafa;
}

.delivery-option:hover {
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.option-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.option-icon {
  font-size: 2rem;
  margin-right: 16px;
  padding: 12px;
  background: linear-gradient(135deg, #33bdd5 0%, #088178 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
}

.option-header h3 {
  font-size: 1.4rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.option-features {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.feature-tag {
  background: #e6fffa;
  color: #00b5ad;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 0.85rem;
  font-weight: 500;
}

.option-description {
  font-size: 1rem;
  line-height: 1.6;
  color: #4a5568;
  margin-bottom: 12px;
}

.pricing-info {
  font-size: 1rem;
  color: #2d3748;
  padding: 12px;
  background: #f7fafc;
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

/* Additional Info */
.additional-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 40px;
}

.info-card {
  background: #f8f9fa;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.info-card h4 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-card ol,
.info-card ul {
  margin: 0;
  padding-left: 20px;
}

.info-card li {
  margin-bottom: 8px;
  line-height: 1.5;
  color: #4a5568;
}


.contact-info p {
  font-size: 1.1rem;
  color: #000000;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {

  .product-spacer {
    width: 500px;
  }

  .delivery-option {
    width: 220px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }


}
@media (min-width: 380px) and (max-width: 768px) {

  .product-spacer {
    width: 400px;
  }

  .delivery-option {
    width: 350px;
  }

  .info-card {
    width: 350px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }
}

@media (min-width: 320px) and (max-width: 380px) {

  .product-spacer {
    width: 300px;
    max-width: 1400px;
  }

  .delivery-option {
    width: 260px;
  }

  .info-card {
    width: 260px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }
}

</style>