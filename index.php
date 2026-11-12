<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>موقعي الثابت</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #0f172a; color: #e2e8f0; line-height: 1.6; }
        
        /* القائمة العلوية */
        header { background-color: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #3b82f6; position: sticky; top: 0; z-index: 100; }
        header h1 { font-size: 1.5rem; color: #3b82f6; }
        nav a { color: #cbd5e1; text-decoration: none; margin-right: 15px; font-weight: bold; transition: color 0.3s; }
        nav a:hover { color: #3b82f6; }

        /* القسم الرئيسي (Hero) */
        .hero { background: linear-gradient(135deg, #1e293b, #0f172a); text-align: center; padding: 100px 20px; border-bottom: 1px solid #334155; }
        .hero h2 { font-size: 3rem; margin-bottom: 15px; color: #ffffff; }
        .hero p { font-size: 1.2rem; margin-bottom: 30px; color: #94a3b8; }
        .btn { display: inline-block; background: linear-gradient(to right, #3b82f6, #8b5cf6); color: white; padding: 15px 35px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 1.1rem; transition: transform 0.3s, box-shadow 0.3s; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.4); }

        /* قسم الخدمات */
        .features { padding: 80px 20px; text-align: center; background-color: #1e293b; }
        .features h2 { margin-bottom: 50px; color: #ffffff; font-size: 2rem; }
        .grid { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
        .card { background: #0f172a; padding: 40px 30px; border-radius: 15px; width: 320px; border: 1px solid #334155; transition: transform 0.3s, border-color 0.3s; }
        .card:hover { transform: translateY(-10px); border-color: #3b82f6; }
        .card h3 { color: #3b82f6; margin-bottom: 15px; font-size: 1.3rem; }
        .card p { color: #94a3b8; }

        /* التذييل */
        footer { background-color: #0f172a; color: #94a3b8; text-align: center; padding: 30px; border-top: 1px solid #334155; margin-top: 40px; }
    </style>
</head>
<body>

    <header>
        <h1>موقعي</h1>
        <nav>
            <a href="#home">الرئيسية</a>
            <a href="#services">الخدمات</a>
            <a href="#contact">تواصل معنا</a>
        </nav>
    </header>

    <section class="hero" id="home">
        <h2>مرحباً بك في موقعي الجديد</h2>
        <p>هذا موقع ثابت تم تصميمه باستخدام HTML و CSS، وهو سريع جداً ومتوافق مع جميع الأجهزة.</p>
        <a href="#services" class="btn">اكتشف خدماتنا</a>
    </section>

    <section class="features" id="services">
        <h2>لماذا تختارنا؟</h2>
        <div class="grid">
            <div class="card">
                <h3>🚀 سرعة فائقة</h3>
                <p>الموقع سريع جداً لأنه لا يحتوي على قاعدة بيانات أو أكواد معقدة.</p>
            </div>
            <div class="card">
                <h3>💻 تصميم متجاوب</h3>
                <p>يعمل بشكل ممتاز على الجوالات، الأجهزة اللوحية، وأجهزة الكمبيوتر.</p>
            </div>
            <div class="card">
                <h3>🔒 أمان عالي</h3>
                <p>بما أنه موقع ثابت، فهو محمي من معظم الثغرات الأمنية الشائعة.</p>
            </div>
        </div>
    </section>

    <footer id="contact">
        <p>جميع الحقوق محفوظة &copy; 2024 - موقعي</p>
        <p>للتواصل: info@mywebsite.com</p>
    </footer>

</body>
</html>
