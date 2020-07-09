<?php
namespace SPHERE\Library\NumberToWord;

define('NUMERAL_SIGN', 'minus');
define('NUMERAL_HUNDREDS_SUFFIX', 'hundert');
define('NUMERAL_INFIX', 'und');

/**
 * Class float NumberToWord
 *
 * return string
 */
class NumberToWord
{

    public static function float2Text($fNumber, $IsMoney = false)
    {

        // Zahl mit korrektem Trennzeichen
        $fNumber = str_replace(',', '.', $fNumber);
        // Zahl auf 2 Nachkommastellen runden (wenn zu lang)
        $fNumber = round($fNumber, 2);
        $positionC = strpos($fNumber, '.');
        if($positionC){
            $numberInt = substr($fNumber, 0, $positionC);
            $numberFloat = substr($fNumber, $positionC + 1);
            // abgeschnittene Nullen auffüllen (0.6 -> 0.60 -> 60 "Cent")
            if(strlen($numberFloat) < 2){
                $numberFloat = str_pad($numberFloat, 2 ,'0', STR_PAD_RIGHT);
            }
        } else {
            $numberInt = $fNumber;
            $numberFloat = '00';
        }

        // Nachkommastelle wird ignoriert.
        if($numberFloat == '00'){
            if($IsMoney){
                return self::num2text($numberInt).' Euro';
            }
            return self::num2text($numberInt);
        }
        if($IsMoney){
            return self::num2text($numberInt).' Euro und '.self::num2text($numberFloat).' Cent';
        }
        return self::num2text($numberInt).' und '.self::num2text($numberFloat);
    }

    /**
     * @author  Thorsten Rotering <support@rotering-net.de>
     * @version 1.1 (2017-08-06)
     *
     * Hiermit wird unentgeltlich, jeder Person, die eine Kopie dieses Skripts erhÃ¤lt, die Erlaubnis erteilt,
     * diese uneingeschrÃ¤nkt zu benutzen, inklusive und ohne Ausnahme, dem Recht, sie zu verwenden, zu kopieren,
     * zu Ã¤ndern, zu fusionieren, zu verlegen, zu verbreiten, zu unterlizenzieren und/oder zu verkaufen, und
     * Personen, die dieses Skript erhalten, diese Rechte zu geben, unter den folgenden Bedingungen:
     *
     * Der obige Urheberrechtsvermerk und dieser Erlaubnisvermerk sind in allen Kopien oder Teilkopien des
     * Skripts beizulegen.
     *
     * DAS SKRIPT WIRD OHNE JEDE AUSDRÃœCKLICHE ODER IMPLIZIERTE GARANTIE BEREITGESTELLT, EINSCHLIESSLICH DER
     * GARANTIE ZUR BENUTZUNG FÃœR DEN VORGESEHENEN ODER EINEM BESTIMMTEN ZWECK SOWIE JEGLICHER RECHTSVERLETZUNG,
     * JEDOCH NICHT DARAUF BESCHRÃ„NKT. IN KEINEM FALL SIND DIE AUTOREN ODER COPYRIGHTINHABER FÃœR JEGLICHEN SCHADEN
     * ODER SONSTIGE ANSPRÃœCHE HAFTBAR ZU MACHEN, OB INFOLGE DER ERFÃœLLUNG EINES VERTRAGES, EINES DELIKTES ODER
     * ANDERS IM ZUSAMMENHANG MIT DEM SKRIPT ODER SONSTIGER VERWENDUNG DES SKRIPTS ENTSTANDEN.
     */

    /**
     * Liefert das Zahlwort zu einer Ganzzahl zurÃ¼ck.
     *
     * @param int    $pNumber Die Ganzzahl, die in ein Zahlwort umgewandelt werden soll.
     *
     * @return string Das Zahlwort.
     * @global array $lNumeral
     */
    public static function num2text($pNumber)
    {

        if ($pNumber == 0){ // "null"
            return 'null';
        } elseif ($pNumber < 0) { // Minus für Negativbeträge
            return NUMERAL_SIGN.' '.self::num2text_group(abs($pNumber));
        } else {
            return self::num2text_group($pNumber);
        }
    }

