<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($data['title'] ?? 'SAIP') ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #081C15 0%, #0F3D2E 50%, #0b1120 100%);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }
        .hero { max-width: 720px; }
        .logo { font-size: 72px; margin-bottom: 16px; filter: drop-shadow(0 0 24px rgba(34, 197, 94, 0.5)); }
        h1 {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 3px;
            background: linear-gradient(135deg, #10b981, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
        }
        .tagline {
            font-size: 14px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 28px;
        }
        .description {
            font-size: 16px;
            color: #cbd5e1;
            line-height: 1.6;
            max-width: 560px;
            margin: 0 auto 36px;
        }
        .actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.6);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #f1f5f9;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-3px);
        }
        .footer {
            margin-top: 60px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="logo">🌱</div>
        <h1>SAIP</h1>
        <div class="tagline">Smart Agriculture Intelligence Platform</div>
        <p class="description">
            Empowering Rwanda's agricultural sector with data-driven insights, 
            real-time analytics, and intelligent tools for farmers, cooperatives, and government.
        </p>
        <div class="actions">
            <a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary">
                🔓 Sign In
            </a>
            <a href="<?= BASE_URL ?>/auth/register" class="btn btn-secondary">
                ✨ Create Account
            </a>
        </div>
        <div class="footer">
            © <?= date('Y') ?> SAIP. All rights reserved.
        </div>
    </div>
</body>
</html>