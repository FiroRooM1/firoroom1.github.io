<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8); font-weight: 600; font-size: 16px; line-height: 1.6;">
        新しいパスワードを設定してください
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="メールアドレス" class="text-white font-semibold text-lg" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="新しいパスワード" class="text-white font-semibold text-lg" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="パスワード確認" class="text-white font-semibold text-lg" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                パスワードをリセット
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
