<?php

namespace App\Services;

use App\Models\Core\Account;
use CooperAV\SmsAero\SmsAero;
use Zelenin\SmsRu\Api;
use Zelenin\SmsRu\Auth\ApiIdAuth;
use Zelenin\SmsRu\Client\Client;
use Zelenin\SmsRu\Entity\Sms;

class SmsHelper
{
    public static function matchClient($account): SmsAero|TargetSMS|Api
    {
        $access = static::getClient($account->subdomain);

        return match ($account->subdomain) {
            'bbeducation' => new TargetSMS($access['login'], $access['pass']),
            'fashionfactoryschool' => new SmsAero($access['login'],$access['api_key']),
            'maed' => new Api(new ApiIdAuth($access['api_key']), new Client()),
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
            ],
            'maed' => [
                'login'   => env('MAED_LOGIN'),
                'api_key' => env('MAED_APIKEY'),
            ],
        ];

        return $tokens[$subdomain];
    }

    public static function getText($subdomain, $lead, $code): string
    {
        return match ($subdomain) {
            'bbeducation' => 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: '.$code.'. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. Cайт https://bangbangeducation.ru',
            'fashionfactoryschool' => 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: '.$code.'. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. ',//TODO Cайт https://bangbangeducation.ru',
            'maed' => 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: '.$code.'. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. ',//TODO Cайт
        };
    }

    public static function generateCode(): int
    {
        return rand(1000, 9999);
    }

    public static function send(string $subdomain, $client, int $phone, string $sms)
    {
        if ($subdomain == 'fashionfactoryschool') {

            $response = $client->send($phone, $sms, 'DIRECT');

            return [
                'status' => $response['success']
            ];
        }

        if ($subdomain == 'bbeducation') {

            //TODO
//            $values = TargetSMS::parsingResponse($result);
//
//            $code   = $values[1]['attributes']['CODE'];
//            $idSms  = $values[1]['attributes']['ID_SMS'];
//            $status = $values[1]['attributes']['STATUS'];
        }
          if ($subdomain == 'maed') {

            $response = new Sms($phone, $sms);

            $client->smsStatus($response->ids[0] ?? null);

            return [
                'status' => $response['success'] ?? $response //TODO
            ];
        }
    }

    public static function matchStatus($subdomain): int
    {
        return match ($subdomain) {
            'bbeducation' => 59740474,
            'fashionfactoryschool' => 63581337,
            'maed' => '',//TODO
        };
    }
}
