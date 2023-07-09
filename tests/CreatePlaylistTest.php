<?php 
namespace AdinanCenci\DescriptiveManager\Tests;

use AdinanCenci\DescriptiveManager\PlaylistManager;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;

class CreatePlaylistTest extends Base
{
    public function testCreateNewPlaylistFilenameBasedOffAString() 
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $playlist = $manager->createPlaylist('random', null, 'Foo Bar');
        $basename = basename($playlist->fileName);

        $this->assertEquals('foo-bar.dpls', $basename);
    }

    public function testCreateNewPlaylistFilenameBasedOffAStringTwice() 
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $playlist1 = $manager->createPlaylist('random', null, 'Foo Bar');
        $basename1 = basename($playlist1->fileName);

        $playlist2 = $manager->createPlaylist('random', null, 'Foo Bar');
        $basename2 = basename($playlist2->fileName);

        $this->assertEquals('foo-bar.dpls', $basename1);
        $this->assertEquals('foo-bar-2.dpls', $basename2);
    }

    public function testCreateNewPlaylistWithRandomFilename() 
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $playlist = $manager->createPlaylist('random');
        $basename = basename($playlist->fileName);
        $strlen   = strlen($basename);

        $this->assertEquals(41, $strlen);
    }

}
