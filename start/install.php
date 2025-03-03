<?php
require __DIR__ . '/../helper/smart_Include.php';
smartInclude('config/env.php');


function getDatabaseConnection($dbname = null) {
    
    $dsn = 'mysql:host=' . host . ($dbname ? ';dbname=' . $dbname : '') . ';charset=utf8mb4';
    $username = user;
    $password = pass;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    return new PDO($dsn, $username, $password, $options);
}

function createDatabase($pdo, $dbname) {
    try {
 
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        $pdo->exec($sql);  
        echo "created '$dbname'  successfully!<br>";
    } catch (PDOException $e) {
        echo "failed  " . $e->getMessage();
    }
}


// إنشاء الجداول
function createTables($pdo) {
    $tables = [
        'roles' => "
            CREATE TABLE roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ",
        'permissions' => "
            CREATE TABLE permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ",
        'users' => "
            CREATE TABLE users (
                uid INT AUTO_INCREMENT PRIMARY KEY,  -- استخدام uid بدلاً من user_id
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone_number VARCHAR(20) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email_verified BOOLEAN DEFAULT false,
                phone_verified BOOLEAN DEFAULT false,
                api_token VARCHAR(80) UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email)  -- فهرس على حقل البريد الإلكتروني
            )
        ",

'verification_codes' => "
    CREATE TABLE verification_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,          -- معرف فريد لكل رمز
        user_id INT NOT NULL,                       -- معرف المستخدم المرتبط بالرمز
        code VARCHAR(255) NOT NULL,                 -- الرمز المُستخدم
        purpose ENUM('email_verification', 'phone_verification', 'password_reset') NOT NULL, -- الغرض من الرمز
        via_method ENUM('email', 'sms') NOT NULL,   -- طريقة الإرسال: بريد إلكتروني أو SMS
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- وقت إنشاء الرمز
        expires_at TIMESTAMP  NULL,              -- تاريخ انتهاء صلاحية الرمز
        used_at TIMESTAMP NULL DEFAULT NULL,        -- وقت استخدام الرمز (إذا تم)
        phone_number VARCHAR(15) DEFAULT NULL,      -- رقم الهاتف (للتحقق عبر SMS)
        email VARCHAR(255) DEFAULT NULL,            -- البريد الإلكتروني (للتحقق عبر البريد)
        FOREIGN KEY (user_id) REFERENCES users(uid) ON DELETE CASCADE, -- مفتاح خارجي مرتبط بجدول users
        INDEX idx_user_id (user_id),                -- فهرس لتحسين عمليات البحث باستخدام user_id
        INDEX idx_code (code),                      -- فهرس لتحسين التحقق من الرموز
        INDEX idx_user_purpose (user_id, purpose)   -- فهرس مركب لتحسين الاستعلامات التي تستخدم user_id و purpose معًا
    )
",
        'subscriptions' => "
            CREATE TABLE subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                price DECIMAL(8, 2) NOT NULL,
                duration VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ",
        'user_subscriptions' => "
            CREATE TABLE user_subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uid INT NOT NULL,  -- استخدام uid بدلاً من user_id
                subscription_id INT NOT NULL,
                start_date TIMESTAMP NOT NULL,
                end_date TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (uid) REFERENCES users(uid) ON DELETE CASCADE,
                FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_subscription (uid, subscription_id)  -- لمنع تكرار الاشتراكات لنفس المستخدم
            )
        ",
        'payment_methods' => "
            CREATE TABLE payment_methods (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                exchange_rate DECIMAL(8, 4) NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ",
        'payments' => "
            CREATE TABLE payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uid INT NOT NULL,  -- استخدام uid بدلاً من user_id
                amount DECIMAL(8, 2) NOT NULL,
                payment_date TIMESTAMP NOT NULL,
                payment_method_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (uid) REFERENCES users(uid) ON DELETE CASCADE,
                FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE CASCADE,
                INDEX idx_payment_date (payment_date)  -- فهرس    
            )
        ",
        'role_user' => "
            CREATE TABLE role_user (
                role_id INT NOT NULL,
                uid INT NOT NULL,  -- استخدام uid بدلاً من user_id
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (role_id, uid),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (uid) REFERENCES users(uid) ON DELETE CASCADE
            )
        ",
        'permission_role' => "
            CREATE TABLE permission_role (
                permission_id INT NOT NULL,
                role_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (permission_id, role_id),
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
            )
        "
    ];

    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "Table '$tableName' created successfully!\n";
        } catch (PDOException $e) {
            echo "Error creating table '$tableName': " . $e->getMessage() . "\n";
        }
    }
}


try {
    $pdo = getDatabaseConnection();
    createDatabase($pdo, db_name);
    $pdo = getDatabaseConnection(db_name);
    createTables($pdo);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}