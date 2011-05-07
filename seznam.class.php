<?php
/**
 * Author: Patrik Šíma <programator@patriksima.cz>
 * License: MIT
 */

class Seznam
{
    const URLQ = 'http://www.firmy.cz/?query=%s';
    const URLD = 'http://www.firmy.cz%s';

    public static function Query( $company )
    {
        $res = array('company'=>'','phones'=>array(),'emails'=>array());

        $html = @file_get_contents( sprintf(Seznam::URLQ, urlencode($company)) );
        if ($html === false) {
            throw new Exception("Seznam.cz not responding.");
        }

        if (!preg_match('/<table class="vysledek"[^>]+>(?:(?!<\/table>).)*<\/table>/msU', $html, $match)) {
            throw new Exception("Company not found.");
        }

        if (!preg_match('/<h3>\W*<a href="([^"]+)"/msU', $match[0], $match)) {
            throw new Exception("Company not found.");
        }

        $html = @file_get_contents( sprintf(Seznam::URLD, $match[1]) );
        if ($html === false) {
            throw new Exception("Seznam.cz not responding.");
        }

        if (preg_match('/<div id="firmCont">\W*<h2>([^>]+)<\/h2>/msU', $html, $match)) {
            $res['company'] = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8'));
        } else {
            throw new Exception("Company not found.");
        }

        if (preg_match_all('/<p class="tel">(?:(?!<\/p>).)*<\/p>/msU', $html, $matches)) {
            foreach($matches[0] as $k=>$v) {
                if (preg_match('/<span class="value">([^<]+)<\/span>/msU', $v, $match)) {
                    $res['phones'][] = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8'));
                }
            }
        }
        
        if (preg_match_all('/<p class="email">(?:(?!<\/p>).)*<\/p>/msU', $html, $matches)) {
            foreach($matches[0] as $k=>$v) {
                if (preg_match('/<a[^>]+class="value">([^<]+)<\/a>/msU', $v, $match)) {
                    $res['emails'][] = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8'));
                }
            }
        }
        return $res;
    }
}
?>