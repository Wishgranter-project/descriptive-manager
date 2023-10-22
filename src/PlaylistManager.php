<?php 
namespace AdinanCenci\DescriptiveManager;

use AdinanCenci\DescriptiveManager\Search\Search;
use AdinanCenci\DescriptiveManager\Crud\Set;
use AdinanCenci\DescriptiveManager\Crud\Add;
use AdinanCenci\DescriptiveManager\Crud\Move;
use AdinanCenci\DescriptivePlaylist\Playlist;
use AdinanCenci\DescriptivePlaylist\PlaylistItem;
use AdinanCenci\DescriptivePlaylist\Utils\Helpers;

class PlaylistManager 
{
    protected string $directory;

    public function __construct(string $directory) 
    {
        $this->directory = rtrim($directory, '/') . '/';
    }

    public function createPlaylist(string $title, ?string $description = null, ?string $name = null, &$id = null) : Playlist
    {
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

    public function playlistExists(string $playlistId) : bool
    {
        $basename = $playlistId . '.dpls';
        $absolutePath = $this->directory . $basename;
        return file_exists($absolutePath);
    }

    public function getPlaylist(string $playlistId) : ?Playlist
    {
        $files = $this->getAllPlaylistFiles();

        return isset($files[$playlistId]) 
            ? new Playlist($files[$playlistId])
            : null;
    }

    public function deletePlaylist(string $playlistId) 
    {
        $basename = $playlistId . '.dpls';
        $absolutePath = $this->directory . $basename;

        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }
    }

    /**
     * @return string[]
     */
    public function getAllPlaylistFiles() : array
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

    public function getAllPlaylists() : array
    {
        $files = $this->getAllPlaylistFiles();

        array_walk($files, function(&$item, $key)
        {
            $item = new Playlist($item);
        });

        return $files;
    }

    public function getItem(string $playlistId, int $position) : ?PlaylistItem
    {
        $playlist = $this->getPlaylist($playlistId);
        return $playlist
            ? $playlist->getItem($position)
            : null;
    }

    public function setItem(string $playlistId, PlaylistItem $item, ?int $position = null) : PlaylistItem
    {
        if (! $item->isValid($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
            return false;
        }

        $set = new Set($this, $playlistId, $item, $position);
        return $set->commit();
    }

    public function addItem(string $playlistId, PlaylistItem $item, ?int $position = null) : PlaylistItem
    {
        if (! $item->isValid($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
            return false;
        }

        $set = new Add($this, $playlistId, $item, $position);
        return $set->commit();
    }

    public function moveItem(string $toPlaylistId, PlaylistItem $item, ?int $position = null) : bool
    {
        $move = new Move($this, $toPlaylistId, $item, $position);
        return $move->commit();
    }

    public function getItemByUuid(string $uuid, ?string $playlistId = null) : ?PlaylistItem 
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

    public function findItemByUuid(string $uuid, &$playlistId = null) : ?PlaylistItem 
    {
        $playlistId = null;
        foreach ($this->getAllPlaylists() as $pId => $playlist) {
            $item = $playlist->getItemByUuid($uuid);
            if ($item) {
                $playlistId = $pId;
                return $item;
            }
        }

        return null;
    }

    public function search(string $operator = 'AND') : Search
    {
        return new Search($this, $operator);
    }

    public function getAllAssociatedItems(string $baseUuid) : array
    {
        $search = $this->search('OR');

        $search
            ->condition('uuid', $baseUuid)
            ->condition('xxxOriginal', $baseUuid);

        return $search->find(false);
    }

    /**
     * Returns an available filename based of a random string.
     */
    public function getUniqueFilename() : string
    {
        do {
            $filename = Helpers::guidv4();
            $basename = $filename . '.dpls';
            $absolutePath = $this->directory . $basename;
        } while(file_exists($absolutePath));

        return $filename;
    }

    /**
     * Receives an ordinary string and sanitizes and normalizes it
     * to be used as a filename.
     *
     * @param string $baseName
     *
     * @return string
     */
    public function sanitizeFilename(string $baseName) : string 
    {
        $filename = strtolower($baseName);
        $filename = str_replace('_', '-', $filename);
        $filename = preg_replace('/ +/', '-', $filename);
        $filename = preg_replace('/-{2,}/', '-', $filename);
        $filename = preg_replace('/[^\w\-]/', '', $filename);

        return $filename;
    }

    /**
     * Returns an available filename based of $base
     * with sanitization ofcourse.
     */
    public function getAvailableFilename(string $base) : string
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
        } while(file_exists($absolutePath));

        return $filename;
    }
}
