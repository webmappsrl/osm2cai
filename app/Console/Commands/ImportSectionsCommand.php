<?php

namespace App\Console\Commands;

use App\Imports\SectionsImport;
use Illuminate\Console\Command;
use App\Imports\SubSectionsImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportSectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:sections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =
    'Import sections and subsections from XLS files';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sectionFilePath = storage_path('imports/Sezioni.xlsx');
        $subsectionFilePath = storage_path('imports/SottoSezioni.xlsx');

        // Import sections
        $this->info('Importing sections...');
        Excel::import(new SectionsImport, $sectionFilePath);
        $this->info('Sections imported successfully.');

        // Import subsections
        $this->info('Importing subsections...');
        Excel::import(new SubSectionsImport, $subsectionFilePath);
        $this->info('Subsections imported successfully.');

        $this->info('Sections associated with regions successfully.');
    }
}
