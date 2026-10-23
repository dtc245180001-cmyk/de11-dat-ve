# Hệ thống đặt vé phim và sự kiện — Đề 11

## 1. Giới thiệu
Dự án xây dựng hệ thống đặt vé phim và sự kiện sử dụng PHP, MySQL và Docker.
Dự án tích hợp Nginx, Prometheus, Grafana, Loki và Promtail để hỗ trợ
truy cập ứng dụng, giám sát và thu thập log.

## 2. Công nghệ sử dụng
- PHP: xử lý logic ứng dụng.
- MySQL 8: lưu trữ dữ liệu.
- Docker Compose: khởi chạy các dịch vụ.
- Nginx: máy chủ web / reverse proxy.
- phpMyAdmin: quản lý cơ sở dữ liệu qua giao diện web.
- Prometheus: thu thập số liệu giám sát.
- Nginx Exporter: cung cấp số liệu của Nginx cho Prometheus.
- Grafana: hiển thị dashboard giám sát.
- Loki và Promtail: thu thập và truy vấn log.

## 3. Yêu cầu
- Docker Engine.
- Docker Compose plugin.
- Git (nếu tải dự án từ GitHub).

## 4. Cấu hình môi trường
Tạo file .env từ file mẫu:

```bash
cp .env.example .env
```

Mở file `.env` và điền các giá trị phù hợp với cấu hình trong
`docker-compose.yml` và `.env.example`.

Không tải file `.env` chứa mật khẩu thật lên GitHub.

## 5. Khởi chạy dự án
Tại thư mục chứa `docker-compose.yml`, chạy:

```bash
docker compose up -d --build
```

Kiểm tra trạng thái dịch vụ:

```bash
docker compose ps
```

Xem log khi cần kiểm tra lỗi:

```bash
docker compose logs --tail=100
```

## 6. Các địa chỉ truy cập
Khi chạy trên chính máy cài Docker:

- Website qua Nginx: http://localhost:8080
- Website trực tiếp qua web container: http://localhost:8081
- phpMyAdmin: http://localhost:8082
- Grafana: http://localhost:3000
- Prometheus: http://localhost:9091

Thông tin đăng nhập Grafana và cơ sở dữ liệu được cấu hình trong file
`.env`. Không đưa mật khẩu thật vào README.

## 7. Giám sát
Prometheus được cấu hình thu thập số liệu từ chính Prometheus và
Nginx Exporter.

Grafana dùng để tạo dashboard giám sát. Loki và Promtail được sử dụng
cho quy trình thu thập và truy vấn log.

## 8. Dừng dịch vụ
Để dừng các container mà không chủ động xóa volume dữ liệu:

```bash
docker compose down
```

Không sử dụng `docker compose down -v` nếu cần giữ dữ liệu cơ sở dữ liệu.

## 9. Cấu trúc thư mục
- `app/`: mã nguồn PHP.
- `database/`: các file SQL.
- `nginx/`: cấu hình Nginx.
- `prometheus/`: cấu hình Prometheus.
- `loki/`: cấu hình Loki và Promtail.
- `docker-compose.yml`: khai báo các dịch vụ Docker.
- `Dockerfile`: cấu hình xây dựng image ứng dụng.
- `.env.example`: mẫu biến môi trường.

## 10. Lưu ý
Các cổng, biến môi trường và thông tin đăng nhập cần khớp với cấu hình
thực tế của dự án. Hãy kiểm tra đề bài để xác nhận đầy đủ các yêu cầu
chức năng, bảo mật và giám sát trước khi nghiệm thu.
