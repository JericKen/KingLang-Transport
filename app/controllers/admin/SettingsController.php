<?php

declare(strict_types=1);



require_once __DIR__ . "/../../models/admin/Settings.php";

require_once __DIR__ . "/../AuditTrailTrait.php";



class SettingsController {

    use AuditTrailTrait;

    private $settings;



    public function __construct() {

        $this->settings = new Settings();

        

        // Only perform auth check if this is an admin route

        $requestUri = $_SERVER['REQUEST_URI'];

        if (strpos($requestUri, '/admin') === 0 && 

            strpos($requestUri, '/admin/login') === false && 

            strpos($requestUri, '/admin/submit-login') === false) {

            

            if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {

                header("Location: /admin/login");

                exit();

            }

        }

    }



    public function index() {

        // Load all settings and display settings dashboard

        $allSettings = $this->settings->getAllSettings();

        

        // Group settings by their group

        $groupedSettings = [];

        foreach ($allSettings as $setting) {

            $group = $setting['setting_group'];

            if (!isset($groupedSettings[$group])) {

                $groupedSettings[$group] = [];

            }

            $groupedSettings[$group][] = $setting;

        }

        

        include __DIR__ . "/../../views/admin/settings.php";

    }



    public function getAllSettings() {

        // For AJAX requests to get all settings

        header('Content-Type: application/json');

        $allSettings = $this->settings->getAllSettings();

        echo json_encode(['success' => true, 'data' => $allSettings]);

    }



    public function getSettingsByGroup() {

        // For AJAX requests to get settings by group

        header('Content-Type: application/json');

        

        if (!isset($_GET['group'])) {

            echo json_encode(['success' => false, 'message' => 'Group parameter is required']);

            return;

        }

        

        $group = htmlspecialchars($_GET['group']);

        $settings = $this->settings->getSettingsByGroup($group);

        echo json_encode(['success' => true, 'data' => $settings]);

    }



    public function updateSettings() {

        // For AJAX requests to update settings

        header('Content-Type: application/json');

        

        // Check if it's a POST request

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            echo json_encode(['success' => false, 'message' => 'Invalid request method']);

            return;

        }

        

        // Get the settings data from the request

        $postData = json_decode(file_get_contents('php://input'), true);

        

        if (!$postData || !isset($postData['settings'])) {

            echo json_encode(['success' => false, 'message' => 'No settings data provided']);

            return;

        }

        

        // Get old settings for audit trail

        $oldSettings = [];

        foreach ($postData['settings'] as $settingKey => $settingValue) {

            $oldSetting = $this->getEntityBeforeUpdate('settings', 'setting_key', $settingKey);

            if ($oldSetting) {

                $oldSettings[$settingKey] = $oldSetting;

            }

        }

        

        // Update the settings

        $success = $this->settings->bulkUpdateSettings($postData['settings']);

        

        if ($success) {

            // Log each setting change to audit trail

            foreach ($postData['settings'] as $settingKey => $settingValue) {

                $oldData = $oldSettings[$settingKey] ?? null;

                $newData = [

                    'setting_key' => $settingKey,

                    'setting_value' => $settingValue,

                    'setting_group' => $oldData['setting_group'] ?? 'general'

                ];

                $this->logAudit('update', 'setting', $settingKey, $oldData, $newData, $_SESSION['admin_id']);

            }

            echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);

        } else {

            echo json_encode(['success' => false, 'message' => 'Failed to update settings']);

        }

    }



    public function addSetting() {

        // For AJAX requests to add a new setting

        header('Content-Type: application/json');

        

        // Check if it's a POST request

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            echo json_encode(['success' => false, 'message' => 'Invalid request method']);

            return;

        }

        

        // Get the setting data from the request

        $postData = json_decode(file_get_contents('php://input'), true);

        

        if (!$postData || !isset($postData['key']) || !isset($postData['value']) || !isset($postData['group'])) {

            echo json_encode(['success' => false, 'message' => 'Missing required fields']);

            return;

        }

        

        $key = $postData['key'];

        $value = $postData['value'];

        $group = $postData['group'];

        $isPublic = isset($postData['is_public']) ? (bool)$postData['is_public'] : false;

        

        // Add the setting

        $success = $this->settings->addSetting($key, $value, $group, $isPublic);

        

        if ($success) {

            echo json_encode(['success' => true, 'message' => 'Setting added successfully']);

        } else {

            echo json_encode(['success' => false, 'message' => 'Failed to add setting']);

        }

    }



    public function deleteSetting() {

        // For AJAX requests to delete a setting

        header('Content-Type: application/json');

        

        // Check if it's a POST request

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            echo json_encode(['success' => false, 'message' => 'Invalid request method']);

            return;

        }

        

        // Get the setting key from the request

        $postData = json_decode(file_get_contents('php://input'), true);

        

        if (!$postData || !isset($postData['key'])) {

            echo json_encode(['success' => false, 'message' => 'No setting key provided']);

            return;

        }

        

        // Delete the setting

        $success = $this->settings->deleteSetting($postData['key']);

        

        if ($success) {

            echo json_encode(['success' => true, 'message' => 'Setting deleted successfully']);

        } else {

            echo json_encode(['success' => false, 'message' => 'Failed to delete setting']);

        }

    }

} 