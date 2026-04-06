<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

class MoveTest extends Base
{
    public function testMoveItemFromOnePlaylistToAnother()
    {
        $manager = $this->getManager(__FUNCTION__);

        $targetSong = $manager->findItemByUuid('c65f0dce-07f6-417a-9be7-0be65071adf8', $originalPlaylistId);

        $manager->moveItem('uplifting-metal-songs', $targetSong);

        //------------------

        $targetSongAfterBeingMoved = $manager->findItemByUuid('c65f0dce-07f6-417a-9be7-0be65071adf8', $newPlaylistId);

        $this->assertEquals('metal', $originalPlaylistId);
        $this->assertEquals('uplifting-metal-songs', $newPlaylistId);
    }

    public function testMoveItemFromOnePlaylistToTheMiddleOfAnother()
    {
        $manager = $this->getManager(__FUNCTION__);

        $targetSong = $manager->findItemByUuid('c65f0dce-07f6-417a-9be7-0be65071adf8', $originalPlaylistId);

        $manager->moveItem('uplifting-metal-songs', $targetSong, 5);

        //------------------

        $targetSongAfterBeingMoved = $manager->findItemByUuid('c65f0dce-07f6-417a-9be7-0be65071adf8', $newPlaylistId, $position);

        $this->assertEquals('metal', $originalPlaylistId);
        $this->assertEquals('uplifting-metal-songs', $newPlaylistId);
        $this->assertEquals(5, $position);
    }
}
