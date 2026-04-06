<?php

namespace WishgranterProject\DescriptiveManager\Tests;

use WishgranterProject\DescriptiveManager\PlaylistManager;

class SearchTest extends Base
{
    public function testSearch()
    {
        $manager = $this->getManager(__FUNCTION__);

        $search = $manager->search();
        $search->condition('artist', 'Rhapsody of Fire');

        $results = $search->find();
        $total = count($results);

        $this->assertEquals(2, $total);
    }

    public function testSearchAssociatedItems()
    {
        $manager = $this->getManager(__FUNCTION__);

        $results = $manager->getAllAssociatedItems('c0299346-65e8-499a-bc1e-9b57ab053787');
        $total = count($results);

        $this->assertEquals(2, $total);
    }

    public function testOrderResults()
    {
        $manager = $this->getManager(__FUNCTION__);

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
        $manager = $this->getManager(__FUNCTION__);

        $search1 = $manager->search();
        $search1->orderRandomly();
        $results1 = $search1->find();

        $search2 = $manager->search();
        $search2->orderRandomly();
        $results2 = $search2->find();

        $equal = json_encode(array_keys($results1)) == json_encode(array_keys($results2));

        $this->assertFalse($equal);
    }

    public function testOrderResultsRandomlyWithSeed()
    {
        $manager = $this->getManager(__FUNCTION__);

        $seed = 'foo-bar' . rand(0, 1000);

        $search1 = $manager->search();
        $search1->orderRandomly($seed);
        $results1 = $search1->find();

        $search2 = $manager->search();
        $search2->orderRandomly($seed);
        $results2 = $search2->find();

        $equal = json_encode(array_keys($results1)) == json_encode(array_keys($results2));

        $this->assertTrue($equal);
    }
}
