<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error — Step Shine Works</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; color: #111827; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 48px; text-align: center; max-width: 400px; width: 90%; }
        .code { font-size: 72px; font-weight: 700; color: #fca5a5; line-height: 1; margin-bottom: 16px; }
        h1 { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
        p { font-size: 14px; color: #6b7280; margin-bottom: 24px; }
        a { display: inline-block; background: #1d1d1f; color: #fff; text-decoration: none; font-size: 14px; font-weight: 500; padding: 10px 24px; border-radius: 8px; }
        a:hover { background: #374151; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">500</div>
        <h1>Terjadi kesalahan</h1>
        <p>Server mengalami masalah. Tim kami sudah diberitahu. Coba lagi dalam beberapa menit.</p>
        @auth
            <a href="{{ url('/dashboard') }}">Kembali ke dashboard</a>
        @else
            <a href="{{ url('/') }}">Kembali ke beranda</a>
        @endauth
    </div>
</body>
</html>
