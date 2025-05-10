document.addEventListener('DOMContentLoaded', () => {
  setupSidebar();
  fetchCategories();
  setupSearch();
});

function setupSidebar() {
  const bar = document.getElementById('bar');
  const close = document.getElementById('close');
  const nav = document.getElementById('navbar');
  const overlay = document.querySelector('.sidebar-overlay');

  if (bar) {
    bar.addEventListener('click', () => {
      nav.classList.add('active');
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  }

  if (close || overlay) {
    const closeNav = () => {
      nav.classList.remove('active');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    };
    close?.addEventListener('click', closeNav);
    overlay?.addEventListener('click', closeNav);
  }
}

async function fetchCategories() {
  try {
    const response = await fetch('http://localhost:8000/api/categories');
    const categories = await response.json();
    console.log('Все категории:', categories);

    const mainCategoryList = document.getElementById('mainCategoryList');
    mainCategoryList.innerHTML = '';

    // Обработка категорий первого уровня (языки)
    categories.forEach(language => {
      // Создаем элемент языка
      const languageItem = createCategoryItem(language, 1);
      mainCategoryList.appendChild(languageItem);

      if (language.children && language.children.length) {
        // Создаем контейнер для издательств (уровень 2)
        const publishersList = document.createElement('div');
        publishersList.classList.add('subcategory-list');
        publishersList.id = `sub-${language.id}`;
        
        // Обработка издательств
        language.children.forEach(publisher => {
          const publisherItem = createCategoryItem(publisher, 2);
          publishersList.appendChild(publisherItem);

          // Проверяем, есть ли у издательства книги
          if (publisher.children && publisher.children.length) {
            // Создаем контейнер для книг (уровень 3)
            const booksList = document.createElement('div');
            booksList.classList.add('subcategory-list');
            booksList.id = `sub-${publisher.id}`;

            // Добавляем книги в список
            publisher.children.forEach(book => {
              const bookItem = createCategoryItem(book, 3);
              booksList.appendChild(bookItem);
            });

            // Добавляем список книг после соответствующего издательства
            publishersList.appendChild(booksList);
          }
        });

        // Добавляем список издательств после соответствующего языка
        mainCategoryList.appendChild(publishersList);
      }
    });

    setupToggleListeners();
  } catch (error) {
    console.error('Помилка при завантаженні категорій:', error);
  }
}


function createCategoryItem(category, level) {
  const wrapper = document.createElement('div');
  wrapper.classList.add('category-item');
  wrapper.setAttribute('data-category-id', category.id);
  wrapper.setAttribute('data-level', level);

  // Проверяем, есть ли у категории дочерние элементы
  const hasChildren = category.children && category.children.length > 0;
  
  // Добавляем кнопку раскрытия только если есть дочерние категории
  const toggleButton = hasChildren ? 
    `<div class="toggle-button">
       <i class="fas fa-chevron-down accordion-icon"></i>
     </div>` : '';

  wrapper.innerHTML = `
    <input type="radio" name="category" id="cat-${category.id}" class="category-input" value="${category.id}">
    <label for="cat-${category.id}" class="category-radio"></label>
    <span class="category-name">${category.name}</span>
    <span class="category-count">${category.count ?? ''}</span>
    ${toggleButton}
  `;
  
  return wrapper;
}


function setupToggleListeners() {
  document.querySelectorAll('.toggle-button').forEach(button => {
    button.addEventListener('click', (e) => {
      e.stopPropagation(); // Предотвращаем всплытие события
      
      const parentItem = button.closest('.category-item');
      const categoryId = parentItem.getAttribute('data-category-id');
      const subList = document.getElementById(`sub-${categoryId}`);
      
      if (subList) {
        subList.classList.toggle('active');
        button.querySelector('.accordion-icon').classList.toggle('rotated');
      }
    });
  });
}

function setupSearch() {
  const searchInput = document.getElementById('searchInput');
  if (!searchInput) return;

  searchInput.addEventListener('input', function (e) {
    const search = e.target.value.toLowerCase();
    const allItems = document.querySelectorAll('.category-item');

    allItems.forEach(item => {
      const name = item.querySelector('.category-name')?.textContent.toLowerCase() || '';
      item.style.display = name.includes(search) ? '' : 'none';
    });
  });
}
