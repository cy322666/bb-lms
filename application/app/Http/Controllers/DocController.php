<?php

namespace App\Http\Controllers;

use App\Models\Core\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\TargetSMS;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocController extends Controller
{
    //push sms
    /**
     * @throws Exception
     */
    public function agreement(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $authCode = new TargetSMS(
            env('TARGET_LOGIN'),
            env('TARGET_PASSWORD')
        );

        $account = Account::query()->first();

        $amoApi = (new Client($account))->init();

        $lead = $amoApi->service->leads()->find($request->toArray()['leads']['status'][0]['id']);

        $contact = $lead->contact;

        $phone = $contact->cf('Телефон')->getValue();

        $text = 'Ознакомиться с договором на обучение можно по ссылке '.$lead->cf('Договор. Ссылка')->getValue().'. Код подтверждения: {код}. Для подписания договора введите его тут '.$lead->cf('Договор. Анкета код')->getValue().'. Cайт https://bangbangeducation.ru';

        try {
            $result = $authCode->generateCode(
                $phone,
                env('TARGET_SENDER'),
                4,
                $text,//setting->text
            );

            $values = TargetSMS::parsingResponse($result);

            $code   = $values[1]['attributes']['CODE'];
            $idSms  = $values[1]['attributes']['ID_SMS'];
            $status = $values[1]['attributes']['STATUS'];

            Log::info(__METHOD__, [
                'code' => $code,
                'id_sms' => $idSms,
                'status' => $status,
            ]);

            $account->doc()->create([
                'id_sms'  => $idSms,
                'status'  => $status,
                'phone'   => $phone,
                'lead_id' => $lead->id,
                'contact_id' => $contact->id,
                'send_code'  => $code,
            ]);

            Notes::addOne($lead, $text);

            $lead->status_id = 59740474; //код отправлен //setting->
            $lead->cf('Договор. Код')->setValue($code);
            $lead->save();

        } catch (\Throwable $e) {

            Log::error(__METHOD__.' '.$e->getMessage().' '.$e->getFile().' '.$e->getFile());

            Notes::addOne($lead, 'При отправке или обработке смс возникла ошибка');
        }
    }

    //update new info to doc (lead)
    public function info(Request $request)
    {

    }

    //check sms code from client

    /**
     * @throws Exception
     */
    public function check(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $account = Account::query()->first();

        $amoApi = (new Client($account))->init();

        $lead = $amoApi->service->leads()->find($request->toArray()['leads']['status'][0]['id']);

        $doc = $account->doc()
            ->where('lead_id', $lead->id)
            ->first();

        $doc->get_code = $lead->cf('Код подтверждения')->getValue();
        $doc->is_agreement = $doc->get_code == $doc->send_code;
        $doc->save();

        if ($doc->is_agreement) {

            $lead->status_id = 142;
            $lead->save();

            Notes::addOne($lead, 'Коды подтверждения совпадают : '.$doc->send_code.' > '.$doc->get_code);
        } else
            Notes::addOne($lead, 'Коды подтверждения не совпадают : '.$doc->send_code.' > '.$doc->get_code);
    }
}
