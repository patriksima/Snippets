<?php
/**
 * Author: Patrik Šíma <programator@patriksima.cz>
 * License: MIT
 */

class Cache
{
    protected $expire = 1; //hours
    protected $cache  = array();
    private $table = '';
    
    public function __construct()
    {
        $this->init();
        $this->clear();
        $this->load();
    }
    
    protected function init()
    {
        $sql = "SHOW TABLES LIKE '".$this->table."'";
        $res = mysql_query($sql);
        if (!mysql_num_rows($res)) {
            $sql = "CREATE TABLE IF NOT EXISTS `".$this->table."` (
                   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                   `meta_key` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                   `meta_value` text COLLATE utf8_czech_ci NOT NULL,
                   `created` datetime NOT NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY `meta_key` (`meta_key`)
                   ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1";
            $res = mysql_query($sql);
        }
    }

    protected function clear()
    {
        $sql = "DELETE FROM `".$this->table."` WHERE DATE_ADD(created, INTERVAL ".$this->expire." HOUR) < NOW()";
        $res = mysql_query($sql);
    }

    protected function load()
    {
        $sql = "SELECT * FROM `".$this->table."` ORDER BY created DESC";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res))
        {
            $this->cache[$row['meta_key']] = $row['meta_value'];
        }  
    }

    public function set( $key, $obj )
    {
        $sql = "INSERT INTO `".$this->table."` SET meta_key = '".mysql_escape_string($key)."', meta_value = '".serialize($obj)."', created = NOW()
                ON DUPLICATE KEY UPDATE meta_value = '".serialize($obj)."', created = NOW()";
        $res = mysql_query($sql);

        $this->cache[$key] = serialize($obj);
    }

    public function get( $key )
    {
        if (!array_key_exists($key, $this->cache)) {
            throw new Exception('Cache not found a key.');
        }
        return unserialize($this->cache[$key]);
    }

    public function get_raw( $key )
    {
        $sql = "SELECT meta_value FROM `".$this->table."` WHERE meta_key = '".mysql_escape_string($key)."'";
        $res = mysql_query($sql);
        if (!mysql_num_rows($res)) {
            throw new Exception('Cache not found a key.');
        }
        return unserialize(mysql_result($res, 0));
    }

    public function del( $key )
    {
        unset($this->cache[$key]);
        $sql = "DELETE FROM `".$this->table."` WHERE meta_key = '".mysql_escape_string($key)."'";
        $res = mysql_query($sql);
    }

    public function flush()
    {
        $this->cache = array();
        $sql = "TRUNCATE TABLE `".$this->table."`";
        $res = mysql_query($sql);
    }

    public function dump()
    {
        $sql = "SELECT * FROM `".$this->table."` ORDER BY created DESC";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res))
        {
            print $row['id'].', '.$row['meta_key'].', '.$row['meta_value'].', '.$row['created'].'<br />'."\n";
        }  
    }
}
?>
