<?php

/**
 * Description of emailcomparer
 *
 * @author ChrisV
 */
class EmailComparer {

    private $email = null;

    public function __construct($email) {
        $this->email = $email;
    }

    public function isOfType($titleContains, $bodyContains = []) {
        If ($bodyContains !== []) {
            $body = $this->email->getBody();
            if ($this->compareItem($body, $bodyContains) === false) {
                return false;
            }
        }
        $subject = $this->email->getSubject();
        return $this->compareItem($subject, $titleContains);
    }

    public function testCompareItem() {
        echo "<h2>Text email compare function</h2>";
        $text = "The quick brown fox jumps over lazy dog";
        echo "<p>Test string: " . $text . "</p>";
        echo '<table>';

        $this->displayResult($text, ["The ", " brown "]);
        $this->displayResult($text, ["The ", " BROWN "]);
        $this->displayResult($text, ["Theres", " BROWN "]);
        $this->displayResult($text, ["Brown", "the"]);
        $this->displayResult($text, ["", " brown "]);
        $this->displayResult($text, ["", " the "]);
        $this->displayResult($text, ["the", "cow"]);

        echo '</table>';
    }

    private function displayResult($text, $contains) {
        $found = $this->compareItem($text, $contains);
        echo "<tr><td>" . json_encode($contains) . "</td><td>" . json_encode($found) . "</td></tr>";
    }

    private function compareItem($item, $contains) {
        $comp = strtolower($item);
        $first = true;
        foreach ($contains as $value) {
            $lc = strtolower($value);
            $inString = strpos($comp, $lc);
            if ($first) {
                if ($inString !== 0) {
                    return false;
                }
            } else {
                if ($inString === false) {
                    return false;
                }
            }


            $first = false;
        }
        return true;
    }

}
