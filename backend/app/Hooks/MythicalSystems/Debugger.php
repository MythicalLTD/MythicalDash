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

namespace MythicalDash\Hooks\MythicalSystems;

class Debugger
{
    /**
     * Display the information.
     *
     * @param mixed $input The input to display the info about!
     * @param bool $collapse This is always false by default
     */
    public static function display_info($input, $collapse = false): void
    {
        try {
            $recursive = function ($data, $level = 0) use (&$recursive, $collapse) {
                global $argv;

                $isTerminal = isset($argv);

                if (!$isTerminal && $level == 0 && !defined('DUMP_DEBUG_SCRIPT')) {
                    define('DUMP_DEBUG_SCRIPT', true);

                    echo '<script language="Javascript">function toggleDisplay(id) {';
                    echo 'var state = document.getElementById("container"+id).style.display;';
                    echo 'document.getElementById("container"+id).style.display = state == "inline" ? "none" : "inline";';
                    echo 'document.getElementById("plus"+id).style.display = state == "inline" ? "inline" : "none";';
                    echo '}</script>' . "\n";
                }

                $type = !is_string($data) && is_callable($data) ? 'Callable' : ucfirst(gettype($data));
                $type_data = null;
                $type_color = null;
                $type_length = null;

                switch ($type) {
                    case 'String':
                        $type_color = 'green';
                        $type_length = strlen($data);
                        $type_data = '"' . htmlentities($data) . '"';
                        break;

                    case 'Double':
                    case 'Float':
                        $type = 'Float';
                        $type_color = '#0099c5';
                        $type_length = strlen($data);
                        $type_data = htmlentities($data);
                        break;

                    case 'Integer':
                        $type_color = 'red';
                        $type_length = strlen($data);
                        $type_data = htmlentities($data);
                        break;

                    case 'Boolean':
                        $type_color = '#92008d';
                        $type_length = strlen($data);
                        $type_data = $data ? 'TRUE' : 'FALSE';
                        break;

                    case 'NULL':
                        $type_length = 0;
                        break;

                    case 'Array':
                        $type_length = count($data);
                }

                if (in_array($type, ['Object', 'Array'])) {
                    $notEmpty = false;

                    foreach ($data as $key => $value) {
                        if (!$notEmpty) {
                            $notEmpty = true;

                            if ($isTerminal) {
                                echo $type . ($type_length !== null ? '(' . $type_length . ')' : '') . "\n";
                            } else {
                                $id = substr(md5(mt_rand() . ':' . $key . ':' . $level), 0, 8);

                                echo "<a href=\"javascript:toggleDisplay('" . $id . "');\" style=\"text-decoration:none\">";
                                echo "<span style='color:#666666'>" . $type . ($type_length !== null ? '(' . $type_length . ')' : '') . '</span>';
                                echo '</a>';
                                echo '<span id="plus' . $id . '" style="display: ' . ($collapse ? 'inline' : 'none') . ';">&nbsp;&#10549;</span>';
                                echo '<div id="container' . $id . '" style="display: ' . ($collapse ? '' : 'inline') . ';">';
                                echo '<br />';
                            }

                            for ($i = 0; $i <= $level; ++$i) {
                                echo $isTerminal ? '|    ' : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                            }

                            echo $isTerminal ? "\n" : '<br />';
                        }

                        for ($i = 0; $i <= $level; ++$i) {
                            echo $isTerminal ? '|    ' : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        }

                        echo $isTerminal ? '[' . $key . '] => ' : "<span style='color:black'>[" . $key . ']&nbsp;=>&nbsp;</span>';

                        call_user_func($recursive, $value, $level + 1);
                    }

                    if ($notEmpty) {
                        for ($i = 0; $i <= $level; ++$i) {
                            echo $isTerminal ? '|    ' : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        }

                        if (!$isTerminal) {
                            echo '</div>';
                        }
                    } else {
                        echo $isTerminal ?
                            $type . ($type_length !== null ? '(' . $type_length . ')' : '') . '  ' :
                            "<span style='color:#666666'>" . $type . ($type_length !== null ? '(' . $type_length . ')' : '') . '</span>&nbsp;&nbsp;';
                    }
                } else {
                    echo $isTerminal ?
                        $type . ($type_length !== null ? '(' . $type_length . ')' : '') . '  ' :
                        "<span style='color:#666666'>" . $type . ($type_length !== null ? '(' . $type_length . ')' : '') . '</span>&nbsp;&nbsp;';

                    if ($type_data != null) {
                        echo $isTerminal ? $type_data : "<span style='color:" . $type_color . "'>" . $type_data . '</span>';
                    }
                }

                echo $isTerminal ? "\n" : '<br />';
            };

            call_user_func($recursive, $input);
            exit;
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Show all errors to a page!
     */
    public static function ShowAllErrors(): void
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    /**
     * Hide all errors from a page!
     */
    public static function HideAllErrors(): void
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
    }
}
