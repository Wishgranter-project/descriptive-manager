<?php

namespace WishgranterProject\DescriptiveManager\Crud;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

/**
 * This will create a copy of the item, unlike Set.
 */
class Add extends Set
{
    /**
     * {@inheritdoc}
     */
    public function commit(): PlaylistItem
    {
        $results = $this->getAllAssociatedItems();
        if (empty($results)) {
            // There is nothing associated, it is a new item, just add and be done.
            $playlist = $this->manager->getPlaylist($this->intendedPlaylistId);
            $playlist->setItem($this->item, $this->position);
            return $this->item;
        }

        foreach ($results as $playlistIdPos => $item) {
            list($playlistId, $pos) = preg_split('/-(?=\d+$)/', $playlistIdPos);
            $this->copyProperties($item, $this->item);

            $playlist = $this->manager->getPlaylist($playlistId);
            $playlist->setItem($item, $pos);
        }

        $copy = $this->item->createCopy();
        $copy->xxxOriginal = $this->item->xxxOriginal ?? $this->item->uuid;

        $this->manager->getPlaylist($this->intendedPlaylistId)->setItem($copy, $this->position);
        return $copy;
    }
}
