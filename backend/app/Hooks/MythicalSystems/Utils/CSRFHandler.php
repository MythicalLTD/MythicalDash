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

/**
 * Usage:
 * 		// Load or Start a new list of tokens
 * 		$csrf_tokens = new CSRF(
 * 			<modifier for the session variable and the form input name>,
 * 			<default time before the token expire, in seconds>
 * 		);
 * 		// Generate an input for a form with a token
 * 		// Tokens on the list are binded on a group so that
 * 		// they can only be matched on that group
 * 		// You can use as a group name the form name
 * 		echo $csrf_tokens->input(<name of the group>);
 */

namespace MythicalDash\Hooks\MythicalSystems\Utils;

class CSRFHandler
{
    private $name;
    private $hashes;
    private $hashTime2Live;
    private $hashSize;
    private $inputName;

    /**
     * Initialize a CSRF instance.
     *
     * @param string $session_name Session name
     * @param string $input_name Form name
     * @param int $hashTime2Live Default seconds hash before expiration
     * @param int $hashSize Default hash size in chars
     */
    public function __construct($session_name = 'csrf-lib', $input_name = 'key-awesome', $hashTime2Live = 0, $hashSize = 64)
    {
        // Session mods
        $this->name = $session_name;
        // Form input name
        $this->inputName = $input_name;
        // Default time before expire for hashes
        $this->hashTime2Live = $hashTime2Live;
        // Default hash size
        $this->hashSize = $hashSize;
        // Load hash list
        $this->_load();
    }

    /**
     * Get the hashes of a context.
     *
     * @param string $context the group to clean
     * @param int $max_hashes max hashes to get
     *
     * @return array array of hashes as strings
     */
    public function getHashes($context = '', $max_hashes = -1)
    {
        $len = count($this->hashes);
        $hashes = [];
        // Check in the hash list
        for ($i = $len - 1; $i >= 0 && $len > 0; --$i) {
            if ($this->hashes[$i]->inContext($context)) {
                array_push($hashes, $this->hashes[$i]->get());
                --$len;
            }
        }

        return $hashes;
    }

    /**
     * Clear the hashes of a context.
     *
     * @param string $context the group to clean
     * @param int $max_hashes ignore first x hashes
     *
     * @return int number of deleted hashes
     */
    public function clearHashes($context = '', $max_hashes = 0)
    {
        $ignore = $max_hashes;
        $deleted = 0;
        // Check in the hash list
        for ($i = count($this->hashes) - 1; $i >= 0; --$i) {
            if ($this->hashes[$i]->inContext($context) && $ignore-- <= 0) {
                array_splice($this->hashes, $i, 1);
                ++$deleted;
            }
        }
        if ($deleted > 0) {
            $this->_save();
        }

        return $deleted;
    }

    /**
     * Generate an input html element.
     *
     * @param string $context Name of the form
     * @param int $time2Live Seconds before expire
     * @param int $max_hashes Clear old context hashes if more than this number
     *
     * @return int html input element code as a string
     */
    public function input($context = '', $time2Live = -1, $max_hashes = 5): string
    {
        // Generate hash
        $hash = $this->generateHash($context, $time2Live, $max_hashes);

        // Generate html input string
        return '<input type="hidden" name="' . htmlspecialchars($this->inputName) . '" id="' . htmlspecialchars($this->inputName) . '" value="' . htmlspecialchars($hash->get()) . '"/>';
    }

    /**
     * Generate a script html element with the hash variable.
     *
     * @param string $context Name of the form
     * @param string $name The name for the variable
     * @param int $time2Live Seconds before expire
     * @param int $max_hashes Clear old context hashes if more than this number
     *
     * @return int html script element code as a string
     */
    public function script($context = '', $name = '', $declaration = 'var', $time2Live = -1, $max_hashes = 5): string
    {
        // Generate hash
        $hash = $this->generateHash($context, $time2Live, $max_hashes);
        // Variable name
        if (strlen($name) === 0) {
            $name = $this->inputName;
        }

        // Generate html input string
        return '<script type="text/javascript">' . $declaration . ' ' . $name . ' = ' . json_encode($hash->get()) . ';</script>';
    }

    /**
     * Generate a javascript variable with the hash.
     *
     * @param string $context Name of the form
     * @param string $name The name for the variable
     * @param int $time2Live Seconds before expire
     * @param int $max_hashes Clear old context hashes if more than this number
     *
     * @return int html script element code as a string
     */
    public function javascript($context = '', $name = '', $declaration = 'var', $time2Live = -1, $max_hashes = 5): string
    {
        // Generate hash
        $hash = $this->generateHash($context, $time2Live, $max_hashes);
        // Variable name
        if (strlen($name) === 0) {
            $name = $this->inputName;
        }

        // Generate html input string
        return $declaration . ' ' . $name . ' = ' . json_encode($hash->get()) . ';';
    }

