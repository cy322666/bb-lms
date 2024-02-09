<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Doc;
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

            $smsClient = SmsHelper::matchClient($account);

            $amoApi = (new Client($account))->init();

            $lead = $amoApi->service->leads()->find($doc->lead_id);

            $contact = $lead->contact;

            $phone = $contact->cf('Телефон')->getValue();

            $code = SmsHelper::generateCode();

            $doc->send_code = $code;
            $doc->phone = $phone;
            $doc->email = $contact->cf('Email')->getValue();
            $doc->contact_id = $contact->id;
            $doc->save();

            $text = SmsHelper::getText($account->subdomain, $lead, $code);

            $response = SmsHelper::send($account->subdomain, $smsClient, $phone, $text);

            Notes::addOne($lead, $text);

            $doc->status = $response['status'];
            $doc->save();

            $lead->cf('Договор. Код')->setValue($code);
            $lead->save();

        } catch (\Throwable $e) {

            Log::error(__METHOD__.' '.$e->getMessage().' '.$e->getFile().' '.$e->getLine());

            if (!empty($lead))
                Notes::addOne($lead, 'При отправке или обработке смс возникла ошибка');
        }
    }
}
