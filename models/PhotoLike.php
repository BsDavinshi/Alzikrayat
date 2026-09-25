<?php
declare(strict_types=1);

class PhotoLike extends Model
{
    protected static string $table = 'photo_likes';

    public function toggle(int $photoId, int $userId): array
    {
        $parameters = ['photoId' => $photoId, 'userId' => $userId];

        $wasRemoved = $this->execute('DELETE FROM photo_likes WHERE photo_id = :photoId AND user_id = :userId', $parameters) > 0;
        if (!$wasRemoved) {
            $this->execute('INSERT IGNORE INTO photo_likes (photo_id, user_id) VALUES (:photoId, :userId)', $parameters);
        }

        $likeCount = (int) $this->fetchValue('SELECT COUNT(*) FROM photo_likes WHERE photo_id = :photoId', ['photoId' => $photoId]);
        return ['liked' => !$wasRemoved, 'count' => $likeCount];
    }
}