    /**
     * Rekursive Methode, die das Zahlwort zu einer Ganzzahl zurÃ¼ckgibt.
     *
     * @param int    $pNumber     Die Ganzzahl, die in ein Zahlwort umgewandelt werden soll.
     * @param int    $pGroupLevel (optional) Das Gruppen-Level der aktuellen Zahl.
     *
     * @return string Das Zahlwort.
     * @global array $lNumeral
     * @global array $lTenner
     * @global array $lGroupSuffix
     */
    public static function num2text_group($pNumber, $pGroupLevel = 0)
    {
        /* Die Zahlwörter von 0 bis 19. */
        $lNumeral = array('null', 'ein', 'zwei', 'drei', 'vier',
            'fünf', 'sechs', 'sieben', 'acht', 'neun',
            'zehn', 'elf', 'zwölf', 'dreizehn', 'vierzehn',
            'fünfzehn', 'sechzehn', 'siebzehn', 'achtzehn', 'neunzehn');

        /* Die Zehner-Zahlwörter. */
        $lTenner = array('', '', 'zwanzig', 'dreißig', 'vierzig',
            'fünfzig', 'sechzig', 'siebzig', 'achtzig', 'neunzig');

        /* Die Gruppen-Suffixe. */
        $lGroupSuffix = array(array('s', ''),
            array('tausend ', 'tausend '),
            array('e Million ', ' Millionen '),
            array('e Milliarde ', ' Milliarden '),
            array('e Billion ', ' Billionen '),
            array('e Billiarde ', ' Billiarden '),
            array('e Trillion ', ' Trillionen '));

        /* Ende der Rekursion ist erreicht, wenn Zahl gleich Null ist */
        if ($pNumber == 0){
            return '';
        }

        /* Zahlengruppe dieser Runde bestimmen */
        $lGroupNumber = $pNumber % 1000;

        /* Zahl der Zahlengruppe ist Eins */
        if ($lGroupNumber == 1){
            $lResult = $lNumeral[1].$lGroupSuffix[$pGroupLevel][0]; // "eine Milliarde"

            /* Zahl der Zahlengruppe ist größer als Eins */
        } elseif ($lGroupNumber > 1) {
            $lResult = '';

            /* Zahlwort der Hunderter */
            $lFirstDigit = floor($lGroupNumber / 100);

            if ($lFirstDigit > 0){
                $lResult .= $lNumeral[$lFirstDigit].NUMERAL_HUNDREDS_SUFFIX; // "fünfhundert"
            }

            /* Zahlwort der Zehner und Einer */
            $lLastDigits = $lGroupNumber % 100;
            $lSecondDigit = floor($lLastDigits / 10);
            $lThirdDigit = $lLastDigits % 10;

            if ($lLastDigits == 1){
                $lResult .= $lNumeral[1].'s'; // "eins"
            } elseif ($lLastDigits > 1 && $lLastDigits < 20) {
                $lResult .= $lNumeral[$lLastDigits]; // "dreizehn"
            } elseif ($lLastDigits >= 20) {
                if ($lThirdDigit > 0){
                    $lResult .= $lNumeral[$lThirdDigit].NUMERAL_INFIX; // "sechsund..."
                }
                $lResult .= $lTenner[$lSecondDigit]; // "...achtzig"
            }

            /* Suffix anhängen */
            $lResult .= $lGroupSuffix[$pGroupLevel][1]; // "Millionen"
        }

        /* Nächste Gruppe auswerten und Zahlwort zurückgeben */
        return self::num2text_group(floor($pNumber / 1000), $pGroupLevel + 1).$lResult;
    }

}