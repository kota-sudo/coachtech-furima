<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('【coachtechフリマ】メールアドレスの認証')
                ->greeting('coachtechフリマ')
                ->line('会員登録ありがとうございます。')
                ->line('以下のボタンをクリックして、メールアドレスの認証を完了してください。')
                ->action('メール認証を完了する', $url)
                ->line('認証の有効期限が切れた場合は、認証案内画面から再度メールを送信できます。')
                ->line('このメールに心当たりがない場合は、破棄していただいて問題ありません。')
                ->salutation('coachtechフリマ');
        });
    }
}
