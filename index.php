<?php
// إعدادات الاتصال بقاعدة البيانات
$host   = "fdb1030.awardspace.net";
$dbuser = "4797238_ohkjj";
$dbpass = "Aa12345678##";
$dbname = "4797238_ohkjj";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
echo "<div style='color:green; font-weight:bold; text-align:center;'>✅ تم الاتصال بقاعدة البيانات بنجاح</div>";

// إنشاء الجداول لو مش موجودة
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $acc_num  = trim($_POST['account_number']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, account_number, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $acc_num, $password);

    if ($stmt->execute()) {
        $message = "<div style='color:green; text-align:center;'>✅ تم إنشاء حساب بنجاح!</div>";
    } else {
        $message = "<div style='color:red; text-align:center;'>❌ فشل إنشاء الحساب: " . $stmt->error . "</div>";
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>إنشاء حساب جديد</h2>
        <?php echo $message; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="اسم المستخدم" required>
            <input type="text" name="account_number" placeholder="رقم الحساب (مثلاً 12345)" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit" name="create_user">إنشاء الحساب</button>
        </form>
    </div>
</body>
</html>
