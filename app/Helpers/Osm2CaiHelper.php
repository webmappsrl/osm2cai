<?php

namespace App\Helpers;

class Osm2CaiHelper
{
    /**
     * It returns RGB string for SAL color according to the following rules
     * SAL <= 0.2 -> #f1eef6
     * 0.2 < SAL <= 0.4 -> #bdc9e1
     * 0.4 < SAL <= 0.6 -> #74a9cf
     * 0.6 < SAL <= 0.8 -> #2b8cbe
     * 0.8 < SAL  -> #045a8d
     *
     * @param float $sal
     * @return string
     */
    public static function getSalColor(float $sal): string
    {
        $color = '';
        if ($sal <= 0.2) {
            $color = '#f1eef6';
        } else if ($sal <= 0.4) {
            $color = '#bdc9e1';
        } else if ($sal <= 0.6) {
            $color = '#74a9cf';
        } else if ($sal <= 0.8) {
            $color = '#2b8cbe';
        } else {
            $color = '#045a8d';
        }
        return $color;
    }

    /**
     * 0 969696
     * 1 F7CA16
     * 2 F7A117
     * 3 F36E45
     * 4 47AC34
     *
     * @param $sda
     * @return string
     */
    public static function getSdaColor($sda): string
    {
        $color = '';
        switch ($sda) {
            case '0':
                $color = '#969696';
                break;
            case '1':
                $color = '#F7CA16';
                break;
            case '2':
                $color = '#F7A117';
                break;
            case '3':
                $color = '#F36E45';
                break;
            case '4':
                $color = '#47AC34';
                break;
        }
        return $color;
    }
}
