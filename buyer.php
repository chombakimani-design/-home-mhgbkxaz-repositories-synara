<?php
// ================================================================
//  DYNAMIC OPEN GRAPH META TAGS
//  Reads product from URL, fetches data, and outputs OG tags.
// ================================================================

// ─── 1. Get product ID from URL ───────────────────────────────
$productId = isset($_GET['product']) ? (int)$_GET['product'] : 0;

// ─── 2. Fetch product data ────────────────────────────────────
// Replace this with your actual product retrieval logic.
// For demonstration, we try to fetch from your /api/products endpoint.
function fetchProductFromApi($id) {
    // If you have an internal API endpoint that returns product data.
    // Example: http://yourdomain.com/api/products
    $apiUrl = '/api/products'; // adjust to your full URL if needed
    // Since this is server-side, we can use file_get_contents or cURL.
    // However, if the API is on the same domain, we can call it.
    // For simplicity, we'll use a sample array as fallback.
    return null; // we'll handle fallback below
}

// ─── 3. Fallback product data (if API call fails) ────────────
function getProductFromLocalArray($id) {
    // This mimics a product database. Replace with your own.
    $products = [
        1 => [
            'name' => 'Wireless Headphones',
            'description' => 'Premium noise-cancelling headphones with 40hr battery.',
            'image' => '/images/headphones.jpg',
            'price' => 2500
        ],
        2 => [
            'name' => 'Smart Watch',
            'description' => 'Fitness tracker with heart rate monitor and GPS.',
            'image' => '/images/watch.jpg',
            'price' => 4500
        ],
        3 => [
            'name' => 'Coffee Maker',
            'description' => 'Automatic drip coffee maker with 12-cup capacity.',
            'image' => '/images/coffee.jpg',
            'price' => 3200
        ],
        // Add more products as needed
    ];
    return isset($products[$id]) ? $products[$id] : null;
}

// Try to fetch from API first, else fallback
$product = null;

// Option 1: Attempt to fetch from your /api/products endpoint (server-side)
// You can use cURL or file_get_contents if the API is on the same domain.
// For this example, we'll use the fallback array.

$product = getProductFromLocalArray($productId);

// ─── 4. Set defaults if no product found ──────────────────────
if (!$product) {
    $title = 'SYNARA - Everything in Sync';
    $desc  = 'Shop online in Kenya with fast delivery.';
    $image = '/images/synara-default.jpg';
} else {
    $title = $product['name'];
    $desc  = $product['description'];
    $image = $product['image'];
}

// Ensure image is an absolute URL (for crawlers)
if (strpos($image, 'http') !== 0) {
    // Assume it's a relative path; prepend domain
    $image = 'https://' . $_SERVER['HTTP_HOST'] . $image;
}

