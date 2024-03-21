<?php /** @noinspection PhpIncludeInspection */

namespace App\Services;

use App\Services\amoCRM\Models\Contacts;
use App\Services\TargetSMS\Messages;
use CooperAV\SmsAero\SmsAero;
use Illuminate\Support\Facades\Log;
use Zelenin\SmsRu\Api;
use Zelenin\SmsRu\Auth\ApiIdAuth;
use Zelenin\SmsRu\Client\Client;
use Zelenin\SmsRu\Entity\Sms;

//require_once 'app/Services/TargetSMS/sms.class.php';

class SmsHelper
{
    public static function matchClient($account): SmsAero|TargetSMS|Api
    {
        $access = static::getClient($account->subdomain);

        return match ($account->subdomain) {
            'bbeducation', 'bclawyers', 'maed' => new TargetSMS($access['login'], $access['pass']),

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
            ],
            'maed' => [
                'login' => env('MAED_LOGIN'),
                'pass'  => env('MAED_PASS'),
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

        if ($subdomain == 'bbeducation' ||
            $subdomain == 'bclawyers' ||
            $subdomain == 'maed') {

            if ($subdomain == 'bbeducation')

                $result = $client->sendSMS($phone, env('BBE_SENDER'), $sms);

            if ($subdomain == 'bclawyers')

                $result = $client->sendSMS($phone, env('MDS_SENDER'), $sms);

            if ($subdomain == 'maed')

                $result = $client->sendSMS($phone, env('MAED_SENDER'), $sms);

            Log::info(__METHOD__.' '.$subdomain, [$result ?? []]);

            return [
                'status' => $result['info']['http_code'] == 200
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
