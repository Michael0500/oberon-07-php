<?php


namespace oberon07;


class Texts
{
    public static function Read(Reader $R): string
    {
        return $R->read();
    }

    public static function Pos(Reader $R)
    {
        return $R->pos();
    }

    public static function WriteLn($W)
    {
        echo PHP_EOL;
    }

    public static function WriteInt($W, int $p, int $int)
    {
        echo "{$p} - {$int}";
    }

    public static function Write($W, string $string)
    {
        echo $string;
    }

    public static function OpenReader(string $T, int $pos): Reader
    {
        return new Reader($T, $pos);
    }

    public static function OpenWriter($W)
    {
        self::$writer = $W;
    }

    public static function tokenName(int $token): string
    {
        switch ($token) {
             case 0: return 'null';
             case 1: return 'times';
             case 2: return 'rdiv';
             case 3: return 'div';
             case 4: return 'mod';
             case 5: return 'and';
             case 6: return 'plus';
             case 7: return 'minus';
             case 8: return 'or';
             case 9: return 'eql';
             case 10: return 'neq';
             case 11: return 'lss';
             case 12: return 'leq';
             case 13: return 'gtr';
             case 14: return 'geq';
             case 15: return 'in';
             case 16: return 'is';
             case 17: return 'arrow';
             case 18: return 'period';
             case 20: return 'char';
             case 21: return 'int';
             case 22: return 'real';
             case 23: return 'false';
             case 24: return 'true';
             case 25: return 'nil';
             case 26: return 'string';
             case 27: return 'not';
             case 28: return 'lparen';
             case 29: return 'lbrak';
             case 30: return 'lbrace';
             case 31: return 'ident';
             case 32: return 'if';
             case 34: return 'while';
             case 35: return 'repeat';
             case 36: return 'case';
             case 37: return 'for';
             case 40: return 'comma';
             case 41: return 'colon';
             case 42: return 'becomes';
             case 43: return 'upto';
             case 44: return 'rparen';
             case 45: return 'rbrak';
             case 46: return 'rbrace';
             case 47: return 'then';
             case 48: return 'of';
             case 49: return 'do';
             case 50: return 'to';
             case 51: return 'by';
             case 52: return 'semicolon';
             case 53: return 'end';
             case 54: return 'bar';
             case 55: return 'else';
             case 56: return 'elsif';
             case 57: return 'until';
             case 58: return 'return';
             case 60: return 'array';
             case 61: return 'record';
             case 62: return 'pointer';
             case 63: return 'const';
             case 64: return 'type';
             case 65: return 'var';
             case 66: return 'procedure';
             case 67: return 'begin';
             case 68: return 'import';
             case 69: return 'module';
             case 70: return 'eot';
            default: new \RuntimeException('Error token!');
        }
    }
}
