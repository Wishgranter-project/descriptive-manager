<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

class AddTest extends Base
{
    public function testAddCompletelyNewItem()
    {
        $manager = $this->getManager(__FUNCTION__);

        $item = new PlaylistItem();
        $item->title = 'Made of Steel';
        $item->artist = 'Bloodbound';

        $manager->addItem('metal', $item);

        //------------------

        $fifthItem = $manager->getItem('metal', 4);
        $this->assertEquals('Made of Steel', $fifthItem->title);
    }

    public function testAddCompletelyNewItemInTheMiddleOfThePlaylist()
    {
        $manager = $this->getManager(__FUNCTION__);

        $item = new PlaylistItem();
        $item->title = 'Made of Steel';
        $item->artist = 'Bloodbound';

        $manager->addItem('metal', $item, 2);

        //------------------

        $thirdItem = $manager->getItem('metal', 2);
        $this->assertEquals('Made of Steel', $thirdItem->title);
    }

    public function testCreateCopyInTheSamePlaylist()
    {
        $manager = $this->getManager(__FUNCTION__);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'metal');
        $original->title = 'Cry of a restless soul ( updated )';

        $manager->addITem('metal', $original);

        //------------------

        $copy = $manager->getItem('metal', 4);
        $itemUpdated = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'metal');

        $this->assertEquals('Cry of a restless soul ( updated )', $copy->title);
        $this->assertEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->xxxOriginal);
        $this->assertNotEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->uuid);

        $this->assertEquals('Cry of a restless soul ( updated )', $itemUpdated->title);
    }

    public function testCreateCopyIntoDifferentPlaylist()
    {
        $manager = $this->getManager(__FUNCTION__);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'metal');
        $original->title = 'Cry of a restless soul ( updated )';
        $manager->addITem('uplifting-metal-songs', $original);

        //------------------

        $copy = $manager->getItem('uplifting-metal-songs', 12);
        $itemUpdated = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'metal');

        $this->assertEquals('Cry of a restless soul ( updated )', $copy->title);
        $this->assertEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->xxxOriginal);
        $this->assertNotEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->uuid);

        $this->assertEquals('Cry of a restless soul ( updated )', $itemUpdated->title);
    }

    public function testCreateCopyOfACopy()
    {
        $manager = $this->getManager(__FUNCTION__);

        $copy = $manager->getItemByUuid('d0c41670-fb92-4c5f-ac13-12c281158fe4', 'metal');
        $this->assertEquals('c0299346-65e8-499a-bc1e-9b57ab053787', $copy->xxxOriginal);
        $this->assertEquals('Emerald Sword', $copy->title);

        //------------------

        $manager->createPlaylist('Newest Playlist', null, 'newest-playlist');

        //------------------

        $copy->title = 'Emerald Sword ( updated )';
        $manager->addITem('newest-playlist', $copy);

        //------------------

        $original = $manager->getItemByUuid('c0299346-65e8-499a-bc1e-9b57ab053787', 'uplifting-metal-songs');
        $updatedCopy = $manager->getItemByUuid('d0c41670-fb92-4c5f-ac13-12c281158fe4', 'metal');
        $copyOfTheCopy = $manager->getItem('newest-playlist', 0);

        $this->assertEquals('Emerald Sword ( updated )', $original->title);
        $this->assertEquals('Emerald Sword ( updated )', $updatedCopy->title);
        $this->assertEquals('Emerald Sword ( updated )', $copyOfTheCopy->title);

        $this->assertEquals('c0299346-65e8-499a-bc1e-9b57ab053787', $original->uuid);
        $this->assertEquals('c0299346-65e8-499a-bc1e-9b57ab053787', $updatedCopy->xxxOriginal);
        $this->assertEquals('c0299346-65e8-499a-bc1e-9b57ab053787', $copyOfTheCopy->xxxOriginal);

        $this->assertEquals(null, $original->xxxOriginal);
    }
}
