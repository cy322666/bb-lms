<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\SmsHelper;
use App\Services\TargetSMS;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DocController extends Controller
{
    /**
     * @throws Exception
     */
    public function agreement(Account $account, Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $doc = $account->doc()
            ->where('lead_id', $request->toArray()['leads']['status'][0]['id'])
            ->where('is_agreement', false)
            ->latest('id')
            ->first();

        if (!$doc) {

            $doc = $account->doc()->create([
                'lead_id' => $request->toArray()['leads']['status'][0]['id'],
                'subdomain' => $account->subdomain,
            ]);

            Artisan::call('app:sms-send', [
                'account' => $account->id,
                'doc' => $doc->id,
            ]);
        } else {

            $doc->get_code = null;
            $doc->status = 0;
            $doc->is_agreement = false;
            $doc->save();

            Artisan::call('app:sms-send', [
                'account' => $account->id,
                'doc' => $doc->id,
            ]);
        }
    }

    //update new info to doc (lead)
    public function info(Account $account, Request $request)
    {

    }

    public function generate(Account $account, Request $request)
    {

    }

    //check sms code from client

    /**
     * @throws Exception
     */
    public function check(Account $account, Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $amoApi = (new Client($account))->init();

        $lead = $amoApi->service->leads()->find($request->toArray()['leads']['status'][0]['id']);

        sleep(5);

        $doc = $account->doc()
            ->where('is_agreement', false)
            ->where('lead_id', $lead->id)
            ->latest('id')
            ->first();

        if (!$doc) {

            Notes::addOne($lead, 'Ошибка проверки кода, зовите @integrator ');

            exit;
        }

        $doc->get_code = $lead->cf('Код подтверждения')->getValue();
        $doc->is_agreement = $doc->get_code == $doc->send_code;
        $doc->save();

        if ($doc->is_agreement) {

            $lead->status_id = SmsHelper::matchStatus($account->subdomain);
            $lead->save();

            Notes::addOne($lead, 'Коды подтверждения совпадают : '.$doc->send_code.' > '.$doc->get_code);
        } else
            Notes::addOne($lead, 'Коды подтверждения не совпадают : '.$doc->send_code.' > '.$doc->get_code);
    }
}
