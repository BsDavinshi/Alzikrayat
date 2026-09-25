<?php
declare(strict_types=1);

class Comment extends Model
{
    protected static string $table = 'comments';

    public const MAX_LENGTH = 1000;

    public static function validate(array $input): array
    {
        $validator = new Validator($input, ['comment' => 'Comment']);
        $validator->validate(['comment' => 'required|max:' . self::MAX_LENGTH]);
        return $validator->errors();
    }

    public function create(int $photoId, int $userId, string $text): int
    {
        return $this->insert(
            'INSERT INTO comments (photo_id, user_id, comment) VALUES (:photoId, :userId, :commentText)',
            ['photoId' => $photoId, 'userId' => $userId, 'commentText' => $text]
        );
    }

    public function forPhoto(int $photoId): array
    {
        return $this->fetchAll(
            'SELECT c.id, c.photo_id, c.user_id, c.comment, c.date_time, u.first_name, u.last_name
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.photo_id = :photoId
             ORDER BY c.date_time ASC, c.id ASC',
            ['photoId' => $photoId]
        );
    }

    public function findWithContext(int $commentId): ?array
    {
        return $this->fetchOne(
            'SELECT c.id, c.photo_id, c.user_id, c.comment, c.date_time,
                    u.first_name, u.last_name, p.user_id AS photo_owner_id
             FROM comments c
             JOIN users u ON u.id = c.user_id
             JOIN photos p ON p.id = c.photo_id
             WHERE c.id = :commentId',
            ['commentId' => $commentId]
        );
    }

    public function countForPhoto(int $photoId): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM comments WHERE photo_id = :photoId', ['photoId' => $photoId]);
    }
}
