<?php
namespace App\Traits;

trait UnloadedHelpers
{
    public static function setEnvironmentValue($envKey, $envValue)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        if (preg_match("/^{$envKey}=.*/m", $str, $matches)) {
            $str = preg_replace("/^{$envKey}=.*/m", "{$envKey}={$envValue}", $str);
        } else {
            $str .= "{$envKey}={$envValue}\n";
        }
        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
        return $envValue;
    }
}
