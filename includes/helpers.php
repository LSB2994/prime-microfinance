<?php
/**
 * ==========================================================================
 * Helper Functions for Formatting and Utilities
 * ==========================================================================
 * 
 * Utility functions for formatting data (currency, dates, phone numbers, etc.)
 * ==========================================================================
 */

/**
 * Format interest rate
 */
function formatInterestRate($value) {
    if (empty($value) || $value === null || $value === '') {
        return '';
    }
    $num = is_numeric($value) ? number_format((float)$value, 2, '.', '') : $value;
    return $num . ' %/1ខែ';
}

/**
 * Format loan period
 */
function formatLoanPeriod($value) {
    if (empty($value) || $value === null || $value === '') {
        return '';
    }
    return $value . ' ខែ';
}

/**
 * Format service fee
 */
function formatServiceFee($value) {
    if (empty($value) || $value === null || $value === '') {
        return '';
    }
    $num = is_numeric($value) ? number_format((float)$value, 2, '.', '') : $value;
    return $num . '%';
}

/**
 * Format amount with currency
 */
function formatAmount($value) {
    if (empty($value) || $value === null || $value === '') {
        return '';
    }
    $num = is_numeric($value) ? number_format((float)$value, 2, '.', ',') : $value;
    return $num . ' ដុល្លារ';
}

/**
 * Get country phone code
 */
function getCountryPhoneCode($phone = '') {
    // Function to determine country phone code based on phone number or other criteria
    // For now, default to Cambodia (855)
    // You can extend this to check phone number patterns or other data to determine country
    
    // Default to Cambodia
    return '855';
}

/**
 * Format phone number
 */
function formatPhone($value) {
    if (empty($value) || $value === null || $value === '') {
        return '';
    }
    
    // Remove any non-digit characters to get clean phone number
    $phone = preg_replace('/[^0-9]/', '', $value);
    
    if (empty($phone)) {
        return $value; // Return original if no digits found
    }
    
    // Get country code
    $countryCode = getCountryPhoneCode($phone);
    
    // Format: (855) 010 500 224
    // Split phone number into groups of 3 digits
    $formatted = '';
    for ($i = 0; $i < strlen($phone); $i += 3) {
        if ($i > 0) $formatted .= ' ';
        $formatted .= substr($phone, $i, 3);
    }
    
    return '(' . $countryCode . ') ' . trim($formatted);
}

/**
 * Truncate text with title attribute for long values
 * Note: CSS handles visual truncation with ellipsis, this function only sets title for hover
 */
function truncateWithTitle($value, $maxLength = 30) {
    if (empty($value)) {
        return ['display' => '', 'title' => ''];
    }
    $str = (string)$value;
    // Use mbstring if available, otherwise fall back to regular string functions
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        $length = mb_strlen($str, 'UTF-8');
        if ($length > $maxLength) {
            // Return full text - CSS will handle ellipsis display
            return [
                'display' => $str,
                'title' => $str
            ];
        }
    } else {
        // Fallback to regular string functions
        $length = strlen($str);
        if ($length > $maxLength) {
            // Return full text - CSS will handle ellipsis display
            return [
                'display' => $str,
                'title' => $str
            ];
        }
    }
    return ['display' => $str, 'title' => ''];
}

