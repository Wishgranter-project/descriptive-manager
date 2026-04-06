<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use PHPUnit\Framework\TestCase;
use WishgranterProject\DescriptiveManager\PlaylistManager;

abstract class Base extends TestCase
{
    /**
     * Resets a test.
     *
     * Delete files and make copies of templates so we may repeat a test.
     *
     * @param string $directoryName.
     *   Basename of a directory.
     *
     * @return string
     *   Relative path to the directory.
     */
    protected function resetTest(string $directoryName)
    {
        $directory = './tests/files/' . $directoryName . '/';
        $this->createDirectory($directory);
        $this->emptyDirectory($directory);
        $this->copyTemplateFilesTo($directory);
        return $directory;
    }

    /**
     * Creates a directory if it does not exist already.
     *
     * @param string $directory
     *   Relative path for the directory.
     */
    protected function createDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * Empties a directory of test related files.
     *
     * @param string $directory
     *   Relative path to the directory.
     */
    protected function emptyDirectory(string $directory): void
    {
        $entries = scandir($directory);

        foreach ($entries as $entry) {
            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            if (substr($entry, -5) != '.dpls') {
                continue;
            }

            unlink($directory . '/' . $entry);
        }
    }

    /**
     * Copy the template files to a given directory.
     *
     * @param string $directory
     *   Direcoty to copy the files to.
     */
    protected function copyTemplateFilesTo(string $directory): void
    {
        foreach (scandir('./tests/') as $entry) {
            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            if (substr($entry, -5) != '.dpls') {
                continue;
            }

            $name = str_replace('template-', '', $entry);

            copy('./tests/' . $entry, $directory . $name);
        }
    }

    /**
     * Get a unique manager for a given function we want to test.
     *
     * @param string $functionName
     *   The name of the test function.
     *
     * @return WishgranterProject\DescriptiveManager\PlaylistManager
     *   Playlist manager.
     */
    protected function getManager(string $functionName)
    {
        $directory = $this->resetTest($functionName);
        $manager = new PlaylistManager($directory);

        return $manager;
    }
}
