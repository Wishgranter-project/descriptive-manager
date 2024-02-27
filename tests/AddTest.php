<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

class AddTest extends Base
{
    public function testAddNewItem()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $item = new PlaylistItem();
        $item->title = 'Made of Steel';
        $item->artist = 'Bloodbound';

        $manager->addItem('template-metal', $item);

        $fifth = $manager->getItem('template-metal', 4);

        $this->assertEquals('Made of Steel', $fifth->title);
    }

    public function testCreateCopyInTheSamePlaylist()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $original->title = 'Cry of a restless soul ( updated )';

        $manager->addITem('template-metal', $original);

        $copy = $manager->getItem('template-metal', 4);
        $this->assertEquals('Cry of a restless soul ( updated )', $copy->title);
        $this->assertEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->xxxOriginal);
        $this->assertNotEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->uuid);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $this->assertEquals('Cry of a restless soul ( updated )', $original->title);
    }

    public function testCreateCopyIntoDifferentPlaylist()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $original->title = 'Cry of a restless soul ( updated )';
        $manager->addITem('template-uplifting-metal-songs', $original);

        $copy = $manager->getItem('template-uplifting-metal-songs', 12);
        $this->assertEquals('Cry of a restless soul ( updated )', $copy->title);
        $this->assertEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->xxxOriginal);
        $this->assertNotEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $copy->uuid);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $this->assertEquals('Cry of a restless soul ( updated )', $original->title);
    }
}
