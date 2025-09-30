<?php

declare(strict_types=1);



class Settings {

    private $pdo;



    public function __construct() {

        global $pdo;

        $this->pdo = $pdo;

        $this->ensureSettingsTableExists();

    }



    private function ensureSettingsTableExists() {

        $query = "CREATE TABLE IF NOT EXISTS settings (

            id INT AUTO_INCREMENT PRIMARY KEY,

            setting_key VARCHAR(50) NOT NULL UNIQUE,

            setting_value TEXT,

            setting_group VARCHAR(50) NOT NULL,

            is_public BOOLEAN DEFAULT FALSE,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

        )";

        $this->pdo->exec($query);

        

        // Add default settings if they don't exist

        $this->addDefaultSettings();

    }



    private function addDefaultSettings() {

        $defaultSettings = [

            // General settings

            ['site_name', 'Kinglang Transport', 'general', true],

            // ['site_email', 'info@kinglangbooking.com', 'general', true],

            // ['contact_phone', '+1234567890', 'general', true],

            

            // Company Information

            ['company_name', 'KINGLANG TOURS AND TRANSPORT SERVICES INC.', 'company', true],

            ['company_address', 'Block 1 Lot 13 Phase 3 Egypt St. Ecotrend Subd. San Nicholas 1, Bacoor, Cavite', 'company', true],

            ['company_contact', '0923-0810061 / 0977-3721958', 'company', true],

            ['company_email', 'jaycris.traveltours@gmail.com', 'company', true],

            

            // Bank Details

            ['bank_name', 'BPI Cainta Ortigas Extension Branch', 'payment', true],

            ['bank_account_name', 'KINGLANG TOURS AND TRANSPORT SERVICES INC.', 'payment', true],

            ['bank_account_number', '4091-0050-05', 'payment', true],

            ['bank_swift_code', 'BPOIPHMM', 'payment', true],

            

            // Booking settings

            // ['min_booking_notice_hours', '24', 'booking', true],

            // ['max_booking_days_in_advance', '60', 'booking', true],

            ['allow_rebooking', '1', 'booking', true],
            ['max_rebookings_per_client', '3', 'booking', true],

            // ['rebooking_fee_percentage', '10', 'booking', true],

            ['diesel_price', '65.00', 'booking', true],

            

            // Payment settings

            ['payment_methods', 'Bank Transfer', 'payment', true],

            ['currency', 'PHP', 'payment', true],

            ['tax_rate', '12', 'payment', true],

            

            // Notification settings

            // ['enable_email_notifications', '1', 'notification', false],

            // ['enable_sms_notifications', '0', 'notification', false]

        ];



        $stmt = $this->pdo->prepare("INSERT IGNORE INTO settings 

                                    (setting_key, setting_value, setting_group, is_public) 

                                    VALUES (?, ?, ?, ?)");

        

        foreach ($defaultSettings as $setting) {

            $stmt->execute($setting);

        }

    }



    public function getAllSettings() {

        $stmt = $this->pdo->query("SELECT * FROM settings ORDER BY setting_group, setting_key");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }



    public function getSettingsByGroup($group) {

        $stmt = $this->pdo->prepare("SELECT * FROM settings WHERE setting_group = ? ORDER BY setting_key");

        $stmt->execute([$group]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }



    public function getPublicSettings() {

        $stmt = $this->pdo->query("SELECT * FROM settings WHERE is_public = 1 ORDER BY setting_group, setting_key");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }



    public function getSetting($key) {

        $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");

        $stmt->execute([$key]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['setting_value'] : null;

    }



    public function updateSetting($key, $value) {

        $stmt = $this->pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");

        return $stmt->execute([$value, $key]);

    }



    public function bulkUpdateSettings($settingsData) {

        $this->pdo->beginTransaction();

        try {

            $stmt = $this->pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");

            foreach ($settingsData as $key => $value) {

                $stmt->execute([$value, $key]);

            }

            $this->pdo->commit();

            return true;

        } catch (Exception $e) {

            $this->pdo->rollBack();

            error_log("Error updating settings: " . $e->getMessage());

            return false;

        }

    }



    public function addSetting($key, $value, $group, $isPublic = false) {

        $stmt = $this->pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group, is_public) 

                                    VALUES (?, ?, ?, ?) 

                                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), 

                                                          setting_group = VALUES(setting_group),

                                                          is_public = VALUES(is_public)");

        return $stmt->execute([$key, $value, $group, $isPublic ? 1 : 0]);

    }



    public function deleteSetting($key) {

        $stmt = $this->pdo->prepare("DELETE FROM settings WHERE setting_key = ?");

        return $stmt->execute([$key]);

    }

} 