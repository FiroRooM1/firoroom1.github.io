<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8); font-weight: 600; font-size: 16px; line-height: 1.6;">
        パスワードをお忘れですか？登録したメールアドレスを入力してください
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="メールアドレス" class="text-white font-semibold text-lg" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);" />
            <input id="email" 
                   class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus
                   style="background-color: #ffffff; color: #000000; border: 2px solid #4f46e5; padding: 12px; font-size: 16px;" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                パスワードリセットリンクを送信
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
