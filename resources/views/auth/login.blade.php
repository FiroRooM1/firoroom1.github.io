<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="メールアドレス" class="lol-label" />
            <x-text-input id="email" class="block mt-2 w-full lol-input px-4 py-3 rounded-lg" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="メールアドレスを入力してください..." />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        <!-- Password -->
        <div class="mt-6">
            <x-input-label for="password" value="パスワード" class="lol-label" />

            <x-text-input id="password" class="block mt-2 w-full lol-input px-4 py-3 rounded-lg"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="パスワードを入力してください..." />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-6">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="lol-checkbox" name="remember">
                <span class="ml-3 text-sm text-yellow-200 hover:text-yellow-100 transition-colors">ログイン状態を保持する</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-8">
            @if (Route::has('password.request'))
                <a class="text-sm text-yellow-400 hover:text-yellow-300 underline transition-colors" href="{{ route('password.request') }}">
                    パスワードを忘れた場合
                </a>
            @endif

            <x-primary-button class="lol-button px-8 py-3 rounded-lg text-lg font-bold">
                ログイン
            </x-primary-button>
        </div>
        
        <!-- Register Link -->
        <div class="text-center">
            <p class="text-gray-300">
                アカウントをお持ちでない場合
                <a href="{{ route('register') }}" class="text-yellow-400 hover:text-yellow-300 underline font-semibold transition-colors">
                    リーグに参加する
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
