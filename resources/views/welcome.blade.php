<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Diner</title>

        <link rel="icon" href="/favicon.ico" sizes="any">

        <style>
            /* Minimal styling using utility-like classes for simplicity */
            :root{--bg:#ffffff;--text:#1b1b18;--muted:#6b7280}
            *,*::before,*::after{box-sizing:border-box}
            html,body{height:100%;margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial}
            /* Prevent horizontal scroll and allow vertical scrolling when strictly needed */
            body{background:var(--bg);color:var(--text);display:flex;flex-direction:column;min-height:100vh;overflow-x:hidden}

            /* Header / nav */
            header{width:100%;padding:.75rem 1rem;display:flex;justify-content:flex-end;align-items:center}
            .nav-links{display:flex;gap:0.5rem}
            .btn{display:inline-block;padding:0.45rem 0.75rem;border-radius:0.5rem;text-decoration:none;font-weight:600}
            .btn-primary{background:#111827;color:white}
            .btn-ghost{background:transparent;color:var(--text);border:1px solid #e5e7eb}

            /* Main hero: center content and ensure it never overflows viewport */
            .hero{flex:1;display:flex;align-items:center;justify-content:center;padding:1rem}
            .logo-wrap{display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.5rem;text-align:center}
            /* Logo: scale to fit both width and height of viewport (leaving space for header/footer) */
            .logo{width:auto;height:auto;max-width:90vw;max-height:calc(100vh - 6.5rem);object-fit:contain}
            .subtitle{margin-top:0.5rem;color:var(--muted);font-size:0.95rem}

            /* Responsive sizing for larger viewports */
            @media (min-width:640px){ .logo{max-width:70vw} }
            @media (min-width:1024px){ .logo{max-width:50vw;max-height:calc(100vh - 8rem)} }

            /* Small fade-in, respects prefers-reduced-motion */
            @media (prefers-reduced-motion: no-preference){
                .logo{opacity:0;transform:translateY(8px);animation:logoIn .65s ease forwards}
                @keyframes logoIn{to{opacity:1;transform:none}}
            }
        </style>
    </head>
    <body>
        <header>
            @if (Route::has('login'))
                <nav class="nav-links" aria-label="Authentication links">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost">Iniciar sesión</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary">Registro</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="hero">
            <div class="logo-wrap" role="img" aria-label="Logo Diner">
                <img src="{{ asset('img/logo.JPG') }}" alt="Logo Diner" class="logo" />
                <div class="subtitle">Sistema de gestión de préstamos</div>
            </div>
        </main>

        <footer style="text-align:center;padding:.75rem;color:var(--muted);font-size:.9rem">
            © {{ date('Y') }} Diner
        </footer>
    </body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    </body>
</html>
