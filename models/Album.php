<?php
declare(strict_types=1);

class Album extends Model
{
    protected static string $table = 'albums';

    private const CARD_COLUMNS = '
        a.id, a.user_id, a.title, a.description, a.created_at,
        u.first_name, u.last_name,
        (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS photo_count,
        (SELECT p2.file_name FROM photos p2 WHERE p2.album_id = a.id
            ORDER BY p2.date_time DESC, p2.id DESC LIMIT 1) AS cover_file';

    public static function validate(array $input): array
    {
        $validator = new Validator($input, ['title' => 'Album title', 'description' => 'Description']);
        $validator->validate([
            'title' => 'required|max:150',
            'description' => 'max:1000',
        ]);
        return $validator->errors();
    }

    public function create(int $userId, string $title, string $description): int
    {
        return $this->insert(
            'INSERT INTO albums (user_id, title, description) VALUES (:userId, :title, :description)',
            ['userId' => $userId, 'title' => $title, 'description' => $description !== '' ? $description : null]
        );
    }

    public function allWithCovers(): array
    {
        return $this->fetchAll(
            'SELECT ' . self::CARD_COLUMNS . '
             FROM albums a JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC, a.id DESC'
        );
    }

    public function forUser(int $userId): array
    {
        return $this->fetchAll(
            'SELECT ' . self::CARD_COLUMNS . '
             FROM albums a JOIN users u ON u.id = a.user_id
             WHERE a.user_id = :userId
             ORDER BY a.title',
            ['userId' => $userId]
        );
    }

    public function findWithOwner(int $albumId): ?array
    {
        return $this->fetchOne(
            'SELECT ' . self::CARD_COLUMNS . '
             FROM albums a JOIN users u ON u.id = a.user_id
             WHERE a.id = :albumId',
            ['albumId' => $albumId]
        );
    }

    public function belongsTo(int $albumId, int $userId): bool
    {
        return (bool) $this->fetchValue(
            'SELECT 1 FROM albums WHERE id = :albumId AND user_id = :userId',
            ['albumId' => $albumId, 'userId' => $userId]
        );
    }
}
