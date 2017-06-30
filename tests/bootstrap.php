<?php

if (!file_exists(__DIR__ . "/../vendor/autoload.php"))
{
    die(
        "\n[ERROR] You need to run composer before running the test suite.\n".
        "To do so run the following commands:\n".
        "    curl -s http://getcomposer.org/installer | php\n".
        "    php composer.phar install\n\n"
    );
}

require_once __DIR__ . '/../vendor/autoload.php';

// These may be necessary once we move to PhpUnit 6
//
//$phpunitAliases = [
//    '\PHPUnit\Framework\Test' => '\PHPUnit_Framework_Test',
//    '\PHPUnit\Framework\TestListener' => '\PHPUnit_Framework_TestListener',
//    '\PHPUnit\Framework\Warning' => '\PHPUnit_Framework_Warning',
//    '\PHPUnit\Framework\AssertionFailedError' => '\PHPUnit_Framework_AssertionFailedError',
//    '\PHPUnit\Framework\TestSuite' => '\PHPUnit_Framework_TestSuite',
//];
//
//foreach ($phpunitAliases as $namespaced => $alias) {
//    if (!class_exists($alias)) {
//        class_alias($namespaced, $alias);
//    }
//}
