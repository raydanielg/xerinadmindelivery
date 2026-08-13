<?php
/**
 * SMS mshastra.com Setup Script
 * Run this on your server: php setup_mshastra_sms.php
 * 
 * This script will:
 * 1. Create sms_logs table
 * 2. Deactivate all other SMS gateways
 * 3. Insert/update mshastra_sms with your credentials
 */

// Database configuration - update these if needed
$dbHost = '127.0.0.1';
$dbPort = 3306;
$dbName = 'xerin_express_delivery';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName}", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.\n";

    // Step 1: Create sms_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `sms_logs` (
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
    )");
    echo "SMS logs table created/verified.\n";

    // Step 2: Deactivate all other SMS gateways (is_active = 0)
    $pdo->exec("UPDATE `settings` SET `is_active` = 0 WHERE `settings_type` = 'sms_config' AND `key_name` != 'mshastra_sms'");
    echo "Deactivated other SMS gateways (is_active).\n";

    // Step 3: Set status=0 in live_values JSON for other gateways
    $stmt = $pdo->query("SELECT id, live_values, test_values FROM `settings` WHERE `settings_type` = 'sms_config' AND `key_name` != 'mshastra_sms'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $values = json_decode($row['live_values'], true);
        if (is_array($values) && isset($values['status'])) {
            $values['status'] = 0;
            $updateStmt = $pdo->prepare("UPDATE `settings` SET `live_values` = ?, `test_values` = ?, `updated_at` = NOW() WHERE `id` = ?");
            $updateStmt->execute([json_encode($values), json_encode($values), $row['id']]);
        }
    }
    echo "Deactivated other SMS gateways (live_values status).\n";

    // Step 4: Insert or update mshastra_sms settings
    $mshastraData = json_encode([
        'status' => 1,
        'user' => 'XERINDELIV',
        'pwd' => 'phh4mpe1',
        'sender_id' => 'XERINDELIV',
        'otp_template' => 'Your Zerin Express OTP is #OTP#',
    ]);

    // Check if exists
    $checkStmt = $pdo->prepare("SELECT id FROM `settings` WHERE `key_name` = 'mshastra_sms' AND `settings_type` = 'sms_config'");
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $updateStmt = $pdo->prepare("UPDATE `settings` SET `live_values` = ?, `test_values` = ?, `is_active` = 1, `updated_at` = NOW() WHERE `id` = ?");
        $updateStmt->execute([$mshastraData, $mshastraData, $existing['id']]);
        echo "Updated mshastra_sms gateway settings.\n";
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO `settings` (`key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`) VALUES (?, ?, ?, 'sms_config', 'live', 1, NOW(), NOW())");
        $insertStmt->execute(['mshastra_sms', $mshastraData, $mshastraData]);
        echo "Inserted mshastra_sms gateway settings.\n";
    }

    echo "\nSMS mshastra.com setup completed successfully!\n";
    echo "Gateway: mshastra_sms\n";
    echo "Profile ID: XERINDELIV\n";
    echo "Sender ID: XERINDELIV\n";
    echo "Status: ACTIVE\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
