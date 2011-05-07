<?php
/**
 * Author: Patrik Šíma <programator@patriksima.cz>
 * License: MIT
 */

class Log
{
    protected static $table = '';
    protected static $expire = 30; //days

    protected static function Rec ( $level, $message, $params )
    {
        $sql = "INSERT INTO `".Log::$table."`
                        SET level = '".$level."',
                            message = '".mysql_escape_string($message)."',
                            params = '".serialize($params)."',
                            recorded = NOW()";
        $res = mysql_query($sql);

        Log::Rotate();
    }
    
    public static function Info( $message, $params = '' )
    {
        Log::Rec('info', $message, $params);
    }
    
    public static function Warn( $message, $params = '' )
    {
        Log::Rec('warn', $message, $params);
    }
    
    public static function Error( $message, $params = '' )
    {
        Log::Rec('error', $message, $params);
    }

    public static function Rotate()
    {
        $sql = "DELETE FROM `".Log::$table."` WHERE DATE_ADD(recorded, INTERVAL ".Log::$expire." DAY) < NOW()";
        $res = mysql_query($sql);
    }
}
?>
