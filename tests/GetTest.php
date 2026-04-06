<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use WishgranterProject\DescriptiveManager\PlaylistManager;

class GetTest extends Base
{
    public function testGetItemByUuid()
    {
        $manager = $this->getManager(__FUNCTION__);

        $item = $manager->getItemByUuid('8a8fc087-a112-4630-8778-afa34df8abf6');
        $this->assertEquals('Stolen Waters', $item->title);
    }

    public function testGetItemThatDoesNotExist()
    {
        $manager = $this->getManager(__FUNCTION__);

        $item = $manager->getItemByUuid('does-not-exist');
        $this->assertEquals(null, $item);
    }
}
