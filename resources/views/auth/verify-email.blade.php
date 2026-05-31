<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-800 mb-6">メール認証</h1>

    <p class="text-sm text-gray-600">
        登録していただいたメールアドレス宛に認証メールを送信しました。<br>
        メール内の「メール認証を完了する」ボタンをクリックして、会員登録を完了してください。
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700">
            新しい認証メールを送信しました。
        </div>
    @endif

    <div class="mt-6 space-y-4">
        <a
            href="http://localhost:8025"
            target="_blank"
            rel="noopener"
            class="block w-full rounded-md bg-red-500 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-red-600"
        >
            認証メールを確認する
        </a>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button
                type="submit"
                class="w-full text-sm text-indigo-600 underline hover:text-indigo-800"
            >
                認証メールを再送する
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-500 underline hover:text-gray-700">
            ログアウト
        </button>
    </form>
</x-guest-layout>
