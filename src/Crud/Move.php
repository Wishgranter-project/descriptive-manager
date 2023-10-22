<?php 
namespace AdinanCenci\DescriptiveManager\Crud;

use AdinanCenci\DescriptiveManager\PlaylistManager;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;

class Move 
{
    protected PlaylistManager $manager;

    protected string $intendedPlaylistId;

    protected PlaylistItem $item;

    protected ?int $position = null;

    public function __construct(PlaylistManager $manager, string $intendedPlaylistId, PlaylistItem $item, ?int $position = null) 
    {
        $this->manager            = $manager;
        $this->intendedPlaylistId = $intendedPlaylistId;
        $this->item               = $item;
        $this->position           = $position;
    }

    public function commit() : bool
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

        return true;
    }
}
