<?php
declare(strict_types=1);

class Photo extends Model
{
    protected static string $table = 'photos';

    public const ALLOWED_FILTERS = [
        'none', 'grayscale', 'sepia', 'vintage', 'warm', 'cool', 'invert',
        'noir', 'sharpen', 'blur', 'emboss', 'edge-detect', 'vignette', 'pixelate',
    ];

    private const LIST_COLUMNS = '
        p.id, p.user_id, p.album_id, p.file_name, p.title, p.description, p.filter_name, p.date_time,
        u.first_name, u.last_name,
        (SELECT COUNT(*) FROM comments c WHERE c.photo_id = p.id)    AS comment_count,
        (SELECT COUNT(*) FROM photo_likes l WHERE l.photo_id = p.id) AS like_count';

    public static function validate(array $input): array
    {
        $validator = new Validator($input, ['title' => 'Title', 'description' => 'Description', 'album_id' => 'Album', 'filter_name' => 'Filter']);
        $validator->validate([
            'title' => 'required|max:200',
            'description' => 'max:2000',
            'album_id' => 'integer',
            'filter_name' => 'in:' . implode(',', self::ALLOWED_FILTERS),
        ]);
        return $validator->errors();
    }

    public function create(array $data): int
    {
        return $this->insert(
            'INSERT INTO photos (user_id, album_id, file_name, title, description, filter_name)
             VALUES (:userId, :albumId, :fileName, :title, :description, :filterName)',
            [
                'userId' => (int) $data['user_id'],
                'albumId' => $data['album_id'],
                'fileName' => $data['file_name'],
                'title' => $data['title'],
                'description' => $data['description'] !== '' ? $data['description'] : null,
                'filterName' => $data['filter_name'],
            ]
        );
    }

    public function findWithDetails(int $photoId, ?int $viewerId): ?array
    {
        return $this->fetchOne(
            'SELECT ' . self::LIST_COLUMNS . ',
                    u.occupation, a.title AS album_title,
                    EXISTS(SELECT 1 FROM photo_likes vl WHERE vl.photo_id = p.id AND vl.user_id = :viewerId) AS liked_by_viewer
             FROM photos p
             JOIN users u ON u.id = p.user_id
             LEFT JOIN albums a ON a.id = p.album_id
             WHERE p.id = :photoId',
            ['photoId' => $photoId, 'viewerId' => $viewerId ?? 0]
        );
    }

    public function paginate(int $page, int $perPage, string $searchTerm = '', ?int $ownerId = null, ?int $albumId = null): array
    {
        $conditions = [];
        $parameters = [];

        if ($searchTerm !== '') {
            $likeTerm = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $searchTerm) . '%';
            $conditions[] = "(p.title LIKE :search1 OR p.description LIKE :search2 OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search3)";
            $parameters += ['search1' => $likeTerm, 'search2' => $likeTerm, 'search3' => $likeTerm];
        }
        if ($ownerId !== null) {
            $conditions[] = 'p.user_id = :ownerId';
            $parameters['ownerId'] = $ownerId;
        }
        if ($albumId !== null) {
            $conditions[] = 'p.album_id = :albumId';
            $parameters['albumId'] = $albumId;
        }

        $whereClause = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $totalCount = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM photos p JOIN users u ON u.id = p.user_id {$whereClause}",
            $parameters
        );

        $pageCount = max(1, (int) ceil($totalCount / $perPage));
        $currentPage = min(max(1, $page), $pageCount);

        $items = $this->fetchAll(
            'SELECT ' . self::LIST_COLUMNS . "
             FROM photos p
             JOIN users u ON u.id = p.user_id
             {$whereClause}
             ORDER BY p.date_time DESC, p.id DESC
             LIMIT :limitCount OFFSET :offsetCount",
            $parameters + ['limitCount' => $perPage, 'offsetCount' => ($currentPage - 1) * $perPage]
        );

        return ['items' => $items, 'total' => $totalCount, 'page' => $currentPage, 'pages' => $pageCount];
    }

    public function latest(int $limit): array
    {
        return $this->fetchAll(
            'SELECT ' . self::LIST_COLUMNS . '
             FROM photos p JOIN users u ON u.id = p.user_id
             ORDER BY p.date_time DESC, p.id DESC
             LIMIT :limitCount',
            ['limitCount' => $limit]
        );
    }

    public function mostLiked(int $limit): array
    {
        return $this->fetchAll(
            'SELECT ' . self::LIST_COLUMNS . '
             FROM photos p JOIN users u ON u.id = p.user_id
             ORDER BY like_count DESC, p.date_time DESC
             LIMIT :limitCount',
            ['limitCount' => $limit]
        );
    }

    public function taggedWith(int $userId): array
    {
        return $this->fetchAll(
            'SELECT ' . self::LIST_COLUMNS . '
             FROM photo_tags t
             JOIN photos p ON p.id = t.photo_id
             JOIN users u ON u.id = p.user_id
             WHERE t.user_id = :userId
             ORDER BY p.date_time DESC',
            ['userId' => $userId]
        );
    }

    public function neighbours(int $photoId, string $dateTime): array
    {
        $newerId = $this->fetchValue(
            'SELECT id FROM photos WHERE (date_time > :dateTime1) OR (date_time = :dateTime2 AND id > :photoId)
             ORDER BY date_time ASC, id ASC LIMIT 1',
            ['dateTime1' => $dateTime, 'dateTime2' => $dateTime, 'photoId' => $photoId]
        );
        $olderId = $this->fetchValue(
            'SELECT id FROM photos WHERE (date_time < :dateTime1) OR (date_time = :dateTime2 AND id < :photoId)
             ORDER BY date_time DESC, id DESC LIMIT 1',
            ['dateTime1' => $dateTime, 'dateTime2' => $dateTime, 'photoId' => $photoId]
        );

        return ['previous' => $newerId !== null ? (int) $newerId : null, 'next' => $olderId !== null ? (int) $olderId : null];
    }

    public function siteStatistics(): array
    {
        $row = $this->fetchOne(
            'SELECT (SELECT COUNT(*) FROM users)    AS members,
                    (SELECT COUNT(*) FROM photos)   AS photos,
                    (SELECT COUNT(*) FROM comments) AS comments,
                    (SELECT COUNT(*) FROM albums)   AS albums'
        ) ?? [];

        return array_map('intval', $row + ['members' => 0, 'photos' => 0, 'comments' => 0, 'albums' => 0]);
    }
}
