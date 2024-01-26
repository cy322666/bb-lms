<?php

namespace App\Services;

use App\Models\Core\Account;
use CooperAV\SmsAero\SmsAero;

class SmsHelper
{
    public static function matchClient(Account $account): SmsAero|TargetSMS
    {
        $access = static::getClient($account->subdomain);

        return match ($account->subdomain) {
            'bbeducation' => new TargetSMS($access['login'], $access['pass']),
            'fashionfactoryschool' => new SmsAero($access['login'],$access['api_key']),
        };
    }

    public static function getClient($subdomain): array
    {
        $tokens = [
            'bbeducation' => [
                'login' => env('BBE_LOGIN'),
                'pass'  => env('BBE_PASS'),
            ],
            'fashionfactoryschool' => [
                'login'   => env('FF_LOGIN'),
                'api_key' => env('FF_API_KEY'),
            ]
        ];

        return $tokens[$subdomain];
    }

    public static function getText($subdomain, $lead): string
    {
        $code = static::generateCode();

        return match ($subdomain) {
            'bbeducation' => 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: '.$code.'. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. Cайт https://bangbangeducation.ru',
            'fashionfactoryschool' => 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: '.$code.'. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. '//TODO Cайт https://bangbangeducation.ru',
        };
    }

    public static function generateCode(): int
    {
        return rand(1000, 9999);
    }

    public static function send(string $subdomain, $client, int $phone, string $sms)
    {
        if ($subdomain == 'fashionfactoryschool') {

            $client->send($phone, $sms, 'DIRECT');
        }

        if ($subdomain == 'bbeducation') {

            //TODO
//            $values = TargetSMS::parsingResponse($result);
//
//            $code   = $values[1]['attributes']['CODE'];
//            $idSms  = $values[1]['attributes']['ID_SMS'];
//            $status = $values[1]['attributes']['STATUS'];
        }
    }
}
