<?php 
namespace AdinanCenci\DescriptiveManager\Crud;

use AdinanCenci\DescriptiveManager\PlaylistManager;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;

class Add extends Set 
{
    public function commit() : void
    {
        $results = $this->getAllAssociatedItems();
        if (empty($results)) {
            // Simply add ...
            $playlist = $this->manager->getPlaylist($this->intendedPlaylistId);
            $playlist->setItem($this->item, $this->position);
            return;
        }

        foreach ($results as $playlistId => $items) {
            foreach ($items as $position => $item) {
                $this->copy($item, $this->item);
            }

            $playlist = $this->manager->getPlaylist($playlistId);
            $playlist->setItem($item, $position);
        }

        $copy = new PlaylistItem();
        $this->copy($copy, $this->item);
        $copy->xxxOriginal = $this->item->xxxOriginal ?? $this->item->uuid;

        $this->manager->getPlaylist($this->intendedPlaylistId)->setItem($copy, $this->position);
    }
}
