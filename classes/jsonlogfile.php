<?php

/**
 * Description of jsonlogfile
 *
 * @author Chris Vaughan
 */
class jsonlogfile {

    private $file;
    private $type;
    private $items;
    private $items_processed;

    public function __construct($file, $type) {
        $this->file = $file;
        $this->type = $type;
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

    public function addItem($name, $item) {
        if (array_key_exists($name, $this->items)) {
            // $record = $this->items[$name];
            $datecreated = $this->items[$name]['dateFirstRecord'];
            $item['dateFirstRecord'] = $datecreated;
        } else {
            // new record
            $item['dateFirstRecord'] = $email->getDate();
            $log->addRecord("New " . $this->type, $name);
        }
        $this->items[$name] = $item;
        $this->items_processed = true;
        echo $this->type . " - " . $name . "<br />\n";
    }

    public function removeItem($name, $log) {

        if (array_key_exists($name, $this->items)) {
            $this->items_processed = true;
            unset($this->items[$name]);
            echo "Remove " . $this->type . " - " . $name . "<br />\n";
            $log->addRecord("Remove " . $this->type, $name);
        } else {
            functions::sendError("Unable to remove " . $this->type . " for " . $name);
        }
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
