<?php 
namespace AdinanCenci\DescriptiveManager\Tests;

use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    protected function resetTest($directoryName) 
    {
        $directory = './tests/files/' . $directoryName . '/';
        $this->createDirectory($directory);
        $this->emptyDirectory($directory);
        $this->copyFilesTo($directory);
        return $directory;
    }

    protected function createDirectory($directory) 
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    protected function emptyDirectory($directory) 
    {
        $entries = scandir($directory);

        foreach ($entries as $e) {
            if (in_array($e, ['.', '..'])) {
                continue;
            }

            unlink($directory . '/' . $e);
        }
    }

    protected function copyFilesTo($directory) 
    {
        copy('./tests/template-metal.dpls', $directory . 'template-metal.dpls');
        copy('./tests/template-uplifting-metal-songs.dpls', $directory . 'template-uplifting-metal-songs.dpls');
    }

}
