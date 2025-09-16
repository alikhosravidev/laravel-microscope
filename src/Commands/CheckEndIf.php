<?php

namespace Imanghafoori\LaravelMicroscope\Commands;

use Illuminate\Console\Command;
use Imanghafoori\LaravelMicroscope\Checks\CheckRubySyntax;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\ErrorReporters\Psr4ReportPrinter;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Reporters\Psr4Report;
use Imanghafoori\LaravelMicroscope\Iterators\DTO\CheckCollection;
use Imanghafoori\LaravelMicroscope\Iterators\ForAutoloadedClassMaps;
use Imanghafoori\LaravelMicroscope\Iterators\ForAutoloadedFiles;
use Imanghafoori\LaravelMicroscope\Iterators\ForAutoloadedPsr4Classes;
use Imanghafoori\LaravelMicroscope\PathFilterDTO;
use JetBrains\PhpStorm\ExpectedValues;

class CheckEndIf extends Command
{
    protected $signature = 'check:endif
    {--f|file=}
    {--d|folder=}
    {--t|test : backup the changed files}
    {--F|except-file= : Comma seperated patterns for file names to exclude}
    {--D|except-folder= : Comma seperated patterns for folder names to exclude}
    ';

    protected $description = 'replaces ruby like syntax of php (endif) with curly brackets.';

    #[ExpectedValues(values: [0, 1])]
    public function handle(ErrorPrinter $errorPrinter)
    {
        if (! $this->startWarning()) {
            return null;
        }

        $errorPrinter->printer = $this->output;

        $pathDTO = PathFilterDTO::makeFromOption($this);

        [$psr4Stats, $classMapStats, $filesStats] = self::applyRubySyntaxCheck($pathDTO);

        $lines = Psr4Report::formatAutoloads($psr4Stats, $classMapStats, $filesStats);
        Psr4ReportPrinter::printAll($lines, $this->getOutput());

        return ErrorPrinter::singleton()->hasErrors() ? 1 : 0;
    }

    private function startWarning()
    {
        $this->info('Checking for endif\'s...');
        $this->warn('This command is going to make changes to your files!');

        return $this->output->confirm('Do you have committed everything in git?');
    }

    public static function applyRubySyntaxCheck($pathDTO)
    {
        $check = CheckCollection::make([CheckRubySyntax::class]);
        $psr4stats = ForAutoloadedPsr4Classes::check($check, [], $pathDTO);
        $classMapStats = ForAutoloadedClassMaps::check(base_path(), $check, [], $pathDTO);
        $autoloadedFilesStats = ForAutoloadedFiles::check(base_path(), $check, [], $pathDTO);

        return [$psr4stats, $classMapStats, $autoloadedFilesStats];
    }
}
