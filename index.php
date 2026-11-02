<?php
// index.php — Landing page (root)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/bcp-logo.png" type="image/x-icon">
    <title>BCP SMS | Welcome</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #1a3d6d 0%, #2c5aa0 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }

        .landing-card {
            background: #fff;
            max-width: 720px; width: 100%;
            border-radius: 16px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            text-align: center;
        }

        .landing-card img { height: 80px; margin-bottom: 16px; }

        .landing-card h1 {
            color: #1a3d6d; font-size: 26px; margin-bottom: 8px;
        }

        .landing-card p.subtitle {
            color: #666; font-size: 15px; margin-bottom: 36px;
        }

        .buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        @media (max-width: 500px) {
            .buttons { grid-template-columns: 1fr; }
            .landing-card { padding: 32px 24px; }
        }

        .btn {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 32px 20px; border-radius: 12px;
            text-decoration: none; font-weight: 600;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .btn .icon { font-size: 40px; margin-bottom: 12px; line-height: 1; }
        .btn .label { font-size: 16px; font-weight: 700; }
        .btn .desc {
            font-size: 12px; font-weight: 400;
            margin-top: 6px; opacity: 0.85;
        }

        .btn-admin { background: #1a3d6d; color: #fff; }
        .btn-admin:hover {
            background: #142f55; transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(26,61,109,0.35);
        }

        .btn-website {
            background: #fff; color: #1a3d6d;
            border-color: #1a3d6d;
        }
        .btn-website:hover {
            background: #f0f5fb; transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(26,61,109,0.2);
        }

        .footer-note { margin-top: 36px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="landing-card">
        <img src="assets/bcp-logo.png" alt="BCP Logo">
        <h1>BCP Student Management System</h1>
        <p class="subtitle">Choose how you want to continue</p>

        <div class="buttons">
            <!-- 🛡️ Admin Login → public_html/index.php (nested) -->
            <a href="public_html/index.php" class="btn btn-admin">
                <span class="icon">🛡️</span>
                <span class="label">Admin Login</span>
                <span class="desc">For employees &amp; staff</span>
            </a>

            <!-- 🎓 Applicant Website → web/index.html -->
            <a href="web/index.html" class="btn btn-website">
                <span class="icon">🎓</span>
                <span class="label">Applicant Website</span>
                <span class="desc">Apply &amp; view information</span>
            </a>
        </div>

        <p class="footer-note">
            © <span id="year"></span> BCP SMS · All rights reserved
        </p>
    </div>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>