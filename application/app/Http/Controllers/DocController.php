<?php

namespace App\Http\Controllers;

use App\Models\Core\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\SmsHelper;
use App\Services\TargetSMS;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocController extends Controller
{
    /**
     * @throws Exception
     */
    public function agreement(Account $account, Request $request)
    {
        try {
            Log::info(__METHOD__, $request->toArray());

            $setting = $account->docSetting;

            $smsClient = SmsHelper::matchClient($account);

            $amoApi = (new Client($account))->init();

            $lead = $amoApi->service->leads()->find($request->toArray()['leads']['status'][0]['id']);

            $contact = $lead->contact;

            $phone = $contact->cf('Телефон')->getValue();

            $text = SmsHelper::getText($account->subdomain, $lead);

            $code = SmsHelper::generateCode();

            $response = SmsHelper::send($account->subdomain, $smsClient, $phone, $text);

            $account->doc()->create([
                'id_sms'  => $idSms,
                'status'  => $status,
                'phone'   => $phone,
                'lead_id' => $lead->id,
                'contact_id' => $contact->id,
                'send_code'  => $code,
            ]);

            Notes::addOne($lead, $text);

            $lead->status_id = $setting->status_id_confirm;//59740474; //код отправлен //
            $lead->cf('Договор. Код')->setValue($code);
            $lead->save();

        } catch (\Throwable $e) {

            Log::error(__METHOD__.' '.$e->getMessage().' '.$e->getFile().' '.$e->getFile());

            Notes::addOne($lead, 'При отправке или обработке смс возникла ошибка');
        }
    }

    //update new info to doc (lead)
    public function info(Account $account, Request $request)
    {

    }

    //check sms code from client

    /**
     * @throws Exception
     */
    public function check(Account $account, Request $request)
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
