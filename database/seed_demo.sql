-- Seed demo VAYGOR (idempotent). Jalankan: mysql -u root db_futsal < database/seed_demo.sql
-- Akun login: demo@vaygor.id / user123

-- Jadwal HVMan (sebelumnya kosong); replace agar idempotent
DELETE FROM field_schedules WHERE field_id = 2;
INSERT INTO field_schedules (field_id, day_of_week, open_time, close_time) VALUES
(2, 'Monday', '08:00:00', '22:00:00'),
(2, 'Tuesday', '08:00:00', '22:00:00'),
(2, 'Wednesday', '08:00:00', '22:00:00'),
(2, 'Thursday', '08:00:00', '22:00:00'),
(2, 'Friday', '08:00:00', '22:00:00'),
(2, 'Saturday', '08:00:00', '23:00:00'),
(2, 'Sunday', '08:00:00', '23:00:00');

-- Akun demo + reviewer
INSERT INTO users (id, name, email, phone, password, role, status) VALUES
(5, 'Demo User', 'demo@vaygor.id', '08111111111', '$2y$12$P.N3Atn2RqBR3H/RN.CrXeX4olgzmsFASkXK7OEoNtvF1eqoPWAVa', 'user', 'active'),
(6, 'Dimas Pratama', 'dimas@example.com', '08111111112', '$2y$12$P.N3Atn2RqBR3H/RN.CrXeX4olgzmsFASkXK7OEoNtvF1eqoPWAVa', 'user', 'active'),
(7, 'Sari Wulandari', 'sari@example.com', '08111111113', '$2y$12$P.N3Atn2RqBR3H/RN.CrXeX4olgzmsFASkXK7OEoNtvF1eqoPWAVa', 'user', 'active'),
(8, 'Bagas Saputra', 'bagas@example.com', '08111111114', '$2y$12$P.N3Atn2RqBR3H/RN.CrXeX4olgzmsFASkXK7OEoNtvF1eqoPWAVa', 'user', 'active'),
(9, 'Nadia Putri', 'nadia@example.com', '08111111115', '$2y$12$P.N3Atn2RqBR3H/RN.CrXeX4olgzmsFASkXK7OEoNtvF1eqoPWAVa', 'user', 'active'),
(10, 'Yoga Permana', 'yoga@example.com', '08111111116', '$2y$12$P.N3Atn2RqBR3H/RN.CrXeX4olgzmsFASkXK7OEoNtvF1eqoPWAVa', 'user', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Ulasan selesai (masa lalu)
INSERT IGNORE INTO bookings (id, booking_code, id_user, id_field, booking_date, start_time, end_time, total_price, status, rate_service, rate_comfort, rate_place, review) VALUES
(101, 'BOOK-DEMO-01', 6, 1, '2026-09-05', '16:00:00', '18:00:00', 30000.00, 'completed', 5, 5, 4, 'Lapangannya bersih, rumputnya enak buat main malam. Recommended.'),
(102, 'BOOK-DEMO-02', 7, 1, '2026-09-08', '19:00:00', '21:00:00', 30000.00, 'completed', 5, 4, 5, 'Harga masuk akal, panitia ramah dan cepat balas chat.'),
(103, 'BOOK-DEMO-03', 8, 2, '2026-09-10', '16:00:00', '18:00:00', 300000.00, 'completed', 4, 5, 5, 'Lapangan luas, main ramai-ramai tetap lega. Puas.'),
(104, 'BOOK-DEMO-04', 9, 2, '2026-09-12', '20:00:00', '22:00:00', 300000.00, 'completed', 5, 5, 4, 'Tempat nyaman buat latihan rutin tim. Booking gampang.'),
(105, 'BOOK-DEMO-05', 10, 1, '2026-09-15', '08:00:00', '10:00:00', 30000.00, 'completed', 5, 4, 5, 'Bola dan lapangan siap pakai, tinggal main. Akan booking lagi.'),
(106, 'BOOK-DEMO-06', 6, 2, '2026-09-18', '19:00:00', '21:00:00', 300000.00, 'completed', 5, 5, 5, 'Jam operasional jelas, tinggal pilih slot di situs. Mantap.'),
(107, 'BOOK-DEMO-07', 5, 1, '2026-09-26', '19:00:00', '21:00:00', 30000.00, 'pending', 0, 0, 0, ''),
(108, 'BOOK-DEMO-08', 7, 1, '2026-10-03', '20:00:00', '22:00:00', 30000.00, 'confirmed', 0, 0, 0, ''),
(109, 'BOOK-DEMO-09', 8, 2, '2026-09-26', '20:00:00', '22:00:00', 300000.00, 'confirmed', 0, 0, 0, ''),
(110, 'BOOK-DEMO-10', 5, 1, '2026-09-20', '10:00:00', '12:00:00', 30000.00, 'completed', 0, 0, 0, '');

INSERT IGNORE INTO payments (id, booking_id, payment_status, amount, payment_date) VALUES
(101, 101, 'paid', 30000.00, '2026-09-05 15:00:00'),
(102, 102, 'paid', 30000.00, '2026-09-08 18:00:00'),
(103, 103, 'paid', 300000.00, '2026-09-10 15:00:00'),
(104, 104, 'paid', 300000.00, '2026-09-12 19:00:00'),
(105, 105, 'paid', 30000.00, '2026-09-15 07:30:00'),
(106, 106, 'paid', 300000.00, '2026-09-18 18:00:00'),
(107, 107, 'unpaid', 30000.00, NULL),
(108, 108, 'unpaid', 30000.00, NULL),
(109, 109, 'unpaid', 300000.00, NULL),
(110, 110, 'paid', 30000.00, '2026-09-20 09:00:00');
