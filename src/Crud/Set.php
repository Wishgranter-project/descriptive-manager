<?php

namespace WishgranterProject\DescriptiveManager\Crud;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

/**
 * This will NOT create a copy of the item, unlike Add.
 */
class Set
{
    protected PlaylistManager $manager;

    protected string $intendedPlaylistId;

    protected PlaylistItem $item;

    protected ?int $position = null;

    public function __construct(
        PlaylistManager $manager,
        string $intendedPlaylistId,
        PlaylistItem $item,
        ?int $position = null
    ) {
        $this->manager            = $manager;
        $this->intendedPlaylistId = $intendedPlaylistId;
        $this->item               = $item;
        $this->position           = $position;
    }

    public function commit(): PlaylistItem
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

            $position = $item->uuid == $this->item->uuid
                ? $this->position // It is the ver subject of this operation.
                : $pos; // a different item.

            $playlist->setItem($item, $position);
        }

        return $this->item;
    }

    protected function getAllAssociatedItems(): array
    {
        $baseUuid = $this->item->xxxOriginal ?? $this->item->uuid;
        return $this->manager->getAllAssociatedItems($baseUuid);
    }

    protected function copyProperties(PlaylistItem $into, PlaylistItem $from): void
    {
        $into->empty(['uuid', 'xxxOriginal']);

        $properties = array_filter($from->getSetPropertiesNames(), function ($prp) {
            return !in_array($prp, ['uuid']);
        });

        foreach ($properties as $prp) {
            $into->{$prp} = $from->{$prp};
        }
    }
}
