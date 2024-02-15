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

            $account = Account::query()->find($this->argument('account'));

            $doc = Doc::query()->find($this->argument('doc'));

            $smsClient = SmsHelper::matchClient($account);

            $amoApi = (new Client($account))->init();

            $lead = $amoApi->service->leads()->find($doc->lead_id);

            $contact = $lead->contact;

            if ($account->subdomain == 'fashionfactoryschool') {

                $arrayContactCF = $contact->toArray()['custom_fields'];

                $phones = $arrayContactCF[122752];

                foreach ($phones->values as $phoneDetail) {

                    if ($phoneDetail->enum == 282376) {

                        $phone = $phoneDetail->value;

                        break;
                    }
                }
            } else
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

            dd(__METHOD__.' '.$e->getMessage().' '.$e->getFile().' '.$e->getLine());

            if (!empty($lead))
                Notes::addOne($lead, 'При отправке или обработке смс возникла ошибка');
        }
    }
}
