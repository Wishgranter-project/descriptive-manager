<?php 
namespace AdinanCenci\DescriptiveManager\Crud;

use AdinanCenci\DescriptiveManager\PlaylistManager;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;

/**
 * This will create a copy of the item, unlike Set.
 */
class Add extends Set 
{
    public function commit() : PlaylistItem
    {
        $results = $this->getAllAssociatedItems();
        if (empty($results)) {
            // There is nothing associated, it is a new item, just add and be done.
            $playlist = $this->manager->getPlaylist($this->intendedPlaylistId);
            $playlist->setItem($this->item, $this->position);
            return $this->item;
        }

        foreach ($results as $playlistId => $items) {
            foreach ($items as $pos => $item) {
                $this->copyProperties($item, $this->item);
            }

            $playlist = $this->manager->getPlaylist($playlistId);
            $playlist->setItem($item, $pos);
        }

        $copy = $this->item->createCopy();
        $copy->xxxOriginal = $this->item->xxxOriginal ?? $this->item->uuid;

        $this->manager->getPlaylist($this->intendedPlaylistId)->setItem($copy, $this->position);
        return $copy;
    }
}
