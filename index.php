<?php

require_once 'Reader.php';
require_once 'Texts.php';
require_once 'ORS.php';

$text = <<<OBRN
MODULE hello;

IMPORT Console;
VAR
    i, j: LONGINT;
BEGIN
    Console.String("Hello World!");
    Console.Ln
    i:= 167;
    j:= i + 3;
END hello.
OBRN;

$token = 0;

$scanner = new \oberon07\ORS();
$scanner->Init($text, 0);
do {
    $scanner->Get($token);
    echo sprintf("pos: %d\ttoken: %s", $scanner->Pos(), \oberon07\Texts::tokenName($token));
    if ($token == \oberon07\ORS::T_ident)
        echo sprintf("\tident: %s", $scanner->id);
    if ($token == \oberon07\ORS::T_string)
        echo sprintf("\tstr: %s", $scanner->str);
    if ($token == \oberon07\ORS::T_int)
        echo sprintf("\tval: %d", $scanner->ival);

    echo PHP_EOL;

} while ($token != \oberon07\ORS::T_eot);
