<?php

namespace WishgranterProject\DescriptiveManager\Search;

use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\Search as PlaylistSearch;

class Search
{
    /**
     * @var WishgranterProject\DescriptiveManager\PlaylistManager
     *   Playlist manager.
     */
    protected PlaylistManager $manager;

    /**
     * @var WishgranterProject\DescriptiveManager\Search\ConditionGroup
     *   Main condition group.
     */
    protected ConditionGroup $mainGroup;

    /**
     * @var string[]
     *   Playlist ids to conduct the search on.
     */
    protected array $playlistIds = [];

    /**
     * Constructor.
     *
     * @param WishgranterProject\DescriptiveManager\PlaylistManager $manager
     *   Playlists manager.
     * @param string $operator
     *   Logic operator.
     */
    public function __construct(PlaylistManager $manager, $operator = 'AND')
    {
        $this->manager = $manager;
        $this->mainGroup = new ConditionGroup($operator);
    }

    /**
     * Sets search's playlists pool.
     *
     * @param string|array $playlistIds
     *   Playlist ids.
     *
     * @return WishgranterProject\DescriptiveManager\Search\Search
     *   Returns itself.
     */
    public function playlists($playlistIds): Search
    {
        $this->playlistIds = (array) $playlistIds;
        return $this;
    }

    /**
     * Adds a condition to the search.
     *
     * @param string|array $property
     *   The property of the playlist we are aiming for.
     * @param array|null|string|int $valueToCompare
     *   The value to compare.
     * @param string $operatorId
     *   The comparison operator.
     */
    public function condition($property, $valueToCompare, string $operatorId = '='): Search
    {
        $this->mainGroup->condition($property, $valueToCompare, $operatorId);
        return $this;
    }

    /**
     * Adds a condition group.
     *
     * @return WishgranterProject\DescriptiveManager\Search\ConditionGroup
     *   The new group.
     */
    public function andConditionGroup()
    {
        return $this->mainGroup->andConditionGroup();
    }

    /**
     * Adds a condition group.
     *
     * @return WishgranterProject\DescriptiveManager\Search\ConditionGroup
     *   The new group.
     */
    public function orConditionGroup()
    {
        return $this->mainGroup->orConditionGroup();
    }

    /**
     * Executes the search and returns the results.
     *
     * @param bool $removeDuplicated
     *   Removes duplicated playlist items.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem[]
     *   Playlist items.
     */
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

    /**
     * Returns a playlist search object.
     *
     * @param WishgranterProject\DescriptivePlaylist\Playlist
     *   The playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\Search
     *   Search object.
     */
    protected function newSearchObject($playlist): PlaylistSearch
    {
        $playlistSearch = $playlist->search($this->mainGroup->operator);
        $this->fromGroupToGroup($this->mainGroup, $playlistSearch);
        return $playlistSearch;
    }

    /**
     * Copy search conditions over.
     *
     * @param WishgranterProject\DescriptiveManager\Search\ConditionGroup $ours
     *   Manager group.
     * @param WishgranterProject\DescriptivePlaylist\Search $theirs
     *   Playlist search object.
     */
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

    /**
     * Removes duplicated results.
     *
     * @param array $results
     *   Array of playlist itemms.
     *
     * @return array
     *   The array with duplicated results removed.
     */
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
