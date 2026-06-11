document.addEventListener('DOMContentLoaded', () => {
    console.log("Shop JS loaded");

    const searchInput = document.getElementById('shop-search');
    const categorySelect = document.getElementById('category-filter');


    function applyFilters() {
        const search = searchInput ? encodeURIComponent(searchInput.value) : '';
        const category = categorySelect ? encodeURIComponent(categorySelect.value) : 'all';
        window.location.href = `index.php?page=1&search=${search}&category=${category}`;
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', applyFilters);
    }
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    }
});


async function addToCart(event, productId) {
    const btn = event.target.closest('button');
    if (!btn || btn.disabled) return;
    const originalContent = btn.innerHTML;
    const originalStyle = btn.style.backgroundColor;
    btn.disabled = true;
    try {
        const response = await fetch('add-to-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(productId)
        });
        const data = await response.json();
        const badge = document.getElementById('cart-badge');
        if (badge) {
            badge.innerText = data.newTotal;
            badge.style.display = 'flex';
        }
        btn.innerHTML = '<span>Добавлено</span>';
        btn.style.backgroundColor = '#34df5c'; 
        btn.style.borderColor = '#34df5c';
        setTimeout(() => { 
            btn.innerHTML = originalContent; 
            btn.style.backgroundColor = originalStyle;
            btn.style.borderColor = '';
            btn.disabled = false;
        }, 1500);

    } catch (err) { 
        console.error('Ошибка при добавлении:', err);
        btn.disabled = false;
        btn.innerHTML = '<span>Ошибка</span>';
        setTimeout(() => { btn.innerHTML = originalContent; }, 1500);
    }
}

function goBack(event) {
    if (window.history.length > 1 && document.referrer.includes(window.location.host)) {
        event.preventDefault();
        window.history.back();
    } 
}

window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const setupLimit = (inputId, errorId, limit) => {
        const input = document.getElementById(inputId);
        const error = document.getElementById(errorId);
        if (!input || !error) return; 

        input.addEventListener('input', () => {
            const submitBtn = document.getElementById('submitBtn');
            
            if (input.value.length > limit) {
                error.style.display = 'block';
                input.style.borderColor = 'var(--danger)';
                if (submitBtn) submitBtn.disabled = true;
            } else {
                error.style.display = 'none';
                input.style.borderColor = '';
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    };

    setupLimit('cat_input', 'cat_error', 25);
    setupLimit('product_title', 'product_error',60);
});