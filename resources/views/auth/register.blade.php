<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Display Name -->
        <div>
            <x-input-label for="display_name" value="表示名" class="lol-label" />
            <x-text-input id="display_name" class="block mt-2 w-full lol-input px-4 py-3 rounded-lg" type="text" name="display_name" :value="old('display_name')" required autofocus autocomplete="name" placeholder="表示名を入力してください..." />
            <x-input-error :messages="$errors->get('display_name')" class="mt-2 text-red-400" />
        </div>

        <!-- Summoner Name -->
        <div class="mt-6">
            <x-input-label for="summoner_name" value="サモナー名" class="lol-label" />
            <x-text-input id="summoner_name" class="block mt-2 w-full lol-input px-4 py-3 rounded-lg" type="text" name="summoner_name" :value="old('summoner_name')" required autocomplete="username" placeholder="例: Test#JP1" />
            <x-input-error :messages="$errors->get('summoner_name')" class="mt-2 text-red-400" />
            <p class="mt-2 text-sm text-gray-300">
                League of LegendsのRiot IDを入力してください（例: Test#JP1）
            </p>
        </div>

        <!-- Email Address -->
        <div class="mt-6">
            <x-input-label for="email" value="メールアドレス" class="lol-label" />
            <x-text-input id="email" class="block mt-2 w-full lol-input px-4 py-3 rounded-lg" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="メールアドレスを入力してください..." />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        <!-- Password -->
        <div class="mt-6">
            <x-input-label for="password" value="パスワード" class="lol-label" />

            <x-text-input id="password" class="block mt-2 w-full lol-input px-4 py-3 rounded-lg"
                            type="password"
                            name="password"
                            required autocomplete="new-password" 
                            placeholder="パスワードを作成してください..." />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-6">
            <x-input-label for="password_confirmation" value="パスワード確認" class="lol-label" />

            <x-text-input id="password_confirmation" class="block mt-2 w-full lol-input px-4 py-3 rounded-lg"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" 
                            placeholder="パスワードを再入力してください..." />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center justify-between mt-8">
            <a class="text-sm text-yellow-400 hover:text-yellow-300 underline transition-colors" href="{{ route('login') }}">
                既にアカウントをお持ちですか？
            </a>

            <x-primary-button class="lol-button px-8 py-3 rounded-lg text-lg font-bold">
                作成
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
