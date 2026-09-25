<?php
declare(strict_types=1);

class PhotoTag extends Model
{
    protected static string $table = 'photo_tags';

    public const MAX_TAGS_PER_PHOTO = 20;

    public function tagUsers(int $photoId, array $userIds, int $taggerId): void
    {
        foreach (array_slice(array_unique($userIds), 0, self::MAX_TAGS_PER_PHOTO) as $userId) {
            $this->execute(
                'INSERT IGNORE INTO photo_tags (photo_id, user_id, tagged_by) VALUES (:photoId, :userId, :taggerId)',
                ['photoId' => $photoId, 'userId' => (int) $userId, 'taggerId' => $taggerId]
            );
        }
    }

    public function forPhoto(int $photoId): array
    {
        return $this->fetchAll(
            'SELECT u.id, u.first_name, u.last_name
             FROM photo_tags t JOIN users u ON u.id = t.user_id
             WHERE t.photo_id = :photoId
             ORDER BY u.first_name, u.last_name',
            ['photoId' => $photoId]
        );
    }

    public function untag(int $photoId, int $userId): bool
    {
        return $this->execute(
            'DELETE FROM photo_tags WHERE photo_id = :photoId AND user_id = :userId',
            ['photoId' => $photoId, 'userId' => $userId]
        ) > 0;
    }
}
