<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Run this script from the command line: php database/seed.php' . PHP_EOL);
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/core/bootstrap.php';

final class DemoSeeder
{
    private const DEMO_PASSWORD = 'Password123';

    private PDO $connection;

    public function __construct()
    {
        $dbConfig = Config::get('db');
        $this->connection = new PDO(
            sprintf('mysql:host=%s;port=%d;charset=%s', $dbConfig['host'], $dbConfig['port'], $dbConfig['charset']),
            $dbConfig['user'],
            $dbConfig['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }

    public function run(): void
    {
        $this->log('Importing database/schema.sql ...');
        $this->connection->exec((string) file_get_contents(BASE_PATH . '/database/schema.sql'));
        $this->connection->exec('USE `' . Config::get('db.name') . '`');
        $this->connection->exec("SET time_zone = '" . date('P') . "'");

        $this->cleanUploads();
        $userIds = $this->seedUsers();
        $albumIds = $this->seedAlbums($userIds);
        $photoIds = $this->seedPhotos($userIds, $albumIds);
        $this->seedInteractions($userIds, $photoIds);

        $this->log('Done! Log in with sara@alzikrayat.test / ' . self::DEMO_PASSWORD);
    }

    private function cleanUploads(): void
    {
        $uploadDirectory = (string) Config::get('upload.directory');
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }
        foreach (glob($uploadDirectory . '/photo_*') ?: [] as $oldFile) {
            unlink($oldFile);
        }
    }

    private function seedUsers(): array
    {
        $members = [
            'sara' => ['Sara', 'Osman', 'sara@alzikrayat.test', 'Khartoum, Sudan', 'Photographer', 'I chase golden hours along the Nile and collect the stories of the people I meet.'],
            'ahmed' => ['Ahmed', 'Ali', 'ahmed@alzikrayat.test', 'Omdurman, Sudan', 'Software Engineer', 'Weekend hiker, weekday debugger. My camera roll is 90% sunsets.'],
            'mona' => ['Mona', 'Hassan', 'mona@alzikrayat.test', 'Port Sudan, Sudan', 'Architect', 'Lines, light and the Red Sea. I sketch buildings and photograph horizons.'],
            'yousif' => ['Yousif', 'Ibrahim', 'yousif@alzikrayat.test', 'Wad Madani, Sudan', 'Student', 'Computer science student at SUST. Documenting campus life one photo at a time.'],
        ];

        $statement = $this->connection->prepare(
            'INSERT INTO users (first_name, last_name, email, password, location, occupation, description)
             VALUES (:firstName, :lastName, :email, :passwordHash, :location, :occupation, :description)'
        );

        $userIds = [];
        foreach ($members as $memberKey => [$firstName, $lastName, $email, $location, $occupation, $description]) {
            $statement->execute([
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'passwordHash' => password_hash(self::DEMO_PASSWORD, PASSWORD_BCRYPT),
                'location' => $location,
                'occupation' => $occupation,
                'description' => $description,
            ]);
            $userIds[$memberKey] = (int) $this->connection->lastInsertId();
        }

        $this->log('Created ' . count($userIds) . ' members.');
        return $userIds;
    }

    private function seedAlbums(array $userIds): array
    {
        $albums = [
            'nile' => ['sara', 'Nile Evenings', 'Sunsets and quiet boats where the Blue and White Nile meet.'],
            'redsea' => ['mona', 'Red Sea Trip', 'A week of coral, wind and turquoise water in Port Sudan.'],
            'campus' => ['yousif', 'Campus Days', 'Late nights in the lab and early mornings on the SUST campus.'],
        ];

        $statement = $this->connection->prepare('INSERT INTO albums (user_id, title, description) VALUES (:userId, :title, :description)');
        $albumIds = [];
        foreach ($albums as $albumKey => [$ownerKey, $title, $description]) {
            $statement->execute(['userId' => $userIds[$ownerKey], 'title' => $title, 'description' => $description]);
            $albumIds[$albumKey] = (int) $this->connection->lastInsertId();
        }
        return $albumIds;
    }

    private function seedPhotos(array $userIds, array $albumIds): array
    {
        $photos = [
            ['sara', 'nile', 'Sunset at Al-Mogran', 'The exact moment the sun touched the water where the two Niles meet. We stayed until the sky turned violet.', 'sunset', 'none', 20],
            ['ahmed', null, 'Jebel Aulia at dawn', 'Woke up at 4 am for this one. Totally worth the cold tea.', 'dawn', 'none', 18],
            ['mona', 'redsea', 'Turquoise morning', 'First morning in Port Sudan. The sea looked unreal.', 'ocean', 'none', 16],
            ['yousif', 'campus', 'Lab lights', 'Last night before the Advanced Web Technologies deadline.', 'night', 'none', 14],
            ['sara', 'nile', 'Felucca silhouettes', 'Two boats, one golden river.', 'golden', 'none', 12],
            ['mona', 'redsea', 'Coral coast', 'Wind, salt and the brightest blues I have ever seen.', 'lagoon', 'none', 10],
            ['ahmed', null, 'Desert road to Meroe', 'Pyramids on the horizon and nothing but sand in between.', 'desert', 'none', 8],
            ['yousif', 'campus', 'Graduation eve', 'Our class on the last evening before graduation. We promised to meet here again in ten years.', 'dusk', 'none', 6],
            ['sara', null, 'Blue hour in Khartoum', 'The city lights just switching on.', 'bluehour', 'none', 4],
            ['mona', 'redsea', 'Night swim', 'Stars above, glowing plankton below.', 'night', 'none', 2],
            ['ahmed', null, 'Rainy season hills', 'Green hills after the first rain of the season.', 'meadow', 'none', 1],
        ];

        $statement = $this->connection->prepare(
            'INSERT INTO photos (user_id, album_id, file_name, title, description, filter_name, date_time)
             VALUES (:userId, :albumId, :fileName, :title, :description, :filterName, :dateTime)'
        );

        $photoIds = [];
        foreach ($photos as $index => [$ownerKey, $albumKey, $title, $description, $paletteName, $filterName, $daysAgo]) {
            $fileName = 'photo_' . bin2hex(random_bytes(8)) . '.png';
            $this->log(sprintf('  generating %-24s -> %s', $title, $fileName));
            [$imageWidth, $imageHeight] = [[960, 640], [640, 860], [900, 900], [960, 540]][$index % 4];
            file_put_contents(Config::get('upload.directory') . '/' . $fileName, LandscapeArtist::paint($paletteName, $imageWidth, $imageHeight, $index + 7));

            $statement->execute([
                'userId' => $userIds[$ownerKey],
                'albumId' => $albumKey !== null ? $albumIds[$albumKey] : null,
                'fileName' => $fileName,
                'title' => $title,
                'description' => $description,
                'filterName' => $filterName,
                'dateTime' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days -" . (($index * 97) % 600) . ' minutes')),
            ]);
            $photoIds[] = (int) $this->connection->lastInsertId();
        }

        $this->log('Created ' . count($photoIds) . ' photos.');
        return $photoIds;
    }

    private function seedInteractions(array $userIds, array $photoIds): void
    {
        $comments = [
            [0, 'ahmed', 'This is breathtaking, Sara! The colours are unreal.'],
            [0, 'mona', 'I can almost hear the water. Framing it for my office!'],
            [0, 'yousif', 'Where exactly did you stand for this shot?'],
            [0, 'sara', '@Yousif on the old bridge, right before sunset 🌅'],
            [1, 'sara', 'Worth every minute of lost sleep.'],
            [2, 'ahmed', 'Adding Port Sudan to my list right now.'],
            [3, 'mona', 'Good luck with the deadline! 💪'],
            [3, 'ahmed', 'Been there. Coffee is the answer.'],
            [4, 'mona', 'The vintage filter fits perfectly.'],
            [7, 'sara', 'Congratulations to all of you! 🎓'],
            [7, 'ahmed', 'Ten years will pass so fast. Beautiful memory.'],
            [9, 'yousif', 'The noir look makes it feel like a movie scene.'],
        ];
        $commentStatement = $this->connection->prepare(
            'INSERT INTO comments (photo_id, user_id, comment, date_time) VALUES (:photoId, :userId, :commentText, :dateTime)'
        );
        foreach ($comments as $position => [$photoIndex, $authorKey, $text]) {
            $commentStatement->execute([
                'photoId' => $photoIds[$photoIndex],
                'userId' => $userIds[$authorKey],
                'commentText' => $text,
                'dateTime' => date('Y-m-d H:i:s', strtotime('-' . (20 - $position) . ' hours')),
            ]);
        }

        $tags = [[0, 'ahmed', 'sara'], [0, 'mona', 'sara'], [7, 'ahmed', 'yousif'], [7, 'sara', 'yousif'], [7, 'mona', 'yousif'], [5, 'sara', 'mona'], [1, 'yousif', 'ahmed']];
        $tagStatement = $this->connection->prepare('INSERT INTO photo_tags (photo_id, user_id, tagged_by) VALUES (:photoId, :userId, :taggedBy)');
        foreach ($tags as [$photoIndex, $taggedKey, $taggerKey]) {
            $tagStatement->execute(['photoId' => $photoIds[$photoIndex], 'userId' => $userIds[$taggedKey], 'taggedBy' => $userIds[$taggerKey]]);
        }

        $likeStatement = $this->connection->prepare('INSERT INTO photo_likes (photo_id, user_id) VALUES (:photoId, :userId)');
        $memberIds = array_values($userIds);
        foreach ($photoIds as $photoIndex => $photoId) {
            foreach ($memberIds as $memberIndex => $memberId) {
                if (($photoIndex * 3 + $memberIndex * 5) % 7 < 4) {
                    $likeStatement->execute(['photoId' => $photoId, 'userId' => $memberId]);
                }
            }
        }

        $this->log('Created ' . count($comments) . ' comments, ' . count($tags) . ' tags and likes.');
    }

    private function log(string $message): void
    {
        echo $message . PHP_EOL;
    }
}

final class LandscapeArtist
{
    private const PALETTES = [
        'sunset' => ['sky' => [[255, 120, 70], [255, 204, 128]], 'sun' => [255, 236, 179], 'hills' => [[168, 70, 60], [110, 40, 55], [58, 22, 40]]],
        'dawn' => ['sky' => [[120, 150, 210], [255, 190, 150]], 'sun' => [255, 245, 220], 'hills' => [[120, 110, 150], [80, 72, 110], [45, 40, 70]]],
        'ocean' => ['sky' => [[70, 160, 230], [190, 230, 250]], 'sun' => [255, 255, 240], 'hills' => [[40, 170, 190], [20, 120, 160], [10, 80, 120]]],
        'night' => ['sky' => [[10, 14, 40], [50, 50, 110]], 'sun' => [240, 240, 255], 'hills' => [[40, 40, 80], [25, 25, 55], [12, 12, 30]]],
        'golden' => ['sky' => [[240, 150, 40], [255, 225, 140]], 'sun' => [255, 250, 220], 'hills' => [[150, 95, 40], [100, 60, 30], [55, 35, 20]]],
        'lagoon' => ['sky' => [[90, 200, 220], [220, 245, 240]], 'sun' => [255, 255, 235], 'hills' => [[240, 220, 170], [60, 190, 180], [20, 130, 140]]],
        'desert' => ['sky' => [[230, 170, 100], [250, 230, 190]], 'sun' => [255, 250, 230], 'hills' => [[215, 160, 100], [185, 125, 75], [140, 90, 55]]],
        'dusk' => ['sky' => [[70, 50, 120], [240, 130, 120]], 'sun' => [255, 210, 170], 'hills' => [[120, 60, 100], [75, 40, 80], [35, 20, 45]]],
        'bluehour' => ['sky' => [[20, 40, 100], [90, 130, 200]], 'sun' => [250, 240, 200], 'hills' => [[40, 60, 110], [25, 40, 80], [12, 20, 45]]],
        'meadow' => ['sky' => [[120, 190, 240], [220, 240, 250]], 'sun' => [255, 250, 225], 'hills' => [[140, 200, 110], [80, 160, 80], [40, 110, 60]]],
    ];

    public static function paint(string $paletteName, int $width, int $height, int $seed): string
    {
        $palette = self::PALETTES[$paletteName];
        mt_srand($seed);

        $sunX = $width * (0.2 + mt_rand(0, 60) / 100);
        $sunY = $height * (0.22 + mt_rand(0, 18) / 100);
        $sunRadius = $height * 0.09;

        $ridges = [];
        foreach ($palette['hills'] as $layerIndex => $unusedColour) {
            $baseLine = $height * (0.55 + $layerIndex * 0.13);
            $amplitude = $height * (0.07 - $layerIndex * 0.012);
            $frequency = (1.5 + mt_rand(0, 150) / 100) * M_PI / $width;
            $phase = mt_rand(0, 628) / 100;
            for ($x = 0; $x < $width; $x++) {
                $ridges[$layerIndex][$x] = $baseLine + $amplitude * sin($x * $frequency + $phase)
                    + $amplitude * 0.35 * sin($x * $frequency * 3.1 + $phase * 2);
            }
        }

        $rawRows = '';
        for ($y = 0; $y < $height; $y++) {
            $row = [0];
            $skyMix = $y / $height;
            for ($x = 0; $x < $width; $x++) {
                $colour = self::mix($palette['sky'][0], $palette['sky'][1], min(1, $skyMix * 1.4));

                $distance = hypot($x - $sunX, $y - $sunY);
                if ($distance < $sunRadius) {
                    $colour = $palette['sun'];
                } elseif ($distance < $sunRadius * 3) {
                    $colour = self::mix($colour, $palette['sun'], 0.45 * (1 - ($distance - $sunRadius) / ($sunRadius * 2)));
                }

                foreach ($palette['hills'] as $layerIndex => $hillColour) {
                    if ($y >= $ridges[$layerIndex][$x]) {
                        $colour = self::mix($hillColour, [0, 0, 0], min(0.35, ($y - $ridges[$layerIndex][$x]) / $height));
                    }
                }

                $grain = (($x * 7 + $y * 13) % 5) - 2;
                $row[] = max(0, min(255, (int) $colour[0] + $grain));
                $row[] = max(0, min(255, (int) $colour[1] + $grain));
                $row[] = max(0, min(255, (int) $colour[2] + $grain));
            }
            $rawRows .= pack('C*', ...$row);
        }

        return "\x89PNG\r\n\x1a\n"
            . self::chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            . self::chunk('IDAT', (string) gzcompress($rawRows, 6))
            . self::chunk('IEND', '');
    }

    private static function mix(array $fromColour, array $toColour, float $amount): array
    {
        return [
            $fromColour[0] + ($toColour[0] - $fromColour[0]) * $amount,
            $fromColour[1] + ($toColour[1] - $fromColour[1]) * $amount,
            $fromColour[2] + ($toColour[2] - $fromColour[2]) * $amount,
        ];
    }

    private static function chunk(string $type, string $data): string
    {
        return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
    }
}

(new DemoSeeder())->run();
