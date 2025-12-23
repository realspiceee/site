<?php
require_once __DIR__ . '/includes/init.php';
include __DIR__ . '/navbar.php';

$items = $cart->items();
$total = $cart->total();
?>

<section class="section">
    <div class="container section-header">
        <h2>Корзина</h2>
        <?php if ($items): ?>
            <a href="<?= BASE_URL ?>/catalog.php" class="link-muted">Продолжить покупки</a>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (!$items): ?>
            <p class="form-help">Ваша корзина пуста.</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <?php foreach ($items as $item): ?>
                    <article class="product-card" data-cart-row
                             data-product-id="<?= (int)$item['id'] ?>"
                             data-size-id="<?= (int)$item['size_id'] ?>">
                        <div style="display:flex; gap:1rem; align-items:flex-start;">
                            <div class="product-image-wrap" style="max-width:140px;">
                                <img src="<?= BASE_URL ?>/assets/images/<?= h($item['main_image'] ?: 'no-image.png') ?>"
                                     alt="<?= h($item['name']) ?>">
                            </div>
                            <div style="flex:1; display:flex; flex-direction:column; gap:0.3rem;">
                                <h3><?= h($item['name']) ?></h3>
                                <p class="product-meta">
                                    <?= h($item['brand']) ?> · размер <?= h($item['size']) ?>
                                </p>
                                <p class="product-price">
                                    <?= number_format($item['price'], 2, ',', ' ') ?> ₽
                                </p>

                                <div style="display:flex; gap:0.7rem; align-items:center; margin-top:0.4rem;">
                                    <label style="font-size:0.8rem; color:var(--text-muted);">
                                        Кол-во:
                                        <input type="number"
                                               class="form-control"
                                               style="max-width:80px; display:inline-block; margin-left:0.4rem;"
                                               min="1"
                                               value="<?= (int)$item['quantity'] ?>"
                                               data-cart-qty>
                                    </label>

                                    <span style="font-size:0.9rem;">
                                        Сумма:
                                        <strong>
                                            <?= number_format($item['subtotal'], 2, ',', ' ') ?> ₽
                                        </strong>
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline btn-sm" data-cart-remove>
                                Удалить
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:1.5rem; display:flex; justify-content:space-between; align-items:center; gap:1rem;">
                <button type="button" class="btn btn-outline" id="btn-clear-cart">Очистить корзину</button>
                <div style="text-align:right;">
                    <div style="font-size:0.9rem; color:var(--text-muted);">Итого</div>
                    <div style="font-size:1.4rem; font-weight:600;" id="cart-total">
                        <?= number_format($total, 2, ',', ' ') ?> ₽
                    </div>
                    <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary" style="margin-top:0.6rem;">
                        Оформить заказ
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace. Все права защищены.</span>
        <span>Корзина с мгновенным обновлением через AJAX.</span>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/script.js"></script>
<script>
// Обработчики изменения количества и удаления
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-cart-qty]').forEach(input => {
        input.addEventListener('change', () => {
            const row = input.closest('[data-cart-row]');
            const productId = row.getAttribute('data-product-id');
            const sizeId    = row.getAttribute('data-size-id');
            let qty         = parseInt(input.value || '1', 10);
            if (qty <= 0) qty = 1;
            input.value = qty;

            updateCartItem(productId, sizeId, qty);
        });
    });

    document.querySelectorAll('[data-cart-remove]').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('[data-cart-row]');
            const productId = row.getAttribute('data-product-id');
            const sizeId    = row.getAttribute('data-size-id');
            removeCartItem(row, productId, sizeId);
        });
    });

    const clearBtn = document.getElementById('btn-clear-cart');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (!confirm('Очистить всю корзину?')) return;
            clearCart();
        });
    }
});

function updateCartItem(productId, sizeId, qty) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            size_id: sizeId,
            quantity: qty
        })
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Ошибка обновления корзины');
                return;
            }
            const totalEl = document.getElementById('cart-total');
            if (totalEl) {
                totalEl.textContent = data.total.toLocaleString('ru-RU', {minimumFractionDigits: 2}) + ' ₽';
            }
            const countEl = document.getElementById('cart-count');
            if (countEl && typeof data.cart_count !== 'undefined') {
                countEl.textContent = data.cart_count;
            }
            // Проще всего просто перезагрузить страницу, чтобы подтянуть подсчёты
            location.reload();
        })
        .catch(console.error);
}

function removeCartItem(row, productId, sizeId) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'remove',
            product_id: productId,
            size_id: sizeId
        })
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Ошибка удаления товара');
                return;
            }
            row.remove();
            const totalEl = document.getElementById('cart-total');
            if (totalEl) {
                totalEl.textContent = data.total.toLocaleString('ru-RU', {minimumFractionDigits: 2}) + ' ₽';
            }
            const countEl = document.getElementById('cart-count');
            if (countEl && typeof data.cart_count !== 'undefined') {
                countEl.textContent = data.cart_count;
            }
            if (data.total <= 0) location.reload();
        })
        .catch(console.error);
}

function clearCart() {
    fetch('api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'clear' })
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Ошибка очистки корзины');
                return;
            }
            location.reload();
        })
        .catch(console.error);
}
</script>
</body>
</html>
