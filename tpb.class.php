<?php
/**
 * Author: Patrik Šíma <programator@patriksima.cz>
 * License: MIT
 */

class TPB
{
    const URL = 'http://thepiratebay.org/top/201';

    public static function GetList()
    {
        $list = array();
        
        $html = @file_get_contents(TPB::URL);
        if ($html === false) {
            throw new Exception("TPB not responding.");
        }

        if (preg_match_all('/<td>\W*<div class="detName">(?:(?!<\/td>).)*<\/td>/msU', $html, $matches)) {
            foreach($matches[0] as $item) {
                $torrent = array('name'=>'', 'magnet'=>'', 'size'=>'', 'type'=>'');
                if (preg_match('/<a[^>]*class="detLink"[^>]*>((?:(?!<\/a>).)*)<\/a>/msU', $item, $match)) {
                    $torrent['name'] = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8'));
                }
                if (preg_match('/<a href="(magnet:[^"]+)"/msU', $item, $match)) {
                    $torrent['magnet'] = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8'));
                }
                if (preg_match('/<font class="detDesc">.*Size ([^,]+)/ms', $item, $match)) {
                    $torrent['size'] = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8'));
                }
                if (preg_match('/(dvdrip|bdrip|brrip|dvdscr|ppvrip)/msi', $torrent['name'], $match)) {
                    $torrent['type'] = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8'));
                }
                $list[] = $torrent;
            }
        } else {
            throw new Exception("No torrent found.");
        }
        
        return $list;
    }
}
?>