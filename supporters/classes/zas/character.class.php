<?php
/*
|-------------------------------------------------------------
| Comments with #.# are required by `zas` for code insertion.
|-------------------------------------------------------------
*/

namespace Zas;

#uns#


/**
 * Used for basic character operations. This is only for ASCII
 */
class Character {

    # use traits
    #ut#

    public function __construct(){
        #code...
    }

    /**
     * Checks if a letter is an upper case letter
     * @param string $char character
     * 
     * @return bool
     */
    public static function isUpper($char){
        $c = ord($char);
        if($c >= 65 && $c <= 90) return true;

        return false;
    }

    /**
     * converts a letter to an upper case letter
     * @param string $char character
     * 
     * @return string
     */
    public static function upper($char){
        if(!static::isLower($char)) return $char;

        $c = ord($char);
        return chr($c - ord('a') + ord('A'));
    }

    /**
     * Checks if a character is lower case
     * @param string $char character
     * 
     * @return bool
     */
    public static function isLower($char){
        $c = ord($char);
        if($c >= 97 && $c <= 122) return true;

        return false;
    }

    /**
     * converts a letter to lower case
     * @param string $char character
     * 
     * @return string
     */
    public static function lower($char){
        if(!static::isUpper($char)) return $char;

        $c = ord($char);
        return chr($c + ord('a') - ord('A'));
    }


    /**
     * Returns true if the character is an ASCII letter
     * @param string $char character
     * 
     * @return bool
     */
    public static function isLetter($char){
        return static::isUpper($char) || static::isLower($char);
    }

}

?>