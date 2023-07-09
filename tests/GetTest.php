<?php 
namespace AdinanCenci\DescriptiveManager\Tests;

use AdinanCenci\DescriptiveManager\PlaylistManager;

class GetTest extends Base
{
    public function testGetItemByUuid() 
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $item = $manager->getItemByUuid('8a8fc087-a112-4630-8778-afa34df8abf6');        
        $this->assertEquals('Stolen Waters', $item->title);
    }
}
