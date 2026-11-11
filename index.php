
<?php
// ==========================================
// admin.php - لوحة التحكم الشاملة
// قاعدة البيانات: fdb1029.awardspace.net (4796311_joker)
// كلمة المرور: 123666
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ==================== إعدادات الاتصال ====================
$host   = "fdb1029.awardspace.net";
$dbuser = "4796311_joker";
$dbpass = "Xxx@123456";
$dbname = "4796311_joker";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// ==================== إنشاء الجداول ====================
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

$conn->query("CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    type VARCHAR(20) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    reference_number VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ==================== إعدادات الدخول ====================
$ADMIN_PASSWORD = "123666";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$login_error = '';
if (isset($_POST['login'])) {
    if ($_POST['admin_pass'] === $ADMIN_PASSWORD) {
        $_SESSION['benten_admin'] = true;
    } else {
        $login_error = "كلمة المرور غير صحيحة";
    }
}
$is_logged_in = $_SESSION['benten_admin'] ?? false;

// ==================== الإحصائيات ====================
$total_users = 0; $banned_users = 0; $active_users = 0;
if ($is_logged_in) {
    if ($r = $conn->query("SELECT COUNT(*) c FROM users")) $total_users = $r->fetch_assoc()['c'];
    if ($r = $conn->query("SELECT COUNT(*) c FROM users WHERE status='banned'")) $banned_users = $r->fetch_assoc()['c'];
    if ($r = $conn->query("SELECT COUNT(*) c FROM users WHERE status='active'")) $active_users = $r->fetch_assoc()['c'];
}

// ==================== معالجة العمليات ====================
$result_data = null;

if ($is_logged_in) {
    
    // 1. فتح حساب جديد
    if (isset($_POST['create_account'])) {
        $name = trim($_POST['acc_name']);
        $pass = trim($_POST['acc_pass']);
        $bal = floatval($_POST['acc_bal']);
        
        $acc_num = rand(10000000, 99999999);
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (account_number, username, password, full_name, balance) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $acc_num, $name, $hashed, $name, $bal);
        if ($stmt->execute()) {
            $copy = "تم ✅ فتح حساب بنجاح ✅\n------------------------\nرقم الحساب: $acc_num\nكلمة المرور: $pass\nاسم المستخدم: $name\nالرصيد: $bal\n------------------------\nتم ✅ فتح حساب بنجاح ✅";
            $result_data = ['status'=>'success', 'title'=>'✅ فتح حساب', 'msg'=>$copy, 'copy'=>$copy];
        }
        $stmt->close();
    }

    // 2. زيادة رصيد
    if (isset($_POST['deposit'])) {
        $acc = trim($_POST['dep_acc']);
        $amt = floatval($_POST['dep_amt']);
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE account_number = ?");
        $stmt->bind_param("ds", $amt, $acc);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $copy = "تم ✅ زيادة رصيد بنجاح ✅\n------------------------\nرقم الحساب: $acc\nالمبلغ المضاف: $amt\n------------------------\nتم ✅ زيادة رصيد بنجاح ✅";
            $result_data = ['status'=>'success', 'title'=>'💰 زيادة رصيد', 'msg'=>$copy, 'copy'=>$copy];
        } else {
            $result_data = ['status'=>'error', 'title'=>'خطأ', 'msg'=>'الحساب غير موجود'];
        }
        $stmt->close();
    }

    // 3. سحب رصيد
    if (isset($_POST['withdraw'])) {
        $acc = trim($_POST['wd_acc']);
        $amt = floatval($_POST['wd_amt']);
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE account_number = ? AND balance >= ?");
        $stmt->bind_param("dsd", $amt, $acc, $amt);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $copy = "تم ✅ سحب رصيد بنجاح ✅\n------------------------\nرقم الحساب: $acc\nالمبلغ المسحوب: $amt\n------------------------\nتم ✅ سحب رصيد بنجاح ✅";
            $result_data = ['status'=>'success', 'title'=>'📤 سحب رصيد', 'msg'=>$copy, 'copy'=>$copy];
        } else {
            $result_data = ['status'=>'error', 'title'=>'خطأ', 'msg'=>'الرصيد غير كافٍ أو الحساب غير موجود'];
        }
        $stmt->close();
    }

    // 4. حظر حساب
    if (isset($_POST['ban'])) {
        $acc = trim($_POST['ban_acc']);
        $conn->query("UPDATE users SET status='banned' WHERE account_number='$acc'");
        $copy = "تم ✅ حظر حساب بنجاح ✅\n------------------------\nرقم الحساب: $acc\n------------------------\nتم ✅ حظر حساب بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'🚫 حظر حساب', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 5. تفعيل حساب
    if (isset($_POST['activate'])) {
        $acc = trim($_POST['act_acc']);
        $conn->query("UPDATE users SET status='active' WHERE account_number='$acc'");
        $copy = "تم ✅ تفعيل حساب بنجاح ✅\n------------------------\nرقم الحساب: $acc\n------------------------\nتم ✅ تفعيل حساب بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'✅ تفعيل حساب', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 6. حذف حساب
    if (isset($_POST['delete_account'])) {
        $acc = trim($_POST['del_acc']);
        $conn->query("DELETE FROM users WHERE account_number='$acc'");
        $copy = "تم ✅ حذف حساب بنجاح ✅\n------------------------\nرقم الحساب: $acc\n------------------------\nتم ✅ حذف حساب بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'🗑️ حذف حساب', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 7. تنشيط كل الحسابات
    if (isset($_POST['activate_all'])) {
        $conn->query("UPDATE users SET status='active'");
        $copy = "تم ✅ تنشيط جميع الحسابات بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'✅ تنشيط الكل', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 8. إيقاف كل الحسابات
    if (isset($_POST['ban_all'])) {
        $conn->query("UPDATE users SET status='banned'");
        $copy = "تم ✅ إيقاف جميع الحسابات بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'🚫 إيقاف الكل', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 9. تصفير الأرصدة
    if (isset($_POST['reset_balances'])) {
        $conn->query("UPDATE users SET balance=0");
        $copy = "تم ✅ تصفير الأرصدة بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'🧹 تصفير الأرصدة', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 10. شحن جماعي
    if (isset($_POST['bulk_deposit'])) {
        $amt = floatval($_POST['bulk_amt']);
        $conn->query("UPDATE users SET balance = balance + $amt");
        $copy = "تم ✅ الشحن الجماعي بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'💸 شحن جماعي', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 11. بحث حساب
    if (isset($_POST['search'])) {
        $acc = trim($_POST['search_acc']);
        $stmt = $conn->prepare("SELECT * FROM users WHERE account_number = ?");
        $stmt->bind_param("s", $acc);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) {
            $copy = "بيانات الحساب:\nالرقم: {$row['account_number']}\nالاسم: {$row['full_name']}\nالرصيد: {$row['balance']}";
            $result_data = ['status'=>'success', 'title'=>'🔍 نتيجة البحث', 'msg'=>$copy, 'copy'=>$copy];
        } else {
            $result_data = ['status'=>'error', 'title'=>'خطأ', 'msg'=>'الحساب غير موجود'];
        }
        $stmt->close();
    }

// 12. حذف كل الحسابات
    if (isset($_POST['delete_all_users'])) {
        $conn->query("TRUNCATE TABLE users");
        $copy = "تم ✅ حذف جميع الحسابات نهائياً ✅";
        $result_data = ['status'=>'success', 'title'=>'🚨 حذف الكل', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 13. اشتراك
    if (isset($_POST['subscribe'])) {
        $acc = trim($_POST['sub_acc']);
        $amt = floatval($_POST['sub_amt']);
        $conn->query("UPDATE users SET balance = balance + $amt WHERE account_number='$acc'");
        $copy = "تم ✅ تفعيل الاشتراك بنجاح ✅";
        $result_data = ['status'=>'success', 'title'=>'⭐ اشتراك', 'msg'=>$copy, 'copy'=>$copy];
    }

    // 14. عرض المستخدمين
    if (isset($_POST['view_users'])) {
        $all_users = [];
        $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
        while ($row = $res->fetch_assoc()) $all_users[] = $row;
        $result_data = ['status'=>'success', 'title'=>'👥 المستخدمين', 'msg'=>'إجمالي: '.count($all_users), 'show_users_table'=>true, 'users'=>$all_users];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>لوحة التحكم - نسخة Benten</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
        * { box-sizing: border-box; font-family: 'Cairo', sans-serif; -webkit-tap-highlight-color: transparent; }
        body { margin: 0; padding: 0; background: #0f172a; color: #fff; min-height: 100vh; overflow-x: hidden; }

        .header { background: linear-gradient(135deg, #1e293b, #0f172a); padding: 15px; text-align: center; border-bottom: 2px solid #3b82f6; position: relative; }
        .logo { font-size: 24px; font-weight: bold; background: -webkit-linear-gradient(#3b82f6, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .logout { position: absolute; left: 15px; top: 20px; background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; cursor: pointer; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 15px; }
        .stat-card { background: #1e293b; padding: 10px 5px; border-radius: 12px; text-align: center; border: 1px solid #334155; }
        .stat-val { font-size: 18px; font-weight: bold; margin-bottom: 3px; }
        .stat-lbl { font-size: 11px; color: #94a3b8; }
        .stat-card:nth-child(1) .stat-val { color: #3b82f6; }
        .stat-card:nth-child(2) .stat-val { color: #ef4444; }
        .stat-card:nth-child(3) .stat-val { color: #10b981; }

        .container { padding: 0 15px 20px; }
        .section-title { font-size: 16px; font-weight: bold; margin: 15px 0 10px; color: #e2e8f0; border-right: 4px solid #3b82f6; padding-right: 8px; }
        .btn-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        
        .action-btn { border: none; color: white; padding: 15px 5px; border-radius: 12px; font-size: 13px; font-weight: bold; text-align: center; cursor: pointer; transition: 0.2s; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; }
        .action-btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .action-btn i { font-style: normal; font-size: 20px; }

        .btn-blue { background: #3b82f6; } .btn-green { background: #10b981; } .btn-red { background: #ef4444; } .btn-yellow { background: #f59e0b; } .btn-purple { background: #8b5cf6; } .btn-gray { background: #64748b; } .btn-dark-red { background: #991b1b; }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
        .modal.open { display: flex; }
        .modal-box { background: #1e293b; border-radius: 20px; padding: 25px 20px; max-width: 400px; width: 100%; border: 1px solid #3b82f6; position: relative; }
        .modal-title { font-size: 18px; font-weight: bold; color: #3b82f6; text-align: center; margin-bottom: 20px; }
        .modal-box.error { border-color: #ef4444; }
        .modal-box.error .modal-title { color: #ef4444; }

        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-size: 13px; color: #cbd5e1; margin-bottom: 5px; }
        .input-group input, .input-group select { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 10px; color: white; font-size: 14px; outline: none; }
        .input-group input:focus { border-color: #3b82f6; }

        .btn-submit { width: 100%; padding: 13px; background: #3b82f6; color: white; border: none; border-radius: 10px; font-weight: bold; font-size: 15px; cursor: pointer; margin-top: 5px; }
        .btn-cancel { width: 100%; padding: 13px; background: transparent; color: #94a3b8; border: 1px solid #334155; border-radius: 10px; font-weight: bold; font-size: 15px; cursor: pointer; margin-top: 10px; }
        .btn-copy { width: 100%; padding: 13px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: bold; font-size: 15px; cursor: pointer; margin-top: 10px; }

        .result-content { background: #0f172a; padding: 15px; border-radius: 12px; border: 1px solid #334155; color: #cbd5e1; font-size: 14px; line-height: 1.8; white-space: pre-line; text-align: right; max-height: 300px; overflow-y: auto; margin-bottom: 15px; }
        
        .table-wrapper { max-height: 350px; overflow-y: auto; margin-bottom: 15px; border-radius: 10px; border: 1px solid #334155; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; text-align: right; }
        th { background: #0f172a; color: #3b82f6; padding: 10px; position: sticky; top: 0; }
        td { padding: 10px; border-bottom: 1px solid #334155; color: #e2e8f0; }

        .login-screen { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; background: radial-gradient(circle, #1e293b 0%, #0f172a 100%); }
        .login-box { background: #1e293b; padding: 40px 30px; border-radius: 20px; border: 1px solid #3b82f6; width: 100%; max-width: 350px; text-align: center; }
    </style>
</head>
<body>

<?php if (!$is_logged_in): ?>
<div class="login-screen">
    <div class="login-box">
        <div class="logo" style="margin-bottom: 10px; font-size: 26px;">لوحة التحكم</div>
        <p style="color: #94a3b8; margin-bottom: 30px; font-size: 14px;">نسخة Benten</p>
        <?php if (!empty($login_error)): ?>
            <div style="background: #ef4444; color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px;"><?= $login_error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="input-group">
                <input type="password" name="admin_pass" placeholder="كلمة المرور" required style="text-align:center; letter-spacing: 5px; font-size: 18px;">
            </div>
            <button type="submit" name="login" class="btn-submit" style="background: linear-gradient(to right, #3b82f6, #8b5cf6);">دخول</button>
        </form>
    </div>
</div>

<?php else: ?>
<div class="header">
    <form method="GET" style="position: absolute; left: 15px; top: 18px;">
        <button type="submit" name="logout" value="1" class="logout">خروج 🚪</button>
    </form>
    <div class="logo">لوحة التحكم</div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-val"><?= $total_users ?></div><div class="stat-lbl">إجمالي الحسابات</div></div>
    <div class="stat-card"><div class="stat-val"><?= $banned_users ?></div><div class="stat-lbl">المحظورين</div></div>
    <div class="stat-card"><div class="stat-val"><?= $active_users ?></div><div class="stat-lbl">النشطين</div></div>
</div>

<div class="container">
    <div class="section-title">إدارة الحسابات</div>
    <div class="btn-grid">
        <div class="action-btn btn-blue" onclick="openModal('mCreate')"><i>➕</i> فتح حساب</div>
        <div class="action-btn btn-green" onclick="openModal('mDeposit')"><i>💰</i> زيادة رصيد</div>
        <div class="action-btn btn-yellow" onclick="openModal('mWithdraw')"><i>📤</i> سحب رصيد</div>
        <div class="action-btn btn-red" onclick="openModal('mBan')"><i>🚫</i> حظر حساب</div>
        <div class="action-btn btn-green" onclick="openModal('mActivate')"><i>✅</i> تفعيل حساب</div>
        <div class="action-btn btn-red" onclick="openModal('mDelete')"><i>🗑️</i> حذف حساب</div>
        <div class="action-btn btn-gray" onclick="document.getElementById('fViewUsers').submit()"><i>👥</i> المستخدمين</div>
        <div class="action-btn btn-green" onclick="openModal('mActAll')"><i>⚙️</i> تنشيط الكل</div>
        <div class="action-btn btn-red" onclick="openModal('mBanAll')"><i>🛑</i> إيقاف الكل</div>
        <div class="action-btn btn-purple" onclick="openModal('mSubs')"><i>⭐</i> اشتراك</div>
        <div class="action-btn btn-blue" onclick="openModal('mSearch')"><i>🔍</i> بحث حساب</div>
        <div class="action-btn btn-yellow" onclick="openModal('mBulk')"><i>💸</i> شحن جماعي</div>
        <div class="action-btn btn-gray" onclick="openModal('mReset')"><i>🧹</i> تصفير الأرصدة</div>
        <div class="action-btn btn-dark-red" onclick="openModal('mDeleteAll')"><i>🚨</i> حذف الكل نهائياً</div>
    </div>
    <form id="fViewUsers" method="POST" style="display:none;"><input type="hidden" name="view_users" value="1"></form>
</div>

<!-- النوافذ المنبثقة -->
<div class="modal" id="mCreate"><div class="modal-box"><div class="modal-title">فتح حساب جديد</div><form method="POST"><div class="input-group"><label>اسم المستخدم</label><input type="text" name="acc_name" required></div><div class="input-group"><label>كلمة المرور</label><input type="text" name="acc_pass" required></div><div class="input-group"><label>الرصيد الابتدائي</label><input type="number" step="0.01" name="acc_bal" value="0" required></div><button type="submit" name="create_account" class="btn-submit">إنشاء</button><button type="button" class="btn-cancel" onclick="closeModal('mCreate')">إلغاء</button></form></div></div>

<div class="modal" id="mDeposit"><div class="modal-box"><div class="modal-title">زيادة رصيد</div><form method="POST"><div class="input-group"><label>رقم الحساب</label><input type="text" name="dep_acc" required></div><div class="input-group"><label>المبلغ</label><input type="number" step="0.01" name="dep_amt" required></div><button type="submit" name="deposit" class="btn-submit btn-green">شحن</button><button type="button" class="btn-cancel" onclick="closeModal('mDeposit')">إلغاء</button></form></div></div>

<div class="modal" id="mWithdraw"><div class="modal-box"><div class="modal-title">سحب رصيد</div><form method="POST"><div class="input-group"><label>رقم الحساب</label><input type="text" name="wd_acc" required></div><div class="input-group"><label>المبلغ</label><input type="number" step="0.01" name="wd_amt" required></div><button type="submit" name="withdraw" class="btn-submit btn-yellow">سحب</button><button type="button" class="btn-cancel" onclick="closeModal('mWithdraw')">إلغاء</button></form></div></div>

<div class="modal" id="mBan"><div class="modal-box"><div class="modal-title">حظر حساب</div><form method="POST"><div class="input-group"><label>رقم الحساب</label><input type="text" name="ban_acc" required></div><button type="submit" name="ban" class="btn-submit btn-red">حظر</button><button type="button" class="btn-cancel" onclick="closeModal('mBan')">إلغاء</button></form></div></div>

<div class="modal" id="mActivate"><div class="modal-box"><div class="modal-title">تفعيل حساب</div><form method="POST"><div class="input-group"><label>رقم الحساب</label><input type="text" name="act_acc" required></div><button type="submit" name="activate" class="btn-submit btn-green">تفعيل</button><button type="button" class="btn-cancel" onclick="closeModal('mActivate')">إلغاء</button></form></div></div>

<div class="modal" id="mDelete"><div class="modal-box"><div class="modal-title">حذف حساب</div><form method="POST"><div class="input-group"><label>رقم الحساب</label><input type="text" name="del_acc" required></div><button type="submit" name="delete_account" class="btn-submit btn-red">حذف نهائي</button><button type="button" class="btn-cancel" onclick="closeModal('mDelete')">إلغاء</button></form></div></div>

<div class="modal" id="mActAll"><div class="modal-box"><div class="modal-title">تنشيط الكل</div><form method="POST"><button type="submit" name="activate_all" class="btn-submit btn-green">نعم، تفعيل</button><button type="button" class="btn-cancel" onclick="closeModal('mActAll')">إلغاء</button></form></div></div>

<div class="modal" id="mBanAll"><div class="modal-box"><div class="modal-title">إيقاف الكل</div><form method="POST"><button type="submit" name="ban_all" class="btn-submit btn-red">نعم، إيقاف</button><button type="button" class="btn-cancel" onclick="closeModal('mBanAll')">إلغاء</button></form></div></div>

<div class="modal" id="mSubs"><div class="modal-box"><div class="modal-title">اشتراك</div><form method="POST"><div class="input-group"><label>رقم الحساب</label><input type="text" name="sub_acc" required></div><div class="input-group"><label>المبلغ المضاف</label><input type="number" step="0.01" name="sub_amt" required></div><button type="submit" name="subscribe" class="btn-submit btn-purple">تفعيل</button><button type="button" class="btn-cancel" onclick="closeModal('mSubs')">إلغاء</button></form></div></div>

<div class="modal" id="mSearch"><div class="modal-box"><div class="modal-title">بحث حساب</div><form method="POST"><div class="input-group"><label>رقم الحساب</label><input type="text" name="search_acc" required></div><button type="submit" name="search" class="btn-submit btn-blue">بحث</button><button type="button" class="btn-cancel" onclick="closeModal('mSearch')">إلغاء</button></form></div></div>

<div class="modal" id="mBulk"><div class="modal-box"><div class="modal-title">شحن جماعي</div><form method="POST"><div class="input-group"><label>المبلغ للجميع</label><input type="number" step="0.01" name="bulk_amt" required></div><button type="submit" name="bulk_deposit" class="btn-submit btn-yellow">شحن الجميع</button><button type="button" class="btn-cancel" onclick="closeModal('mBulk')">إلغاء</button></form></div></div>

<div class="modal" id="mReset"><div class="modal-box"><div class="modal-title">تصفير الأرصدة</div><p style="color:#ef4444;text-align:center;">هل أنت متأكد؟</p><form method="POST"><button type="submit" name="reset_balances" class="btn-submit btn-red">نعم، تصفير</button><button type="button" class="btn-cancel" onclick="closeModal('mReset')">إلغاء</button></form></div></div>

<div class="modal" id="mDeleteAll"><div class="modal-box" style="border-color:#991b1b;"><div class="modal-title" style="color:#ef4444;">⚠️ تحذير خطير</div><p style="color:#ef4444;text-align:center; font-weight:bold;">سيتم حذف جميع الحسابات نهائياً!</p><form method="POST"><button type="submit" name="delete_all_users" class="btn-submit" style="background:#991b1b;">نعم، احذف الكل</button><button type="button" class="btn-cancel" onclick="closeModal('mDeleteAll')">إلغاء</button></form></div></div>

<!-- نافذة النتيجة -->
<?php if ($result_data): ?>
<div class="modal open" id="resultModal">
    <div class="modal-box <?= $result_data['status'] === 'error' ? 'error' : '' ?>" <?= isset($result_data['show_users_table']) ? 'style="max-width:600px;"' : '' ?>>
        <div class="modal-title"><?= htmlspecialchars($result_data['title']) ?></div>
        <?php if (isset($result_data['show_users_table']) && !empty($result_data['users'])): ?>
            <div class="table-wrapper">
                <table>
                    <tr><th>الحساب</th><th>الاسم</th><th>الرصيد</th><th>الحالة</th></tr>

<?php foreach ($result_data['users'] as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['account_number']) ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= number_format($u['balance'], 2) ?></td>
                            <td style="color:<?= $u['status']=='active'?'#10b981':'#ef4444' ?>"><?= $u['status']=='active'?'نشط':'محظور' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php else: ?>
            <div class="result-content"><?= htmlspecialchars($result_data['msg']) ?></div>
        <?php endif; ?>
        <?php if (!empty($result_data['copy'])): ?>
            <button class="btn-copy" onclick="copyResult()">📋 نسخ الملصق</button>
        <?php endif; ?>
        <button class="btn-cancel" onclick="document.getElementById('resultModal').classList.remove('open')">إغلاق</button>
    </div>
</div>
<script>
function copyResult() {
    const text = <?= json_encode($result_data['copy'] ?? '') ?>;
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.focus(); ta.select();
    try { document.execCommand('copy'); alert('✅ تم نسخ الملصق بنجاح!'); } catch (err) { alert('❌ فشل النسخ'); }
    document.body.removeChild(ta);
}
</script>
<?php endif; ?>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>

<?php endif; ?>
</body>
</html>
