<?php 
namespace AdinanCenci\DescriptiveManager\Crud;

use AdinanCenci\DescriptiveManager\PlaylistManager;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;

class Set 
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

    public function commit() : void
    {
        $results = $this->getAllAssociatedItems();
        if (empty($results)) {
            // Simply add ...
            $playlist = $this->manager->getPlaylist($this->intendedPlaylistId);
            $playlist->setItem($this->item, $this->position);
        }

        foreach ($results as $playlistId => $items) {
            foreach ($items as $position => $item) {
                $this->copy($item, $this->item);
            }

            $playlist = $this->manager->getPlaylist($playlistId);
            $playlist->setItem($item, $item->uuid == $this->item->uuid ? $this->position : $position);
        }
    }

    protected function getAllAssociatedItems() : array
    {
        $baseUuid = $this->item->xxxOriginal ?? $this->item->uuid;
        return $this->manager->getAllAssociatedItems($baseUuid);
    }

    protected function copy(PlaylistItem $into, PlaylistItem $from) : void
    {
        $properties = array_filter($from->getSetPropertiesNames(), function($prp) 
        {
            return !in_array($prp, ['uuid']);
        });

        foreach ($properties as $prp) {
            $into->{$prp} = $from->{$prp};
        }
    }
}
