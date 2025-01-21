<?php

namespace WishgranterProject\DescriptiveManager\Crud;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

class Move extends Set
{
    /**
     * {@inheritdoc}
     */
    public function commit(): PlaylistItem
    {
        $item = $this->manager->findItemByUuid($this->item->uuid, $originalPlaylistId);

        if (!$newPlaylist = $this->manager->getPlaylist($this->intendedPlaylistId)) {
            throw new \LogicException('Playlist ' . $this->intendedPlaylistId . ' does not exist ');
        }

        if (!$item) {
            throw new \LogicException('Item ' . $this->itemUuid . ' does not exist');
        }

        if ($originalPlaylistId == $this->intendedPlaylistId) {
            throw new \LogicException('Item ' . $this->itemUuid . ' already belongs to playlist ' . $this->itemUuid);
        }

        $originalPlaylist = $this->manager->getPlaylist($originalPlaylistId);
        $originalPlaylist->deleteItem($item);

        $newPlaylist->setItem($item, $this->position);

        return $this->item;
    }
}
