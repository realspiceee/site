document.addEventListener('DOMContentLoaded', function() {
    // Добавление в корзину
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const size = document.getElementById('size-select')?.value;
            const quantity = document.querySelector('input[name="quantity"]')?.value || 1;
            
            if (!size) {
                alert('Выберите размер!');
                return;
            }
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add',
                    product_id: parseInt(productId),
                    size: parseFloat(size),
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✅ Товар добавлен в корзину!', 'success');
                    updateCartCount(data.count);
                } else {
                    showNotification('❌ ' + (data.error || 'Ошибка'), 'error');
                }
            })
            .catch(() => showNotification('❌ Ошибка соединения', 'error'));
        });
    });
    
    // Обновление количества в корзине
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            const quantity = this.value;
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update',
                    item_id: parseInt(itemId),
                    quantity: parseInt(quantity)
                })
            });
        });
    });
    
    // Удаление из корзины
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Удалить товар из корзины?')) {
                const itemId = this.dataset.itemId;
                fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'remove',
                        item_id: parseInt(itemId)
                    })
                }).then(() => location.reload());
            }
        });
    });
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.remove(), 3000);
}

function updateCartCount(count) {
    const cartLinks = document.querySelectorAll('.nav-links a[href="cart.php"]');
    cartLinks.forEach(link => {
        link.textContent = `🛒 ${count}`;
    });
}
