<?php
/**
 * Currency Helper Functions for Ethiopian Birr (ETB)
 */

/**
 * Format currency amount in Ethiopian Birr
 * @param float $amount The amount to format
 * @param bool $showSymbol Whether to show the currency symbol
 * @return string Formatted currency string
 */
function formatCurrency($amount, $showSymbol = true) {
    $formatted = number_format((float)$amount, 2);
    return $showSymbol ? "ETB {$formatted}" : $formatted;
}

/**
 * Format currency for display (alternative function name)
 * @param float $amount The amount to format
 * @return string Formatted currency string
 */
function formatETB($amount) {
    return formatCurrency($amount, true);
}

/**
 * Format currency without symbol
 * @param float $amount The amount to format
 * @return string Formatted amount without currency symbol
 */
function formatAmount($amount) {
    return number_format((float)$amount, 2);
}

/**
 * Convert USD to ETB (for migration purposes)
 * @param float $usdAmount USD amount
 * @param float $exchangeRate Exchange rate (default: 55)
 * @return float ETB amount
 */
function convertUSDToETB($usdAmount, $exchangeRate = 55.0) {
    return round((float)$usdAmount * $exchangeRate, 2);
}

/**
 * Get currency settings
 * @return array Currency configuration
 */
function getCurrencySettings() {
    return [
        'code' => 'ETB',
        'symbol' => 'ETB',
        'name' => 'Ethiopian Birr',
        'position' => 'before', // before or after the amount
        'decimal_places' => 2,
        'thousands_separator' => ',',
        'decimal_separator' => '.'
    ];
}

/**
 * Format currency with custom settings
 * @param float $amount The amount to format
 * @param array $settings Custom currency settings
 * @return string Formatted currency string
 */
function formatCurrencyCustom($amount, $settings = null) {
    if (!$settings) {
        $settings = getCurrencySettings();
    }
    
    $formatted = number_format(
        (float)$amount,
        $settings['decimal_places'],
        $settings['decimal_separator'],
        $settings['thousands_separator']
    );
    
    if ($settings['position'] === 'before') {
        return $settings['symbol'] . ' ' . $formatted;
    } else {
        return $formatted . ' ' . $settings['symbol'];
    }
}

/**
 * Parse currency string to float
 * @param string $currencyString Currency string like "ETB 1,234.56"
 * @return float Parsed amount
 */
function parseCurrency($currencyString) {
    // Remove currency symbol and spaces
    $cleaned = preg_replace('/[^\d.,]/', '', $currencyString);
    // Remove thousands separators and convert to float
    $cleaned = str_replace(',', '', $cleaned);
    return (float)$cleaned;
}

/**
 * Validate currency amount
 * @param mixed $amount Amount to validate
 * @return bool True if valid currency amount
 */
function isValidCurrencyAmount($amount) {
    if (!is_numeric($amount)) {
        return false;
    }
    
    $amount = (float)$amount;
    return $amount >= 0 && $amount <= 999999999.99; // Max reasonable amount
}

/**
 * Format currency for JSON API responses
 * @param float $amount The amount to format
 * @return array Currency data for API
 */
function formatCurrencyForAPI($amount) {
    return [
        'amount' => (float)$amount,
        'formatted' => formatCurrency($amount),
        'currency' => 'ETB',
        'symbol' => 'ETB'
    ];
}

/**
 * Calculate discount percentage
 * @param float $originalPrice Original price
 * @param float $salePrice Sale price
 * @return int Discount percentage
 */
function calculateDiscountPercentage($originalPrice, $salePrice) {
    if ($originalPrice <= 0 || $salePrice >= $originalPrice) {
        return 0;
    }
    
    return round((($originalPrice - $salePrice) / $originalPrice) * 100);
}

/**
 * Format price with discount information
 * @param float $originalPrice Original price
 * @param float $salePrice Sale price (optional)
 * @return array Price formatting data
 */
function formatPriceWithDiscount($originalPrice, $salePrice = null) {
    $result = [
        'original_price' => (float)$originalPrice,
        'original_price_formatted' => formatCurrency($originalPrice),
        'has_discount' => false,
        'discount_percentage' => 0
    ];
    
    if ($salePrice && $salePrice < $originalPrice) {
        $result['sale_price'] = (float)$salePrice;
        $result['sale_price_formatted'] = formatCurrency($salePrice);
        $result['has_discount'] = true;
        $result['discount_percentage'] = calculateDiscountPercentage($originalPrice, $salePrice);
        $result['savings'] = $originalPrice - $salePrice;
        $result['savings_formatted'] = formatCurrency($originalPrice - $salePrice);
    }
    
    return $result;
}

// Helper function for backward compatibility
if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
?>
