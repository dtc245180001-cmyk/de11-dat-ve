SET NAMES utf8mb4;

UPDATE events
SET
    title = 'Đêm phim cuối tuần',
    description = 'Chương trình chiếu phim cuối tuần dành cho khán giả.',
    location = 'Rạp chiếu phim Demo'
WHERE id = 1;

UPDATE events
SET
    title = 'Đêm nhạc mùa thu',
    description = 'Sự kiện âm nhạc giao lưu dành cho sinh viên.',
    location = 'Hội trường Demo'
WHERE id = 2;
