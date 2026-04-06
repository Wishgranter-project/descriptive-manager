<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use WishgranterProject\DescriptiveManager\PlaylistManager;

class SetTest extends Base
{
    public function testUpdateItem()
    {
        $manager = $this->getManager(__FUNCTION__);

        $item = $manager->getItemByUuid('8a8fc087-a112-4630-8778-afa34df8abf6', 'metal');
        $item->title = 'Stolen Waters ( updated )';

        $manager->setItem('metal', $item);

        $item2 = $manager->getItemByUuid('8a8fc087-a112-4630-8778-afa34df8abf6', 'metal');
        $this->assertEquals('Stolen Waters ( updated )', $item2->title);
    }

    public function testUpdateOriginalItemAndRelatedItems()
    {
        $manager = $this->getManager(__FUNCTION__);

        $item = $manager->getItemByUuid('c0299346-65e8-499a-bc1e-9b57ab053787', 'uplifting-metal-songs');
        $item->title = 'Emerald Sword ( updated )';

        $manager->setItem('uplifiting-metal', $item);

        $item2 = $manager->getItemByUuid('c0299346-65e8-499a-bc1e-9b57ab053787', 'uplifting-metal-songs');
        $related = $manager->getItemByUuid('d0c41670-fb92-4c5f-ac13-12c281158fe4', 'metal');
        $this->assertEquals('Emerald Sword ( updated )', $item2->title);
        $this->assertEquals('Emerald Sword ( updated )', $related->title);
    }
}
