<?php

namespace WishgranterProject\DescriptiveManager\Crud;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

/**
 * This will NOT create a copy of the item, unlike Add.
 */
class Set
{
    /**
     * @var WishgranterProject\DescriptiveManager\PlaylistManager
     *   Playlist manager.
     */
    protected PlaylistManager $manager;

    /**
     * @var string
     *   The playlist to add the item to.
     */
    protected string $intendedPlaylistId;

    /**
     * @var WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item.
     */
    protected PlaylistItem $item;

    /**
     * @var int|null
     *   The intended position within the playlist.
     */
    protected ?int $position = null;

    /**
     * Constructor.
     *
     * @param WishgranterProject\DescriptiveManager\PlaylistManager $manager
     *   Playlist manager.
     * @param string $intendedPlaylistId
     *   The playlist to add the item to.
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $item
     *   The playlist item.
     * @param int|null $position
     *   The intended position within the playlist.
     */
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

    /**
     * Commits the changes to the playlists.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item.
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

        foreach ($results as $playlistId => $items) {
            foreach ($items as $pos => $item) {
                $this->copyProperties($item, $this->item);
            }

            $playlist = $this->manager->getPlaylist($playlistId);

            $position = $item->uuid == $this->item->uuid
                ? $this->position // It is the very subject of this operation.
                : $pos; // a different item.

            $playlist->setItem($item, $position);
        }

        return $this->item;
    }

    /**
     * Retrieves all associated playlist items.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem[]
     *   Playlist items.
     */
    protected function getAllAssociatedItems(): array
    {
        $uuid = $this->item->xxxOriginal ?? $this->item->uuid;
        return $this->manager->getAllAssociatedItems($uuid);
    }

    /**
     * Copies the properties of an item into another one.
     *
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $into
     *   The target.
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $from
     *   The source.
     */
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
