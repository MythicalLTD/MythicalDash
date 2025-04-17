<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 */

namespace MythicalDash\Hooks\MythicalSystems\Utils;

class NumberFormatter
{
    private static $numberFormat;

    public static function format($value)
    {
        return $value < 1000 ? number_format($value, 1) : self::formatLarge($value, 0);
    }

    private static function getNumberFormat()
    {
        if (self::$numberFormat === null) {
            self::$numberFormat = explode(';', 'k;M;B;T;Q;QQ;S;SS;OC;N;D;UN;DD;TR;QT;QN;SD;SPD;OD;ND;VG;UVG;DVG;TVG;QTV;QNV;SEV;SPV;OVG;NVG;TG');
        }

        return self::$numberFormat;
    }

    private static function formatLarge($n, $iteration)
    {
        $f = $n / 1000.0;

        return $f < 1000 || $iteration >= count(self::getNumberFormat()) - 1 ?
            number_format($f, 1) . self::getNumberFormat()[$iteration] : self::formatLarge($f, $iteration + 1);
    }
}
