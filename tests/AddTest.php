<?php 
namespace AdinanCenci\DescriptiveManager\Tests;

use AdinanCenci\DescriptiveManager\PlaylistManager;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;

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

    public function testAddItemAgain() 
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $item = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $item->title = 'Cry of a restless soul ( updated )';

        $manager->addITem('template-metal', $item);

        $lastItem = $manager->getItem('template-metal', 4);
        $this->assertEquals('Cry of a restless soul ( updated )', $lastItem->title);
        $this->assertEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $lastItem->xxxOriginal);
        $this->assertNotEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $lastItem->uuid);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $this->assertEquals('Cry of a restless soul ( updated )', $original->title);
    }

    public function testAddItemToADifferentPlaylist() 
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $item = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $item->title = 'Cry of a restless soul ( updated )';

        $manager->addITem('template-uplifting-metal-songs', $item);

        $lastItem = $manager->getItem('template-uplifting-metal-songs', 12);
        $this->assertEquals('Cry of a restless soul ( updated )', $lastItem->title);
        $this->assertEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $lastItem->xxxOriginal);
        $this->assertNotEquals('1f2e56de-22d8-41a2-8efb-54aebb8b502f', $lastItem->uuid);

        $original = $manager->getItemByUuid('1f2e56de-22d8-41a2-8efb-54aebb8b502f', 'template-metal');
        $this->assertEquals('Cry of a restless soul ( updated )', $original->title);
    }

}
