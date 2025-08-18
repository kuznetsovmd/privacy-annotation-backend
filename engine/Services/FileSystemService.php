<?php

namespace Engine\Services;

use PharData;

use Engine\Service;
use ZipArchive;
use Engine\Config;

/**
 * FileSystemService.php
 *
 * Service for sessions management.
 */
class FileSystemService extends Service
{

    /**
     * Resources path.
     *
     * @access private
     * @var string
     */
    private string $_resources;

    /**
     * Constructor of service class.
     */
    public function __construct()
    {
        $this->_resources = Config::get('env')['resources'];
    }

    /**
     * Resolves related path.
     *
     * @access protected
     * @param string $path - Directory to resolve
     * @return string
     */
    protected function resolve(string $path): string
    {
        return "{$this->_resources}/$path";
    }

    /**
     * Gives file to download.
     *
     * @access protected
     * @param string $file - Path of file
     * @param bool $rm - Remove after download
     * @return bool
     */
    protected function download(string $file, bool $rm = false): bool
    {
        $file_path = $this->resolve($file);
        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file_path));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            ob_end_flush();
            readfile($file_path);
            if ($rm) return unlink($file_path);
            return true;
        }
        return false;
    }

    /**
     * Adds resource to resources directory.
     *
     * @access protected
     * @param string $tmp - Old location
     * @param string $file - Location in resources
     */
    protected function resource(string $tmp, string $file): bool
    {
        if (is_file($tmp)) return move_uploaded_file($tmp, "{$this->_resources}/$file");
        return false;
    }

    /**
     * Unzips archive.
     *
     * @access protected
     * @param string $file - Which file
     * @param string $path - Where to
     * @param bool $rm - Remove after unzip
     * @return bool
     */
    protected function unzip(string $file, string $path, bool $rm = true): bool
    {
        $file_path = $this->resolve($file);
        if (!is_file($file_path)) return false;
        $zip = new ZipArchive;
        if ($zip->open($file_path) === true) {
            $zip->extractTo($this->resolve($path));
            $zip->close();
        }
        if ($rm) return unlink($file_path);
        return true;
    }

    /**
     * Untars archive.
     *
     * @access protected
     * @param string $file - Which file
     * @param string $path - Where to
     * @return bool
     */
    protected function untar(string $file, string $path, bool $rm = false)
    {
        $file_path = $this->resolve($file);
        $phar = new PharData($file_path);
        $phar->decompress();
        $tarPath = preg_replace('/\.gz$/', '', $file_path);
        $tar = new PharData($tarPath);
        $tar->extractTo($this->resolve($path));

        if ($rm) return unlink($tarPath);
        return true;
    }

    /**
     * Reads file content.
     *
     * @access protected
     * @param string $file - File to read
     * @return null|string
     */
    protected function read(string $file): ?string
    {
        $file_path = $this->resolve($file);
        if (is_file($file_path)) return file_get_contents($file_path);
        return null;
    }

    /**
     * Writes content to file.
     *
     * @access protected
     * @param string $file - Directory to delete
     * @param string $content - Recursive argument
     * @return bool
     */
    protected function write(string $file, string $content): bool
    {
        return file_put_contents($this->resolve($file), $content);
    }

    /**
     * Removes a file.
     *
     * @access protected
     * @param string $file - File to delete
     * @return bool
     */
    protected function rm(string $file): bool
    {
        $file_path = $this->resolve($file);
        if (is_file($file_path)) return unlink($file_path);
        return false;
    }

    /**
     * Recursively removes directory content.
     *
     * @access protected
     * @param string $directory - Directory to delete
     * @param bool $resolve - Resolve path
     * @return bool
     */
    protected function rmdir(string $directory, bool $resolve = true): bool
    {
        $dir = $resolve ? $this->resolve($directory) : $directory;
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
          (is_dir("$dir/$file")) ? $this->rmdir("$dir/$file", false) : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
    

}
