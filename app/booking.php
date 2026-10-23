<?php
header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/db.php';

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$eventId || $eventId < 1) {
    http_response_code(400);
    exit('Mã phim hoặc sự kiện không hợp lệ.');
}

// Lấy thông tin phim hoặc sự kiện
$stmt = $pdo->prepare(
    "SELECT id, title, event_type, description, location
     FROM events
     WHERE id = :id"
);
$stmt->execute(['id' => $eventId]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    exit('Không tìm thấy phim hoặc sự kiện.');
}

// Lấy các suất chiếu còn trong tương lai
$stmt = $pdo->prepare(
    "SELECT id, show_date, show_time, ticket_price, total_seats
     FROM showtimes
     WHERE event_id = :event_id
       AND show_date >= CURDATE()
     ORDER BY show_date ASC, show_time ASC"
);
$stmt->execute(['event_id' => $eventId]);
$showtimes = $stmt->fetchAll();

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt vé - <?= e($event['title']) ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
        }

        .header {
            padding: 24px;
            border-radius: 14px;
            background: #172554;
            color: white;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0 0 8px;
        }

        .card {
            padding: 25px;
            margin-bottom: 20px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .type {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .description {
            color: #475569;
            line-height: 1.6;
        }

        .showtime {
            display: block;
            padding: 15px;
            margin: 12px 0;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            cursor: pointer;
        }

        .showtime:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .showtime input {
            margin-right: 10px;
        }

        .price {
            color: #dc2626;
            font-weight: bold;
        }

        .field {
            margin-top: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 16px;
        }

        .submit-btn {
            display: block;
            width: 100%;
            margin-top: 24px;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #1d4ed8;
        }

        .back {
            display: inline-block;
            margin-top: 8px;
            color: #2563eb;
            text-decoration: none;
        }

        .notice {
            padding: 15px;
            background: #fff7ed;
            color: #9a3412;
            border-radius: 8px;
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <h1>Hệ thống đặt vé</h1>
        <p>Đặt vé phim và sự kiện trực tuyến</p>
    </div>

    <div class="card">
        <span class="type">
            <?= $event['event_type'] === 'movie' ? 'Phim chiếu rạp' : 'Sự kiện' ?>
        </span>

        <h2><?= e($event['title']) ?></h2>

        <p class="description">
            <?= nl2br(e($event['description'])) ?>
        </p>

        <p>
            <strong>Địa điểm:</strong>
            <?= e($event['location']) ?>
        </p>
    </div>

    <div class="card">
        <h2>Chọn suất chiếu và đặt vé</h2>

        <?php if (count($showtimes) > 0): ?>

            <form action="process-booking.php" method="POST">

                <input
                    type="hidden"
                    name="event_id"
                    value="<?= (int)$event['id'] ?>"
                >

                <h3>Chọn suất chiếu</h3>

                <?php foreach ($showtimes as $index => $showtime): ?>
                    <label class="showtime">

                        <input
                            type="radio"
                            name="showtime_id"
                            value="<?= (int)$showtime['id'] ?>"
                            required
                            <?= $index === 0 ? 'checked' : '' ?>
                        >

                        <strong>
                            Ngày <?= e(date('d/m/Y', strtotime($showtime['show_date']))) ?>
                        </strong>

                        - <?= e(substr($showtime['show_time'], 0, 5)) ?>

                        <br><br>

                        Giá vé:
                        <span class="price">
                            <?= number_format((float)$showtime['ticket_price'], 0, ',', '.') ?>đ/vé
                        </span>

                        <br>

                        Tổng số ghế:
                        <?= (int)$showtime['total_seats'] ?>
                    </label>
                <?php endforeach; ?>

                <div class="field">
                    <label for="customer_name">Họ và tên</label>
                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        maxlength="100"
                        required
                        placeholder="Nhập họ và tên"
                    >
                </div>

                <div class="field">
                    <label for="customer_email">Email</label>
                    <input
                        type="email"
                        id="customer_email"
                        name="customer_email"
                        maxlength="190"
                        required
                        placeholder="example@gmail.com"
                    >
                </div>

                <div class="field">
                    <label for="seat_count">Số lượng vé</label>
                    <select id="seat_count" name="seat_count" required>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>">
                                <?= $i ?> vé
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">
                    Xác nhận đặt vé
                </button>

            </form>

        <?php else: ?>

            <div class="notice">
                Hiện chưa có suất chiếu trong tương lai cho phim hoặc sự kiện này.
            </div>

        <?php endif; ?>

        <a class="back" href="index.php">
            ← Quay lại trang chủ
        </a>
    </div>

</div>
</body>
</html>
