<?php


namespace oberon07;


class Reader
{
    private string $text;
    private int $length;
    private int $pos;

    /**
     * Reader constructor.
     * @param string $text
     * @param int $pos
     */
    public function __construct(string $text, int $pos)
    {
        $this->pos = $pos;
        $this->text = $text;
        $this->length = strlen($text);
    }

    public function eot()
    {
        return $this->pos >= $this->length;
    }

    public function pos()
    {
        return $this->pos;
    }

    public function read(): string
    {
        if (!$this->eot()) {
            return $this->text[$this->pos++];
        } else {
            return "\0";
        }
    }
}
