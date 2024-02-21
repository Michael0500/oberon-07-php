<?php

namespace oberon07;


class ORS
{
    const IdLen = 32;
    const NKW = 34;  // nof keywords
    const maxExp = 38;
    const stringBufSize = 256;

    // lexical symbols
    const T_null = 0;
    const T_times = 1;
    const T_rdiv = 2;
    const T_div = 3;
    const T_mod = 4;
    const T_and = 5;
    const T_plus = 6;
    const T_minus = 7;
    const T_or = 8;
    const T_eql = 9;
    const T_neq = 10;
    const T_lss = 11;
    const T_leq = 12;
    const T_gtr = 13;
    const T_geq = 14;
    const T_in = 15;
    const T_is = 16;
    const T_arrow = 17;
    const T_period = 18;
    const T_char = 20;
    const T_int = 21;
    const T_real = 22;
    const T_false = 23;
    const T_true = 24;
    const T_nil = 25;
    const T_string = 26;
    const T_not = 27;
    const T_lparen = 28;
    const T_lbrak = 29;
    const T_lbrace = 30;
    const T_ident = 31;
    const T_if = 32;
    const T_while = 34;
    const T_repeat = 35;
    const T_case = 36;
    const T_for = 37;
    const T_comma = 40;
    const T_colon = 41;
    const T_becomes = 42;
    const T_upto = 43;
    const T_rparen = 44;
    const T_rbrak = 45;
    const T_rbrace = 46;
    const T_then = 47;
    const T_of = 48;
    const T_do = 49;
    const T_to = 50;
    const T_by = 51;
    const T_semicolon = 52;
    const T_end = 53;
    const T_bar = 54;
    const T_else = 55;
    const T_elsif = 56;
    const T_until = 57;
    const T_return = 58;
    const T_array = 60;
    const T_record = 61;
    const T_pointer = 62;
    const T_const = 63;
    const T_type = 64;
    const T_var = 65;
    const T_procedure = 66;
    const T_begin = 67;
    const T_import = 68;
    const T_module = 69;
    const T_eot = 70;

    public int $ival; // results of Get
    public int $slen;
    public float $rval;
    public string $id = '';  // for identifiers string[IdLen] -> string[32]
    public string $str = ''; // string[stringBufSize] -> string[256]
    public int $errcnt;

    public string $ch; // last character read
    private int $errpos;
    private Reader $R; //    R: Texts.Reader;
    private $W; //    W: Texts.Writer;
    private int $k;
    /** @var array<int>[10] */
    private array $KWX = []; // ARRAY[10] OF INTEGER;
    /** @var array<array<int, string>>[34] */
    private array $keyTab;// : ARRAY[NKW] OF RECORD sym: INTEGER; id: ARRAY[12] OF CHAR END;

    /**
     * ORS constructor.
     */
    public function __construct()
    {
        $this->k = 0;
        $this->KWX[0] = 0;
        $this->KWX[1] = 0;

        $this->EnterKW(self::T_if, "IF");
        $this->EnterKW(self::T_do, "DO");
        $this->EnterKW(self::T_of, "OF");
        $this->EnterKW(self::T_or, "OR");
        $this->EnterKW(self::T_to, "TO");
        $this->EnterKW(self::T_in, "IN");
        $this->EnterKW(self::T_is, "IS");
        $this->EnterKW(self::T_by, "BY");
        $this->KWX[2] = $this->k;
        $this->EnterKW(self::T_end, "END");
        $this->EnterKW(self::T_nil, "NIL");
        $this->EnterKW(self::T_var, "VAR");
        $this->EnterKW(self::T_div, "DIV");
        $this->EnterKW(self::T_mod, "MOD");
        $this->EnterKW(self::T_for, "FOR");
        $this->KWX[3] = $this->k;
        $this->EnterKW(self::T_else, "ELSE");
        $this->EnterKW(self::T_then, "THEN");
        $this->EnterKW(self::T_true, "TRUE");
        $this->EnterKW(self::T_type, "TYPE");
        $this->EnterKW(self::T_case, "CASE");
        $this->KWX[4] = $this->k;
        $this->EnterKW(self::T_elsif, "ELSIF");
        $this->EnterKW(self::T_false, "FALSE");
        $this->EnterKW(self::T_array, "ARRAY");
        $this->EnterKW(self::T_begin, "BEGIN");
        $this->EnterKW(self::T_const, "CONST");
        $this->EnterKW(self::T_until, "UNTIL");
        $this->EnterKW(self::T_while, "WHILE");
        $this->KWX[5] = $this->k;
        $this->EnterKW(self::T_record, "RECORD");
        $this->EnterKW(self::T_repeat, "REPEAT");
        $this->EnterKW(self::T_return, "RETURN");
        $this->EnterKW(self::T_import, "IMPORT");
        $this->EnterKW(self::T_module, "MODULE");
        $this->KWX[6] = $this->k;
        $this->EnterKW(self::T_pointer, "POINTER");
        $this->KWX[7] = $this->k;
        $this->KWX[8] = $this->k;
        $this->EnterKW(self::T_procedure, "PROCEDURE");
        $this->KWX[9] = $this->k;
    }

