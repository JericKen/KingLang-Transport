<?php
declare(strict_types=1);

// Include the Settings model
require_once __DIR__ . "/../app/models/admin/Settings.php";

// Initialize settings instance
$settingsModel = new Settings();

/**
 * Get a setting value by key
 * 
 * @param string $key The setting key
 * @param mixed $default Default value if setting is not found
 * @return mixed The setting value or default if not found
 */
function get_setting($key, $default = null) {
    global $settingsModel;
    $value = $settingsModel->getSetting($key);
    return $value !== null ? $value : $default;
}

/**
 * Check if a setting exists
 * 
 * @param string $key The setting key
 * @return bool True if the setting exists, false otherwise
 */
function has_setting($key) {
    global $settingsModel;
    return $settingsModel->getSetting($key) !== null;
}

/**
 * Get all settings by group
 * 
 * @param string $group The settings group
 * @return array Array of settings in the specified group
 */
function get_settings_by_group($group) {
    global $settingsModel;
    return $settingsModel->getSettingsByGroup($group);
}

/**
 * Get all public settings
 * 
 * @return array Array of all public settings
 */
function get_public_settings() {
    global $settingsModel;
    return $settingsModel->getPublicSettings();
}

/**
 * Update a setting
 * 
 * @param string $key The setting key
 * @param mixed $value The new setting value
 * @return bool True on success, false on failure
 */
function update_setting($key, $value) {
    global $settingsModel;
    return $settingsModel->updateSetting($key, $value);
}

/**
 * Add or update a setting
 * 
 * @param string $key The setting key
 * @param mixed $value The setting value
 * @param string $group The settings group
 * @param bool $isPublic Whether the setting is public
 * @return bool True on success, false on failure
 */
function add_setting($key, $value, $group, $isPublic = false) {
    global $settingsModel;
    return $settingsModel->addSetting($key, $value, $group, $isPublic);
}

/**
 * Delete a setting
 * 
 * @param string $key The setting key
 * @return bool True on success, false on failure
 */
function delete_setting($key) {
    global $settingsModel;
    return $settingsModel->deleteSetting($key);
} 