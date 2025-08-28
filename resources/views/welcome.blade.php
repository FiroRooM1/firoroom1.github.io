<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>League of Legends フレンド募集</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            body {
                font-family: 'Nunito', sans-serif;
                background: url('/images/KogMaw_37.jpg') no-repeat center center;
                background-size: cover;
                background-attachment: scroll;
                color: white;
                margin: 0;
                min-height: 100vh;
            }
            
            .lol-header {
                background: rgba(0, 0, 0, 0.7);
                border-bottom: 2px solid #c89b3c;
                padding: 1rem 0;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                backdrop-filter: blur(10px);
            }
            
            .lol-nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 2rem;
            }
            
            .lol-logo {
                font-size: 1.5rem;
                font-weight: bold;
                color: #c89b3c;
                text-transform: uppercase;
                letter-spacing: 2px;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            }
            
            .lol-nav-links a {
                color: #ffffff;
                text-decoration: none;
                margin-left: 2rem;
                padding: 0.5rem 1rem;
                border: 1px solid transparent;
                border-radius: 5px;
                transition: all 0.3s ease;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            }
            
            .lol-nav-links a:hover {
                border-color: #c89b3c;
                background: rgba(200, 155, 60, 0.2);
            }
            
            .lol-hero {
                text-align: center;
                padding: 8rem 2rem 4rem;
                background: rgba(0, 0, 0, 0.4);
                margin: 2rem;
                border-radius: 15px;
                backdrop-filter: blur(5px);
            }
            
            .lol-hero h1 {
                font-size: 3.5rem;
                font-weight: bold;
                color: #c89b3c;
                text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.9);
                margin-bottom: 1rem;
                text-transform: uppercase;
                letter-spacing: 3px;
            }
            
            .lol-hero p {
                font-size: 1.3rem;
                color: #ffffff;
                margin-bottom: 2rem;
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.9);
                line-height: 1.6;
            }
            
            .lol-cta {
                display: inline-block;
                background: #00bfff;
                color: #000000;
                padding: 1rem 2rem;
                text-decoration: none;
                border-radius: 10px;
                font-weight: bold;
                font-size: 1.1rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            }
            
            .lol-cta:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(0, 191, 255, 0.4);
            }
            
            .lol-features {
                max-width: 1200px;
                margin: 0 auto;
                padding: 4rem 2rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
            }
            
            .lol-feature-card {
                background: rgba(0, 0, 0, 0.8);
                border: 2px solid #c89b3c;
                border-radius: 15px;
                padding: 2rem;
                text-align: center;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
            }
            
            .lol-feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 35px rgba(200, 155, 60, 0.3);
            }
            
            .lol-feature-icon {
                width: 4rem;
                height: 4rem;
                background: linear-gradient(45deg, #c89b3c, #f4e4a6);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1rem;
                font-size: 1.5rem;
                color: #000;
            }
            
            .lol-feature-card h3 {
                color: #ffffff;
                font-size: 1.5rem;
                margin-bottom: 1rem;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .lol-feature-card p {
                color: #ffffff;
                line-height: 1.6;
            }
            
            .lol-footer {
                background: rgba(0, 0, 0, 0.8);
                border-top: 2px solid #c89b3c;
                text-align: center;
                padding: 2rem;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                backdrop-filter: blur(10px);
            }
            
            .lol-footer p {
                color: #ffffff;
                margin: 0;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
            }
            
            @media (max-width: 768px) {
                .lol-hero h1 {
                    font-size: 2.5rem;
                }
                
                .lol-nav {
                    flex-direction: column;
                    gap: 1rem;
                }
                
                .lol-nav-links a {
                    margin: 0 0.5rem;
                }
                
                .lol-hero {
                    margin: 1rem;
                    padding: 6rem 1rem 3rem;
                }
            }
        </style>
    </head>
    <body>
        <header class="lol-header">
            <nav class="lol-nav">
                <div class="lol-logo">LoL フレンド募集</div>
                <div class="lol-nav-links">
                    @if (Route::has('login'))
                        @guest
                            <a href="{{ route('login') }}">ログイン</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}">作成</a>
                            @endif
                        @endguest
                    @endif
                </div>
            </nav>
        </header>

        <main>
            <section class="lol-hero">
                <h1>League of Legends</h1>
                <p>サモナーズ・リフトで最高のチームメイトを見つけよう！ランク戦、ノーマルゲーム、ARAMなど、あなたに合ったプレイスタイルでフレンドと一緒に戦い抜こう。</p>
                <a href="{{ route('register') }}" class="lol-cta">今すぐ参加する</a>
            </section>
        </main>

        <footer class="lol-footer">
            <p>&copy; 2025 League of Legends フレンド募集. すべての権利はRiot Gamesに帰属します。</p>
        </footer>
    </body>
</html>
