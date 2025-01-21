<?php

namespace WishgranterProject\DescriptiveManager;

use WishgranterProject\DescriptiveManager\Search\Search;
use WishgranterProject\DescriptiveManager\Crud\Set;
use WishgranterProject\DescriptiveManager\Crud\Add;
use WishgranterProject\DescriptiveManager\Crud\Move;
use WishgranterProject\DescriptivePlaylist\Playlist;
use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use WishgranterProject\DescriptivePlaylist\Utils\Helpers;

class PlaylistManager
{
    /**
     * @var string
     *   Absolute path to the directory where the playlists are located.
     */
    protected string $directory;

    /**
     * @param string $directory
     *   Absolute path to the playlist directory.
     */
    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, '/') . '/';
    }

    /**
     * Creates a new playlist.
     *
     * @param string $title
     *   Playlist title.
     * @param ?string $description
     *   Description.
     * @param string &$id
     *   Will turn into the id generated for the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\Playlist
     *   The new playlist.
     */
    public function createPlaylist(
        string $title,
        null|string $description = null,
        null|string $name = null,
        &$id = null
    ): Playlist {
        $filename = $name
            ? $this->getAvailableFilename($name)
            : $this->getUniqueFilename();

        $id = $filename;

        $absolutePath = $this->directory . $filename . '.dpls';
        $playlist = new Playlist($absolutePath);

        $playlist->title = $title;
        if ($description) {
            $playlist->description = $description;
        }

        return $playlist;
    }

    /**
     * Checks if a playlist already exists.
     *
     * @param string $playlistId
     *   Playlist id.
     *
     * @return bool
     *   True if it exists.
     */
    public function playlistExists(string $playlistId): bool
    {
        $basename = $playlistId . '.dpls';
        $absolutePath = $this->directory . $basename;
        return file_exists($absolutePath);
    }

    /**
     * Given an id, retrieves the corresponding playlist.
     *
     * @param string $playlistId.
     *   Playlist id.
     *
     * @return null|WishgranterProject\DescriptivePlaylist\Playlist
     *   The new playlist, null if it does not exists.
     */
    public function getPlaylist(string $playlistId): ?Playlist
    {
        $files = $this->getAllPlaylistFiles();

        return isset($files[$playlistId])
            ? new Playlist($files[$playlistId])
            : null;
    }

    /**
     * Deletes a given playlist.
     *
     * @param string $playlistId.
     *   Playlist id.
     */
    public function deletePlaylist(string $playlistId): void
    {
        $basename = $playlistId . '.dpls';
        $absolutePath = $this->directory . $basename;

        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }
    }

    /**
     * Retrieves all playlist files.
     *
     * @return string[]
     *   Absolute paths to the playlists.
     */
    public function getAllPlaylistFiles(): array
    {
        $entries = scandir($this->directory);
        $playlists = [];

        foreach ($entries as $entry) {
            if (substr_count($entry, '.dpls')) {
                $playlists[ basename($entry, '.dpls') ] = $this->directory . $entry;
            }
        }

        return $playlists;
    }

    /**
     * Retrieves all playlists.
     *
     * @return WishgranterProject\DescriptivePlaylist\Playlist[]
     *   The playlists instantiated.
     */
    public function getAllPlaylists(): array
    {
        $files = $this->getAllPlaylistFiles();

        array_walk($files, function (&$item, $key) {
            $item = new Playlist($item);
        });

        return $files;
    }

    /**
     * Retrieves a playlist item.
     *
     * @param string $playlistId
     *   The playlist id.
     * @param int $position
     *   The position in the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item, null if it could not be retrieved.
     */
    public function getItem(string $playlistId, int $position): ?PlaylistItem
    {
        $playlist = $this->getPlaylist($playlistId);
        return $playlist
            ? $playlist->getItem($position)
            : null;
    }

    /**
     * Adds an item to the specified playlist.
     *
     * If the item is already present, it will be moved to $position.
     *
     * @param string $playlistId
     *   Playlist id.
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $item
     *   Playlist item.
     * @param int|null $position.
     *   The position within the playlist.
     *   If not provided ant the item is already present, then it will remain
     *   in that position. If not provided and the item does not exist, it will
     *   be added to the end of the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item.
     */
    public function setItem(string $playlistId, PlaylistItem $item, ?int $position = null): PlaylistItem
    {
        if (! $item->isValid($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
            return false;
        }

        $set = new Set($this, $playlistId, $item, $position);
        return $set->commit();
    }

    /**
     * Adds an item to the specified playlist.
     *
     * If the item is already present, it creates a copy.
     *
     * @param string $playlistId
     *   Playlist id.
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $item
     *   Playlist item.
     * @param int|null $position.
     *   The position within the playlist. If not provided, the item will be
     *   added to the end of the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item.
     */
    public function addItem(string $playlistId, PlaylistItem $item, ?int $position = null): PlaylistItem
    {
        if (! $item->isValid($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
            return false;
        }

        $set = new Add($this, $playlistId, $item, $position);
        return $set->commit();
    }

    /**
     * Move a playlist item to a different playlist or just position.
     *
     * @param string $toPlaylist
     *   Playlist id.
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $item
     *   Playlist item.
     * @param null|int $position
     *   The position within the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item.
     */
    public function moveItem(string $toPlaylistId, PlaylistItem $item, ?int $position = null): PlaylistItem
    {
        $move = new Move($this, $toPlaylistId, $item, $position);
        return $move->commit();
    }

    /**
     * Retrieves a playlist item with a given uuid.
     *
     * @param string $uuid
     *   The playlist item uuid.
     * @param null|string $playlistId
     *   The playlist to retrieve the item from, if not provided, all playlists
     *   will be checked.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item, null if it could not be retrieved.
     */
    public function getItemByUuid(string $uuid, ?string $playlistId = null): ?PlaylistItem
    {
        foreach ($this->getAllPlaylists() as $pId => $playlist) {
            if (($playlistId && $playlistId == $pId) || !$playlistId) {
                $item = $playlist->getItemByUuid($uuid);
                if ($item) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Finds a playlist item with a given uuid.
     *
     * @param string $uuid
     *   The playlist item uuid.
     * @param null|string $playlistId
     *   Will turn into the id of the playlist where the item resides.
     * @param int $position
     *   Will turn into the item's position within the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   The playlist item, null if it could not be retrieved.
     */
    public function findItemByUuid(string $uuid, &$playlistId = null, &$position = null): ?PlaylistItem
    {
        $playlistId = null;
        foreach ($this->getAllPlaylists() as $pId => $playlist) {
            $item = $playlist->getItemByUuid($uuid, $pos);
            if ($item) {
                $position = $pos;
                $playlistId = $pId;
                return $item;
            }
        }

        return null;
    }

    /**
     * Returns a search object.
     *
     * @param string $operator
     *   A logic operator.
     *
     * @return WishgranterProject\DescriptiveManager\Search\Search
     *   Search object.
     */
    public function search(string $operator = 'AND'): Search
    {
        return new Search($this, $operator);
    }

    /**
     * Retrieves all the playlist items associated to a given $uuid.
     *
     * Original and copies alike.
     *
     * @param string $uuid
     *   The uuid.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem[]
     *   Playlist items.
     */
    public function getAllAssociatedItems(string $uuid): array
    {
        $search = $this->search('OR');

        $search
            ->condition('uuid', $uuid)
            ->condition('xxxOriginal', $uuid);

        return $search->find(false);
    }

    /**
     * Generates a random available filename.
     *
     * To be used in for a new playlist.
     *
     * @param string
     *   Absolute path.
     */
    public function getUniqueFilename(): string
    {
        do {
            $filename = Helpers::guidv4();
            $basename = $filename . '.dpls';
            $absolutePath = $this->directory . $basename;
        } while (file_exists($absolutePath));

        return $filename;
    }

    /**
     * Sanitizes an ordinary string to be used as a filename.
     *
     * @param string $baseName
     *   The raw string.
     *
     * @return string
     *   The sanitized string.
     */
    public function sanitizeFilename(string $baseName): string
    {
        $filename = strtolower($baseName);
        $filename = str_replace('_', '-', $filename);
        $filename = preg_replace('/ +/', '-', $filename);
        $filename = preg_replace('/-{2,}/', '-', $filename);
        $filename = preg_replace('/[^\w\-]/', '', $filename);

        return $filename;
    }

    /**
     * Given a base string, returns an available filename.
     *
     * To be used for a new playlist.
     *
     * @param string $base
     *   Base string.
     *
     * @return string
     *   Absolute path for a new playlist.
     */
    public function getAvailableFilename(string $base): string
    {
        $filename = $this->sanitizeFilename($base);

        if (empty($base)) {
            throw new \InvalidArgumentException('Invalid file name');
        }

        $basename = $filename . '.dpls';
        $absolutePath = $this->directory . $basename;

        if (!file_exists($absolutePath)) {
            return $filename;
        }

        do {
            $n = (preg_match('/-([\d]+)$/', $filename, $matches) ? $matches[1] : 1) + 1;
            $filename = preg_replace('/-[\d]+$/', '', $filename) . '-' . $n;
            $basename = $filename . '.dpls';
            $absolutePath = $this->directory . $basename;
        } while (file_exists($absolutePath));

        return $filename;
    }
}
