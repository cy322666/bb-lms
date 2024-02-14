<?php

namespace App\Services;

use App\Models\Core\Account;
use App\Services\amoCRM\Models\Contacts;
use App\Services\Sms\Xml\Messages;
use CooperAV\SmsAero\SmsAero;
use Illuminate\Support\Facades\Log;
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
            'bbeducation', 'bclawyers' => new TargetSMS($access['login'], $access['pass']),

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
            'bclawyers' => [
                'login' => env('MDS_LOGIN'),
                'pass'  => env('MDS_PASS'),
            ],
        ];

        return $tokens[$subdomain];
    }

    public static function getText($subdomain, $lead, $code): string
    {
        return match ($subdomain) {
            'bbeducation' => 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: '.$code.'. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. Cайт https://bangbangeducation.ru',
            'fashionfactoryschool', 'maed', 'bclawyers' => 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: '.$code.'. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. ',//TODO Cайт https://bangbangeducation.ru',
        };
    }

    public static function generateCode(): int
    {
        return rand(1000, 9999);
    }

    public static function send(string $subdomain, $client, string $phone, string $sms)
    {
        if ($subdomain == 'fashionfactoryschool') {

            $response = $client->send($phone, $sms, 'DIRECT');

            return [
                'status' => $response['success']
            ];
        }

        if ($subdomain == 'bbeducation' || $subdomain = 'bclawyers') {

            if ($subdomain == 'bbeducation') {

                $messages = new Messages(env('BBE_LOGIN'), env('BBE_PASS'));
                $messages->setUrl('https://sms.targetsms.ru');
                $mes = $messages->createNewMessage(env('BBE_SENDER'), $sms);
            }

            if ($subdomain == 'bclawyers') {

                $messages = new Messages(env('MDS_LOGIN'), env('MDS_PASS'));
                $messages->setUrl('https://sms.targetsms.ru');
                $mes = $messages->createNewMessage(env('MDS_SENDER'), $sms);
            }

            $abonent = $mes->createAbonent(Contacts::clearPhone($phone));
            $abonent->setNumberSms(1);
            $mes->addAbonent($abonent);

            $result = $messages->send();

            Log::info(__METHOD__, [$result]);

            return [
                'status' => $result[1]['tag'] ?? 'undefined'
            ];
        }

        if ($subdomain == 'maed') {

            $sms = new Sms($phone, $sms);

            $response = $client->smsSend($sms);

            Log::alert(__METHOD__, [$response]);

            $client->smsStatus($response->ids[0] ?? null);

            return [
                'status' => $response['success'] //TODO
            ];
        }
    }

    public static function matchStatus($subdomain): int
    {
        return match ($subdomain) {
            'bbeducation' => 59740474,
            'fashionfactoryschool' => 63581337,
            'maed' => 63649681,
            'bclawyers' => 63843810,
        };
    }
}
