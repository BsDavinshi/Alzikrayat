<?php
declare(strict_types=1);

class User extends Model
{
    protected static string $table = 'users';

    private const LABELS = [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirm' => 'Password confirmation',
        'location' => 'Location',
        'occupation' => 'Occupation',
        'description' => 'About you',
    ];

    public static function validateRegistration(array $input): array
    {
        $validator = new Validator($input, self::LABELS);
        $validator->validate([
            'first_name' => 'required|max:50|name',
            'last_name' => 'required|max:50|name',
            'email' => 'required|max:100|email',
            'password' => 'required|min:8|max:72|strongPassword',
            'password_confirm' => 'required|matches:password',
        ]);
        return $validator->errors();
    }

    public static function validateLogin(array $input): array
    {
        $validator = new Validator($input, self::LABELS);
        $validator->validate([
            'email' => 'required|max:100|email',
            'password' => 'required|max:72',
        ]);
        return $validator->errors();
    }

    public static function validateProfile(array $input): array
    {
        $validator = new Validator($input, self::LABELS);
        $validator->validate([
            'first_name' => 'required|max:50|name',
            'last_name' => 'required|max:50|name',
            'location' => 'max:100',
            'occupation' => 'max:100',
            'description' => 'max:1000',
        ]);
        return $validator->errors();
    }

    public function create(array $data): int
    {
        return $this->insert(
            'INSERT INTO users (first_name, last_name, email, password)
             VALUES (:firstName, :lastName, :email, :passwordHash)',
            [
                'firstName' => $data['first_name'],
                'lastName' => $data['last_name'],
                'email' => mb_strtolower($data['email']),
                'passwordHash' => password_hash($data['password'], PASSWORD_BCRYPT),
            ]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => mb_strtolower($email)]);
    }

    public function emailExists(string $email): bool
    {
        return (bool) $this->fetchValue('SELECT 1 FROM users WHERE email = :email LIMIT 1', ['email' => mb_strtolower($email)]);
    }

    public function rehashPassword(int $userId, string $plainPassword): void
    {
        $this->execute('UPDATE users SET password = :passwordHash WHERE id = :id', [
            'passwordHash' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'id' => $userId,
        ]);
    }

    public function findProfile(int $userId): ?array
    {
        return $this->fetchOne(
            'SELECT u.id, u.first_name, u.last_name, u.email, u.location, u.description, u.occupation, u.created_at,
                    (SELECT COUNT(*) FROM photos p WHERE p.user_id = u.id)      AS photo_count,
                    (SELECT COUNT(*) FROM albums a WHERE a.user_id = u.id)      AS album_count,
                    (SELECT COUNT(*) FROM photo_tags t WHERE t.user_id = u.id)  AS tagged_count,
                    (SELECT COUNT(*) FROM photo_likes l
                        JOIN photos lp ON lp.id = l.photo_id WHERE lp.user_id = u.id) AS likes_received
             FROM users u
             WHERE u.id = :id',
            ['id' => $userId]
        );
    }

    public function updateProfile(int $userId, array $data): void
    {
        $this->execute(
            'UPDATE users
             SET first_name = :firstName, last_name = :lastName, location = :location,
                 occupation = :occupation, description = :description
             WHERE id = :id',
            [
                'firstName' => $data['first_name'],
                'lastName' => $data['last_name'],
                'location' => $data['location'] !== '' ? $data['location'] : null,
                'occupation' => $data['occupation'] !== '' ? $data['occupation'] : null,
                'description' => $data['description'] !== '' ? $data['description'] : null,
                'id' => $userId,
            ]
        );
    }

    public function searchByName(string $term, int $excludeUserId, int $limit = 8): array
    {
        $likeTerm = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

        return $this->fetchAll(
            "SELECT id, first_name, last_name
             FROM users
             WHERE id <> :excludeId
               AND (first_name LIKE :term1 OR last_name LIKE :term2
                    OR CONCAT(first_name, ' ', last_name) LIKE :term3)
             ORDER BY first_name, last_name
             LIMIT :limitCount",
            ['excludeId' => $excludeUserId, 'term1' => $likeTerm, 'term2' => $likeTerm, 'term3' => $likeTerm, 'limitCount' => $limit]
        );
    }

    public function filterExistingIds(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $placeholders = [];
        $parameters = [];
        foreach (array_values($userIds) as $index => $userId) {
            $placeholders[] = ':id' . $index;
            $parameters['id' . $index] = (int) $userId;
        }

        $rows = $this->fetchAll('SELECT id FROM users WHERE id IN (' . implode(',', $placeholders) . ')', $parameters);
        return array_map(static fn(array $row): int => (int) $row['id'], $rows);
    }

    public function topContributors(int $limit = 4): array
    {
        return $this->fetchAll(
            'SELECT u.id, u.first_name, u.last_name, u.occupation, COUNT(p.id) AS photo_count
             FROM users u
             JOIN photos p ON p.user_id = u.id
             GROUP BY u.id, u.first_name, u.last_name, u.occupation
             ORDER BY photo_count DESC, u.id
             LIMIT :limitCount',
            ['limitCount' => $limit]
        );
    }
}
