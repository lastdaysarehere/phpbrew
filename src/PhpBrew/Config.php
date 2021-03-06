<?php
namespace PhpBrew;

use Exception;
use Symfony\Component\Yaml\Yaml;

class Config
{
    protected static $currentPhpVersion = null;

    public static function getPhpbrewHome()
    {
        if ($custom = getenv('PHPBREW_HOME')) {
            return $custom;
        }

        if ($home = getenv('HOME')) {
            return $home . DIRECTORY_SEPARATOR . '.phpbrew';
        }

        throw new Exception('Environment variable PHPBREW_HOME or HOME is required');
    }

    public static function getPhpbrewRoot()
    {
        if ($root = getenv('PHPBREW_ROOT')) {
            return $root;
        }

        if ($home = getenv('HOME')) {
            return $home . DIRECTORY_SEPARATOR . '.phpbrew';
        }

        throw new Exception('Environment variable PHPBREW_ROOT is required');
    }

    /**
     * Variants is private, so we use HOME path.
     */
    static public function getVariantsDir()
    {
        return self::getPhpbrewHome() . DIRECTORY_SEPARATOR . 'variants';
    }

    /**
     * php(s) could be global, so we use ROOT path.
     */
    static public function getBuildDir()
    {
        return self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'build';
    }


    static public function getCurrentBuildDir() {
        return self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . self::getCurrentPhpName();
    }

    static public function getDistFileDir()
    {
        $dir =  self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'distfiles';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    static public function getTempFileDir()
    {
        $dir =  self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'tmp';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    static public function getPHPReleaseListPath()
    {
        // Release list from php.net
        return self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'php-releases.json';
    }

    /**
     * A build prefix is the prefix we specified when we install the PHP.
     *
     * @return string
     */
    static public function getInstallPrefix()
    {
        return self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'php';
    }

    static public function getVersionInstallPrefix($version)
    {
        return self::getInstallPrefix() . DIRECTORY_SEPARATOR . $version;
    }


    /**
     * XXX: This method should be migrated to PhpBrew\Build class.
     *
     * @param string $version
     *
     * @return string
     */
    static public function getVersionEtcPath($version)
    {
        return self::getVersionInstallPrefix($version) . DIRECTORY_SEPARATOR . 'etc';
    }

    static public function getVersionBinPath($version)
    {
        return self::getVersionInstallPrefix($version) . DIRECTORY_SEPARATOR . 'bin';
    }

    static public function getInstalledPhpVersions()
    {
        $versions = array();
        $path = self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'php';

        if (file_exists($path) && $fp = opendir($path)) {
            while (($item = readdir($fp)) !== false) {
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if (file_exists($path . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'php')) {
                    $versions[] = $item;
                }
            }

            closedir($fp);
        }

        return $versions;
    }

    static public function getCurrentPhpConfigBin() 
    {
        return self::getCurrentPhpDir() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'php-config';
    }

    static public function getCurrentPhpizeBin() 
    {
        return self::getCurrentPhpDir() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpize';
    }

    /**
     * XXX: This method should be migrated to PhpBrew\Build class.
     */
    static public function getCurrentPhpConfigScanPath()
    {
        return self::getCurrentPhpDir() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'db';
    }

    static public function getCurrentPhpDir()
    {
        return self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . self::getCurrentPhpName();
    }

    // XXX: needs to be removed.
    static public function useSystemPhpVersion()
    {
        self::$currentPhpVersion = null;
    }

    // XXX: needs to be removed.
    static public function setPhpVersion($phpVersion)
    {
        self::$currentPhpVersion = 'php-'.$phpVersion;
    }

    static public function getCurrentPhpName()
    {
        if (self::$currentPhpVersion !== null) {
            return self::$currentPhpVersion;
        }
        return getenv('PHPBREW_PHP');
    }

    static public function getLookupPrefix()
    {
        return getenv('PHPBREW_LOOKUP_PREFIX');
    }

    static public function getCurrentPhpBin()
    {
        return getenv('PHPBREW_PATH');
    }

    static public function getConfigParam($param = null)
    {
        $configFile = self::getPhpbrewRoot() . DIRECTORY_SEPARATOR . 'config.yaml';
        $yaml = Yaml::parse($configFile);

        if (is_array($yaml)) {
            if ($param === null) {
                return $yaml;
            } elseif ($param != null && isset($yaml[$param])) {
                return $yaml[$param];
            }
        }

        return array();
    }

    static public function initDirectories($buildName = NULL) {
        $dirs[] = self::getPhpbrewHome();
        $dirs[] = self::getPhpbrewRoot();
        $dirs[] = self::getVariantsDir();
        $dirs[] = self::getBuildDir();
        $dirs[] = self::getDistFileDir();
        if ($buildName) {
            $dirs[] = self::getCurrentBuildDir($buildName);
            $dirs[] = self::getCurrentBuildDir($buildName) . DIRECTORY_SEPARATOR . 'ext';
            $dirs[] = self::getInstallPrefix($buildName) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'db';
        }
        foreach($dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

}
