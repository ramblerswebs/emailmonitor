<?php

/**
 * Description of jsonlogfile
 *
 * @author Chris Vaughan
 */
class jsonlogfile {

    private $file;
    private $type;
    private $log;
    private $items;
    private $items_processed;

    public function __construct($file, $type, $log) {
        $this->file = $file;
        $this->type = $type;
        $this->log = $log;
        if (file_exists($file)) {
            $string = file_get_contents($file);
        } else {
            $string = false;
        }

        if ($string === false) {
            $this->items = [];
        } else {
            $this->items = json_decode($string, true);
            if ($this->items === null) {
                // error
                $this->items = [];
            }
        }
        $this->items_processed = false;
    }

    public function addItem($name, $item, $date) {
        if (array_key_exists($name, $this->items)) {
            // $record = $this->items[$name];
            $datecreated = $this->items[$name]['dateFirstRecord'];
            $item['dateFirstRecord'] = $datecreated;
        } else {
            // new record
            $item['dateFirstRecord'] = $date;
            $this->log->addRecord("New " . $this->type, $name);
        }
        $this->items[$name] = $item;
        $this->items_processed = true;
    }

    public function removeItem($name) {
        if (array_key_exists($name, $this->items)) {
            $this->items_processed = true;
            unset($this->items[$name]);
            $this->log->addRecord("Remove " . $this->type, $name);
            return true;
        }
        return false;
    }

    Public function storeItems() {
        if ($this->items_processed) {
            $myJSON = json_encode($this->items, JSON_PRETTY_PRINT);
            //echo $myJSON;
            file_put_contents($this->file, $myJSON);
            // check file to see if any not backed up
            // send email to say what new sites or not backup up sites
        }
    }

}
