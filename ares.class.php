<?php
/**
 * Author: Patrik Šíma <ja@patriksima.cz>
 * License: MIT
 */

class Ares
{
    const URL = "http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?ico=%d";

    public static function Query( $ic )
    {
        $res = array('ic'=>$ic,'company'=>'','street'=>'','city'=>'','post'=>'');
        
        if (!is_numeric($ic)) {
            throw new Exception("Argument must be a number.");
        }
        
        $xml = @file_get_contents( sprintf(Ares::URL, $ic) );
        if ($xml === false) {
            throw new Exception("Ares not responding.");
        }
        
        if (preg_match("/<are:Obchodni_firma>([^<]+)<\/are:Obchodni_firma>/msU", $xml, $match)) {
            $res['company'] = $match[1];
        } else {
            throw new Exception("Cannot match company name.");
        }
        
        if (preg_match("/<dtt:Nazev_ulice>([^<]+)<\/dtt:Nazev_ulice>/msU", $xml, $match)) {
            $res['street'] = $match[1];
        } else {
            throw new Exception("Cannot match street.");
        }
        if (preg_match("/<dtt:Cislo_domovni>([^<]+)<\/dtt:Cislo_domovni>/msU", $xml, $match)) {
            $res['street'] .= ' '.$match[1];
        } else {
            throw new Exception("Cannot match street number.");
        }
        if (preg_match("/<dtt:Cislo_orientacni>([^<]+)<\/dtt:Cislo_orientacni>/msU", $xml, $match)) {
            $res['street'] .= '/'.$match[1];
        } else {
            throw new Exception("Cannot match street number.");
        }

        if (preg_match("/<dtt:Nazev_obce>([^<]+)<\/dtt:Nazev_obce>/msU", $xml, $match)) {
            $res['city'] = $match[1];
        } else {
            throw new Exception("Cannot match city name.");
        }
        if (preg_match("/<dtt:Nazev_casti_obce>([^<]+)<\/dtt:Nazev_casti_obce>/msU", $xml, $match)) {
            if ($res['city'] != $match[1]) {
                $res['city'] .= ' '.$match[1];
            }
        } else {
            throw new Exception("Cannot match city name.");
        }
        
        if (preg_match("/<dtt:PSC>([^<]+)<\/dtt:PSC>/msU", $xml, $match)) {
            $res['post'] = $match[1];
        } else {
            throw new Exception("Cannot match post code.");
        }

        return $res;
    }
}
?>