<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'documentchecker' => '/classes/DocumentChecker.inc.php',
                'contentparser' => '/classes/ContentParser.inc.php',
                'documentchecklist' => '/classes/DocumentChecklist.inc.php',
                'spatie\\pdftotext\\exceptions\\couldnotextracttext' => '/vendor/spatie/pdf-to-text/src/Exceptions/CouldNotExtractText.php',
                'spatie\\pdftotext\\exceptions\\pdfnotfound' => '/vendor/spatie/pdf-to-text/src/Exceptions/PdfNotFound.php',
                'spatie\\pdftotext\\pdf' => '/vendor/spatie/pdf-to-text/src/Pdf.php',
                'stringable' => '/vendor/symfony/polyfill-php80/Resources/stubs/Stringable.php',
                'symfony\\component\\process\\exception\\exceptioninterface' => '/vendor/symfony/process/Exception/ExceptionInterface.php',
                'symfony\\component\\process\\exception\\invalidargumentexception' => '/vendor/symfony/process/Exception/InvalidArgumentException.php',
                'symfony\\component\\process\\exception\\logicexception' => '/vendor/symfony/process/Exception/LogicException.php',
                'symfony\\component\\process\\exception\\processfailedexception' => '/vendor/symfony/process/Exception/ProcessFailedException.php',
                'symfony\\component\\process\\exception\\processsignaledexception' => '/vendor/symfony/process/Exception/ProcessSignaledException.php',
                'symfony\\component\\process\\exception\\processtimedoutexception' => '/vendor/symfony/process/Exception/ProcessTimedOutException.php',
                'symfony\\component\\process\\exception\\runtimeexception' => '/vendor/symfony/process/Exception/RuntimeException.php',
                'symfony\\component\\process\\executablefinder' => '/vendor/symfony/process/ExecutableFinder.php',
                'symfony\\component\\process\\inputstream' => '/vendor/symfony/process/InputStream.php',
                'symfony\\component\\process\\phpexecutablefinder' => '/vendor/symfony/process/PhpExecutableFinder.php',
                'symfony\\component\\process\\phpprocess' => '/vendor/symfony/process/PhpProcess.php',
                'symfony\\component\\process\\pipes\\abstractpipes' => '/vendor/symfony/process/Pipes/AbstractPipes.php',
                'symfony\\component\\process\\pipes\\pipesinterface' => '/vendor/symfony/process/Pipes/PipesInterface.php',
                'symfony\\component\\process\\pipes\\unixpipes' => '/vendor/symfony/process/Pipes/UnixPipes.php',
                'symfony\\component\\process\\pipes\\windowspipes' => '/vendor/symfony/process/Pipes/WindowsPipes.php',
                'symfony\\component\\process\\process' => '/vendor/symfony/process/Process.php',
                'symfony\\component\\process\\processutils' => '/vendor/symfony/process/ProcessUtils.php',
                'symfony\\polyfill\\php80\\php80' => '/vendor/symfony/polyfill-php80/Php80.php',
                'valueerror' => '/vendor/symfony/polyfill-php80/Resources/stubs/ValueError.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd
