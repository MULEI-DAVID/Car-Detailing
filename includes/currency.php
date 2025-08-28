<?php
/**
 * Currency Helper Functions
 * 
 * Handles currency conversion, formatting, and display for the Car Detailing system.
 */

require_once __DIR__ . '/../database/config.php';

/**
 * Get current user's preferred currency from session
 */
function getCurrentCurrency() {
    if (isset($_SESSION['user_currency'])) {
        return $_SESSION['user_currency'];
    }
    return DEFAULT_CURRENCY;
}

/**
 * Set user's preferred currency
 */
function setCurrentCurrency($currency) {
    if (in_array($currency, SUPPORTED_CURRENCIES)) {
        $_SESSION['user_currency'] = $currency;
        return true;
    }
    return false;
}

/**
 * Convert amount from KES to target currency
 */
function convertCurrency($amount, $fromCurrency = 'KES', $toCurrency = null) {
    if ($toCurrency === null) {
        $toCurrency = getCurrentCurrency();
    }
    
    if ($fromCurrency === $toCurrency) {
        return $amount;
    }
    
    // Convert to KES first if not already in KES
    if ($fromCurrency !== 'KES') {
        $amount = $amount / CURRENCY_RATES[$fromCurrency];
    }
    
    // Convert from KES to target currency
    if ($toCurrency !== 'KES') {
        $amount = $amount * CURRENCY_RATES[$toCurrency];
    }
    
    return round($amount, 2);
}

/**
 * Format currency for display
 */
function formatCurrency($amount, $currency = null) {
    if ($currency === null) {
        $currency = getCurrentCurrency();
    }
    
    $symbol = CURRENCY_SYMBOLS[$currency];
    $formattedAmount = number_format($amount, 2);
    
    // Different formatting for different currencies
    switch ($currency) {
        case 'KES':
            return "KSh " . $formattedAmount;
        case 'EUR':
            return "€" . $formattedAmount;
        case 'USD':
            return "$" . $formattedAmount;
        default:
            return $symbol . " " . $formattedAmount;
    }
}

/**
 * Get currency options for select dropdown
 */
function getCurrencyOptions($selectedCurrency = null) {
    if ($selectedCurrency === null) {
        $selectedCurrency = getCurrentCurrency();
    }
    
    $options = '';
    foreach (SUPPORTED_CURRENCIES as $currency) {
        $selected = ($currency === $selectedCurrency) ? 'selected' : '';
        $name = CURRENCY_NAMES[$currency];
        $symbol = CURRENCY_SYMBOLS[$currency];
        
        $options .= "<option value='$currency' $selected>$symbol - $name</option>";
    }
    
    return $options;
}

/**
 * Get currency flag emoji
 */
function getCurrencyFlag($currency) {
    $flags = [
        'KES' => '🇰🇪',
        'EUR' => '🇪🇺',
        'USD' => '🇺🇸'
    ];
    
    return $flags[$currency] ?? '💰';
}

/**
 * Update all prices in the system to new currency
 */
function updatePricesToCurrency($prices, $targetCurrency = null) {
    if ($targetCurrency === null) {
        $targetCurrency = getCurrentCurrency();
    }
    
    $updatedPrices = [];
    foreach ($prices as $key => $price) {
        $updatedPrices[$key] = convertCurrency($price, 'KES', $targetCurrency);
    }
    
    return $updatedPrices;
}

/**
 * Calculate total with tax in current currency
 */
function calculateTotalWithTax($subtotal, $currency = null) {
    if ($currency === null) {
        $currency = getCurrentCurrency();
    }
    
    $taxAmount = $subtotal * TAX_RATE;
    $total = $subtotal + $taxAmount;
    
    return [
        'subtotal' => $subtotal,
        'tax_rate' => TAX_RATE * 100,
        'tax_amount' => $taxAmount,
        'total' => $total,
        'currency' => $currency
    ];
}

/**
 * Display currency selector component
 */
function displayCurrencySelector($class = '') {
    $currentCurrency = getCurrentCurrency();
    $flag = getCurrencyFlag($currentCurrency);
    $symbol = CURRENCY_SYMBOLS[$currentCurrency];
    
    $html = "
    <div class='currency-selector-wrapper $class'>
        <select id='currency-selector' class='currency-selector' onchange='changeCurrency(this.value)'>
            " . getCurrencyOptions($currentCurrency) . "
        </select>
    </div>";
    
    return $html;
}

/**
 * Get currency exchange rates (placeholder for real API)
 */
function getExchangeRates() {
    // In a real application, you would fetch this from an API
    // For now, we'll use static rates
    return CURRENCY_RATES;
}

/**
 * Validate currency code
 */
function isValidCurrency($currency) {
    return in_array($currency, SUPPORTED_CURRENCIES);
}

/**
 * Get currency information
 */
function getCurrencyInfo($currency) {
    if (!isValidCurrency($currency)) {
        return null;
    }
    
    return [
        'code' => $currency,
        'name' => CURRENCY_NAMES[$currency],
        'symbol' => CURRENCY_SYMBOLS[$currency],
        'flag' => getCurrencyFlag($currency),
        'rate' => CURRENCY_RATES[$currency]
    ];
}

/**
 * Format price range
 */
function formatPriceRange($minPrice, $maxPrice, $currency = null) {
    if ($currency === null) {
        $currency = getCurrentCurrency();
    }
    
    $minFormatted = formatCurrency($minPrice, $currency);
    $maxFormatted = formatCurrency($maxPrice, $currency);
    
    return "$minFormatted - $maxFormatted";
}

/**
 * AJAX handler for currency change
 */
if (isset($_POST['action']) && $_POST['action'] === 'change_currency') {
    $newCurrency = $_POST['currency'] ?? '';
    
    if (isValidCurrency($newCurrency)) {
        setCurrentCurrency($newCurrency);
        echo json_encode(['success' => true, 'currency' => $newCurrency]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid currency']);
    }
    exit;
}
?>
