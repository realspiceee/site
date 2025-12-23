// assets/script.js

document.addEventListener('DOMContentLoaded', () => {
    // Делегирование для кнопок "Добавить в корзину"
    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-add-to-cart]');
        if (!btn) return;

        const productId = btn.getAttribute('data-product-id');
        const sizeId    = btn.getAttribute('data-size-id');
        let quantity    = parseInt(btn.getAttribute('data-quantity') || '1', 10);

        // если на карточке товара есть отдельное поле qty
        const qtyInputId = btn.getAttribute('data-qty-input');
        if (qtyInputId) {
            const input = document.getElementById(qtyInputId);
            if (input) {
                const q = parseInt(input.value || '1', 10);
                if (!isNaN(q) && q > 0) quantity = q;
            }
        }

        if (!productId || !sizeId) {
            alert('Пожалуйста, выберите размер товара.');
            return;
        }

        addToCart(productId, sizeId, quantity);
    });
});

function addToCart(productId, sizeId, quantity = 1) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            size_id: sizeId,
            quantity: quantity
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.error || 'Ошибка добавления в корзину');
            return;
        }
        const countEl = document.getElementById('cart-count');
        if (countEl && typeof data.cart_count !== 'undefined') {
            countEl.textContent = data.cart_count;
        }
        // небольшой визуальный фидбек
        alert('Товар добавлен в корзину');
    })
    .catch(err => {
        console.error(err);
        alert('Ошибка связи с сервером при добавлении в корзину.');
    });
}
