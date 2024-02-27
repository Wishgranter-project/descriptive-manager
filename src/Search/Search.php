<?php

namespace WishgranterProject\DescriptiveManager\Search;

use WishgranterProject\DescriptiveManager\PlaylistManager;

class Search
{
    protected PlaylistManager $manager;
    protected ConditionGroup $mainGroup;
    protected array $playlistIds = [];

    public function __construct(PlaylistManager $manager, $operator = 'AND')
    {
        $this->manager = $manager;
        $this->mainGroup = new ConditionGroup($operator);
    }

    public function playlists($playlistIds)
    {
        $this->playlistIds = (array) $playlistIds;
        return $this;
    }

    public function condition($property, $valueToCompare, string $operatorId = '=')
    {
        $this->mainGroup->condition($property, $valueToCompare, $operatorId);
        return $this;
    }

    public function andConditionGroup()
    {
        return $this->mainGroup->andConditionGroup();
    }

    public function orConditionGroup()
    {
        return $this->mainGroup->orConditionGroup();
    }

    public function find(bool $removeDuplicated = true): array
    {
        $results = [];
        foreach ($this->manager->getAllPlaylists() as $playlistId => $playlist) {
            if ($this->playlistIds && !in_array($playlistId, $this->playlistIds)) {
                continue;
            }

            $search = $this->newSearchObject($playlist);
            if ($finds = $search->find()) {
                $results[$playlistId] = $finds;
            }
        }

        return $removeDuplicated
            ? $this->removeDuplicatedResults($results)
            : $results;
    }

    protected function newSearchObject($playlist)
    {
        $playlistSearch = $playlist->search($this->mainGroup->operator);
        $this->fromGroupToGroup($this->mainGroup, $playlistSearch);
        return $playlistSearch;
    }

    protected function fromGroupToGroup($ours, $theirs)
    {
        foreach ($ours->conditions as $con) {
            $theirs->condition($con['property'], $con['valueToCompare'], $con['operatorId']);
        }

        foreach ($ours->groups as $group) {
            $g = $group->operator == 'AND'
                ? $theirs->andConditionGroup()
                : $theirs->orConditionGroup();

            $this->fromGroupToGroup($group, $g);
        }
    }

    protected function removeDuplicatedResults(array $results)
    {
        $uuids = [];

        foreach ($results as $playlistId => $items) {
            foreach ($items as $position => $item) {
                $uuid = $item->xxxOriginal ?? $item->uuid;

                if (! in_array($uuid, $uuids)) {
                    $uuids[] = $uuid;
                    continue;
                }

                unset($results[ $playlistId ][ $position ]);
            }

            if (empty($results[ $playlistId ])) {
                unset($results[ $playlistId ]);
            }
        }

        return $results;
    }
}
