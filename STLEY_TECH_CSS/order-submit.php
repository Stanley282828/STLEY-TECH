<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('shop.html');
require_csrf();
$product_slug = post_string('product_slug', 80);
$name = post_string('name', 160);
$phone = post_string('phone', 40);
$email = post_string('email', 190);
$qty = max(1, min(99, (int)($_POST['qty'] ?? 1)));
$color = post_string('color', 50);
$storage = post_string('storage', 30);
$address = post_string('address', 2000);
$payment = post_string('payment', 10);
$addons = array_values(array_intersect((array)($_POST['addon'] ?? []), ['case', 'screen']));
$addons_text = implode(', ', $addons);

if ($product_slug === '' || $name === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $address === '' || !in_array($color, ['black','white'], true) || !in_array($storage, ['256gb','512gb'], true) || !in_array($payment, ['cod','gcash'], true)) {
    flash('error', 'Please complete all order fields correctly.'); redirect_to('shop.html');
}
$product_stmt = $db->prepare('SELECT id, name, price FROM products WHERE slug = ? AND is_active = 1 LIMIT 1');
$product_stmt->bind_param('s', $product_slug); $product_stmt->execute();
$product = $product_stmt->get_result()->fetch_assoc();
if (!$product) { flash('error', 'The selected product was not found.'); redirect_to('shop.html'); }
$variant_stmt = $db->prepare('SELECT id, price FROM product_variants WHERE product_id = ? AND storage = ? AND color = ? AND is_active = 1 LIMIT 1');
$variant_stmt->bind_param('iss', $product['id'], $storage, $color); $variant_stmt->execute();
$variant = $variant_stmt->get_result()->fetch_assoc();
$unit_price = (float)($variant['price'] ?? $product['price']);
$subtotal = $unit_price * $qty;
$order_number = 'STLEY-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));

$db->begin_transaction();
try {
    $user_id = logged_user_id();
    if ($user_id === null) {
        $order_stmt = $db->prepare('INSERT INTO orders (order_number, user_id, customer_name, customer_phone, customer_email, shipping_address, payment_method, subtotal, total_amount) VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)');
        $order_stmt->bind_param('ssssssdd', $order_number, $name, $phone, $email, $address, $payment, $subtotal, $subtotal);
    } else {
        $order_stmt = $db->prepare('INSERT INTO orders (order_number, user_id, customer_name, customer_phone, customer_email, shipping_address, payment_method, subtotal, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $order_stmt->bind_param('sisssssdd', $order_number, $user_id, $name, $phone, $email, $address, $payment, $subtotal, $subtotal);
    }
    $order_stmt->execute();
    $order_id = $db->insert_id;
    $product_id = (int)$product['id']; $variant_id = isset($variant['id']) ? (int)$variant['id'] : null; $product_name = $product['name']; $line_total = $subtotal;
    $item_stmt = $db->prepare('INSERT INTO order_items (order_id, product_id, variant_id, product_name, storage, color, addons, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $item_stmt->bind_param('iiissssidd', $order_id, $product_id, $variant_id, $product_name, $storage, $color, $addons_text, $qty, $unit_price, $line_total);
    $item_stmt->execute();
    $db->commit();
    header('Location: order.html?order=' . rawurlencode($order_number)); exit;
} catch (Throwable $error) {
    $db->rollback();
    flash('error', 'The order could not be saved. Please try again.');
    redirect_to('shop.html');
}
?>
