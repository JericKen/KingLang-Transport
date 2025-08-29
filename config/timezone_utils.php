<?php

/**
 * Timezone utility functions for consistent timestamp handling
 */

/**
 * Convert UTC timestamp to Asia/Manila timezone
 * 
 * @param string $utcTimestamp UTC timestamp string
 * @param string $format Output format (default: 'Y-m-d H:i:s')
 * @return string Formatted timestamp in Asia/Manila timezone
 */
function convertToManilaTime($utcTimestamp, $format = 'Y-m-d H:i:s') {
    if (empty($utcTimestamp)) {
        return '';
    }
    
    try {
        // Create DateTime object from UTC timestamp
        $utcDateTime = new DateTime($utcTimestamp, new DateTimeZone('UTC'));
        
        // Convert to Asia/Manila timezone
        $utcDateTime->setTimezone(new DateTimeZone('Asia/Manila'));
        
        return $utcDateTime->format($format);
    } catch (Exception $e) {
        error_log("Timezone conversion error: " . $e->getMessage());
        return $utcTimestamp; // Return original if conversion fails
    }
}

/**
 * Get current timestamp in Asia/Manila timezone
 * 
 * @param string $format Output format (default: 'Y-m-d H:i:s')
 * @return string Current timestamp in Asia/Manila timezone
 */
function getCurrentManilaTime($format = 'Y-m-d H:i:s') {
    try {
        $manilaDateTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
        return $manilaDateTime->format($format);
    } catch (Exception $e) {
        error_log("Current time error: " . $e->getMessage());
        return date($format); // Fallback to PHP's default timezone
    }
}

/**
 * Convert Manila time to UTC for database storage
 * 
 * @param string $manilaTimestamp Manila timestamp string
 * @param string $format Output format (default: 'Y-m-d H:i:s')
 * @return string Formatted timestamp in UTC
 */
function convertToUTC($manilaTimestamp, $format = 'Y-m-d H:i:s') {
    if (empty($manilaTimestamp)) {
        return '';
    }
    
    try {
        // Create DateTime object from Manila timestamp
        $manilaDateTime = new DateTime($manilaTimestamp, new DateTimeZone('Asia/Manila'));
        
        // Convert to UTC
        $manilaDateTime->setTimezone(new DateTimeZone('UTC'));
        
        return $manilaDateTime->format($format);
    } catch (Exception $e) {
        error_log("UTC conversion error: " . $e->getMessage());
        return $manilaTimestamp; // Return original if conversion fails
    }
}

/**
 * Format timestamp for display with timezone indicator
 * 
 * @param string $timestamp Timestamp string
 * @param string $format Output format (default: 'M d, Y g:i A')
 * @return string Formatted timestamp with timezone
 */
function formatTimestampForDisplay($timestamp, $format = 'M d, Y g:i A') {
    if (empty($timestamp)) {
        return '';
    }
    
    try {
        // Create DateTime object from timestamp
        $dateTime = new DateTime($timestamp, new DateTimeZone('UTC'));
        
        // Convert to Asia/Manila timezone
        $dateTime->setTimezone(new DateTimeZone('Asia/Manila'));
        
        return $dateTime->format($format) . ' (PHT)';
    } catch (Exception $e) {
        error_log("Display formatting error: " . $e->getMessage());
        return $timestamp; // Return original if formatting fails
    }
}

/**
 * Get timezone offset for Asia/Manila
 * 
 * @return string Timezone offset (e.g., '+08:00')
 */
function getManilaTimezoneOffset() {
    try {
        $manilaDateTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
        return $manilaDateTime->format('P');
    } catch (Exception $e) {
        error_log("Timezone offset error: " . $e->getMessage());
        return '+08:00'; // Default fallback
    }
}