$url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

    <!-- ─── OPEN GRAPH META TAGS (for Facebook, WhatsApp, etc.) ─── -->
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($desc) ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($image) ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($url) ?>" />
    <meta property="og:type" content="product" />
    <meta property="og:site_name" content="SYNARA" />

    <!-- ─── TWITTER CARD ────────────────────────────────────────── -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($desc) ?>" />
    <meta name="twitter:image" content="<?= htmlspecialchars($image) ?>" />

    <title><?= htmlspecialchars($title) ?> | SYNARA</title>

    <!-- ===== STYLES (your existing CSS) ===== -->
    <style>
        /* ── RESET & BASE ── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: linear-gradient(145deg, #f0f2f5 0%, #e9ecef 100%);
            color: #1a1a2e;
            overflow-x: hidden;
        }

        /* ── HEADER ── */
        .top-bar {
            background: #0A1A4A;
            color: white;
            text-align: center;
            padding: 3px;
            font-size: 10px;
        }
        .header {
            background: white;
            padding: 6px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            position: sticky;
            top: 0;
            z-index: 101;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .logo img {
            height: 75px;
        }
        .logo span {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #00BFFF, #1A6FD4);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .search-bar {
            flex: 1;
            max-width: 800px;
            display: flex;
        }
        .search-bar input {
            padding: 6px 12px;
            border: 2px solid #00BFFF;
            border-radius: 30px 0 0 30px;
            outline: none;
            font-size: 12px;
            width: 100%;
        }
        .search-bar button {
            background: linear-gradient(135deg, #00BFFF, #1A6FD4);
            border: none;
            padding: 0 20px;
            border-radius: 0 30px 30px 0;
            color: white;
            cursor: pointer;
            font-size: 12px;
        }
        .cart-icon {
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 30px;
            cursor: pointer;
            position: relative;
            font-size: 12px;
        }
        .cart-count {
            background: #00BFFF;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            font-size: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -3px;
            right: -3px;
        }
        .download-btn {
            background: linear-gradient(135deg, #00BFFF, #1A6FD4);
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        /* ── RECRUIT ── */
        .recruit-strip {
            max-width: 100%;
            margin: 4px auto;
            padding: 0 16px;
            display: flex;
            gap: 12px;
        }
        .recruit-card {
            flex: 1;
            border-radius: 30px;
            padding: 3px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        .recruit-card.agent {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .recruit-card.rider {
            background: linear-gradient(135deg, #00BFFF, #1A6FD4);
        }
        .recruit-text h4 {
            font-size: 11px;
            font-weight: 800;
            color: white;
        }
        .recruit-text p {
            font-size: 7px;
            color: rgba(255, 255, 255, 0.9);
        }
        .recruit-btn {
            background: white;
            padding: 2px 12px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 9px;
            border: none;
            cursor: pointer;
        }

        /* ── OFFERS BANNER ── */
        .offers-banner {
            max-width: 100%;
            margin: 6px auto 0;
            padding: 0 16px;
        }
        .sales-banner {
            background: linear-gradient(135deg, #ff0000, #cc0000);
            border-radius: 10px;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .animated-offer {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .rotating-product {
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        .countdown {
            background: rgba(0, 0, 0, 0.3);
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
        }

        /* ── MAIN LAYOUT ── */
        .main-container {
            max-width: 100%;
            margin: 8px auto;
            padding: 0 16px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        /* ── CATEGORIES SIDEBAR ── */
        .categories-mega {
            width: 240px;
            background: white;
            border-radius: 12px;
            padding: 6px 0;
            max-height: 700px;
            overflow-y: auto;
            position: sticky;
            top: 70px;
            flex-shrink: 0;
        }
        .categories-mega h3 {
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 800;
            border-bottom: 2px solid #00BFFF;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px 4px;
            padding: 8px 6px;
        }
        .category-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #1a1a2e;
            padding: 4px 2px;
            border-radius: 6px;
            transition: 0.15s;
            text-align: center;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .category-item:hover {
            background: rgba(0, 191, 255, 0.08);
            transform: scale(1.03);
        }
        .category-item.active {
            background: rgba(0, 191, 255, 0.15);
            border-color: #00BFFF;
        }
        .category-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #0A1A4A;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 2px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .category-name {
            font-size: 9px;
            font-weight: 600;
            line-height: 1.2;
            max-width: 60px;
            word-break: break-word;
        }

        /* ── BRAND LIST (Official Stores sub-categories) ── */
        .brand-list {
            display: none;
            padding: 8px 10px;
            border-top: 1px solid #eef2f6;
            max-height: 400px;
            overflow-y: auto;
        }
        .brand-list.open {
            display: block;
        }
        .brand-list .brand-item {
            display: inline-block;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            margin: 3px 4px 3px 0;
            cursor: pointer;
            transition: 0.15s;
            border: 1px solid transparent;
        }
        .brand-list .brand-item:hover {
            background: #00BFFF;
            color: white;
            border-color: #00BFFF;
            transform: scale(1.04);
        }
        .brand-list .brand-item.active-brand {
            background: #0A1A4A;
            color: white;
            border-color: #0A1A4A;
        }

        /* ── RIGHT COLUMN ── */
        .right-col {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ── HERO ── */
        .hero-section {
            background: white;
            border-radius: 14px;
            padding: 12px;
            display: block;
        }
        .hero-images {
            background: #f8fafc;
            border-radius: 10px;
            padding: 15px 10px;
            text-align: center;
            position: relative;
            min-height: 280px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .main-image {
            font-size: 140px;
            min-height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 14px;
        }
        .carousel-prev { left: 8px; }
        .carousel-next { right: 8px; }
        .thumbnail-strip {
            display: flex;
            gap: 4px;
            justify-content: center;
            margin-top: 12px;
        }
        .thumbnail {
            width: 35px;
            height: 35px;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            border: 1px solid #e2e8f0;
        }
        .thumbnail.active {
            border-color: #00BFFF;
        }

        .product-details-below {
            margin-top: 12px;
        }
        .product-name-large {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .price-large {
            font-size: 22px;
            font-weight: 800;
            color: #ff0000;
            margin: 4px 0;
        }
        .agent-info-large {
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
            font-size: 10px;
            margin: 4px 0;
        }
        .description-large {
            background: #f8fafc;
            padding: 6px 8px;
            border-radius: 8px;
            margin: 6px 0;
            font-size: 11px;
            line-height: 1.4;
            border-left: 3px solid #00BFFF;
        }
        .quantity-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 8px 0;
        }
        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: white;
            font-size: 14px;
            cursor: pointer;
        }
        .qty-value {
            font-size: 14px;
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            margin: 10px 0;
            flex-wrap: wrap;
        }
        .action-buttons button {
            flex: 1;
            padding: 8px 6px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 10px;
            cursor: pointer;
            text-align: center;
            border: none;
            min-width: 80px;
        }
        .btn-add-cart {
            background: #ff0000;
            color: white;
        }
        .btn-checkout {
            background: #ff0000;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .btn-continue {
            background: #f1f5f9;
            border: 1px solid #ff0000 !important;
            color: #ff0000;
        }

        /* ── PRODUCT GRID (2 rows, vertical scroll) ── */
        .product-grid-wrap {
            display: none;
            background: white;
            border-radius: 14px;
            padding: 16px 18px 20px 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .product-grid-wrap.visible {
            display: block;
        }
        .grid-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 6px;
        }
        .grid-header h4 {
            font-size: 16px;
            font-weight: 700;
            color: #0A1A4A;
        }
        .grid-header h4 span {
            color: #64748b;
            font-weight: 400;
            font-size: 13px;
        }

        /* ── 2‑row vertical‑scroll grid ── */
        .product-grid-scroll {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            max-height: 540px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .product-grid-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .product-grid-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .product-grid-scroll::-webkit-scrollbar-thumb {
            background: #00BFFF;
            border-radius: 10px;
        }

        .product-card {
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid #eef2f6;
            transition: 0.2s;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            border-color: #00BFFF;
        }
        .product-img {
            height: 110px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 44px;
            border-bottom: 1px solid #eef2f6;
        }
        .product-info {
            padding: 8px 10px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-name {
            font-weight: 600;
            font-size: 13px;
            line-height: 1.3;
            color: #0A1A4A;
        }
        .product-price {
            font-weight: 800;
            color: #ff0000;
            font-size: 15px;
            margin-top: 4px;
        }
        .product-price .old {
            font-weight: 400;
            font-size: 11px;
            color: #94a3b8;
            text-decoration: line-through;
            margin-left: 4px;
        }
        .product-card .agent-tag {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
            color: #cbd5e1;
        }

        /* ── DELIVERY CARD ── */
        .delivery-card {
            width: 240px;
            background: white;
            border-radius: 14px;
            padding: 12px;
            height: fit-content;
            position: sticky;
            top: 70px;
            flex-shrink: 0;
        }
        .delivery-title {
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .delivery-form-group {
            margin-bottom: 8px;
        }
        .delivery-form-group label {
            font-size: 10px;
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
            color: #475569;
        }
        .delivery-form-group input,
        .delivery-form-group select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 11px;
        }
        .other-town-input {
            margin-top: 6px;
            display: none;
        }
        .other-town-input input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 11px;
        }
        .pickup-info {
            background: #f8fafc;
            padding: 8px;
            border-radius: 8px;
            margin: 8px 0;
            cursor: pointer;
            transition: all 0.2s;
        }
        .pickup-info:hover {
            background: #e0f2fe;
            transform: scale(1.01);
        }
        .pickup-title {
            font-weight: 700;
            font-size: 11px;
        }
        .pickup-details {
            font-size: 9px;
            color: #64748b;
        }
        .delivery-fee {
            font-weight: 800;
            color: #10b981;
            font-size: 14px;
            margin: 6px 0;
        }
        .delivery-date {
            font-size: 9px;
            color: #64748b;
            margin: 4px 0;
        }
        .return-policy {
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 10px;
            color: #10b981;
        }
        .delivery-summary {
            background: #f0fdf4;
            padding: 8px;
            border-radius: 8px;
            margin: 8px 0;
            font-size: 10px;
            border-left: 3px solid #10b981;
            display: none;
            white-space: pre-line;
        }
        .save-delivery-btn {
            background: #00BFFF;
            color: white;
            border: none;
            padding: 6px;
            border-radius: 30px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 6px;
        }
        .seller-info-card {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }
        .seller-name {
            font-weight: 700;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
        }
        .follow-btn {
            background: none;
            border: 1px solid #00BFFF;
            color: #00BFFF;
            padding: 3px 10px;
            border-radius: 30px;
            font-size: 9px;
            cursor: pointer;
            margin-top: 6px;
            width: 100%;
        }

        /* ── CART MODAL ── */
        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .cart-modal-content {
            background: white;
            max-width: 500px;
            width: 90%;
            border-radius: 20px;
            max-height: 85vh;
            overflow-y: auto;
        }
        .cart-header {
            padding: 12px 16px;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-header h3 {
            font-size: 16px;
        }
        .cart-items {
            padding: 8px 12px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eef2f6;
        }
        .cart-item-info {
            flex: 2;
        }
        .cart-item-name {
            font-weight: 600;
            font-size: 12px;
        }
        .cart-item-price {
            font-size: 11px;
            color: #ff0000;
            font-weight: 700;
        }
        .cart-item-agent {
            font-size: 9px;
            color: #64748b;
        }
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 6px 0;
        }
        .cart-qty-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
            font-size: 12px;
        }
        .cart-item-actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: flex-end;
        }
        .delete-item {
            background: none;
            border: none;
            color: #ff0000;
            cursor: pointer;
            font-size: 12px;
        }
        .visit-shop {
            background: none;
            border: 1px solid #00BFFF;
            color: #00BFFF;
            padding: 2px 6px;
            border-radius: 15px;
            font-size: 9px;
            cursor: pointer;
        }
        .cart-total {
            padding: 12px 16px;
            border-top: 1px solid #eef2f6;
            background: #f8fafc;
        }
        .cart-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .cart-total-grand {
            font-weight: 800;
            font-size: 14px;
            color: #ff0000;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            margin-top: 6px;
        }
        .cart-buttons {
            display: flex;
            gap: 10px;
            padding: 12px 16px;
            border-top: 1px solid #eef2f6;
        }
        .resume-shopping-btn {
            flex: 1;
            background: #f1f5f9;
            border: 1px solid #64748b;
            color: #64748b;
            padding: 8px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 11px;
            cursor: pointer;
        }
        .cart-checkout-btn {
            flex: 1;
            background: #ff0000;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 11px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        /* ── HORIZONTAL BANDS ── */
        .same-shop-band,
        .other-products {
            max-width: 100%;
            margin: 12px auto;
            padding: 0 16px;
        }
        .band-header,
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .band-title,
        .section-title {
            font-size: 13px;
            font-weight: 800;
            border-left: 3px solid #ff0000;
            padding-left: 8px;
        }
        .scroll-btn,
        .more-arrow {
            background: #e2e8f0;
            border: none;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            cursor: pointer;
        }
        .horizontal-scroll {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            padding-bottom: 4px;
            scroll-behavior: smooth;
        }
        .shop-product-card {
            flex: 0 0 140px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid #eef2f6;
        }
        .shop-product-img {
            height: 100px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }
        .shop-product-info {
            padding: 6px;
        }
        .shop-product-name {
            font-weight: 600;
            font-size: 10px;
        }
        .shop-product-price {
            font-weight: 800;
            color: #ff0000;
            font-size: 11px;
        }

        /* ── FOOTER ── */
        .footer-main {
            background: #0A1A4A;
            color: #cbd5e1;
            margin-top: 30px;
        }
        .footer-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 30px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
        }
        .footer-section h4 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 15px;
            color: white;
        }
        .footer-section a {
            display: block;
            color: #94a3b8;
            text-decoration: none;
            font-size: 11px;
            margin-bottom: 8px;
        }
        .footer-section a:hover {
            color: #00BFFF;
        }
        .newsletter-form {
            display: flex;
            margin: 10px 0;
        }
        .newsletter-form input {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 30px 0 0 30px;
            font-size: 11px;
        }
        .newsletter-form button {
            background: #ff0000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 0 30px 30px 0;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
        }
        .checkbox-label {
            font-size: 9px;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 8px 0;
        }
        .app-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .app-btn {
            background: #1e293b;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .brand-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .brand-strip span {
            background: #1e293b;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 9px;
        }
        .footer-bottom {
            border-top: 1px solid #1e293b;
            padding: 20px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        .payment-icons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1000px) {
            .main-container {
                flex-direction: column;
            }
            .categories-mega {
                width: 100%;
                max-height: none;
                overflow-y: visible;
                padding: 6px 0;
                position: static;
            }
            .categories-mega h3 {
                display: inline-block;
                padding: 6px 16px;
                margin: 0;
                border-bottom: none;
                border-right: 2px solid #00BFFF;
                font-size: 14px;
            }
            .category-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .delivery-card {
                width: 100%;
                position: static;
                margin-top: 16px;
            }
            .right-col {
                width: 100%;
            }
            .recruit-strip {
                flex-direction: column;
                gap: 8px;
            }
            .recruit-card {
                padding: 6px 16px;
            }
            .recruit-text h4 {
                font-size: 13px;
            }
            .recruit-btn {
                padding: 3px 16px;
                font-size: 11px;
            }
            .header {
                padding: 8px 12px;
                gap: 10px;
            }
            .logo img {
                height: 55px;
            }
            .logo span {
                font-size: 18px;
            }
            .search-bar {
                max-width: 100%;
                order: 3;
                width: 100%;
            }
            .search-bar input {
                padding: 10px 14px;
                font-size: 14px;
            }
            .search-bar button {
                padding: 0 18px;
                font-size: 13px;
            }
            .product-name-large {
                font-size: 20px;
            }
            .price-large {
                font-size: 24px;
            }
            .description-large {
                font-size: 13px;
                padding: 8px 10px;
            }
            .agent-info-large {
                font-size: 12px;
            }
            .action-buttons button {
                padding: 10px 8px;
                font-size: 12px;
            }
            .qty-btn {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }
            .qty-value {
                font-size: 18px;
                min-width: 40px;
            }
            .sales-banner {
                flex-wrap: wrap;
                justify-content: center;
                text-align: center;
                gap: 10px;
            }
            .rotating-product {
                font-size: 12px;
            }
            .countdown {
                font-size: 11px;
            }
            .cart-modal-content {
                max-width: 95%;
            }
            .cart-item {
                flex-wrap: wrap;
                gap: 10px;
            }
            .cart-qty-btn {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }
            .cart-buttons {
                flex-direction: column;
                gap: 10px;
            }
            .product-grid-wrap {
                padding: 12px;
            }
            .product-grid-scroll {
                grid-template-columns: repeat(4, 1fr);
                max-height: 500px;
            }
        }

        @media (max-width: 768px) {
            .product-grid-scroll {
                grid-template-columns: repeat(2, 1fr);
                max-height: 600px;
            }
            .brand-list .brand-item {
                font-size: 9px;
                padding: 3px 10px;
            }
        }

        @media (max-width: 480px) {
            .product-name-large {
                font-size: 18px;
            }
            .price-large {
                font-size: 22px;
            }
            .action-buttons button {
                font-size: 10px;
                padding: 8px 6px;
            }
            .shop-product-card {
                flex: 0 0 130px;
            }
            .shop-product-img {
                height: 95px;
                font-size: 38px;
            }
            .footer-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .category-icon {
                width: 42px;
                height: 42px;
                font-size: 20px;
            }
            .category-name {
                font-size: 8px;
            }
            .product-grid-scroll {
                grid-template-columns: 1fr 1fr;
                max-height: 700px;
            }
            .brand-list .brand-item {
                font-size: 8px;
                padding: 2px 8px;
            }
        }
    </style>
</head>
<body>

    <!-- TOP BAR -->
    <div class="top-bar">🏍️ Free delivery on first order | M-Pesa accepted | SYNARA x Boda Ke</div>

    <!-- HEADER -->
    <div class="header">
        <div class="logo"><img src="/images/synara-logo.png" alt="SYNARA"><span>SYNARA<span style="color:#00BFFF;">.</span></span></div>
        <div class="search-bar"><input type="text" id="searchInput" placeholder="I'm looking for..."><button onclick="searchProducts()">🔍</button></div>
        <div class="cart-icon" onclick="openCartModal()">🛒 Cart <span class="cart-count" id="cartCount">0</span></div>
        <button class="download-btn" onclick="alert('Download App - Coming Soon')">📱 App</button>
    </div>

    <!-- RECRUIT -->
    <div class="recruit-strip">
        <div class="recruit-card agent" onclick="location.href='/agent-login.html'"><div class="recruit-text"><h4>🏪 Become an Agent</h4><p>Sell & reach thousands</p></div><button class="recruit-btn">Start →</button></div>
        <div class="recruit-card rider" onclick="location.href='/rider-login.html'"><div class="recruit-text"><h4>🏍️ Join Boda Ke</h4><p>Deliver & earn daily</p></div><button class="recruit-btn">Start →</button></div>
    </div>

    <!-- OFFERS BANNER -->
    <div class="offers-banner">
        <div class="sales-banner">
            <div class="animated-offer"><span>🔥 FLASH SALE:</span><div class="rotating-product" id="rotatingOffer">🎧 Up to 50% OFF</div></div>
            <div class="countdown">⏱️ Ends: <span id="countdownTimer">23:59:59</span></div>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-container">

        <!-- ===== CATEGORIES SIDEBAR ===== -->
        <div class="categories-mega" id="categoriesMega">
            <h3>📂 Shop by Categories</h3>
            <div class="category-grid" id="categoryGrid"></div>
            <!-- Brand list (shown only for Official Stores) -->
            <div class="brand-list" id="brandList">
                <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:6px;">🏷️ Select a brand:</div>
                <div id="brandItemsContainer"></div>
            </div>
        </div>

        <!-- ===== RIGHT COLUMN ===== -->
        <div class="right-col">

            <!-- HERO -->
            <div class="hero-section" id="heroSection">
                <div class="hero-images">
                    <button class="carousel-btn carousel-prev" onclick="changeImage(-1)">❮</button>
                    <div class="main-image" id="mainImage">📦</div>
                    <button class="carousel-btn carousel-next" onclick="changeImage(1)">❯</button>
                    <div class="thumbnail-strip" id="thumbnailStrip"></div>
                </div>
                <div class="product-details-below">
                    <div class="product-name-large" id="productName">Wireless Headphones</div>
                    <div class="price-large" id="priceDisplay">KES 2,500</div>
                    <div class="agent-info-large" id="agentInfo">🏪 Shop: TechZone KE</div>
                    <div class="description-large" id="productDescription">Premium wireless headphones with noise cancellation.</div>
                    <div class="quantity-box"><button class="qty-btn" onclick="changeQuantity(-1)">−</button><span class="qty-value" id="quantityValue">1</span><button class="qty-btn" onclick="changeQuantity(1)">+</button></div>
                    <div class="action-buttons">
                        <button class="btn-add-cart" id="addToCartBtn">🛒 ADD TO CART</button>
                        <button class="btn-checkout" id="checkoutNowBtn">📱💵 CHECKOUT with M-Pesa</button>
                        <button class="btn-continue" id="continueBtn">📦 CONTINUE SHOPPING</button>
                    </div>
                </div>
            </div>

            <!-- PRODUCT GRID (2 rows, vertical scroll) -->
            <div class="product-grid-wrap" id="productGridWrap">
                <div class="grid-header">
                    <h4 id="gridTitle">All Products <span id="gridCount"></span></h4>
                </div>
                <div class="product-grid-scroll" id="productGrid"></div>
            </div>

        </div>

        <!-- ===== DELIVERY CARD ===== -->
        <div class="delivery-card">
            <div class="delivery-title">🚚 Delivery Information</div>
            <div class="delivery-form-group"><label>Full Name</label><input type="text" id="fullName" placeholder="Official name"></div>
            <div class="delivery-form-group"><label>Phone Number</label><input type="tel" id="phoneNumber" placeholder="2547XXXXXXX"></div>
            <div class="delivery-form-group"><label>County</label><select id="countySelect" onchange="updateTownOptions()"><option value="">-- Select County --</option><option value="Nairobi">Nairobi</option><option value="Mombasa">Mombasa</option><option value="Kisumu">Kisumu</option><option value="Nakuru">Nakuru</option><option value="Kiambu">Kiambu</option><option value="Kitui">Kitui</option></select></div>
            <div class="delivery-form-group"><label>Town</label><select id="townSelect" onchange="checkOtherTown()"><option value="">-- Select Town --</option></select></div>
            <div class="other-town-input" id="otherTownDiv"><input type="text" id="otherTown" placeholder="Enter town name"></div>
            <div class="delivery-form-group"><label>Street / Estate</label><input type="text" id="streetEstate" placeholder="Street name, building"></div>
            <div class="pickup-info" onclick="selectPickupStation()"><div class="pickup-title">📦 Pickup Station</div><div class="pickup-details" id="pickupStation">Click to select nearest station</div></div>
            <div class="delivery-fee" id="deliveryFeeDisplay">Delivery Fee: --</div>
            <div class="delivery-date" id="deliveryDate">Est. delivery: --</div>
            <div class="delivery-summary" id="deliverySummary"></div>
            <button class="save-delivery-btn" onclick="saveDeliveryInfo()">✓ Confirm & Save Delivery Info</button>
            <div class="return-policy">✅ Easy Return, Quick Refund.</div>
            <div class="seller-info-card"><div class="seller-name"><span id="sellerName">AMTEC Store</span><span class="seller-score" id="sellerScore">90%</span></div><button class="follow-btn" onclick="alert('Store followed!')">+ Follow</button></div>
        </div>
    </div>

    <!-- HORIZONTAL BANDS -->
    <div class="same-shop-band">
        <div class="band-header"><div class="band-title">🏪 More from <span id="shopName">this shop</span></div><div class="scroll-arrows"><button class="scroll-btn" onclick="scrollSameShop('left')">◀</button><button class="scroll-btn" onclick="scrollSameShop('right')">▶</button></div></div>
        <div class="horizontal-scroll" id="sameShopGrid"></div>
    </div>

    <div class="other-products">
        <div class="section-header"><div class="section-title">🔥 What's New</div><button class="more-arrow" id="whatsNewMoreBtn">More →</button></div>
        <div class="product-grid-scroll" id="whatsNewGrid" style="max-height:400px;"></div>
        <div class="section-header" style="margin-top:12px;"><div class="section-title">📦 You May Also Like</div><button class="more-arrow" id="recommendedMoreBtn">More →</button></div>
        <div class="product-grid-scroll" id="recommendedGrid" style="max-height:400px;"></div>
    </div>

    <!-- CART MODAL -->
    <div id="cartModal" class="cart-modal">
        <div class="cart-modal-content">
            <div class="cart-header"><h3>🛒 Your Cart</h3><span onclick="closeCartModal()" style="font-size:20px;cursor:pointer;">&times;</span></div>
            <div id="cartItemsList" class="cart-items"></div>
            <div id="cartTotal" class="cart-total"></div>
            <div class="cart-buttons"><button class="resume-shopping-btn" onclick="closeCartModal()">📦 Resume Shopping</button><button class="cart-checkout-btn" id="cartCheckoutBtn">📱💵 CHECKOUT with M-Pesa</button></div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer-main">
        <div class="footer-container">
            <div class="footer-section">
                <h4>New to SYNARA?</h4>
                <p style="font-size:10px; margin-bottom:10px;">Subscribe to our newsletter to get updates on our latest offers.</p>
                <div class="newsletter-form"><input type="email" id="newsletterEmail" placeholder="Enter E-mail Address"><button onclick="alert('Subscribed!')">Subscribe</button></div>
                <div class="checkbox-label"><input type="checkbox" id="acceptTerms"> <span style="font-size:9px;">I accept the Legal Terms. I agree to SYNARA's Privacy and Cookie Policy.</span></div>
                <div class="app-buttons"><div class="app-btn">📱 DOWNLOAD SYNARA FREE APP</div><div class="app-btn">🎁 Get access to exclusive offers!</div></div>
            </div>
            <div class="footer-section"><h4>NEED HELP?</h4><a href="#">Chat with us</a><a href="#">Help Center</a><a href="#">Contact Us</a></div>
            <div class="footer-section"><h4>USEFUL LINKS</h4><a href="#">Track Your Order</a><a href="#">Shipping and delivery</a><a href="#">Pick-up Stations</a><a href="#">Return Policy</a></div>
            <div class="footer-section"><h4>ABOUT SYNARA</h4><a href="#">About us</a><a href="#">Returns and Refunds Policy</a><a href="#">SYNARA Careers</a><a href="#">Terms and Conditions</a></div>
            <div class="footer-section"><h4>MAKE MONEY WITH SYNARA</h4><a href="#" onclick="location.href='/agent-login.html'">Sell on SYNARA</a><a href="#" onclick="location.href='/rider-login.html'">Become A SYNARA Rider</a></div>
            <div class="footer-section"><h4>CONTACT US</h4><p style="font-size:10px;">Nairobi, Kenya</p><div class="payment-icons">📱 M-Pesa | 💳 Visa</div></div>
        </div>
        <div class="footer-bottom"><p>© 2026 SYNARA. All rights reserved. Everything in sync.</p></div>
    </div>

    <script>
        // ================================================================
        //  PRODUCTS  (expanded with placeholder items for all categories)
        // ================================================================
        const products = [];

        // Helper to generate product objects
        function makeProduct(id, name, price, oldPrice, icon, agent, seller, score, desc, images, discount, cat, brand) {
            return {
                id,
                name,
                price,
                oldPrice: oldPrice || null,
                icon,
                agent,
                seller,
                sellerScore: score,
                description: desc,
                images: images || [icon, '⚡', '🌟'],
                hasDiscount: !!oldPrice,
                discountPercent: oldPrice ? Math.round((1 - price / oldPrice) * 100) : 0,
                category: cat,
                brand: brand || null
            };
        }

        // ---- Official Stores (all brands) ----
        const officialBrands = [
            'Samsung', 'Tecno', 'Infinix', 'Itel', 'Oraimo',
            'Solarmax', 'Nunix', 'Annov', 'Redberry', 'Miniso',
            'adidas', 'Ecko Unltd', 'DeFacto',
            'Vitron', 'Vision Plus', 'TCL', 'Hisense', 'Multichoice',
            'Garnier', 'Nivea', 'Maybelline', 'MAC', 'Nice & Lovely',
            'Huggies',
            'Roch', 'Ramtons', 'Hotpoint', 'Mika',
            'HP', 'Lenovo', 'Dell', 'Canon', 'Asus',
            'PRK', 'EABL', 'Unilever', 'P&G'
        ];

        // Create dummy products for each brand (at least 2 per brand)
        let idCounter = 1;
        officialBrands.forEach(b => {
            const brandLower = b.toLowerCase();
            // Create 2 products per brand
            for (let i = 1; i <= 2; i++) {
                const name = `${b} ${i === 1 ? 'Pro' : 'Lite'} ${Math.floor(Math.random() * 100) + 1}`;
                const price = Math.floor(Math.random() * 50000) + 1000;
                const oldPrice = i === 1 ? Math.floor(price * 1.3) : null;
                const icons = ['📱', '💻', '⌚', '🎧', '📺', '🔊', '📷', '🖥️', '⌨️', '🖨️', '📋', '🎮'];
                const icon = icons[idCounter % icons.length];
                const agent = `${b} KE`;
                const seller = `${b} Official`;
                const score = 80 + Math.floor(Math.random() * 15);
                const desc = `Premium ${b} product with advanced features.`;
                products.push(makeProduct(
                    idCounter++, name, price, oldPrice, icon, agent, seller, score, desc, [icon, '⭐', '⚡'], true,
                    'Official Stores', b
                ));
            }
        });

        // ---- PHONES & TABLETS ----
        const phones = [
            { name: 'iPhone 16 Pro', price: 130000, icon: '📱', agent: 'Apple KE', seller: 'Apple Official', score: 99,
                desc: 'A18 Pro chip, 48MP camera.' },
            { name: 'Samsung Galaxy S24 Ultra', price: 140000, icon: '📱', agent: 'Samsung KE', seller: 'Samsung Official',
                score: 98, desc: 'AI features, 200MP camera.' },
            { name: 'Tecno Camon 30', price: 50000, icon: '📱', agent: 'Tecno KE', seller: 'Tecno Official', score: 92,
                desc: '64MP camera, 256GB storage.' },
            { name: 'Infinix Zero 40', price: 60000, icon: '📱', agent: 'Infinix KE', seller: 'Infinix Official', score: 91,
                desc: '108MP camera, 120Hz AMOLED.' },
            { name: 'iPad Air', price: 45000, icon: '📋', agent: 'Apple KE', seller: 'Apple Official', score: 97,
                desc: '10.9" with M2 chip.' },
            { name: 'Samsung Tab S9', price: 35000, icon: '📋', agent: 'Samsung KE', seller: 'Samsung Official', score: 96,
                desc: '11" AMOLED, S Pen included.' },
            { name: 'Google Pixel 8', price: 70000, icon: '📱', agent: 'Google KE', seller: 'Google Official', score: 94,
                desc: 'AI-powered camera, 7 years updates.' },
            { name: 'OnePlus 12', price: 65000, icon: '📱', agent: 'OnePlus KE', seller: 'OnePlus Official', score: 93,
                desc: 'Snapdragon 8 Gen 3, 100W charging.' },
            { name: 'Xiaomi 14', price: 55000, icon: '📱', agent: 'Xiaomi KE', seller: 'Xiaomi Official', score: 90,
                desc: 'Leica camera, 120W fast charge.' },
            { name: 'Huawei Mate 60', price: 75000, icon: '📱', agent: 'Huawei KE', seller: 'Huawei Official', score: 89,
                desc: 'Satellite communication, 5G.' },
            { name: 'Oppo Find X7', price: 58000, icon: '📱', agent: 'Oppo KE', seller: 'Oppo Official', score: 88,
                desc: '6.8" AMOLED, 80W charging.' },
            { name: 'Vivo X100', price: 62000, icon: '📱', agent: 'Vivo KE', seller: 'Vivo Official', score: 87,
                desc: 'Zeiss camera, 120W fast charge.' },
            { name: 'Realme GT 5', price: 48000, icon: '📱', agent: 'Realme KE', seller: 'Realme Official', score: 86,
                desc: 'Snapdragon 8 Gen 2, 240W charging.' },
            { name: 'Nothing Phone (2)', price: 42000, icon: '📱', agent: 'Nothing KE', seller: 'Nothing Official',
                score: 85, desc: 'Glyph interface, 50MP dual camera.' },
            { name: 'Asus ROG Phone 7', price: 85000, icon: '📱', agent: 'Asus KE', seller: 'Asus Official', score: 92,
                desc: 'Gaming phone with 6000mAh battery.' },
            { name: 'Motorola Edge 40', price: 38000, icon: '📱', agent: 'Motorola KE', seller: 'Motorola Official',
                score: 84, desc: '144Hz display, 68W charging.' }
        ];
        phones.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.2) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon, '📲',
                    '⚡'
                ], !!oldPrice, 'PHONES & TABLETS'));
        });

        // ---- TVs & AUDIO ----
        const tvAudio = [
            { name: 'Samsung QLED 65"', price: 120000, icon: '📺', agent: 'Samsung KE', seller: 'Samsung Official',
                score: 97, desc: '4K QLED, HDR10+, 120Hz.' },
            { name: 'LG OLED 55"', price: 100000, icon: '📺', agent: 'LG KE', seller: 'LG Official', score: 96,
                desc: 'OLED evo, Dolby Vision.' },
            { name: 'Sony Bravia XR 75"', price: 180000, icon: '📺', agent: 'Sony KE', seller: 'Sony Official', score: 98,
                desc: 'Cognitive processor, 4K HDR.' },
            { name: 'TCL 55" C845', price: 60000, icon: '📺', agent: 'TCL KE', seller: 'TCL Official', score: 90,
                desc: 'Mini LED, Google TV.' },
            { name: 'Hisense 65" U8K', price: 70000, icon: '📺', agent: 'Hisense KE', seller: 'Hisense Official', score: 89,
                desc: 'ULED, 144Hz, Dolby Atmos.' },
            { name: 'Vision Plus 43"', price: 28000, icon: '📺', agent: 'Vision Plus KE', seller: 'Vision Plus Official',
                score: 85, desc: 'Full HD Smart TV.' },
            { name: 'Sony Soundbar HT-A7000', price: 45000, icon: '🔊', agent: 'Sony KE', seller: 'Sony Official', score: 94,
                desc: '7.1.2 channel, Dolby Atmos.' },
            { name: 'Bose 700 Headphones', price: 25000, icon: '🎧', agent: 'Bose KE', seller: 'Bose Official', score: 93,
                desc: 'Noise cancelling, 20hr battery.' },
            { name: 'JBL Flip 6', price: 6000, icon: '🔊', agent: 'JBL KE', seller: 'JBL Official', score: 88,
                desc: 'Portable speaker, waterproof.' },
            { name: 'Samsung HW-Q990C', price: 55000, icon: '🔊', agent: 'Samsung KE', seller: 'Samsung Official', score: 92,
                desc: '11.1.4ch soundbar with subwoofer.' },
            { name: 'LG XBOOM 360', price: 12000, icon: '🔊', agent: 'LG KE', seller: 'LG Official', score: 86,
                desc: '360° sound, mood lighting.' },
            { name: 'Apple AirPods Max', price: 35000, icon: '🎧', agent: 'Apple KE', seller: 'Apple Official', score: 95,
                desc: 'Over‑ear, spatial audio.' },
            { name: 'Sony WH-1000XM5', price: 28000, icon: '🎧', agent: 'Sony KE', seller: 'Sony Official', score: 94,
                desc: 'Industry‑leading noise cancelling.' },
            { name: 'Marshall Acton III', price: 15000, icon: '🔊', agent: 'Marshall KE', seller: 'Marshall Official',
                score: 87, desc: 'Compact speaker, vintage design.' },
            { name: 'Harman Kardon Onyx Studio 8', price: 18000, icon: '🔊', agent: 'Harman KE', seller: 'Harman Official',
                score: 88, desc: '360° sound, 24hr battery.' },
            { name: 'Samsung The Frame 65"', price: 150000, icon: '📺', agent: 'Samsung KE', seller: 'Samsung Official',
                score: 93, desc: 'Art mode, 4K QLED.' }
        ];
        tvAudio.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.15) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '🎬', '⚡'
                ], !!oldPrice, 'TVs & AUDIO'));
        });

        // ---- APPLIANCES ----
        const appliances = [
            { name: 'LG Fridge 500L', price: 55000, icon: '🧊', agent: 'LG KE', seller: 'LG Official', score: 92,
                desc: 'Double door, Inverter linear compressor.' },
            { name: 'Samsung Washer/Dryer', price: 60000, icon: '🧺', agent: 'Samsung KE', seller: 'Samsung Official',
                score: 91, desc: 'AddWash, 9kg capacity.' },
            { name: 'Bosch Dishwasher', price: 45000, icon: '🍽️', agent: 'Bosch KE', seller: 'Bosch Official', score: 90,
                desc: 'Silent operation, 14 place settings.' },
            { name: 'Philips Airfryer', price: 12000, icon: '🔥', agent: 'Philips KE', seller: 'Philips Official', score: 89,
                desc: 'Rapid Air technology, 4.5L.' },
            { name: 'Miele Vacuum Cleaner', price: 20000, icon: '🧹', agent: 'Miele KE', seller: 'Miele Official', score: 93,
                desc: 'HEPA filter, 2000W motor.' },
            { name: 'Ramtons Microwave', price: 10000, icon: '🔥', agent: 'Ramtons KE', seller: 'Ramtons Official', score: 86,
                desc: '25L, grill function, digital display.' },
            { name: 'Hotpoint Cooker', price: 42000, icon: '🔥', agent: 'Hotpoint KE', seller: 'Hotpoint Official', score: 87,
                desc: 'Gas hob, electric oven, 4 burners.' },
            { name: 'Mika Fridge', price: 38000, icon: '🧊', agent: 'Mika KE', seller: 'Mika Official', score: 85,
                desc: 'Single door, 250L, energy efficient.' },
            { name: 'Roch Kettle', price: 1500, icon: '🫖', agent: 'Roch KE', seller: 'Roch Official', score: 82,
                desc: '1.7L, stainless steel, auto shut‑off.' },
            { name: 'Nunix Blender', price: 3500, icon: '🥤', agent: 'Nunix KE', seller: 'Nunix Official', score: 83,
                desc: 'High‑speed, 2L jar, smoothie maker.' },
            { name: 'Solarmax Solar Panel Kit', price: 45000, icon: '☀️', agent: 'Solarmax KE', seller: 'Solarmax Official',
                score: 90, desc: '500W, off‑grid, with inverter.' },
            { name: 'Annov Air Conditioner', price: 55000, icon: '❄️', agent: 'Annov KE', seller: 'Annov Official', score: 89,
                desc: 'Inverter, 18,000 BTU, Wi‑Fi control.' },
            { name: 'Redberry Heater', price: 8000, icon: '🔥', agent: 'Redberry KE', seller: 'Redberry Official', score: 84,
                desc: 'Ceramic, 2000W, fan‑assisted.' },
            { name: 'Miniso Desk Fan', price: 2000, icon: '🌀', agent: 'Miniso KE', seller: 'Miniso Official', score: 82,
                desc: 'USB‑C, 4 speeds, quiet operation.' },
            { name: 'Mika Dishwasher', price: 25000, icon: '🍽️', agent: 'Mika KE', seller: 'Mika Official', score: 87,
                desc: '8 place settings, energy class A+' },
            { name: 'Ramtons Vacuum', price: 7000, icon: '🧹', agent: 'Ramtons KE', seller: 'Ramtons Official', score: 86,
                desc: 'Bagless, 1000W, lightweight.' }
        ];
        appliances.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.25) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '🏠', '⚡'
                ], !!oldPrice, 'APPLIANCES'));
        });

        // ---- HEALTH & BEAUTY ----
        const health = [
            { name: 'Garnier SkinActive Moisturizer', price: 900, icon: '🧴', agent: 'Garnier KE',
                seller: 'Garnier Official', score: 87, desc: 'SPF 30, 48hr hydration.' },
            { name: 'Nivea Body Lotion', price: 700, icon: '🧴', agent: 'Nivea KE', seller: 'Nivea Official', score: 88,
                desc: 'Deep nourishing, 72hr moisture.' },
            { name: 'Maybelline Superstay Lipstick', price: 500, icon: '💋', agent: 'Maybelline KE',
                seller: 'Maybelline Official', score: 89, desc: '24hr wear, matte finish.' },
            { name: 'MAC Studio Fix Fluid', price: 1200, icon: '💄', agent: 'MAC KE', seller: 'MAC Official', score: 92,
                desc: 'Foundation with SPF 15.' },
            { name: 'Nice & Lovely Skin Cream', price: 400, icon: '🧴', agent: 'Nice & Lovely KE',
                seller: 'Nice & Lovely Official', score: 84, desc: 'Lightens and brightens.' },
            { name: 'Olay Regenerist Serum', price: 1500, icon: '🧴', agent: 'Olay KE', seller: 'Olay Official', score: 90,
                desc: 'Anti‑aging, vitamin B3.' },
            { name: 'L’Oréal Paris Shampoo', price: 600, icon: '🧴', agent: 'L’Oréal KE', seller: 'L’Oréal Official',
                score: 88, desc: 'Repairing, with keratin.' },
            { name: 'Dove Body Wash', price: 500, icon: '🧴', agent: 'Dove KE', seller: 'Dove Official', score: 87,
                desc: 'Microbiome gentle, nourishing.' },
            { name: 'Oral-B Electric Toothbrush', price: 3000, icon: '🪥', agent: 'Oral‑B KE', seller: 'Oral‑B Official',
                score: 89, desc: '3D cleaning, pressure sensor.' },
            { name: 'Colgate Toothpaste', price: 250, icon: '🦷', agent: 'Colgate KE', seller: 'Colgate Official', score: 86,
                desc: 'Whitening, fluoride protection.' },
            { name: 'Neutrogena Sunscreen', price: 800, icon: '☀️', agent: 'Neutrogena KE', seller: 'Neutrogena Official',
                score: 87, desc: 'SPF 50, oil‑free.' },
            { name: 'Garnier Micellar Water', price: 600, icon: '💧', agent: 'Garnier KE', seller: 'Garnier Official',
                score: 85, desc: 'Cleanses, removes makeup.' },
            { name: 'Nivea Men Face Wash', price: 400, icon: '🧴', agent: 'Nivea KE', seller: 'Nivea Official', score: 86,
                desc: 'Deep clean, oil control.' },
            { name: 'Maybelline Mascara', price: 600, icon: '👁️', agent: 'Maybelline KE', seller: 'Maybelline Official',
                score: 88, desc: 'Volumizing, waterproof.' },
            { name: 'MAC Prep + Prime', price: 800, icon: '✨', agent: 'MAC KE', seller: 'MAC Official', score: 91,
                desc: 'Skin base, reduces pores.' },
            { name: 'Nice & Lovely Soap', price: 300, icon: '🧼', agent: 'Nice & Lovely KE', seller: 'Nice & Lovely Official',
                score: 83, desc: 'Beauty soap with vitamin E.' }
        ];
        health.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.2) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '✨', '🌿'
                ], !!oldPrice, 'HEALTH & BEAUTY'));
        });

        // ---- HOME & OFFICE ----
        const homeOffice = [
            { name: 'Herman Miller Aeron Chair', price: 85000, icon: '💺', agent: 'Herman Miller KE',
                seller: 'Herman Miller Official', score: 95, desc: 'Ergonomic, fully adjustable.' },
            { name: 'IKEA Desk 120cm', price: 12000, icon: '📐', agent: 'IKEA KE', seller: 'IKEA Official', score: 90,
                desc: 'White, with cable management.' },
            { name: 'HP LaserJet Printer', price: 15000, icon: '🖨️', agent: 'HP KE', seller: 'HP Official', score: 91,
                desc: 'Wireless, duplex printing.' },
            { name: 'Canon PIXMA Scanner', price: 8000, icon: '📠', agent: 'Canon KE', seller: 'Canon Official', score: 89,
                desc: 'Flatbed, high resolution.' },
            { name: 'Bic Pens (10 pack)', price: 200, icon: '🖊️', agent: 'Bic KE', seller: 'Bic Official', score: 82,
                desc: 'Round stick, medium point.' },
            { name: 'Fellowes Paper Shredder', price: 4000, icon: '📄', agent: 'Fellowes KE', seller: 'Fellowes Official',
                score: 86, desc: 'Strip‑cut, 10 sheets.' },
            { name: 'Sony Clock Radio', price: 2500, icon: '⏰', agent: 'Sony KE', seller: 'Sony Official', score: 87,
                desc: 'Dual alarm, FM/AM.' },
            { name: 'Desk Lamp (LED)', price: 2200, icon: '💡', agent: 'Philips KE', seller: 'Philips Official', score: 88,
                desc: 'Dim‑mable, touch control.' },
            { name: 'Whiteboard 90x60cm', price: 3000, icon: '📋', agent: 'Office Plus', seller: 'WorkSpace KE', score: 84,
                desc: 'Magnetic, dry‑erase.' },
            { name: 'Paper Ream (A4)', price: 500, icon: '📄', agent: 'Office Plus', seller: 'WorkSpace KE', score: 83,
                desc: '500 sheets, 80gsm.' },
            { name: 'Stapler (Heavy Duty)', price: 300, icon: '📎', agent: 'Office Plus', seller: 'WorkSpace KE', score: 82,
                desc: '50‑sheet capacity.' },
            { name: 'File Cabinet 2‑drawer', price: 8000, icon: '🗄️', agent: 'Office Plus', seller: 'WorkSpace KE', score: 86,
                desc: 'Steel, lockable.' },
            { name: 'Desk Organizer', price: 1200, icon: '🧰', agent: 'Office Plus', seller: 'WorkSpace KE', score: 84,
                desc: 'Multi‑compartment.' },
            { name: 'Post‑it Notes (5 pack)', price: 300, icon: '📝', agent: 'Office Plus', seller: 'WorkSpace KE', score: 82,
                desc: 'Assorted colors.' },
            { name: 'Correction Tape', price: 100, icon: '📄', agent: 'Office Plus', seller: 'WorkSpace KE', score: 80,
                desc: 'Roller type, 5m.' },
            { name: 'Ruler Set', price: 150, icon: '📏', agent: 'Office Plus', seller: 'WorkSpace KE', score: 80,
                desc: 'Plastic ruler with stencils.' }
        ];
        homeOffice.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.1) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '🏢', '📎'
                ], !!oldPrice, 'HOME & OFFICE'));
        });

        // ---- FASHION ----
        const fashion = [
            { name: 'Nike Air Max 270', price: 8000, icon: '👟', agent: 'Nike KE', seller: 'Nike Official', score: 92,
                desc: 'Air cushioning, mesh upper.' },
            { name: 'Adidas Ultraboost', price: 12000, icon: '👟', agent: 'adidas KE', seller: 'adidas Official', score: 93,
                desc: 'Boost foam, responsive.' },
            { name: 'Levi’s 501 Jeans', price: 4000, icon: '👖', agent: 'Levi’s KE', seller: 'Levi’s Official', score: 90,
                desc: 'Classic straight fit.' },
            { name: 'H&M Hoodie', price: 2000, icon: '🧥', agent: 'H&M KE', seller: 'H&M Official', score: 85,
                desc: 'Cotton blend, soft fleece.' },
            { name: 'Zara T‑Shirt', price: 1200, icon: '👕', agent: 'Zara KE', seller: 'Zara Official', score: 87,
                desc: '100% cotton, crew neck.' },
            { name: 'Ecko Unltd Cap', price: 800, icon: '🧢', agent: 'Ecko KE', seller: 'Ecko Official', score: 84,
                desc: 'Embroidered logo, snapback.' },
            { name: 'DeFacto Jacket', price: 4000, icon: '🧥', agent: 'DeFacto KE', seller: 'DeFacto Official', score: 88,
                desc: 'Waterproof, with hood.' },
            { name: 'Tommy Hilfiger Belt', price: 1500, icon: '🧷', agent: 'Tommy KE', seller: 'Tommy Official', score: 89,
                desc: 'Leather, metal buckle.' },
            { name: 'Puma Socks (3 pack)', price: 600, icon: '🧦', agent: 'Puma KE', seller: 'Puma Official', score: 85,
                desc: 'Cotton, ankle length.' },
            { name: 'Ray‑Ban Sunglasses', price: 6000, icon: '🕶️', agent: 'Ray‑Ban KE', seller: 'Ray‑Ban Official', score: 91,
                desc: 'Polarized, classic style.' },
            { name: 'Fossil Watch', price: 5000, icon: '⌚', agent: 'Fossil KE', seller: 'Fossil Official', score: 88,
                desc: 'Stainless steel, chronograph.' },
            { name: 'Timberland Boots', price: 10000, icon: '👢', agent: 'Timberland KE', seller: 'Timberland Official',
                score: 92, desc: 'Waterproof, premium leather.' },
            { name: 'CK Underwear (set)', price: 3000, icon: '🩲', agent: 'Calvin Klein KE', seller: 'Calvin Klein Official',
                score: 90, desc: 'Cotton stretch, 3‑pack.' },
            { name: 'Herschel Backpack', price: 3500, icon: '🎒', agent: 'Herschel KE', seller: 'Herschel Official', score: 87,
                desc: 'Classic, with laptop sleeve.' },
            { name: 'Uniqlo Down Jacket', price: 5000, icon: '🧥', agent: 'Uniqlo KE', seller: 'Uniqlo Official', score: 89,
                desc: 'Lightweight, ultra‑warm.' },
            { name: 'New Balance 574', price: 7000, icon: '👟', agent: 'New Balance KE', seller: 'New Balance Official',
                score: 88, desc: 'Classic sneaker, suede overlay.' }
        ];
        fashion.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.2) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '👗', '⭐'
                ], !!oldPrice, 'FASHION'));
        });

        // ---- COMPUTING ----
        const computing = [
            { name: 'Dell XPS 13', price: 80000, icon: '💻', agent: 'Dell KE', seller: 'Dell Official', score: 95,
                desc: 'Intel Core i7, 16GB RAM, 512GB SSD.' },
            { name: 'HP Spectre x360', price: 75000, icon: '💻', agent: 'HP KE', seller: 'HP Official', score: 94,
                desc: 'Convertible, OLED touchscreen.' },
            { name: 'Lenovo ThinkPad X1', price: 65000, icon: '💻', agent: 'Lenovo KE', seller: 'Lenovo Official', score: 93,
                desc: 'Business laptop, 14" 4K.' },
            { name: 'Asus ROG Zephyrus', price: 95000, icon: '💻', agent: 'Asus KE', seller: 'Asus Official', score: 92,
                desc: 'Gaming, RTX 4060, 16GB RAM.' },
            { name: 'Apple MacBook Pro 14"', price: 130000, icon: '💻', agent: 'Apple KE', seller: 'Apple Official',
                score: 98, desc: 'M3 Pro, 18GB RAM, 1TB SSD.' },
            { name: 'Samsung Galaxy Book 3', price: 70000, icon: '💻', agent: 'Samsung KE', seller: 'Samsung Official',
                score: 90, desc: 'ARM processor, 16GB, 512GB.' },
            { name: 'Canon EOS R50', price: 45000, icon: '📷', agent: 'Canon KE', seller: 'Canon Official', score: 93,
                desc: 'Mirrorless, 24.2MP, 4K video.' },
            { name: 'HP LaserJet Printer', price: 15000, icon: '🖨️', agent: 'HP KE', seller: 'HP Official', score: 91,
                desc: 'Wireless, duplex, fast print.' },
            { name: 'Dell Monitor 27" 4K', price: 18000, icon: '🖥️', agent: 'Dell KE', seller: 'Dell Official', score: 92,
                desc: 'IPS panel, HDR400, USB‑C.' },
            { name: 'Lenovo Tablet 10"', price: 25000, icon: '📋', agent: 'Lenovo KE', seller: 'Lenovo Official', score: 88,
                desc: 'Android, 128GB, stylus supported.' },
            { name: 'Asus ROG Mouse', price: 2500, icon: '🖱️', agent: 'Asus KE', seller: 'Asus Official', score: 87,
                desc: 'RGB, 16,000 DPI, wireless.' },
            { name: 'Seagate External SSD 1TB', price: 8000, icon: '💾', agent: 'Seagate KE', seller: 'Seagate Official',
                score: 89, desc: 'USB 3.2, 1050MB/s.' },
            { name: 'Logitech Webcam C922', price: 2500, icon: '📷', agent: 'Logitech KE', seller: 'Logitech Official',
                score: 88, desc: '1080p, 60fps, dual mics.' },
            { name: 'Kingston RAM 16GB', price: 5000, icon: '🧠', agent: 'Kingston KE', seller: 'Kingston Official', score: 86,
                desc: 'DDR4 3200MHz, desktop.' },
            { name: 'Cooler Master CPU Cooler', price: 3000, icon: '❄️', agent: 'Cooler Master KE',
                seller: 'Cooler Master Official', score: 87, desc: 'Air cooler, RGB fan.' },
            { name: 'Anker USB‑C Hub', price: 2800, icon: '🔌', agent: 'Anker KE', seller: 'Anker Official', score: 88,
                desc: '7‑in‑1, HDMI, SD card.' }
        ];
        computing.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.1) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '💻', '⚡'
                ], !!oldPrice, 'COMPUTING'));
        });

        // ---- GAMING ----
        const gaming = [
            { name: 'PlayStation 5 (Digital)', price: 45000, icon: '🎮', agent: 'Sony KE', seller: 'Sony Official', score: 96,
                desc: '8K gaming, ultra‑fast SSD.' },
            { name: 'Xbox Series X', price: 48000, icon: '🎮', agent: 'Microsoft KE', seller: 'Microsoft Official', score: 95,
                desc: '4K, 1TB SSD, backward compatible.' },
            { name: 'Nintendo Switch OLED', price: 25000, icon: '🎮', agent: 'Nintendo KE', seller: 'Nintendo Official',
                score: 94, desc: '7" OLED, detachable Joy‑Cons.' },
            { name: 'Razer Blade 15 Gaming Laptop', price: 100000, icon: '💻', agent: 'Razer KE', seller: 'Razer Official',
                score: 93, desc: 'RTX 4070, 16GB RAM, 1TB SSD.' },
            { name: 'SteelSeries Arctis 7 Headset', price: 12000, icon: '🎧', agent: 'SteelSeries KE',
                seller: 'SteelSeries Official', score: 90, desc: 'Wireless, surround sound, 24hr battery.' },
            { name: 'Logitech G Pro Mouse', price: 3500, icon: '🖱️', agent: 'Logitech KE', seller: 'Logitech Official',
                score: 91, desc: 'Lightweight, 25,600 DPI.' },
            { name: 'Corsair K100 Keyboard', price: 8000, icon: '⌨️', agent: 'Corsair KE', seller: 'Corsair Official',
                score: 92, desc: 'RGB, mechanical, optical switches.' },
            { name: 'BenQ ZOWIE Monitor 24"', price: 12000, icon: '🖥️', agent: 'BenQ KE', seller: 'BenQ Official', score: 89,
                desc: '144Hz, 1ms, for e‑sports.' },
            { name: 'GameSir G7 Controller', price: 3000, icon: '🎮', agent: 'GameSir KE', seller: 'GameSir Official',
                score: 86, desc: 'Wired, for Xbox/PC, ergonomic.' },
            { name: 'Astro A50 Headset', price: 15000, icon: '🎧', agent: 'Astro KE', seller: 'Astro Official', score: 91,
                desc: 'Wireless, Dolby Atmos, base station.' },
            { name: 'Razer Kraken V3 Pro', price: 10000, icon: '🎧', agent: 'Razer KE', seller: 'Razer Official', score: 90,
                desc: 'Haptic feedback, THX spatial.' },
            { name: 'Elgato Stream Deck', price: 6000, icon: '⌨️', agent: 'Elgato KE', seller: 'Elgato Official', score: 88,
                desc: '15 LCD keys, for streaming.' },
            { name: 'NZXT H510 Case', price: 4000, icon: '🖥️', agent: 'NZXT KE', seller: 'NZXT Official', score: 87,
                desc: 'Mid‑tower, tempered glass.' },
            { name: 'Cooler Master 850W PSU', price: 6000, icon: '⚡', agent: 'Cooler Master KE',
                seller: 'Cooler Master Official', score: 86, desc: 'Gold certified, modular.' },
            { name: 'Samsung 980 PRO SSD 1TB', price: 15000, icon: '💾', agent: 'Samsung KE', seller: 'Samsung Official',
                score: 94, desc: 'NVMe, 7000 MB/s read.' },
            { name: 'Corsair Vengeance RAM 32GB', price: 8000, icon: '🧠', agent: 'Corsair KE', seller: 'Corsair Official',
                score: 88, desc: 'DDR5, 6000MHz, RGB.' }
        ];
        gaming.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.15) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '🎮', '⚡'
                ], !!oldPrice, 'GAMING'));
        });

        // ---- SUPERMARKET ----
        const supermarket = [
            { name: 'Cooking Oil 5L', price: 1200, icon: '🛢️', agent: 'PRK KE', seller: 'PRK Official', score: 86,
                desc: 'Pure vegetable oil.' },
            { name: 'Sugar 2kg', price: 400, icon: '🍬', agent: 'PRK KE', seller: 'PRK Official', score: 85,
                desc: 'Fine white sugar.' },
            { name: 'Rice 5kg', price: 800, icon: '🍚', agent: 'EABL KE', seller: 'EABL Official', score: 87,
                desc: 'Premium long‑grain.' },
            { name: 'Flour 2kg', price: 350, icon: '🍞', agent: 'Unilever KE', seller: 'Unilever Official', score: 84,
                desc: 'All‑purpose wheat flour.' },
            { name: 'Milk 1L', price: 150, icon: '🥛', agent: 'P&G KE', seller: 'P&G Official', score: 86,
                desc: 'Whole milk, fresh.' },
            { name: 'Bread loaf', price: 100, icon: '🍞', agent: 'Unilever KE', seller: 'Unilever Official', score: 85,
                desc: 'Freshly baked.' },
            { name: 'Eggs 30 pcs', price: 500, icon: '🥚', agent: 'P&G KE', seller: 'P&G Official', score: 87,
                desc: 'Grade A, free‑range.' },
            { name: 'Chicken Breast 1kg', price: 800, icon: '🍗', agent: 'EABL KE', seller: 'EABL Official', score: 86,
                desc: 'Boneless, skinless.' },
            { name: 'Pasta 500g', price: 200, icon: '🍝', agent: 'Unilever KE', seller: 'Unilever Official', score: 84,
                desc: 'Durum wheat, dried.' },
            { name: 'Tomatoes 1kg', price: 150, icon: '🍅', agent: 'PRK KE', seller: 'PRK Official', score: 85,
                desc: 'Fresh ripe tomatoes.' },
            { name: 'Onions 1kg', price: 120, icon: '🧅', agent: 'PRK KE', seller: 'PRK Official', score: 84,
                desc: 'Red onions.' },
            { name: 'Potatoes 2kg', price: 250, icon: '🥔', agent: 'EABL KE', seller: 'EABL Official', score: 85,
                desc: 'Irish potatoes.' },
            { name: 'Bananas 1kg', price: 200, icon: '🍌', agent: 'Unilever KE', seller: 'Unilever Official', score: 86,
                desc: 'Ripe bananas.' },
            { name: 'Apples 1kg', price: 300, icon: '🍎', agent: 'P&G KE', seller: 'P&G Official', score: 87,
                desc: 'Fresh apples.' },
            { name: 'Orange Juice 1L', price: 250, icon: '🍊', agent: 'PRK KE', seller: 'PRK Official', score: 86,
                desc: '100% pure juice.' },
            { name: 'Cheese 500g', price: 400, icon: '🧀', agent: 'Unilever KE', seller: 'Unilever Official', score: 85,
                desc: 'Cheddar block.' }
        ];
        supermarket.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.1) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '🛒', '🏠'
                ], !!oldPrice, 'SUPERMARKET'));
        });

        // ---- BABY PRODUCTS ----
        const baby = [
            { name: 'Huggies Diapers (M)', price: 1200, icon: '🧷', agent: 'Huggies KE', seller: 'Huggies Official',
                score: 90, desc: 'Super absorbent, size M (4‑8kg).' },
            { name: 'Huggies Diapers (L)', price: 1400, icon: '🧷', agent: 'Huggies KE', seller: 'Huggies Official',
                score: 91, desc: 'Size L (8‑14kg), dry touch.' },
            { name: 'Huggies Wipes', price: 400, icon: '🧻', agent: 'Huggies KE', seller: 'Huggies Official', score: 88,
                desc: 'Aloe vera, 80 wipes.' },
            { name: 'Huggies Baby Lotion', price: 500, icon: '🧴', agent: 'Huggies KE', seller: 'Huggies Official', score: 87,
                desc: 'Gentle, shea butter.' },
            { name: 'Johnson’s Baby Shampoo', price: 300, icon: '🧴', agent: 'Johnson’s KE', seller: 'Johnson’s Official',
                score: 89, desc: 'No tears, mild formula.' },
            { name: 'Johnson’s Baby Oil', price: 400, icon: '🧴', agent: 'Johnson’s KE', seller: 'Johnson’s Official',
                score: 88, desc: 'For massage, moisturizes.' },
            { name: 'Pampers Diapers (M)', price: 1300, icon: '🧷', agent: 'Pampers KE', seller: 'Pampers Official', score: 90,
                desc: 'Size M, 3‑way fit.' },
            { name: 'Pampers Diapers (L)', price: 1500, icon: '🧷', agent: 'Pampers KE', seller: 'Pampers Official', score: 91,
                desc: 'Size L, extra absorb.' },
            { name: 'Baby Bottle Set', price: 600, icon: '🍼', agent: 'Chicco KE', seller: 'Chicco Official', score: 87,
                desc: '2 bottles + nipples.' },
            { name: 'Nuby Pacifier', price: 200, icon: '🍼', agent: 'Nuby KE', seller: 'Nuby Official', score: 84,
                desc: 'Silicone, 0‑6 months.' },
            { name: 'Fisher‑Price Baby Monitor', price: 3000, icon: '📡', agent: 'Fisher‑Price KE',
                seller: 'Fisher‑Price Official', score: 89, desc: 'Audio/video, night vision.' },
            { name: 'Baby Stroller', price: 8000, icon: '🚼', agent: 'Graco KE', seller: 'Graco Official', score: 90,
                desc: 'Lightweight, foldable.' },
            { name: 'Car Seat', price: 5000, icon: '🚗', agent: 'Maxi‑Cosi KE', seller: 'Maxi‑Cosi Official', score: 91,
                desc: 'Group 0+, side protection.' },
            { name: 'Baby Carrier', price: 2500, icon: '🧸', agent: 'Ergobaby KE', seller: 'Ergobaby Official', score: 88,
                desc: 'Ergonomic, 4 positions.' },
            { name: 'Play Mat', price: 1500, icon: '🧩', agent: 'Skip Hop KE', seller: 'Skip Hop Official', score: 86,
                desc: 'Non‑slip, 140x200cm.' },
            { name: 'Baby Toothbrush', price: 200, icon: '🪥', agent: 'Nuby KE', seller: 'Nuby Official', score: 83,
                desc: 'Soft bristles, finger style.' }
        ];
        baby.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.1) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '👶', '⭐'
                ], !!oldPrice, 'BABY PRODUCTS'));
        });

        // ---- OTHER CATEGORIES (catch‑all) ----
        // Add a few random items that don't fit elsewhere
        const otherItems = [
            { name: 'Bicycle 26"', price: 12000, icon: '🚲', agent: 'Sports KE', seller: 'Sports World', score: 85,
                desc: 'Mountain bike, 21 speeds.' },
            { name: 'Camping Tent 4‑person', price: 6000, icon: '⛺', agent: 'Outdoor KE', seller: 'Outdoor Store', score: 84,
                desc: 'Waterproof, 2‑season.' },
            { name: 'Fishing Rod Kit', price: 3000, icon: '🎣', agent: 'Fishing KE', seller: 'Fishing Store', score: 82,
                desc: '2‑piece rod, reel included.' },
            { name: 'Guitar (Acoustic)', price: 8000, icon: '🎸', agent: 'Music KE', seller: 'Music Store', score: 87,
                desc: 'Full‑size, steel strings.' },
            { name: 'Puzzles 1000 pcs', price: 500, icon: '🧩', agent: 'Toys KE', seller: 'Toy Store', score: 80,
                desc: 'Landscape image, boxed.' },
            { name: 'Board Game Monopoly', price: 2000, icon: '🎲', agent: 'Games KE', seller: 'Game Store', score: 86,
                desc: 'Family edition.' },
            { name: 'Pet Dog Harness', price: 1500, icon: '🐕', agent: 'Pets KE', seller: 'Pet Store', score: 83,
                desc: 'Adjustable, reflective strips.' },
            { name: 'Gardening Tool Set', price: 2500, icon: '🌱', agent: 'Garden KE', seller: 'Garden Store', score: 84,
                desc: '5 tools, with gloves.' }
        ];
        otherItems.forEach(p => {
            const id = idCounter++;
            const oldPrice = Math.random() > 0.5 ? Math.floor(p.price * 1.1) : null;
            products.push(makeProduct(id, p.name, p.price, oldPrice, p.icon, p.agent, p.seller, p.score, p.desc, [p.icon,
                    '📦', '🔄'
                ], !!oldPrice, 'OTHER CATEGORIES'));
        });

        // ─── CATEGORIES (new list) ──────────────────────────────
        const categories = [
            { key: 'Official Stores', label: 'Official Stores', icon: '🏪' },
            { key: 'PHONES & TABLETS', label: 'PHONES & TABLETS', icon: '📱' },
            { key: 'TVs & AUDIO', label: 'TVs & AUDIO', icon: '📺' },
            { key: 'APPLIANCES', label: 'APPLIANCES', icon: '🔌' },
            { key: 'HEALTH & BEAUTY', label: 'HEALTH & BEAUTY', icon: '💄' },
            { key: 'HOME & OFFICE', label: 'HOME & OFFICE', icon: '🏢' },
            { key: 'FASHION', label: 'FASHION', icon: '👗' },
            { key: 'COMPUTING', label: 'COMPUTING', icon: '💻' },
            { key: 'GAMING', label: 'GAMING', icon: '🎮' },
            { key: 'SUPERMARKET', label: 'SUPERMARKET', icon: '🛒' },
            { key: 'BABY PRODUCTS', label: 'BABY PRODUCTS', icon: '👶' },
            { key: 'OTHER CATEGORIES', label: 'OTHER CATEGORIES', icon: '📦' }
        ];

        // ─── BRANDS (official brands list) ──────────────────────
        const officialBrandsList = [
            'Samsung', 'Tecno', 'Infinix', 'Itel', 'Oraimo',
            'Solarmax', 'Nunix', 'Annov', 'Redberry', 'Miniso',
            'adidas', 'Ecko Unltd', 'DeFacto',
            'Vitron', 'Vision Plus', 'TCL', 'Hisense', 'Multichoice',
            'Garnier', 'Nivea', 'Maybelline', 'MAC', 'Nice & Lovely',
            'Huggies',
            'Roch', 'Ramtons', 'Hotpoint', 'Mika',
            'HP', 'Lenovo', 'Dell', 'Canon', 'Asus',
            'PRK', 'EABL', 'Unilever', 'P&G'
        ];

        // ─── STATE ──────────────────────────────────────────────
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        let currentProduct = null;
        let currentQuantity = 1;
        let currentImageIndex = 0;
        let currentCategory = 'Official Stores'; // default
        let currentBrand = null;
        let currentFilteredProducts = [];

        // ─── DOM refs ────────────────────────────────────────────
        const heroSection = document.getElementById('heroSection');
        const gridWrap = document.getElementById('productGridWrap');
        const productGrid = document.getElementById('productGrid');
        const gridTitle = document.getElementById('gridTitle');
        const gridCount = document.getElementById('gridCount');
        const categoryGrid = document.getElementById('categoryGrid');
        const brandList = document.getElementById('brandList');
        const brandItemsContainer = document.getElementById('brandItemsContainer');

        // ─── Render categories ──────────────────────────────────
        function renderCategories() {
            categoryGrid.innerHTML = '';
            categories.forEach(cat => {
                const div = document.createElement('div');
                div.className = `category-item${currentCategory === cat.key ? ' active' : ''}`;
                div.dataset.category = cat.key;
                div.innerHTML = `
                    <div class="category-icon">${cat.icon}</div>
                    <div class="category-name">${cat.label}</div>
                `;
                div.addEventListener('click', function(e) {
                    e.preventDefault();
                    const key = this.dataset.category;
                    if (key === 'Official Stores') {
                        currentBrand = null;
                        showBrands(true);
                    } else {
                        showBrands(false);
                        currentBrand = null;
                    }
                    filterProducts(key);
                });
                categoryGrid.appendChild(div);
            });
        }

        // ─── Show/hide brand list ──────────────────────────────
        function showBrands(show) {
            if (show) {
                brandList.classList.add('open');
                renderBrands();
            } else {
                brandList.classList.remove('open');
            }
        }

        // ─── Render brands ──────────────────────────────────────
        function renderBrands() {
            brandItemsContainer.innerHTML = '';
            officialBrandsList.forEach(b => {
                const span = document.createElement('span');
                span.className = `brand-item${currentBrand === b ? ' active-brand' : ''}`;
                span.textContent = b;
                span.addEventListener('click', function() {
                    currentBrand = b;
                    const filtered = products.filter(p =>
                        p.category === 'Official Stores' && p.brand === b
                    );
                    if (filtered.length > 0) {
                        currentFilteredProducts = filtered;
                        currentCategory = 'Official Stores';
                        renderProductGrid(currentFilteredProducts);
                        document.querySelectorAll('.brand-item').forEach(el => {
                            el.classList.toggle('active-brand', el.textContent === b);
                        });
                        gridTitle.innerHTML = `${b} <span id="gridCount"></span>`;
                        gridCount.innerText = `(${filtered.length} items)`;
                        gridWrap.classList.add('visible');
                        heroSection.style.display = 'none';
                        gridWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        alert(`No products found for brand: ${b}`);
                    }
                });
                brandItemsContainer.appendChild(span);
            });
        }

        // ─── Filter products ────────────────────────────────────
        function filterProducts(categoryKey) {
            currentCategory = categoryKey;
            document.querySelectorAll('.category-item').forEach(el => {
                el.classList.toggle('active', el.dataset.category === categoryKey);
            });

            let categoryName = categoryKey;
            let filtered = [];

            if (categoryKey === 'Official Stores') {
                filtered = products.filter(p => p.category === 'Official Stores');
                showBrands(true);
            } else {
                filtered = products.filter(p => p.category === categoryKey);
                showBrands(false);
                currentBrand = null;
            }

            currentFilteredProducts = filtered;
            renderProductGrid(filtered);
            gridTitle.innerHTML = `${categoryName} <span id="gridCount"></span>`;
            gridCount.innerText = `(${filtered.length} items)`;
            gridWrap.classList.add('visible');
            heroSection.style.display = 'none';
            gridWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ─── Render product grid (2 rows, vertical scroll) ──────
        function renderProductGrid(items) {
            if (!items || items.length === 0) {
                productGrid.innerHTML =
                    `<div class="empty-state"><i>📭</i> No products in this category.</div>`;
                return;
            }
            productGrid.innerHTML = items.map(p => `
                <div class="product-card" onclick="displayProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                    <div class="product-img">${p.icon}</div>
                    <div class="product-info">
                        <div class="product-name">${p.name}</div>
                        <div class="product-price">KES ${p.price.toLocaleString()}</div>
                    </div>
                </div>
            `).join('');
        }

        // ─── Display product (hero) ─────────────────────────────
        function displayProduct(p) {
            currentProduct = p;
            currentQuantity = 1;
            currentImageIndex = 0;
            document.getElementById('quantityValue').innerText = currentQuantity;
            document.getElementById('productName').innerHTML = p.name;
            let priceHtml = `KES ${p.price.toLocaleString()}`;
            if (p.hasDiscount && p.oldPrice) {
                priceHtml =
                    `KES ${p.price.toLocaleString()} <span style="font-size:12px; text-decoration:line-through;">KES ${p.oldPrice.toLocaleString()}</span> <span style="background:#ff0000; color:white; padding:1px 5px; border-radius:20px; font-size:9px;">-${p.discountPercent}%</span>`;
            }
            document.getElementById('priceDisplay').innerHTML = priceHtml;
            document.getElementById('agentInfo').innerHTML = `🏪 Shop: ${p.agent}`;
            document.getElementById('productDescription').innerHTML = p.description;
            document.getElementById('sellerName').innerHTML = p.seller;
            document.getElementById('sellerScore').innerHTML = `${p.sellerScore}%`;
            document.getElementById('mainImage').innerHTML = p.images[0];
            let thumb = '';
            p.images.forEach((img, i) => {
                thumb +=
                    `<div class="thumbnail ${i===0?'active':''}" onclick="setImage(${i})">${img}</div>`;
            });
            document.getElementById('thumbnailStrip').innerHTML = thumb;
            document.getElementById('shopName').innerHTML = p.agent;
            renderSameShopProducts(p.agent);
            gridWrap.classList.remove('visible');
            heroSection.style.display = 'block';
            heroSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ─── Continue Shopping ──────────────────────────────────
        function continueShopping() {
            if (!gridWrap.classList.contains('visible')) {
                if (currentCategory === 'Official Stores' && currentBrand) {
                    const filtered = products.filter(p =>
                        p.category === 'Official Stores' && p.brand === currentBrand
                    );
                    if (filtered.length > 0) {
                        currentFilteredProducts = filtered;
                        renderProductGrid(filtered);
                        gridTitle.innerHTML = `${currentBrand} <span id="gridCount"></span>`;
                        gridCount.innerText = `(${filtered.length} items)`;
                        gridWrap.classList.add('visible');
                        heroSection.style.display = 'none';
                    } else {
                        filterProducts(currentCategory);
                    }
                } else {
                    filterProducts(currentCategory);
                }
            }
        }

        // ─── Render "More from this shop" ──────────────────────
        function renderSameShopProducts(agentName) {
            let sameShop = products.filter(p => p.agent === agentName && p.id !== currentProduct.id).slice(0, 6);
            let grid = document.getElementById('sameShopGrid');
            if (sameShop.length === 0) { grid.innerHTML = '<div style="padding:20px;">No other products</div>'; return; }
            grid.innerHTML = sameShop.map(p =>
                `<div class="shop-product-card" onclick="displayProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})"><div class="shop-product-img">${p.icon}</div><div class="shop-product-info"><div class="shop-product-name">${p.name}</div><div class="shop-product-price">KES ${p.price.toLocaleString()}</div></div></div>`
                ).join('');
        }

        // ─── Cart functions ──────────────────────────────────────
        function updateCartCount() {
            document.getElementById('cartCount').innerText = cart.reduce((s, i) => s + i.qty, 0);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCartModal();
        }

        function addToCart(p, q) {
            let existing = cart.find(i => i.id === p.id);
            if (existing) { existing.qty += q; } else { cart.unshift({ ...p, qty: q, addedAt: Date.now() }); }
            updateCartCount();
            alert(`✓ ${p.name} added to cart`);
        }

        function updateCartItemQty(id, delta) {
            let item = cart.find(i => i.id === id);
            if (item) { item.qty += delta; if (item.qty <= 0) { cart = cart.filter(i => i.id !== id); } updateCartCount(); }
        }

        function deleteCartItem(id) { cart = cart.filter(i => i.id !== id);
            updateCartCount();
            alert('Item removed from cart'); }

        function visitShop(agentName) { alert(`Visit ${agentName} shop - Feature coming soon`); }

        function renderCartModal() {
            let container = document.getElementById('cartItemsList');
            if (cart.length === 0) { container.innerHTML = '<div style="text-align:center; padding:20px;">Your cart is empty</div>';
                document.getElementById('cartTotal').innerHTML = ''; return; }
            let itemsHtml = '',
                subtotal = 0;
            cart.forEach((item) => {
                let itemTotal = item.price * item.qty;
                subtotal += itemTotal;
                itemsHtml += `<div class="cart-item"><div class="cart-item-info"><div class="cart-item-name">${item.name}</div><div class="cart-item-price">KES ${item.price.toLocaleString()}</div><div class="cart-item-agent">🏪 ${item.agent}</div><div class="cart-item-quantity"><button class="cart-qty-btn" onclick="updateCartItemQty(${item.id}, -1)">−</button><span>${item.qty}</span><button class="cart-qty-btn" onclick="updateCartItemQty(${item.id}, 1)">+</button></div></div><div class="cart-item-actions"><button class="delete-item" onclick="deleteCartItem(${item.id})">🗑 Delete</button><button class="visit-shop" onclick="visitShop('${item.agent}')">🏪 Visit Shop</button></div></div>`;
            });
            let delivery = 150,
                total = subtotal + delivery;
            document.getElementById('cartItemsList').innerHTML = itemsHtml;
            document.getElementById('cartTotal').innerHTML =
                `<div class="cart-total-row"><span>Subtotal</span><span>KES ${subtotal.toLocaleString()}</span></div><div class="cart-total-row"><span>Delivery Fee</span><span>KES ${delivery}</span></div><div class="cart-total-grand">Total: KES ${total.toLocaleString()}</div>`;
        }

        function openCartModal() { renderCartModal();
            document.getElementById('cartModal').style.display = 'flex'; }

        function closeCartModal() { document.getElementById('cartModal').style.display = 'none'; }

        // ─── Other helpers ──────────────────────────────────────
        function scrollSameShop(d) { document.getElementById('sameShopGrid').scrollLeft += d === 'left' ? -150 : 150; }

        function changeImage(d) {
            if (!currentProduct) return;
            currentImageIndex = (currentImageIndex + d + currentProduct.images.length) % currentProduct.images.length;
            document.getElementById('mainImage').innerHTML = currentProduct.images[currentImageIndex];
            document.querySelectorAll('.thumbnail').forEach((t, i) => t.classList.toggle('active', i === currentImageIndex));
        }

        function setImage(i) {
            if (!currentProduct) return;
            currentImageIndex = i;
            document.getElementById('mainImage').innerHTML = currentProduct.images[i];
            document.querySelectorAll('.thumbnail').forEach((t, idx) => t.classList.toggle('active', idx === i));
        }

        function changeQuantity(d) { let n = currentQuantity + d; if (n >= 1) currentQuantity = n;
            document.getElementById('quantityValue').innerText = currentQuantity; }

        function handleAddToCart() { if (currentProduct) addToCart(currentProduct, currentQuantity); }

        function handleCheckoutNow() { handleAddToCart();
            openCartModal(); }

        function searchProducts() {
            let t = document.getElementById('searchInput').value.toLowerCase();
            let f = products.find(p => p.name.toLowerCase().includes(t));
            if (f) displayProduct(f);
            else alert('No product found');
        }

        // ─── Delivery ────────────────────────────────────────────
        const townData = {
            "Nairobi": ["Nairobi CBD", "Westlands", "Karen"],
            "Mombasa": ["Mombasa CBD", "Nyali", "Bamburi"],
            "Kisumu": ["Kisumu CBD", "Milimani", "Kondele"],
            "Nakuru": ["Nakuru Town", "Naivasha", "Gilgil"],
            "Kiambu": ["Kiambu Town", "Thika", "Limuru"],
            "Kitui": ["Kitui Town", "Mwingi", "Mutomo"]
        };

        function updateTownOptions() {
            let county = document.getElementById('countySelect').value;
            let townSelect = document.getElementById('townSelect');
            townSelect.innerHTML = '<option value="">-- Select Town --</option>';
            if (county && townData[county]) {
                townData[county].forEach(town => { townSelect.innerHTML += `<option value="${town}">${town}</option>`; });
                townSelect.innerHTML += '<option value="OTHER">--- OTHER ---</option>';
            }
            document.getElementById('otherTownDiv').style.display = 'none';
            updateDeliveryFee();
        }

        function checkOtherTown() {
            let townSelect = document.getElementById('townSelect');
            document.getElementById('otherTownDiv').style.display = townSelect.value === 'OTHER' ? 'block' : 'none';
            if (townSelect.value !== 'OTHER') updateDeliveryFee();
        }

        function selectPickupStation() {
            let stations = ["Kitui Central - Post Office", "Nairobi CBD - Moi Avenue", "Mombasa CBD - Digo Road",
                "Kisumu CBD - Oginga Street"
            ];
            let randomStation = stations[Math.floor(Math.random() * stations.length)];
            document.getElementById('pickupStation').innerHTML = randomStation;
            alert(`Pickup station set to: ${randomStation}`);
            updateDeliveryFee();
        }

        function updateDeliveryFee() {
            let county = document.getElementById('countySelect').value;
            let fees = { "Nairobi": 150, "Mombasa": 350, "Kisumu": 250, "Nakuru": 180, "Kiambu": 120, "Kitui": 210 };
            let fee = fees[county] || 250;
            document.getElementById('deliveryFeeDisplay').innerHTML = `Delivery Fee: KSh ${fee}`;
            let date = new Date();
            let deliveryDate = new Date(date.setDate(date.getDate() + 3)).toLocaleDateString('en-GB', { day: 'numeric',
                month: 'short' });
            document.getElementById('deliveryDate').innerHTML = `Est. delivery: ${deliveryDate}`;
        }

        function saveDeliveryInfo() {
            let name = document.getElementById('fullName').value;
            let phone = document.getElementById('phoneNumber').value;
            let county = document.getElementById('countySelect').value;
            let town = document.getElementById('townSelect').value;
            if (town === 'OTHER') town = document.getElementById('otherTown').value;
            let street = document.getElementById('streetEstate').value;
            if (!name || !phone || !county || !town || !street) { alert('Please fill all delivery fields'); return; }
            let summary = `📍 ${street}, ${town}, ${county}\n📞 ${phone}`;
            document.getElementById('deliverySummary').innerHTML = summary;
            document.getElementById('deliverySummary').style.display = 'block';
            localStorage.setItem('deliveryInfo', JSON.stringify({ name, phone, county, town, street }));
            alert('✅ Delivery information saved!');
        }

        // ─── "What's New" & "Recommended" load more ──────────────
        let whatsNewPage = 1,
            recommendedPage = 1;
        const itemsPerPage = 8;

        function loadMoreWhatsNew() {
            whatsNewPage++;
            const end = whatsNewPage * itemsPerPage;
            const moreProducts = products.slice(0, end);
            document.getElementById('whatsNewGrid').innerHTML = moreProducts.map(p =>
                `<div class="product-card" onclick="displayProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})"><div class="product-img">${p.icon}</div><div class="product-info"><div class="product-name">${p.name}</div><div class="product-price">KES ${p.price.toLocaleString()}</div></div></div>`
            ).join('');
            if (end >= products.length) document.getElementById('whatsNewMoreBtn').style.display = 'none';
        }

        function loadMoreRecommended() {
            recommendedPage++;
            const end = recommendedPage * itemsPerPage;
            const moreProducts = products.slice(0, end);
            document.getElementById('recommendedGrid').innerHTML = moreProducts.map(p =>
                `<div class="product-card" onclick="displayProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})"><div class="product-img">${p.icon}</div><div class="product-info"><div class="product-name">${p.name}</div><div class="product-price">KES ${p.price.toLocaleString()}</div></div></div>`
            ).join('');
            if (end >= products.length) document.getElementById('recommendedMoreBtn').style.display = 'none';
        }

        // ─── Init ─────────────────────────────────────────────────
        function init() {
            renderCategories();
            // Default: show Official Stores products, hero visible with first product
            currentCategory = 'Official Stores';
            const defaultProducts = products.filter(p => p.category === 'Official Stores');
            currentFilteredProducts = defaultProducts;
            renderProductGrid(defaultProducts);
            gridTitle.innerHTML = `Official Stores <span id="gridCount"></span>`;
            gridCount.innerText = `(${defaultProducts.length} items)`;
            gridWrap.classList.add('visible');
            heroSection.style.display = 'none'; // start with grid

            // ─── Load product from URL if present ───────────────
            const urlParams = new URLSearchParams(window.location.search);
            const productIdFromUrl = urlParams.get('product');
            if (productIdFromUrl) {
                const targetProduct = products.find(p => p.id == productIdFromUrl);
                if (targetProduct) {
                    displayProduct(targetProduct);
                    // Hide grid and show hero
                    gridWrap.classList.remove('visible');
                    heroSection.style.display = 'block';
                }
            } else {
                // Show first product as hero (if products exist)
                if (products.length > 0) {
                    currentProduct = products[0];
                    displayProduct(products[0]);
                    // re‑show grid after displayProduct
                    gridWrap.classList.add('visible');
                    heroSection.style.display = 'none';
                }
            }

            // Render "What's New" and "Recommended"
            document.getElementById('whatsNewGrid').innerHTML = products.slice(0, 8).map(p =>
                `<div class="product-card" onclick="displayProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})"><div class="product-img">${p.icon}</div><div class="product-info"><div class="product-name">${p.name}</div><div class="product-price">KES ${p.price.toLocaleString()}</div></div></div>`
            ).join('');
            document.getElementById('recommendedGrid').innerHTML = products.slice(0, 8).map(p =>
                `<div class="product-card" onclick="displayProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})"><div class="product-img">${p.icon}</div><div class="product-info"><div class="product-name">${p.name}</div><div class="product-price">KES ${p.price.toLocaleString()}</div></div></div>`
            ).join('');
            updateCartCount();
            // Rotating offer
            const offerItems = ["🎧 Headphones 50% OFF", "⌚ Smart Watch KES 4,500", "☕ Coffee Maker -52%"];
            let offerIndex = 0;
            setInterval(() => {
                offerIndex = (offerIndex + 1) % offerItems.length;
                document.getElementById('rotatingOffer').innerHTML = offerItems[offerIndex];
            }, 3000);
            // Countdown
            let hours = 23,
                minutes = 59,
                seconds = 59;
            setInterval(() => {
                if (seconds === 0) { if (minutes === 0) { if (hours === 0) { hours = 23;
                        minutes = 59;
                        seconds = 59; } else { hours--;
                        minutes = 59;
                        seconds = 59; } } else { minutes--;
                        seconds = 59; } } else seconds--;
                document.getElementById('countdownTimer').innerHTML =
                    `${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
            }, 1000);
        }

        // ─── Expose globals ──────────────────────────────────────
        window.changeImage = changeImage;
        window.setImage = setImage;
        window.changeQuantity = changeQuantity;
        window.openCartModal = openCartModal;
        window.closeCartModal = closeCartModal;
        window.searchProducts = searchProducts;
        window.displayProduct = displayProduct;
        window.continueShopping = continueShopping;
        window.updateTownOptions = updateTownOptions;
        window.checkOtherTown = checkOtherTown;
        window.scrollSameShop = scrollSameShop;
        window.updateCartItemQty = updateCartItemQty;
        window.deleteCartItem = deleteCartItem;
        window.visitShop = visitShop;
        window.selectPickupStation = selectPickupStation;
        window.saveDeliveryInfo = saveDeliveryInfo;
        window.loadMoreWhatsNew = loadMoreWhatsNew;
        window.loadMoreRecommended = loadMoreRecommended;
        window.filterProducts = filterProducts;
        window.handleAddToCart = handleAddToCart;
        window.handleCheckoutNow = handleCheckoutNow;

        document.getElementById('addToCartBtn').onclick = handleAddToCart;
        document.getElementById('checkoutNowBtn').onclick = handleCheckoutNow;
        document.getElementById('continueBtn').onclick = continueShopping;
        document.getElementById('cartCheckoutBtn').onclick = () => { closeCartModal();
            alert('Proceed to payment'); };
        document.getElementById('whatsNewMoreBtn').onclick = loadMoreWhatsNew;
        document.getElementById('recommendedMoreBtn').onclick = loadMoreRecommended;

        // ─── Start ───────────────────────────────────────────────
        init();

        // ─── Auto‑refresh from server (fetches /api/products) ──
        async function fetchProductsFromServer() {
            try {
                const response = await fetch('/api/products');
                if (!response.ok) throw new Error('Network response was not ok');
                const serverProducts = await response.json();

                // Transform server data to match the expected structure
                const transformed = serverProducts.map(p => ({
                    id: p.id,
                    name: p.name,
                    price: p.price_kes,
                    oldPrice: p.discount_percent > 0 ? Math.round(p.price_kes / (1 - p.discount_percent / 100)) : null,
                    icon: (p.images && p.images.length) ? '🛍️' : '📦',
                    agent: p.agent_name || p.agent_id || 'Unknown',
                    seller: p.agent_name || 'Unknown',
                    sellerScore: 90,
                    description: p.description || '',
                    images: (p.images && p.images.length) ? p.images : ['📦', '⚡', '🌟'],
                    hasDiscount: p.discount_percent > 0,
                    discountPercent: p.discount_percent || 0,
                    category: p.category || 'Uncategorized',
                    brand: null,
                    stock: p.stock,
                    status: p.status || 'in_stock'
                }));

                // Replace global products array
                products.length = 0;
                products.push(...transformed);

                // Re‑apply current filter and re‑render the UI
                const currentCategoryKey = currentCategory || 'Official Stores';
                filterProducts(currentCategoryKey);

                // Re‑display product from URL if present
                const urlParams = new URLSearchParams(window.location.search);
                const productIdFromUrl = urlParams.get('product');
                if (productIdFromUrl) {
                    const targetProduct = products.find(p => p.id == productIdFromUrl);
                    if (targetProduct) {
                        displayProduct(targetProduct);
                        gridWrap.classList.remove('visible');
                        heroSection.style.display = 'block';
                    }
                }

                // Also refresh "What's New" and "Recommended"
                whatsNewPage = 1;
                recommendedPage = 1;
                loadMoreWhatsNew();
                loadMoreRecommended();

            } catch (error) {
                console.error('Failed to fetch products:', error);
                // Silent fail – keep static data
            }
        }

        // Fetch once immediately after init, then every 30 seconds
        setTimeout(() => {
            fetchProductsFromServer();
        }, 500); // small delay to let init finish

        setInterval(fetchProductsFromServer, 30000);
    </script>
</body>
</html>