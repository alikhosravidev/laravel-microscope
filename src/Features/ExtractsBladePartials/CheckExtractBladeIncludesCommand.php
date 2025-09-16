<?php

namespace Imanghafoori\LaravelMicroscope\Features\ExtractsBladePartials;

use Illuminate\Console\Command;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Reporters\BladeReport;
use Imanghafoori\LaravelMicroscope\Iterators\DTO\CheckCollection;
use Imanghafoori\LaravelMicroscope\Iterators\ForBladeFiles;
use Imanghafoori\LaravelMicroscope\Traits\LogsErrors;

class CheckExtractBladeIncludesCommand extends Command
{
    use LogsErrors;

    protected $signature = 'check:extract_blades';

    protected $description = 'Checks to extract blade partials';

    public function handle(ErrorPrinter $errorPrinter)
    {
        if (! $this->startWarning()) {
            return;
        }

        event('microscope.start.command');

        $errorPrinter->printer = $this->output;

        $bladeStats = ForBladeFiles::check(
            CheckCollection::make([ExtractBladePartial::class])
        );

        $this->getOutput()->writeln(PHP_EOL.BladeReport::getBladeStats($bladeStats));

        $this->info('Blade files extracted.');
    }

    private function startWarning()
    {
        $this->info('Checking to extract blade partials...');
        $this->warn('This command is going to make changes to your files!');

        return $this->output->confirm('Do you have committed everything in git?', true);
    }
}
