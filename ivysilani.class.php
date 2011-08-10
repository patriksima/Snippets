<?
/*
    Usage: iVysilani::GetRTMP('http://www.ceskatelevize.cz/ivysilani/10165490172-kickbox/21047129251-k1-souboj-titanu-plzen/')
*/
class iVysilani
{
    const PLAYLISTURL = 'http://www.ceskatelevize.cz/ajax/playlistURL.php';

    public static function GetRTMP( $url, $bitrate=500 )
    {
        $html = @file_get_contents($url);
        if ($html === false) {
            throw new Exception("CT24 not responding.");
        }

        if (!preg_match('/callSOAP\(([^)]+)\)/', $html, $match)) {
            throw new Exception("callSOAP not found.");
        }

        $decode = json_decode($match[1], true);
        $data = http_build_query($decode);

        $opts = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Content-type: application/x-www-form-urlencoded\r\n" .
                          "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

        $context = stream_context_create($opts);

        $stream = @fopen(iVysilani::PLAYLISTURL, 'r', false, $context);
        if ($stream===false) {
            throw new Exception("CT24 not responding.");
        }

        $header = stream_get_meta_data($stream);
        if (!preg_match('/200 OK/', $header['wrapper_data'][0])) {
            throw new Exception("CT24 not responding.");
        }

        $smilurl = @stream_get_contents($stream); 
        if ($smilurl === false) { 
            throw new Exception("Cannot read data.");
        }

        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"User-Agent: wget\r\n"
            )
        );    

        $context = stream_context_create($opts);

        $stream = @fopen($smilurl, 'r', false, $context);
        if ($stream===false) {
            throw new Exception("CT24 not responding.");
        }

        $header = stream_get_meta_data($stream);
        if (!preg_match('/200 OK/', $header['wrapper_data'][0])) {
            throw new Exception("CT24 not responding.");
        }

        $smilxml = @stream_get_contents($stream); 
        if ($smilxml === false) { 
            throw new Exception("Cannot read SMIL data.");
        }

        if (!preg_match_all('/<switchItem.*base="([^"]+)"/', $smilxml, $matches)) {
            throw new Exception("Unknown SMIL format");
        }

        $base = array_pop($matches[1]);

        if (!preg_match_all('/<video src="([^"]+)" system-bitrate="'.$bitrate.'"/', $smilxml, $matches)) {
            throw new Exception("Unknown SMIL format");
        }

        $src = array_pop($matches[1]);

        return html_entity_decode($base.'/'.$src);
    }
}
?>