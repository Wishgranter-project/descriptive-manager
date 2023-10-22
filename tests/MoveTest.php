<?php 
namespace AdinanCenci\DescriptiveManager\Tests;

use AdinanCenci\DescriptiveManager\PlaylistManager;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;

class MoveTest extends Base
{
    public function testMoveItem() 
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $viperSong = $manager->findItemByUuid('c65f0dce-07f6-417a-9be7-0be65071adf8', $originalPlaylistId);

        $manager->moveItem('template-uplifting-metal-songs', $viperSong);

        $viperSongAfterMoved = $manager->findItemByUuid('c65f0dce-07f6-417a-9be7-0be65071adf8', $newPlaylistId);

        $this->assertEquals('template-uplifting-metal-songs', $newPlaylistId);
    }
}
