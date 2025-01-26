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
        $total = count($results);

        $this->assertEquals(2, $total);
    }

    public function testSearchAssociatedItems()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $results = $manager->getAllAssociatedItems('c0299346-65e8-499a-bc1e-9b57ab053787');
        $total = count($results);

        $this->assertEquals(2, $total);
    }

    public function testOrderResults()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $search = $manager->search();
        $search->orderBy('title', 'ASC');

        $results = $search->find();

        $first = reset($results);
        $last = end($results);

        $this->assertEquals('Cry of a restless soul', $first->title);
        $this->assertEquals('Wisdom of the Kings', $last->title);
    }

    public function testOrderResultsRandomly()
    {
        $directory = $this->resetTest(__FUNCTION__);
        $manager = new PlaylistManager($directory);

        $search1 = $manager->search();
        $search1->orderBy('RAND()');
        $results1 = $search1->find();

        $search2 = $manager->search();
        $search2->orderBy('RAND()');
        $results2 = $search2->find();

        $equal = json_encode(array_keys($results1)) == json_encode(array_keys($results2));

        $this->assertFalse($equal);
    }
}
