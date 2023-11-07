<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;

class GenerateDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-doc';

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
            //https://app.clevercrm.pro/api/yandex/redirect
            $_doc = new TemplateProcessor(storage_path('docs/test.docx'));

            $_doc->setValue('date', Carbon::now()->format('Y-m-d'));

            $_doc->saveAs(storage_path('docs/gen-1'.".docx"));

        } catch (CopyFileException $e) {
        } catch (CreateTemporaryFileException $e) {


        }
    }
}
