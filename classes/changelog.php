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
        if ($this->handle===false){
            echo "ERROR: unable to set up log file ".$filename;
            die();
        }
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
        fclose($this->handle);
    }

}
