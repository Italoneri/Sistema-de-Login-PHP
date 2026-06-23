<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Auth Seguro') }}</title>
    {{-- CSS embutido (sem CDN externo) — coerente com a CSP default-src 'self' --}}
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #f4f5f7;
            margin: 0;
            padding: 0;
            color: #1f2430;
        }
        .container {
            max-width: 420px;
            margin: 80px auto;
            background: #fff;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 { font-size: 1.4rem; margin-bottom: 24px; }
        label { display: block; margin-bottom: 4px; font-size: 0.9rem; font-weight: 600; }
        input[type=text], input[type=email], input[type=password] {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #2d6cdf;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover { background: #2559b8; }
        .errors { background: #fdecea; color: #b3261e; padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 0.9rem; }
        .status { background: #eaf6ec; color: #1e6b34; padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 0.9rem; }
        .links { margin-top: 16px; font-size: 0.9rem; text-align: center; }
        .links a { color: #2d6cdf; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <ul style="margin:0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
