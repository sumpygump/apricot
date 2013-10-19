<?php
/**
 * Randomness
 *
 * @package Qi
 */

/**
 * Randomness
 *
 * This class will generate random strings, words, numbers, website addresses, street addresses, etc.
 *
 * @package Qi
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version 0.9.1
 */
class Qi_Randomness {

    /**
     * @var string Vowels
     */
    public $vowels;

    /**
     * @var string consonants
     */
    public $consonants;

    /**
     * @var string Store top level domains to use for random websites
     */
    public $tlds = array('com','net','org');

    /**
     * @var string Storage for the word
     */
    public $word = '';

    /**
     * @var bool Show syllables (for debugging)
     */
    protected $show_syllables;

    /**
     * Constructor
     *
     * @param string $letterset The letterset to use
     * @return void
     */
    public function __construct($letterset='caesar') {
        $this->use_letterset($letterset);
        $this->show_syllables = false; // For debugging
    }

    /**
     * Use a specific letterset
     *
     * @param mixed $letterset The letter set to use
     * @return void
     */
    public function use_letterset($letterset) {
        switch ($letterset) {
        case 'original':
            $this->vowels     = 'aaaeeeeiioouy';
            $this->consonants = 'bbbccddffghkjlmmmnnnppprrrrsssssttttvwz';
            break;
        case 'equal':
            // Probability of all vowels and consonants equal
            $this->vowels     = 'aeiou';
            $this->consonants = 'bcdfghjklmnpqrstvwxyz';
            break;
        case 'baba':
            // This letter set picks 1 random vowel and 1 random consonant
            // and those two letters will be used as the letter set
            $vlist = 'aeiou';
            $clist = 'bcdfghjklmnpqrstvwxyz';

            $this->vowels     = $vlist[mt_rand(0, 4)];
            $this->consonants = $clist[mt_rand(0, 20)];
            break;
        case 'caesar':
            // This is based on the frequency that letters occur in the English language (from the man page of caesar)
            $this->vowels = str_repeat('e', 1300)
                .str_repeat('a', 810)
                .str_repeat('o', 790)
                .str_repeat('i', 630)
                .str_repeat('u', 240)
                .str_repeat('y', 95);

            $this->consonants = str_repeat('t', 1050)
                .str_repeat('n', 710)
                .str_repeat('r', 680)
                .str_repeat('s', 610)
                .str_repeat('h', 520)
                .str_repeat('d', 380)
                .str_repeat('l', 340)
                .str_repeat('f', 290)
                .str_repeat('c', 270)
                .str_repeat('m', 250)
                .str_repeat('g', 200)
                .str_repeat('p', 190)
                .str_repeat('y', 95)
                .str_repeat('w', 150)
                .str_repeat('b', 140)
                .str_repeat('v', 90)
                .str_repeat('k', 40)
                .str_repeat('x', 15)
                .str_repeat('j', 13)
                .str_repeat('q', 11)
                .str_repeat('z', 7);
            break;
        }
    }

