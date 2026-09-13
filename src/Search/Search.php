<?php

namespace WishgranterProject\DescriptiveManager\Search;

use AdinanCenci\JsonLines\Search\Iterator\DataWrapper;
use AdinanCenci\FileEditor\Search\Order\Order;
use AdinanCenci\FileEditor\Search\Condition\AndConditionGroup;
use AdinanCenci\FileEditor\Search\Condition\OrConditionGroup;
use AdinanCenci\FileEditor\Search\Iterator\Metadata;
use WishgranterProject\DescriptiveManager\PlaylistManager;
use WishgranterProject\DescriptivePlaylist\Search\Search as PlaylistSearch;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

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
     * @var AdinanCenci\FileEditor\Search\Order\Order;
     *   Object to order the results.
     */
    protected Order $order;

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
        $this->order = new Order();
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
        $results = $this->retrieveAndOrder($removeDuplicated);
        array_walk($results, function (&$item) {
            $item = new PlaylistItem($item->data);
        });

        return $results;
    }

    /**
     * Compile a list of playlists to apply the search.
     *
     * Sometimes we want to constrain search to a narrow set of playlists,
     * so it would be wasteful to iterate through playlists that we do not want.
     *
     * @todo There must be a better and more elegant way to accomplish this...
     *
     * @return string[]
     *   List of playlists to apply the search.
     */
    public function compilePlaylistIds()
    {
        $playlistIds = array_keys($this->manager->getAllPlaylists());

        $group = $this->mainGroup->operator == 'AND'
            ? new AndConditionGroup()
            : new OrConditionGroup();

        $this->fromGroupToGroup($this->mainGroup, $group, [['@metadata', 'playlistId']]);

        $filtered = [];

        foreach ($playlistIds as $id) {
            $wrapper = new DataWrapper('');
            $metadata = new Metadata($wrapper, ['playlistId' => $id]);
            $wrapper->setMetadata($metadata);

            if ($group->evaluate($wrapper)) {
                $filter[] = $id;
            }
        }

        return $filtered;
    }

    /**
     * Retrieves the search results.
     *
     * @param bool $removeDuplicated
     *   Removes duplicated playlist items.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem[]
     *   Playlist items.
     */
    public function retrieveAndOrder(bool $removeDuplicated = true): array
    {
        $results = [];
        $this->playlistIds = $this->compilePlaylistIds();
        foreach ($this->manager->getAllPlaylists() as $playlistId => $playlist) {
            if (in_array($playlistId, $this->playlistIds)) {
                continue;
            }

            $search = $this->newSearchObject($playlist);
            $finds = $search->retrieveAndOrder();
            foreach ($finds as $position => $find) {
                $results[$playlistId . '-' . $position] = $find;
            }
        }

        $results = $removeDuplicated
            ? $this->removeDuplicatedResults($results)
            : $results;

        $this->order->order($results);

        return $results;
    }

    /**
     * Adds a new criteria to order the results by.
     *
     * @param array|string $property
     *   The property to order by.
     * @param string $direction
     *   Ascending or descending.
     *
     * @return WishgranterProject\DescriptiveManager\Search\Search
     *   Returns itself.
     */
    public function orderBy(mixed $property, string $direction = 'ASC'): Search
    {
        $this->order->orderBy($property, $direction);
        return $this;
    }

    /**
     * Adds a new criteria to order the results randomly.
     *
     * @param null|string $seed
     *   If informed, the seed will be used to order the results.
     *   The items will be order the same every time.
     *
     * @return WishgranterProject\DescriptiveManager\Search\Search
     *   Return itself.
     */
    public function orderRandomly(?string $seed = null): Search
    {
        $this->order->orderRandomly($seed);
        return $this;
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
        $playlistSearch->setMetadataEagerGetter('playlistId', function ($iterator, $dataWrapper) {
            return basename($iterator->filename, '.dpls');
        });

        return $playlistSearch;
    }

    /**
     * Copy search conditions over.
     *
     * @param WishgranterProject\DescriptiveManager\Search\ConditionGroup $ours
     *   Manager group.
     * @param WishgranterProject\DescriptivePlaylist\Search $theirs
     *   Playlist search object.
     * @param $filter array
     *   Filter for properties.
     */
    protected function fromGroupToGroup($ours, $theirs, $filter = [])
    {
        foreach ($ours->conditions as $con) {
            if ($filter && !in_array($con['property'], $filter)) {
                continue;
            }
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
        foreach ($results as $playlistIdPos => $item) {
            $uuid = $item?->data?->xxxOriginal ?? $item?->data?->uuid;

            if (! in_array($uuid, $uuids)) {
                $uuids[] = $uuid;
                continue;
            }

            unset($results[ $playlistIdPos ]);
        }

        return $results;
    }
}
