-- SMS Configuration Script for mshastra.com
-- Run this on your server database (169.58.54.110)

-- Step 1: Create SMS logs table
CREATE TABLE IF NOT EXISTS `sms_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `gateway` VARCHAR(255) NULL,
    `receiver` VARCHAR(255) NULL,
    `message` TEXT NULL,
    `type` VARCHAR(50) DEFAULT 'otp',
    `status` VARCHAR(50) DEFAULT 'pending',
    `response` TEXT NULL,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    INDEX `sms_logs_status_created_at_index` (`status`, `created_at`),
    INDEX `sms_logs_gateway_index` (`gateway`),
    INDEX `sms_logs_receiver_index` (`receiver`)
);

-- Step 2: Deactivate all other SMS gateways
UPDATE `settings` SET `is_active` = 0 WHERE `settings_type` = 'sms_config' AND `key_name` != 'mshastra_sms';

-- Step 3: Set status=0 in live_values for other gateways
-- (This needs to be done per gateway - see PHP migration for automated version)

-- Step 4: Insert or Update mshastra_sms gateway settings
INSERT INTO `settings` (`key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`)
VALUES (
    'mshastra_sms',
    '{"status":1,"user":"XERINDELIV","pwd":"phh4mpe1","sender_id":"XERINDELIV","otp_template":"Your Zerin Express OTP is #OTP#"}',
    '{"status":1,"user":"XERINDELIV","pwd":"phh4mpe1","sender_id":"XERINDELIV","otp_template":"Your Zerin Express OTP is #OTP#"}',
    'sms_config',
    'live',
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `live_values` = VALUES(`live_values`),
    `test_values` = VALUES(`test_values`),
    `is_active` = 1,
    `updated_at` = NOW();

-- Note: If you have a unique constraint on (key_name, settings_type), the ON DUPLICATE KEY UPDATE will work.
-- If not, you may need to check if the record exists first:

-- Alternative: Check and update if exists
-- UPDATE `settings` SET 
--     `live_values` = '{"status":1,"user":"XERINDELIV","pwd":"phh4mpe1","sender_id":"XERINDELIV","otp_template":"Your Zerin Express OTP is #OTP#"}',
--     `test_values` = '{"status":1,"user":"XERINDELIV","pwd":"phh4mpe1","sender_id":"XERINDELIV","otp_template":"Your Zerin Express OTP is #OTP#"}',
--     `is_active` = 1,
--     `updated_at` = NOW()
-- WHERE `key_name` = 'mshastra_sms' AND `settings_type` = 'sms_config';
