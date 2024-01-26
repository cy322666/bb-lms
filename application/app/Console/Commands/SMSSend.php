<?php

namespace App\Console\Commands;

use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\SmsHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SMSSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sms-send {account} {doc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $account = $this->argument('account');

            $doc = $this->argument('doc');

            $setting = $account->docSetting;

            $smsClient = SmsHelper::matchClient($account);

            $amoApi = (new Client($account))->init();

            $lead = $amoApi->service->leads()->find($doc->lead_id);

            $contact = $lead->contact;

            $phone = $contact->cf('Телефон')->getValue();

            $text = SmsHelper::getText($account->subdomain, $lead);

            $code = SmsHelper::generateCode();

            $response = SmsHelper::send($account->subdomain, $smsClient, $phone, $text);

            dd($response);

            Notes::addOne($lead, $text);

            $lead->status_id = $setting->status_id_confirm;//59740474; //код отправлен //
            $lead->cf('Договор. Код')->setValue($code);
            $lead->save();

        } catch (\Throwable $e) {

            Log::error(__METHOD__.' '.$e->getMessage().' '.$e->getFile().' '.$e->getFile());

            Notes::addOne($lead, 'При отправке или обработке смс возникла ошибка');
        }
    }
}
