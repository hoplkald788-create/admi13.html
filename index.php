<?php
// إعدادات الاتصال بقاعدة البيانات
$host   = "fdb1029.awardspace.net";
$dbuser = "4796311_joker";
$dbpass = "Xxx@123456";
$dbname = "4796311_joker";

// الاتصال
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// إنشاء الجدول لو مش موجود
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$message = "";

// معالجة إنشاء الحساب
if (isset($_POST['create_account'])) {
    $name = trim($_POST['acc_name']);
    $pass = trim($_POST['acc_pass']);
    $bal = floatval($_POST['acc_bal']);
    
    // توليد رقم حساب عشوائي
    $acc_num = rand(10000000, 99999999);
    $hashed = password_hash($pass, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (account_number, username, password, full_name, balance) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $acc_num, $name, $hashed, $name, $bal);
    
    if ($stmt->execute()) {
        $message = "<div style='color: green; font-weight: bold; margin-bottom: 15px;'>تم إنشاء الحساب بنجاح ✅<br>رقم الحساب: $acc_num</div>";
    } else {
        $message = "<div style='color: red; font-weight: bold; margin-bottom: 15px;'>حدث خطأ أثناء الإنشاء ❌</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f4f4; padding: 50px; text-align: center; }
        .box { background: white; padding: 30px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

<div class="box">
    <h2>إنشاء مستخدم جديد</h2>
    
    <?= $message ?>
    
    <form method="POST">
        <input type="text" name="acc_name" placeholder="اسم المستخدم" required>
        <input type="text" name="acc_pass" placeholder="كلمة المرور" required>
        <input type="number" step="0.01" name="acc_bal" placeholder="الرصيد الابتدائي (مثال: 0)" value="0" required>
        <button type="submit" name="create_account">إنشاء الحساب</button>
    </form>
</div>

</body>
</html>
