<?php

namespace Debug;

// Name            FG  BG
// Black           30  40
// Red             31  41
// Green           32  42
// Yellow          33  43
// Blue            34  44
// Magenta         35  45
// Cyan            36  46
// White           37  47
// Bright Black    90  100
// Bright Red      91  101
// Bright Green    92  102
// Bright Yellow   93  103
// Bright Blue     94  104
// Bright Magenta  95  105
// Bright Cyan     96  106
// Bright White    97  107

final class CMDOutput {
    private string $output = '';

    public static function new() : self {
        
        return new Static();

    }

    public function add(string $text, ?int $fg = null, ?int $bg = null,) : self {

        $this->output .= "\x1B[" . $fg . 'm' . $text . "\033[0m";
        return $this;
    }

    public function __toString()
    {
        return $this->output;
    }
}