    /**
     * Generate a string hash.
     *
     * @param string $context Name of the form
     * @param int $time2Live Seconds before expire
     * @param int $max_hashes Clear old context hashes if more than this number
     *
     * @return int hash as a string
     */
    public function string($context = '', $time2Live = -1, $max_hashes = 5): string
    {
        // Generate hash
        $hash = $this->generateHash($context, $time2Live, $max_hashes);

        // Generate html input string
        return $hash->get();
    }

    /**
     * Validate by context.
     *
     * @param string $context Name of the form
     *
     * @return bool Valid or not
     */
    public function validate($context = '', $hash = null)
    {
        // If hash was not given, find hash
        if (is_null($hash)) {
            if (isset($_POST[$this->inputName])) {
                $hash = $_POST[$this->inputName];
            } elseif (isset($_GET[$this->inputName])) {
                $hash = $_GET[$this->inputName];
            } else {
                return false;
            }
        }

        // Check in the hash list
        for ($i = count($this->hashes) - 1; $i >= 0; --$i) {
            if ($this->hashes[$i]->verify($hash, $context)) {
                array_splice($this->hashes, $i, 1);

                return true;
            }
        }

        return false;
    }

    /**
     * Generate a CSRF_Hash.
     *
     * @param string $context Name of the form
     * @param int $time2Live Seconds before expiration
     * @param int $max_hashes Clear old context hashes if more than this number
     *
     * @return CSRF_Hash
     */
    private function generateHash($context = '', $time2Live = -1, $max_hashes = 5)
    {
        // If no time2live (or invalid) use default
        if ($time2Live < 0) {
            $time2Live = $this->hashTime2Live;
        }
        // Generate new hash
        $hash = new CSRF_Hash($context, $time2Live, $this->hashSize);
        // Save it
        array_push($this->hashes, $hash);
        if ($this->clearHashes($context, $max_hashes) === 0) {
            $this->_save();
        }

        // Return hash info
        return $hash;
    }

    /**
     * Load hash list.
     */
    private function _load()
    {
        $this->hashes = [];
        // If there are hashes on the session
        if (isset($_SESSION[$this->name])) {
            // Load session hashes
            $session_hashes = unserialize($_SESSION[$this->name]);
            // Ignore expired
            for ($i = count($session_hashes) - 1; $i >= 0; --$i) {
                // If an expired found, the rest will be expired
                if ($session_hashes[$i]->hasExpire()) {
                    break;
                }
                array_unshift($this->hashes, $session_hashes[$i]);
            }
            if (count($this->hashes) != count($session_hashes)) {
                $this->_save();
            }
        }
    }

    /**
     * Save hash list.
     */
    private function _save()
    {
        $_SESSION[$this->name] = serialize($this->hashes);
    }
}

class CSRF_Hash
{
    private $hash;
    private $context;
    private $expire;

    /**
     * [__construct description].
     *
     * @param string $context [description]
     * @param int $time2Live Number of seconds before expiration
     */
    public function __construct($context, $time2Live = 0, $hashSize = 64)
    {
        // Save context name
        $this->context = $context;

        // Generate hash
        $this->hash = $this->_generateHash($hashSize);

        // Set expiration time
        if ($time2Live > 0) {
            $this->expire = time() + $time2Live;
        } else {
            $this->expire = 0;
        }
    }

    /**
     * Check if hash has expired.
     *
     * @return bool
     */
    public function hasExpire()
    {
        if ($this->expire === 0 || $this->expire > time()) {
            return false;
        }

        return true;
    }

    /**
     * Verify hash.
     *
     * @return bool
     */
    public function verify($hash, $context = '')
    {
        if (strcmp($context, $this->context) === 0 && !$this->hasExpire() && strcmp($hash, $this->hash) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Check Context.
     *
     * @return bool
     */
    public function inContext($context = '')
    {
        if (strcmp($context, $this->context) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Get hash.
     *
     * @return string
     */
    public function get()
    {
        return $this->hash;
    }

    /**
     * The hash function to use.
     *
     * @param int $n Size in bytes
     *
     * @return string The generated hash
     */
    private function _generateHash($n)
    {
        return bin2hex(openssl_random_pseudo_bytes($n / 2));
    }
}