    private function EnterKW(int $sym, string $name)
    {
        $this->keyTab[$this->k]['id'] = $name;
        $this->keyTab[$this->k]['sym'] = $sym;
        $this->k++;
    }

    public function Init(string $T, int $pos)
    {
        $this->errpos = $pos;
        $this->errcnt = 0;
        $this->R = Texts::OpenReader($T, $pos);
        $this->ch = Texts::Read($this->R);
    }

    public function CopyId(int &$ident)
    {
        $ident = $this->id;
    }

    public function Get(int &$sym)
    {
        do {
            while (!$this->R->eot() && ($this->ch <= " ")) {
                $this->ch = Texts::Read($this->R);
            }
            if ($this->R->eot()) {
                $sym = self::T_eot;
            } elseif ($this->ch < "A") {
                if ($this->ch < "0") {
                    if ($this->ch === "\"") {
                        $this->String();
                        $sym = self::T_string;
                    } elseif ($this->ch === "#") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_neq;
                    } elseif ($this->ch === "$") {
                        $this->HexString();
                        $sym = self::T_string;
                    } elseif ($this->ch === "&") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_and;
                    } elseif ($this->ch === "(") {
                        $this->ch = Texts::Read($this->R);
                        if ($this->ch === "*") {
                            $sym = self::T_null;
                            $this->comment();
                        } else {
                            $sym = self::T_lparen;
                        }
                    } elseif ($this->ch === ")") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_rparen;
                    } elseif ($this->ch === "*") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_times;
                    } elseif ($this->ch === "+") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_plus;
                    } elseif ($this->ch === ",") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_comma;
                    } elseif ($this->ch === "-") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_minus;
                    } elseif ($this->ch === ".") {
                        $this->ch = Texts::Read($this->R);
                        if ($this->ch === ".") {
                            $this->ch = Texts::Read($this->R);
                            $sym = self::T_upto;
                        } else {
                            $sym = self::T_period;
                        }
                    } elseif ($this->ch === "/") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_rdiv;
                    } else {
                        /* ! % ' */
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_null;
                    }
                } elseif ($this->ch < ":") {
                    $this->Number($sym);
                } elseif ($this->ch === ":") {
                    $this->ch = Texts::Read($this->R);
                    if ($this->ch === "=") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_becomes;
                    } else {
                        $sym = self::T_colon;
                    }
                } elseif ($this->ch === ";") {
                    $this->ch = Texts::Read($this->R);
                    $sym = self::T_semicolon;
                } elseif ($this->ch === "<") {
                    $this->ch = Texts::Read($this->R);
                    if ($this->ch === "=") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_leq;
                    } else {
                        $sym = self::T_lss;
                    }
                } elseif ($this->ch === "=") {
                    $this->ch = Texts::Read($this->R);
                    $sym = self::T_eql;
                } elseif ($this->ch === ">") {
                    $this->ch = Texts::Read($this->R);
                    if ($this->ch === "=") {
                        $this->ch = Texts::Read($this->R);
                        $sym = self::T_geq;
                    } else {
                        $sym = self::T_gtr;
                    }
                } else { /* ? @ */
                    $this->ch = Texts::Read($this->R);
                    $sym = self::T_null;
                }
            } elseif ($this->ch < "[") {
                $this->Identifier($sym);
            } elseif ($this->ch < "a") {
                if ($this->ch === "[") {
                    $sym = self::T_lbrak;
                } elseif ($this->ch === "]") {
                    $sym = self::T_rbrak;
                } elseif ($this->ch === "^") {
                    $sym = self::T_arrow;
                } else { /* _ ` */
                    $sym = self::T_null;
                }
                $this->ch = Texts::Read($this->R);
            } elseif ($this->ch < "{") {
                $this->Identifier($sym);
            } else {
                if ($this->ch === "{") {
                    $sym = self::T_lbrace;
                } elseif ($this->ch === "}") {
                    $sym = self::T_rbrace;
                } elseif ($this->ch === "|") {
                    $sym = self::T_bar;
                } elseif ($this->ch === "~") {
                    $sym = self::T_not;
                } elseif (ord($this->ch) === 0x7F) {
                    $sym = self::T_upto;
                } else {
                    $sym = self::T_null;
                }
                $this->ch = Texts::Read($this->R);
            }
        } while ($sym === null);
    }

    private function String()
    {
        $i = 0;
        $this->ch = Texts::Read($this->R);
        while (!$this->R->eot() && ($this->ch !== "\"")) {
            if ($this->ch >= " ") {
                if ($i < self::stringBufSize - 1) {
                    $this->str .= $this->ch;
                    $i++;
                } else {
                    $this->Mark("string too long");
                }
            }
            $this->ch = Texts::Read($this->R);
        }
        $this->str .= "";
        $i++;
        $this->ch = Texts::Read($this->R);
        $this->slen = $i;
    }

    public function Mark(string $msg)
    {
        $p = $this->Pos();
        if (($p > $this->errpos) && ($this->errcnt < 25)) {
            echo "Warning! pos: {$p} - {$msg}\n";
        }
        $this->errcnt++;
        $this->errpos = $p + 4;
    }

    public function Pos(): int
    {
        return Texts::Pos($this->R) - 1;
    }

    private function HexString()
    {
        $i = 0;
        $this->ch = Texts::Read($this->R);
        while (!$this->R->eot() && ($this->ch !== "$")) {
            while (!$this->R->eot() && ($this->ch <= " ")) {
                $this->ch = Texts::Read($this->R);
            }

            if (("0" <= $this->ch) && ($this->ch <= "9")) {
                $m = ord($this->ch) - 0x30;
            } elseif (("A" <= $this->ch) && ($this->ch <= "F")) {
                $m = ord($this->ch) - 0x37;
            } else {
                $m = 0;
                $this->Mark("hexdig expected");
            }

            $this->ch = Texts::Read($this->R);
            if (("0" <= $this->ch) && ($this->ch <= "9")) {
                $n = ord($this->ch) - 0x30;
            } elseif (("A" <= $this->ch) && ($this->ch <= "F")) {
                $n = ord($this->ch) - 0x37;
            } else {
                $n = 0;
                $this->Mark("hexdig expected");
            }

            if ($i < self::stringBufSize) {
                $this->str .= chr($m * 0x10 + $n);
                $i++;
            } else {
                $this->Mark("string too long");
            }
            $this->ch = Texts::Read($this->R);
        }
        $this->ch = Texts::Read($this->R);
        $this->slen = $i;  // no 0x0 appended!
    }

    private function comment()
    {
        $this->ch = Texts::Read($this->R);
        do {
            while (!$this->R->eot() && ($this->ch !== "*")) {

                if ($this->ch === "(") {
                    $this->ch = Texts::Read($this->R);
                    if ($this->ch === "*") {
                        $this->comment();
                    }
                } else {
                    $this->ch = Texts::Read($this->R);
                }
            }

            while ($this->ch === "*") {
                $this->ch = Texts::Read($this->R);
            }

        } while (($this->ch !== ")") || !$this->R->eot());

        if (!$this->R->eot()) {
            $this->ch = Texts::Read($this->R);
        } else {
            $this->Mark("unterminated comment");
        }
    }

    private function Number(int &$sym)
    {
        $this->ival = 0;
        $i = 0;
        $n = 0;
        $k = 0;
        $MAX = 2147483647;
        $d = [];

        do {
            if ($n < 16) {
                $d[$n] = ord($this->ch) - 0x30;
                $n++;
            } else {
                $this->Mark("too many digits");
                $n = 0;
            }
            $this->ch = Texts::Read($this->R);
        } while (!(($this->ch < "0") || ($this->ch > "9") && ($this->ch < "A") || ($this->ch > "F")));

        if (($this->ch === "H") || ($this->ch === "R") || ($this->ch === "X")) {  // hex
            do {
                $h = $d[$i];
                if ($h >= 10) {
                    $h = $h - 7;
                }
                $k = $k * 0x10 + $h;
                $i++;
            } while ($i !== $n);

            if ($this->ch === "X") {
                $sym = self::T_char;
                if ($k < 0x100) {
                    $this->ival = $k;
                } else {
                    $this->Mark("illegal value");
                    $this->ival = 0;
                }
            } elseif ($this->ch = "R") {
                $sym = self::T_real;
                $this->rval = floatval($k);
            } else {
                $sym = self::T_int;
                $this->ival = $k;
            }
            $this->ch = Texts::Read($this->R);
        } elseif ($this->ch === ".") {
            $this->ch = Texts::Read($this->R);
            if ($this->ch === ".") { // double dot
                $this->ch = 0x7F;  // decimal integer
                do {
                    if ($d[$i] < 10) {
                        if ($k <= ($MAX - $d[$i]) / 10) {
                            $k = $k * 10 + $d[$i];
                        } else {
                            $this->Mark("too large");
                            $k = 0;
                        }
                    } else {
                        $this->Mark("bad integer");
                    }
                    $i++;
                } while ($i !== $n);
                $sym = self::T_int;
                $this->ival = $k;
            } else { // real number
                $x = 0.0;
                $e = 0;
                do {  // integer part
                    $x = $x * 10.0 + $d[$i];
                    $i++;
                } while ($i !== $n);

                while (($this->ch >= "0") && ($this->ch <= "9")) { // fraction
                    $x = $x * 10.0 + ord($this->ch) - 0x30;
                    $e--;
                    $this->ch = Texts::Read($this->R);
                }

                if (($this->ch = "E") || ($this->ch = "D")) { // scale factor
                    $this->ch = Texts::Read($this->R);
                    $s = 0;

                    if ($this->ch === "-") {
                        $negE = true;
                        $this->ch = Texts::Read($this->R);
                    } else {
                        $negE = false;
                        if ($this->ch === "+") {
                            $this->ch = Texts::Read($this->R);
                        }
                    }

                    if (($this->ch >= "0") && ($this->ch <= "9")) {
                        do {
                            $s = $s * 10 + ord($this->ch) - 0x30;
                            $this->ch = Texts::Read($this->R);
                        } while (!(($this->ch < "0") || ($this->ch > "9")));
                        if ($negE) {
                            $e = $e - $s;
                        } else {
                            $e = $e + $s;
                        }
                    } else {
                        $this->Mark("digit?");
                    }
                }

                if ($e < 0) {
                    if ($e >= -self::maxExp) {
                        $x = $x / $this->Ten(-$e);
                    } else {
                        $x = 0.0;
                    }
                } elseif ($e > 0) {
                    if ($e <= self::maxExp) {
                        $x = $this->Ten($e) * $x;
                    } else {
                        $x = 0.0;
                        $this->Mark("too large");
                    }
                }

                $sym = self::T_real;
                $this->rval = $x;
            }
        } else {  // decimal integer
            do {
                if ($d[$i] < 10) {
                    if ($k <= ($MAX - $d[$i]) / 10) {
                        $k = $k * 10 + $d[$i];
                    } else {
                        $this->Mark("too large");
                        $k = 0;
                    }
                } else {
                    $this->Mark("bad integer");
                }
                $i++;
            } while ($i !== $n);
            $sym = self::T_int;
            $this->ival = $k;
        }
    }

    private function Ten(int $e): float
    {
        $x = 1.0;
        $t = 10.0;
        while ($e > 0) {
            if ($e % 2 !== 0) {
                $x = $t * $x;
            }
            $t = $t * $t;
            $e = intval($e / 2);
        }

        return $x;
    }

    private function Identifier(int &$sym)
    {
        $this->id = '';
        $i = 0;
        do {
            if ($i < self::IdLen - 1) {
                $this->id .= $this->ch;
                $i++;
            }
            $this->ch = Texts::Read($this->R);
        } while (!(($this->ch < "0") or ($this->ch > "9") && ($this->ch < "A") || ($this->ch > "Z") && ($this->ch < "a") || ($this->ch > "z")));

        $this->id .= "\0";
        if ($i < 10) {
            $k = $this->KWX[$i - 1]; // search for keyword
            while (($this->id != $this->keyTab[$k]['id']) && ($k < $this->KWX[$i])) {
                $k++;
            }

            if ($k < $this->KWX[$i]) {
                $sym = $this->keyTab[$k]['sym'];
            } else {
                $sym = self::T_ident;
            }
        } else {
            $sym = self::T_ident;
        }
    }
}
