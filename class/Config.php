<?php

namespace StudioDemmys\CalendarImage;

$__global_config = null;

class Config
{
    const CONFIG_FILE = "./config/config.yml";
    const CONFIG_FILE_FILTER = "*.yml";
    const RECURSIVE_INCLUDE_LIMIT = 3;
    
    protected function __construct()
    {
    }
    
    public static function loadConfig(?string $path = self::CONFIG_FILE): void
    {
        global $__global_config;
        if (!isset($__global_config["recursive_count"]))
            $__global_config["recursive_count"] = 0;
        if ($__global_config["recursive_count"] >= self::RECURSIVE_INCLUDE_LIMIT)
            return;
        $__global_config["recursive_count"]++;
        
        if (empty($path))
            $path = self::CONFIG_FILE;
        $config_path = Common::getAbsolutePath($path);
        
        if (!isset($__global_config["yaml"]))
            $__global_config["yaml"] = [];
        
        $yaml_config = yaml_parse_file($config_path);
        
        if ($yaml_config === false)
            throw new \Exception("[FATAL] failed to load " . $config_path . ".");
        $__global_config["yaml"] = array_merge_recursive($__global_config["yaml"], $yaml_config);
        
        if (isset($__global_config["yaml"]["include_dir"])) {
            $include_dir = $__global_config["yaml"]["include_dir"];
            unset($__global_config["yaml"]["include_dir"]);
            $include_files = self::listConfigFile($include_dir);
            foreach ($include_files as $file) {
                self::loadConfig($file);
            }
        }
        
    }
    
    public static function getConfig(string $key): array|string|float|int|bool|null
    {
        global $__global_config;
        $key_array = explode("/", $key);
        if (empty($key_array))
            throw new \Exception("[FATAL](getConfig) config key is empty.");
        $target_config =& $__global_config["yaml"];
        foreach ($key_array as $key_part) {
            if (array_key_exists($key_part, $target_config)) {
                $target_config =& $target_config[$key_part];
            } else {
                throw new \Exception("[FATAL](getConfig) config key ({$key}) is undefined.");
            }
        }
        return $target_config;
    }
    
    public static function setConfig(string $key, array|string|float|int|bool|null $value = null): void
    {
        global $__global_config;
        $key_array = explode("/", $key);
        if (empty($key_array))
            throw new \Exception("[FATAL](setConfig) config key is empty.");
        $target_config =& $__global_config["yaml"];
        foreach ($key_array as $key_part) {
            if (!isset($target_config[$key_part])) {
                $target_config[$key_part] = null;
            }
            $target_config =& $target_config[$key_part];
        }
        $target_config = $value;
    }
    
    public static function getConfigOrSetIfUndefined(string $key,
                                                     array|string|float|int|bool|null $default_value = null)
    : array|string|float|int|bool|null
    {
        try {
            $value = self::getConfig($key);
        } catch (\Exception) {
            self::setConfig($key, $default_value);
            return $default_value;
        }
        return $value;
    }
    
    public static function listConfigFile(string $dir): array
    {
        $result = glob(Common::getAbsolutePath($dir) . "/" . self::CONFIG_FILE_FILTER);
        if ($result === false)
            return [];
        return $result;
    }
    
}