<?php
class CSFD
{
    const URL = 'http://www.csfd.cz/film/%d/';
    
    public static function GetMovie( $id )
    {
        $movie = array('id'=>$id, 'title'=>'', 'content'=>'', 'genres'=>array(), 'origin'=>'', 'year'=>'', 'rating'=>'');
        
        $html = @file_get_contents(sprintf(CSFD::URL, $id));
        if ($html === false) {
            throw new Exception("CSFD not responding.");
        }

        if (preg_match("/<meta property=\"og:title\" content=\"([^\/]+)(?: \/ ([^\(]+))? \(([0-9]+)\)\"/ms", $html, $match)) {
            $movie['title'] = ($match[2]=='') ? trim($match[1]) : trim($match[2]);
            $movie['year'] = intval($match[3]);
        }

        if (preg_match('/<div data-truncate[^>]+>(?:(?!<\/div>).)*<\/div>/msU', $html, $match)) {
            $movie['content'] = trim(strip_tags($match[0]));
        }

        if (preg_match("/<p class=\"genre\">([^<]+)<\/p>/ms", $html, $match)) {
            $tmp = explode('/', $match[1]);
            foreach($tmp as $v) {
                $movie['genres'][] = trim($v);
            }
        }
        
        if (preg_match("/<p class=\"origin\">([^,]+)/ms", $html, $match)) {
            $movie['origin'] = trim($match[1]);
        }
        
        if (preg_match("/<p class=\"origin\">[^0-9]*([0-9]+)/ms", $html, $match)) {
            if ($movie['year']=='') $movie['year'] = $match[1];
        }
        
        if (preg_match("/<h2 class=\"average\">([0-9]+)/ms", $html, $match)) {
            $movie['rating'] = intval($match[1]);
        }

        return $movie;
    }
}
?>