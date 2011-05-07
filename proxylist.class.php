<?php
/**
 * Author: Patrik Šíma <programator@patriksima.cz>
 * License: MIT
 */

class ProxyList
{
    public static function Factory( $site )
    {
        switch( $site )
        {
            case 'samair.ru':
                return new Samair();
                break;
            case 'xroxy.com':
                return new Xroxy();
                break;
            case 'aliveproxy.com':
                return new AliveProxy();
                break;
            default:
                throw new Exception('Site class not found');
        }
    }
    
    public static function Check( $proxy )
    {
        $res = false;
        $url = "http://www.seznam.cz/st/img/favicon.ico";

        $opts = array(
                'http' => array(
                   'method' => 'get',
                   'proxy' => 'tcp://'.$proxy,
                   'timeout' => 1.0, // experimental, fast proxy = low timeout
                   'max_redirects' => '0',
                   'ignore_errors' => '1')
                );

        $context = stream_context_create($opts);
        
        $stream = @fopen($url, 'r', false, $context);
        if ($stream===false) {
            return $res;
        }

        $header = stream_get_meta_data($stream);
        if (preg_match('/200 OK/', $header['wrapper_data'][0])) {
            $res = true;
        }

        fclose($stream);

        return $res;
    }
}

class Samair
{
    const URL = 'http://www.samair.ru/proxy/time-%02d.htm';

    protected function GetPage( $page )
    {
        $html = @file_get_contents(sprintf(Samair::URL, $page));
        if ($html === false) {
            throw new Exception("Samair not responding.");
        }
        return $html;
    }

    protected function GetKeys( $html )
    {
        $keys = array();
        if (preg_match('/<script[^>]+>\W*(([a-z]{1}=[0-9]+;)+)\W*<\/script>/msU', $html, $match)) {
            $tmp = explode(';', $match[1]);
            array_pop($tmp);
            foreach($tmp as $tmp2) {
                list($k, $v) = explode('=', $tmp2);
                $keys[$k] = $v;
            }
        } else {
            throw new Exception("Keys not found.");
        }
        return $keys;
    }

    protected function DecodePort( $html, $keys )
    {
        $port = '';
        $tmp = explode('+', $html);
        foreach($tmp as $k) {
            $port.= $keys[$k];
        }
        return $port;
    }

    protected function GetIpAddresses( $html, $keys )
    {
        $iplist = array();
        if (preg_match_all('/<td>([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)<script[^>]+>document\.write\(":"\+([^)]+)\)<\/script><\/td>/msU', $html, $matches)) {
            foreach($matches[1] as $i=>$ip) {
                $iplist[] = $ip.':'.$this->DecodePort($matches[2][$i], $keys);
            }
        } else {
            throw new Exception("No ip address found.");
        }
        return $iplist;
    }

    public function GetList( $pages = 9 )
    {
        $iplist = array();
        
        for ($page=1; $page<=$pages; $page++)
        {
            $html = $this->GetPage($page);
            $keys = $this->GetKeys($html);
            $list = $this->GetIpAddresses($html, $keys);
            $iplist = array_merge($iplist, $list);
        }
        return $iplist;
    }
}

class Xroxy
{
    const URL = 'http://www.xroxy.com/proxylist.php?sort=reliability&desc=true&pnum=%d#table';

    protected function GetPage( $page )
    {
        $html = @file_get_contents(sprintf(Xroxy::URL, $page));
        if ($html === false) {
            throw new Exception("XRoxy not responding.");
        }
        return $html;
    }

    protected function GetIpAddresses( $html )
    {
        $iplist = array();
        if (preg_match_all('/host=([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)&port=([0-9]+)/ms', $html, $matches)) {
            foreach($matches[1] as $i=>$ip) {
                $iplist[] = $matches[1][$i].':'.$matches[2][$i];
            }
        } else {
            throw new Exception("No ip address found.");
        }
        return $iplist;
    }
    
    public function GetList( $pages = 9 )
    {
        $iplist = array();
        
        for ($page=0; $page<=$pages; $page++)
        {
            $html = $this->GetPage($page);
            $list = $this->GetIpAddresses($html);
            $iplist = array_merge($iplist, $list);
        }
        return $iplist;
    }
}

class AliveProxy
{
    const URL = 'http://aliveproxy.com/fastest-proxies/';
    
    protected function GetPage()
    {
        $html = @file_get_contents(AliveProxy::URL);
        if ($html === false) {
            throw new Exception("AliveProxy not responding.");
        }
        return $html;
    }

    protected function GetIpAddresses( $html )
    {
        $iplist = array();
        if (preg_match_all('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+:[0-9]+/ms', $html, $matches)) {
            $iplist = $matches[0];
        } else {
            throw new Exception("No ip address found.");
        }
        return $iplist;
    }
    
    public function GetList()
    {
        $html = $this->GetPage();
        $list = $this->GetIpAddresses($html);

        return $list;
    }
}

/*
$site = ProxyList::Factory('aliveproxy.com');
$list = $site->GetList();

print 'Checking proxies'.PHP_EOL;
foreach($list as $i=>$proxy) {
    if (!ProxyList::Check($proxy)) {
        unset($list[$i]);
    } else {
        print $proxy.PHP_EOL;
    }
}
*/
?>