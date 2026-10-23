<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/db.php';
$stmt = $pdo->query(
    "SELECT id, title, event_type, description, location
     FROM events
     ORDER BY id DESC"
);

$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Vé Phim &amp; Sự Kiện</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #222;
        }

        header {
            background: #172554;
            color: white;
            padding: 24px 7%;
        }

        header h1 {
            margin: 0 0 8px;
        }

        header p {
            margin: 0;
            color: #dbeafe;
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .section-title {
            margin-top: 30px;
            color: #172554;
        }

        .event-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .event-card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 4px 14px #0000000d;
        }

        .event-type {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 13px;
        }

        .event-card h3 {
            margin: 15px 0 10px;
        }

        .event-card p {
            color: #555;
            line-height: 1.6;
        }

        .book-button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 16px;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            text-decoration: none;
        }

        .book-button:hover {
            background: #1d4ed8;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #666;
        }
    </style>
</head>
<body>
    <header>
        <h1>Đặt Vé Phim &amp; Sự Kiện</h1>
        <p>Khám phá chương trình yêu thích và đặt vé dễ dàng</p>
    </header>

    <main>
        <h2 class="section-title">Phim và sự kiện nổi bật</h2>

        <div class="event-list">
            <?php if (empty($events)): ?>
                <p>Hiện chưa có phim hoặc sự kiện nào.</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <article class="event-card">
                        <span class="event-type">
                            <?= $event['event_type'] === 'movie'
                                ? 'Phim'
                                : 'Sự kiện' ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars(
                                $event['title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars(
                                $event['description'] ?? 'Thông tin đang được cập nhật.',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <?php if (!empty($event['location'])): ?>
                            <p>
                                Địa điểm:
                                <?= htmlspecialchars(
                                    $event['location'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                        <?php endif; ?>

                        <a class="book-button"
                           href="booking.php?id=<?= (int)$event['id'] ?>">
                            Xem lịch và đặt vé
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        Hệ thống đặt vé phim và sự kiện - Đề tài thực hành số 11
    </footer>
</body>
</html>
