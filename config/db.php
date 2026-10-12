<?php
/**
 * Campus Cart - Database Connection & Auto-Schema Initializer
 * Technology: PHP PDO with SQLite
 */

// Define SQLite database file path in config directory
$dbPath = __DIR__ . '/database.sqlite';

try {
    // Initialize PDO connection to SQLite
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Enforce foreign key constraints in SQLite
    $pdo->exec("PRAGMA foreign_keys = ON;");

    // 1. Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        avatar_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Ensure avatar_url column exists if table was already created
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar_url TEXT;");
    } catch (Exception $e) {
        // Column already exists, ignore
    }

    // 2. Products Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        category TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        image_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 3. Cart Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // 4. Orders Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        total_amount REAL NOT NULL,
        payment_method TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Seed initial demo data if tables are empty
    $userCountStmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $userCountStmt->fetch()['count'];

    if ($userCount == 0) {
        // Insert sample student users (Password for all demo users is: password123)
        $defaultPassword = password_hash('password123', PASSWORD_BCRYPT);
        
        $insertUser = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $insertUser->execute(['Alex Morgan (CSE 3rd Yr)', 'alex@campus.edu', $defaultPassword]);
        $insertUser->execute(['Samantha Roy (ECE 2nd Yr)', 'samantha@campus.edu', $defaultPassword]);
        $insertUser->execute(['David Kim (Mech 4th Yr)', 'david@campus.edu', $defaultPassword]);

        // Insert sample college marketplace items
        $insertProduct = $pdo->prepare("INSERT INTO products (user_id, title, category, description, price, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        
        $initialProducts = [
            [
                1,
                'Casio FX-991EX ClassWiz Calculator',
                'Electronics',
                'Perfect condition, used for two semesters in Engineering Maths. Original cover included.',
                850.00,
                'https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?auto=format&fit=crop&w=600&q=80'
            ],
            [
                1,
                'Engineering Mathematics (10th Edition) - Erwin Kreyszig',
                'Books',
                'Hardcover, clean pages with very few pencil annotations in chapters 1-3. Mandatory for 1st/2nd semester.',
                450.00,
                'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80'
            ],
            [
                2,
                'Data Structures & Algorithms Handwritten Spiral Notes',
                'Notes',
                'Comprehensive lecture notes covering Trees, Graphs, DP, and Sorts with diagrams and C++ snippets.',
                180.00,
                'https://images.unsplash.com/photo-1517842645767-c639042777db?auto=format&fit=crop&w=600&q=80'
            ],
            [
                2,
                'Rotring Technical Drawing & Drafting Instrument Box',
                'Stationery',
                'Complete engineering graphics compass set, divider, and mini drafter attachments. High precision.',
                320.00,
                'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=600&q=80'
            ],
            [
                3,
                'Arduino Uno R3 Starter Kit with Breadboard & Jumper Wires',
                'Electronics',
                'Includes Arduino Uno board, breadboard, resistors, LEDs, ultrasonic sensor, and USB cable. Used for embedded systems project.',
                650.00,
                'https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&w=600&q=80'
            ],
            [
                3,
                'Concepts of Physics Vol 1 & 2 - H.C. Verma',
                'Books',
                'Both volumes in good readable condition. Great for foundational mechanics, thermodynamics, and optics.',
                380.00,
                'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=600&q=80'
            ]
        ];

        foreach ($initialProducts as $item) {
            $insertProduct->execute($item);
        }
    }

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
