<?php
header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/db.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function showMessage($title, $message, $backUrl = 'index.php')
{
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f1f5f9;
                padding: 30px 15px;
                color: #1e293b;
            }
            .card {
                max-width: 600px;
                margin: 40px auto;
                padding: 30px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,.08);
            }
            h1 { color: #1d4ed8; }
            a {
                display: inline-block;
                margin-top: 15px;
                color: #2563eb;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1><?= e($title) ?></h1>
            <p><?= e($message) ?></p>
            <a href="<?= e($backUrl) ?>">Quay lại</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Chỉ tiếp nhận dữ liệu gửi bằng POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    showMessage('Không hợp lệ', 'Vui lòng đặt vé từ biểu mẫu trên website.');
}

// Lấy và kiểm tra dữ liệu đầu vào
$showtimeId = filter_input(INPUT_POST, 'showtime_id', FILTER_VALIDATE_INT);
$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$customerName = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$seatCount = filter_input(INPUT_POST, 'seat_count', FILTER_VALIDATE_INT);

$backUrl = 'booking.php?id=' . (int)$eventId;

if (
    !$eventId || $eventId < 1 ||
    !$showtimeId || $showtimeId < 1 ||
    $customerName === '' ||
    mb_strlen($customerName, 'UTF-8') > 150 ||
    !filter_var($customerEmail, FILTER_VALIDATE_EMAIL) ||
    strlen($customerEmail) > 255 ||
    !$seatCount || $seatCount < 1 || $seatCount > 10
) {
    http_response_code(400);
    showMessage(
        'Thông tin chưa hợp lệ',
        'Vui lòng kiểm tra họ tên, email, suất chiếu và số lượng vé từ 1 đến 10.',
        $backUrl
    );
}

try {
    $pdo->beginTransaction();

    // Khóa dòng suất chiếu trong giao dịch để hạn chế đặt vượt số ghế
    $stmt = $pdo->prepare(
        "SELECT s.id, s.event_id, s.show_date, s.show_time,
                s.ticket_price, s.total_seats, e.title
         FROM showtimes s
         INNER JOIN events e ON e.id = s.event_id
         WHERE s.id = :showtime_id
           AND s.event_id = :event_id
           AND s.show_date >= CURDATE()
         FOR UPDATE"
    );

    $stmt->execute([
        'showtime_id' => $showtimeId,
        'event_id' => $eventId
    ]);

    $showtime = $stmt->fetch();

    if (!$showtime) {
        $pdo->rollBack();
        http_response_code(404);
        showMessage(
            'Không tìm thấy suất chiếu',
            'Suất chiếu không tồn tại hoặc đã qua ngày diễn ra.',
            $backUrl
        );
    }

    // Tính tổng số vé đã được đặt, không tính đơn đã hủy
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(seat_count), 0)
         FROM bookings
         WHERE showtime_id = :showtime_id
           AND status IN ('pending', 'confirmed')"
    );

    $stmt->execute(['showtime_id' => $showtimeId]);
    $bookedSeats = (int)$stmt->fetchColumn();

    $availableSeats = (int)$showtime['total_seats'] - $bookedSeats;

    if ($seatCount > $availableSeats) {
        $pdo->rollBack();
        http_response_code(409);
        showMessage(
            'Không đủ ghế trống',
            'Suất chiếu chỉ còn ' . max(0, $availableSeats) .
            ' ghế. Vui lòng quay lại và chọn số lượng vé phù hợp.',
            $backUrl
        );
    }

    // Tạo mã đặt vé ngẫu nhiên
    $bookingCode = 'DV' . strtoupper(bin2hex(random_bytes(5)));

    // Lưu đơn đặt vé
    $stmt = $pdo->prepare(
        "INSERT INTO bookings
            (showtime_id, customer_name, customer_email,
             seat_count, booking_code, status)
         VALUES
            (:showtime_id, :customer_name, :customer_email,
             :seat_count, :booking_code, 'pending')"
    );

    $stmt->execute([
        'showtime_id' => $showtimeId,
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'seat_count' => $seatCount,
        'booking_code' => $bookingCode
    ]);

    $bookingId = (int)$pdo->lastInsertId();
    $totalPrice = (float)$showtime['ticket_price'] * $seatCount;

    $pdo->commit();

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Booking processing error: ' . $exception->getMessage());

    http_response_code(500);
    showMessage(
        'Chưa thể đặt vé',
        'Hệ thống gặp sự cố khi lưu đơn. Vui lòng thử lại sau.',
        $backUrl
    );
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt vé thành công</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        .card {
            max-width: 650px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }
        h1 { color: #15803d; }
        .code {
            padding: 15px;
            border-radius: 8px;
            background: #dcfce7;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            overflow-wrap: anywhere;
        }
        .button {
            display: inline-block;
            padding: 12px 18px;
            margin: 8px 8px 0 0;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            text-decoration: none;
        }
        .secondary { background: #475569; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Đặt vé thành công!</h1>
        <p>Đơn đặt vé của bạn đã được lưu vào hệ thống.</p>

        <p><strong>Mã đặt vé:</strong></p>
        <div class="code"><?= e($bookingCode) ?></div>

        <p><strong>Phim/sự kiện:</strong> <?= e($showtime['title']) ?></p>
        <p><strong>Ngày chiếu:</strong>
            <?= e(date('d/m/Y', strtotime($showtime['show_date']))) ?>
        </p>
        <p><strong>Giờ chiếu:</strong>
            <?= e(substr($showtime['show_time'], 0, 5)) ?>
        </p>
        <p><strong>Khách hàng:</strong> <?= e($customerName) ?></p>
        <p><strong>Email:</strong> <?= e($customerEmail) ?></p>
        <p><strong>Số lượng vé:</strong> <?= (int)$seatCount ?></p>
        <p><strong>Giá mỗi vé:</strong>
            <?= number_format((float)$showtime['ticket_price'], 0, ',', '.') ?>đ
        </p>
        <p><strong>Tổng tiền:</strong>
            <?= number_format($totalPrice, 0, ',', '.') ?>đ
        </p>
        <p><strong>Trạng thái:</strong> Chờ xác nhận</p>

        <a class="button" href="index.php">Về trang chủ</a>
        <a class="button secondary"
           href="booking.php?id=<?= (int)$eventId ?>">Đặt vé khác</a>
    </div>
</body>
</html>
