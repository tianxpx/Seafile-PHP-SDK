<?php

namespace Seafile\Resource;

use \Seafile\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile multi file/folder operations.
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Resource
 * @author    Christoph Haas <christoph.h@sprinternet.at>
 * @copyright 2015 Christoph Haas <christoph.h@sprinternet.at>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Multi extends AbstractResource
{
    /**
     * Move multiple files or folders
     *
     * @param LibraryType $srcLibrary       Source library object
     * @param array       $srcPaths         Array with file/folder paths
     * @param LibraryType $dstLibrary       Destination library object
     * @param String      $dstDirectoryPath Destination directory Path
     * @return bool
     */
    public function move(LibraryType $srcLibrary, array $srcPaths, LibraryType $dstLibrary, $dstDirectoryPath)
    {
        // do not allow empty paths
        if (empty($srcPaths) || empty($dstDirectoryPath)) {
            return false;
        }

        // get the source folder path
        // this path must be the same for all files!
        $srcFolderPath = dirname($srcPaths[0]);

        $dstFileNames = $this->preparePaths($srcFolderPath, $srcPaths);

        if (empty($dstFileNames)) {
            return false;
        }

        $srcFolderPath = str_replace("\\", "/", $srcFolderPath); // windows compatibility

        $uri = sprintf(
            '%s/repos/%s/fileops/move/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $srcLibrary->id,
            $srcFolderPath
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'file_names',
                        'contents' => $dstFileNames
                    ],
                    [
                        'name' => 'dst_repo',
                        'contents' => $dstLibrary->id
                    ],
                    [
                        'name' => 'dst_dir',
                        'contents' => $dstDirectoryPath
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * Copy multiple files or folders
     *
     * @param LibraryType $srcLibrary       Source library object
     * @param array       $srcPaths         Array with file/folder paths (they must be in the same folder)
     * @param LibraryType $dstLibrary       Destination library object
     * @param String      $dstDirectoryPath Destination directory Path
     * @return bool
     */
    public function copy(LibraryType $srcLibrary, array $srcPaths, LibraryType $dstLibrary, $dstDirectoryPath)
    {
        // do not allow empty paths
        if (empty($srcPaths) || empty($dstDirectoryPath)) {
            return false;
        }

        // get the source folder path
        // this path must be the same for all files!
        $srcFolderPath = dirname($srcPaths[0]);

        $dstFileNames = $this->preparePaths($srcFolderPath, $srcPaths);

        if (empty($dstFileNames)) {
            return false;
        }

        $srcFolderPath = str_replace("\\", "/", $srcFolderPath); // windows compatibility

        $uri = sprintf(
            '%s/repos/%s/fileops/copy/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $srcLibrary->id,
            $srcFolderPath
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'file_names',
                        'contents' => $dstFileNames
                    ],
                    [
                        'name' => 'dst_repo',
                        'contents' => $dstLibrary->id
                    ],
                    [
                        'name' => 'dst_dir',
                        'contents' => $dstDirectoryPath
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * check source folders paths and build the file_names string
     *
     * @param string $folder    Folder path
     * @param array  $paths     Paths of files
     * @param string $fileNames Optional file names
     * @return string
     */
    protected function preparePaths($folder, array $paths, $fileNames = '')
    {
        foreach ($paths as $path) {
            if (dirname($path) != $folder) {
                return ''; // all source paths must be the same
            }
            if ($fileNames != '') {
                $fileNames .= ':';
            }
            $fileNames .= basename($path);
        }

        return $fileNames;
    }

    /**
     * Delete multiple files or folders
     *
     * @param LibraryType $library Library object
     * @param array       $paths   Array with file and folder paths (they must be in the same folder)
     * @return bool
     */
    public function delete(LibraryType $library, array $paths)
    {
        // do not allow empty paths
        if (empty($paths)) {
            return false;
        }

        // get the folder path
        // this path must be the same for all files!
        $folderPath = dirname($paths[0]);

        $fileNames = $this->preparePaths($folderPath, $paths);

        if (empty($fileNames)) {
            return false;
        }

        $folderPath = str_replace("\\", "/", $folderPath); // windows compatibility

        $uri = sprintf(
            '%s/repos/%s/fileops/delete/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id,
            $folderPath
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'file_names',
                        'contents' => $fileNames
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }
}
