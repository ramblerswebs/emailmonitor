<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of changelog
 *
 * @author Chris Vaughan
 */
class changelog {

    private $file;
    private $handle;

    public function __construct($filename) {
        $this->file = $filename;
        $this->handle = fopen($this->file, "a"); //append
    }

    public function fileOpen() {
        return $this->handle !== false;
    }

    Public function addRecord($function, $text) {
        if ($this->handle !== false) {
            $out = date("Y-m-d") . ": " . $function . " - " . $text . PHP_EOL;
            fwrite($this->handle, $out);
        }
    }

    public function close() {
        
    }

}
