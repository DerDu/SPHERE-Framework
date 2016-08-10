<?php
namespace SPHERE\Application\Transfer\Gateway\Converter;

use SPHERE\System\Extension\Extension;

/**
 * Class Sanitizer
 *
 * @package SPHERE\Application\Transfer\Gateway\Converter
 */
class Sanitizer extends Extension
{

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeFullTrim($Value)
    {

        return trim($Value);
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeAddressCityCode($Value)
    {

        return str_pad($Value, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeAddressCityDistrict($Value)
    {

        $Value = explode('OT', $Value);
        return ( count($Value) > 1 ? trim(end($Value)) : '' );
    }

    /**
     * @param $Value
     *
     * @return string
     * @throws \Exception
     */
    protected function sanitizeAddressCityName($Value)
    {

        if (empty( $Value )) {
            throw new \Exception('Adresse: Stadtname darf nicht leer sein');
        }

        $Value = explode('OT', $Value);
        return trim(current($Value));
    }

    /**
     * @param $Value
     *
     * @return bool|string
     * @throws \Exception
     */
    protected function sanitizeDate($Value)
    {

        if (empty( $Value )) {
            throw new \Exception('Schüler: Geburtstag darf nicht leer sein');
        }

        return date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($Value));
    }

    /**
     * @param $Value
     *
     * @return bool|string
     * @throws \Exception
     */
    protected function sanitizeMailAddress($Value)
    {

//        if (empty( $Value )) {
//            throw new \Exception('Email: Adresse darf nicht leer sein');
//        }

        if( !empty( $Value ) ) {
            $Value = filter_var($Value, FILTER_SANITIZE_EMAIL);
            $Value = filter_var($Value, FILTER_VALIDATE_EMAIL);

            if (empty( $Value )) {
                throw new \Exception('Email: Adresse muss ein gültiges Format haben');
            }

            return $Value;
        }

        return '';
    }

    /**
     * @param $Value
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function sanitizeCustodyFirstName($Value)
    {

        if (empty( $Value )) {
            throw new \Exception('Sorgeberechtigter: Vorname darf nicht leer sein');
        }

        $Value = explode(' ', $Value);
        $Value = current($Value);
        return $Value;
    }

    /**
     * @param $Value
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function sanitizeCustodyLastName($Value)
    {

        if (empty( $Value )) {
            throw new \Exception('Sorgeberechtigter: Nachname darf nicht leer sein');
        }

        $Value = explode(' ', $Value);
        $Value = end($Value);

        return $Value;
    }
}
