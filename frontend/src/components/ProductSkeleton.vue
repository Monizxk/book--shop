<template>
  <div class="book-product skeleton">
    <div class="book-image skeleton-image"></div>
    <div class="book-details">
      <div class="book-title skeleton-text"></div>
      <div class="book-title skeleton-text short"></div>
      <div class="book-price-container">
        <div class="book-price skeleton-text price"></div>
        <div class="book-stock skeleton-text stock"></div>
      </div>
      <div class="buy-button skeleton-button"></div>
    </div>
  </div>
</template>

<style scoped>
.skeleton {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  max-height: 450px;
  display: flex;
  flex-direction: column;
  background-color: white;
  overflow: hidden;
  box-sizing: border-box;
  width: 100%;
  overflow-wrap: break-word;
}

.skeleton-image {
  width: 200px;
  height: 250px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 2s infinite;
  margin: 8px auto;
  border-radius: 4px;
}

.book-details {
  padding: 15px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.skeleton-text {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 2s infinite;
  border-radius: 4px;
  margin-bottom: 10px;
}

.skeleton-text.book-title {
  height: 20px;
  width: 100%;
  margin-bottom: 8px;
}

.skeleton-text.short {
  width: 70%;
  height: 16px;
  margin-bottom: 15px;
}

.book-price-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  margin-bottom: 15px;
}

.skeleton-text.price {
  width: 80px;
  height: 24px;
  margin: 0;
}

.skeleton-text.stock {
  width: 90px;
  height: 16px;
  margin: 0;
}

.skeleton-button {
  width: 100%;
  height: 40px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 2s infinite;
  border-radius: 4px;
  margin: 0;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Pulse effect for variety */
.skeleton:nth-child(even) .skeleton-image,
.skeleton:nth-child(even) .skeleton-text,
.skeleton:nth-child(even) .skeleton-button {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Responsive styles */
@media (max-width: 1264px) {
  .skeleton-image {
    width: 70%;
    height: 200px;
  }

  .skeleton-text.book-title {
    height: 18px;
  }

  .skeleton-text.short {
    height: 14px;
  }

  .skeleton-text.price {
    height: 20px;
    width: 70px;
  }

  .skeleton-text.stock {
    height: 14px;
    width: 80px;
  }

  .skeleton-button {
    height: 34px;
  }
}

@media (min-width: 320px) and (max-width: 350px) {
  .skeleton {
    width: 280px;
  }

  .skeleton-image {
    width: 60%;
    height: 180px;
  }
}</style>