    /**
     * Generate a random string
     *
     * By default, an eight character, base64url string is returned.
     *
     * @param integer $length Length of the returned string
     * @param string $characters The characters to use
     * @return string
     */
    public function generateRandomString($length=8, $characters='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_') {
        $string = '';
        for ($i=0; $i<$length; ++$i) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

    /**
     * Generate a random pronouncable word
     *
     * based on method by Eric Sizemore ( www.secondversion.com & www.phpsociety.com )
     *
     * @param int $length The length of the word
     * @param bool $lower_case Return the word in lowercase
     * @param bool $ucfirst Whether to capitalize the first letter
     * @param bool $upper_case Return the word in uppercase
     * @return string
     */
    public function generateRandomWord($length = 5, $lower_case = true, $ucfirst = false, $upper_case = false) {
        $syllable_types = array('V','CV','CV','CVC','CVC','CVVC','CVV');
        //$syllable_types = array('V','CV','CVC','CVC','VCC');

        $const_or_vowel = 1;
        $done           = false;
        $word           = '';

        while (!$done) {
            $syllable_type = $syllable_types[mt_rand(0, count($syllable_types)-1)];
            if ($this->show_syllables) {
                echo $syllable_type;
            }

            $word .= $this->generateSyllable($syllable_type);

            //ob_flush();
            flush();  // needed ob_flush

            if (strlen($word) >= $length) {
                $done = true;
            }
        }

        if ($this->show_syllables) {
            echo "\n";
        }

        $word = substr($word, 0, $length);
        $word = ($lower_case) ? strtolower($word) : $word;
        $word = ($ucfirst) ? ucfirst(strtolower($word)) : $word;
        $word = ($upper_case) ? strtoupper($word) : $word;

        return $word;
    }

    /**
     * generateSyllable
     *
     * @param string $syllable_type The syllable type (a string containing Cs and Vs)
     * @return string
     */
    public function generateSyllable($syllable_type) {
        $out = '';

        for ($i = 0; $i < strlen($syllable_type); $i++) {
            switch ($syllable_type[$i]) {
            case "C":
                $out .= $this->consonants[mt_rand(0, strlen($this->consonants)-1)];
                break;
            case "V":
                $out .= $this->vowels[mt_rand(0, strlen($this->vowels)-1)];
                break;
            }
        }
        return $out;
    }

    /**
     * Generate a random name
     *
     * @param int $maxlength The maximum length of the name
     * @return string
     */
    public function generateRandomName($maxlength=10) {
        $length = mt_rand(1, $maxlength);
        return $this->generateRandomWord($length, false, true);
    }

    /**
     * Generate a random company name
     *
     * @param int $maxwords The maxiumum number of words in the name
     * @return string
     */
    public function generateRandomCompany($maxwords=2) {
        $suffixes = array(', Inc.', ' Enterprises', ' Corporation', ', LLC.');

        $words   = mt_rand(1, $maxwords);
        $company = '';

        for ($i = 0; $i < $words; $i++) {
             $company .= $this->generateRandomName(mt_rand(4, 15)) . " ";
        }

        $company = substr($company, 0, -1);

        $company .= $suffixes[mt_rand(0, count($suffixes)-1)];
        return $company;
    }

    /**
     * Generate a random website address
     *
     * @return string
     */
    public function generateRandomWebsite() {
        $out = "";

        $include_www = mt_rand(0, 1);
        if ($include_www) {
            $out = "www.";
        }

        $out .= $this->generateRandomWord(mt_rand(3, 15));

        $out .= "." . $this->tlds[mt_rand(0, count($this->tlds)-1)];
        return $out;
    }

    /**
     * Generate a random email address
     *
     * @return string
     */
    public function generateRandomEmail() {
        $out .= "";

        $out .= $this->generateRandomWord(mt_rand(2, 10));
        $out .= "@";
        $out .= $this->generateRandomWord(mt_rand(3, 16));
        $out .= "." . $this->tlds[mt_rand(0, count($this->tlds)-1)];
        return $out;

    }

    /**
     * Generate a random number (string of digits)
     *
     * @param int $minlength The minimum number of digits
     * @param int $maxlength The maximum number of digits
     * @param int $exactlength Generate a number with exactly this many digits
     * @return string
     */
    public function generateRandomNumber($minlength=4, $maxlength=16, $exactlength=0) {
        if ($exactlength != 0) {
            $length = $exactlength;
        } else {
            $length = mt_rand($minlength, $maxlength);
        }
        return $this->generateRandomString($length, '1234567890');
    }

    /**
     * Generate a random phone number
     *
     * @return string
     */
    public function generateRandomPhoneNumber() {
        $out  = '';
        $out .= $this->generateRandomNumber(0, 0, 3);
        $out .= "-";
        $out .= $this->generateRandomNumber(0, 0, 3);
        $out .= "-";
        $out .= $this->generateRandomNumber(0, 0, 4);
        return $out;
    }

    /**
     * Generate a random street address
     *
     * @return string
     */
    public function generateRandomAddress() {
        $roads  = array("St.","Ave.","Blvd.","Way","Rd.","Parkway","Place");
        $length = mt_rand(1, 10);

        $out  = $this->generateRandomNumber(2, 5);
        $out .= " ";
        $out .= $this->generateRandomWord($length, false, true);
        $out .= " ";
        $out .= $roads[mt_rand(0, count($roads)-1)];
        return $out;
    }
}
