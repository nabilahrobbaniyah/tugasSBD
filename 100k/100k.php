<?php
require_once __DIR__ . '/vendor/autoload.php';

$faker = Faker\Factory::create('id_ID');
$pdo = new PDO("mysql:host=localhost;dbname=100k", "root", "");

// ---------------------------------
// 1. Seeder Courts
// ---------------------------------
function seedCourts($pdo, $faker) {
    for ($i = 0; $i < 20; $i++) {
        $stmt = $pdo->prepare("INSERT INTO courts (court_name, type, price_per_hour) VALUES (?, ?, ?)");
        $stmt->execute([
            "Lapangan " . ($i + 1),
            $faker->randomElement(['indoor','outdoor']),
            $faker->numberBetween(50000, 150000)
        ]);
    }
    echo "✅ 20 courts inserted\n";
}

// ---------------------------------
// 2. Seeder Users
// ---------------------------------
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
$pdo->exec("TRUNCATE TABLE bookings");
$pdo->exec("TRUNCATE TABLE users");
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");


function seedUsers($pdo, $faker) {
    for ($i = 0; $i < 1000; $i++) {
    $name = $faker->name;
    $email = "user{$i}@example.com";
    $offset = 1000; // atau ambil dari MAX(user_id) di tabel
    $email = "user" . ($i + $offset) . "@example.com";
    //$email = $faker->unique()->safeEmail;
    $phone = $faker->phoneNumber;

    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $phone]);
    }
    echo "✅ 1000 users inserted\n";
}

// ---------------------------------
// 3. Seeder Schedules
// ---------------------------------
function seedSchedules($pdo, $faker) {
    $pdo->beginTransaction();
    $totalCourts = $pdo->query("SELECT COUNT(*) FROM courts")->fetchColumn();

    for ($i = 0; $i < 200; $i++) {
        $stmt = $pdo->prepare("
            INSERT INTO schedules (court_id, available_date, open_time, close_time)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $faker->numberBetween(1, $totalCourts),
            $faker->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            '08:00:00',
            '22:00:00'
        ]);
    }
    $pdo->commit();
    echo "✅ 200 schedules inserted\n";
}

// ---------------------------------
// 4. Seeder Bookings
// ---------------------------------
function seedBookings($pdo, $faker, $target = 100000) {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalCourts = $pdo->query("SELECT COUNT(*) FROM courts")->fetchColumn();

    echo "Mulai generate $target bookings...\n";
    $pdo->beginTransaction();

    for ($i = 1; $i <= $target; $i++) {
        $user_id  = $faker->numberBetween(1, $totalUsers);
        $court_id = $faker->numberBetween(1, $totalCourts);
        $start    = $faker->dateTimeBetween('-6 months', 'now');
        $end      = (clone $start)->modify('+2 hours');
        $status   = $faker->randomElement(['pending','confirmed','canceled']);

        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, court_id, start_time, end_time, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $court_id,
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s'),
            $status
        ]);

        if ($i % 100000 === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
            echo "✅ Inserted $i bookings...\n";
        }
    }
    $pdo->commit();
    echo "🎉 Selesai generate $target bookings\n";
}

// ---------------------------------
// 5. Seeder Payments
// ---------------------------------
function seedPayments($pdo, $faker, $jumlah = 50000) {
    $totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

    $pdo->beginTransaction();
    for ($i = 0; $i < $jumlah; $i++) {
        $stmt = $pdo->prepare("
            INSERT INTO payments (booking_id, amount, payment_date, method)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $faker->numberBetween(1, $totalBookings),
            $faker->numberBetween(50000, 300000),
            $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
            $faker->randomElement(['cash','transfer','ewallet'])
        ]);
    }
    $pdo->commit();
    echo "✅ Selesai generate $jumlah payments\n";
}

// ---------------------------------
// Main eksekusi semua seeder
// ---------------------------------
seedCourts($pdo, $faker);
seedUsers($pdo, $faker);
seedSchedules($pdo, $faker);
seedBookings($pdo, $faker, 100000); // misal 100 ribu bookings
seedPayments($pdo, $faker, 50000);
