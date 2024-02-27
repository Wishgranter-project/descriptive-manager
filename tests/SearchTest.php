<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use WishgranterProject\DescriptiveManager\PlaylistManager;

class SearchTest extends Base
{
    public function testSearch()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $search = $manager->search();
        $search->condition('artist', 'Rhapsody of Fire');

        $results = $search->find();
        $total = $this->countResults($results);

        $this->assertEquals(2, $total);
    }

    public function testSearchAssociatedItems()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $results = $manager->getAllAssociatedItems('c0299346-65e8-499a-bc1e-9b57ab053787');
        $total = $this->countResults($results);

        $this->assertEquals(2, $total);
    }

    protected function countResults($results): int
    {
        $total = 0;
        foreach ($results as $playlistId => $items) {
            $total += count($items);
        }
        return $total;
    }
}
