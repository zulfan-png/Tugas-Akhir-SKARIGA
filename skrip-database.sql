-- Buat database (jika belum ada)
DROP DATABASE IF EXISTS bus_management_web;
CREATE DATABASE IF NOT EXISTS bus_management_web;
USE bus_management_web;

-- Tabel perusahaan_bus
CREATE TABLE IF NOT EXISTS perusahaan_bus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_perusahaan VARCHAR(255) NOT NULL,
    whatsapp_perusahaan VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel bus
CREATE TABLE IF NOT EXISTS bus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perusahaan_id INT,
    `tipe bus` VARCHAR(255) NOT NULL,
    jenis VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    status ENUM('Tersedia', 'Dalam Perawatan', 'Tidak Tersedia') DEFAULT 'Tersedia',
    whatsapp_perusahaan VARCHAR(20),
    fasilitas TEXT,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perusahaan_id) REFERENCES perusahaan_bus(id) ON DELETE SET NULL
);

-- Tabel bus_gambar
CREATE TABLE IF NOT EXISTS bus_gambar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    gambar_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES bus(id) ON DELETE CASCADE
);

-- Tabel harga_bus
CREATE TABLE IF NOT EXISTS harga_bus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    jenis_harga VARCHAR(100) NOT NULL,
    harga DECIMAL(15,2) NOT NULL,
    satuan VARCHAR(50) DEFAULT 'Paket',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES bus(id) ON DELETE CASCADE
);

-- Tabel datauser (untuk semua jenis user)
CREATE TABLE IF NOT EXISTS datauser (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nomor_hp VARCHAR(20),
    alamat TEXT,
    level ENUM('admin', 'operator', 'supir', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel pemesanan (booking) dengan fitur edit dan batal
CREATE TABLE IF NOT EXISTS pemesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bus_id INT NOT NULL,
    jenis_paket VARCHAR(100) NOT NULL,
    tanggal_berangkat DATE NOT NULL,
    jam_berangkat TIME NOT NULL,
    tanggal_kembali DATE NOT NULL,
    lokasi_penjemputan TEXT NOT NULL,
    tujuan TEXT NOT NULL,
    jumlah_penumpang INT NOT NULL,
    keterangan TEXT,
    total_harga DECIMAL(15,2) NOT NULL,
    status ENUM('Menunggu Konfirmasi', 'Dikonfirmasi', 'Dibatalkan') DEFAULT 'Menunggu Konfirmasi',
    permintaan_edit ENUM('Ya', 'Tidak') DEFAULT 'Tidak',
    permintaan_batal ENUM('Ya', 'Tidak') DEFAULT 'Tidak',
    alasan_batal TEXT,
    data_edit_sebelumnya JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES datauser(id) ON DELETE CASCADE,
    FOREIGN KEY (bus_id) REFERENCES bus(id) ON DELETE CASCADE
);

-- Insert sample data perusahaan_bus
INSERT INTO perusahaan_bus (nama_perusahaan) VALUES 
('PT. Bus Sejahtera'),
('PT. Transportasi Mandiri'),
('PT. Angkasa Jaya'),
('PT. Bus Mewah'),
('PT. Transportasi Andalan');

-- Insert sample data bus
INSERT INTO bus (perusahaan_id, `tipe bus`, jenis, kapasitas, status, whatsapp_perusahaan, fasilitas, deskripsi) VALUES 
(1, 'Big Bus', 'Executive', 40, 'Tersedia', '+628123456789', 'AC, Toilet, TV, WiFi, Charging Port, Reclining Seat', 'Bus executive dengan fasilitas lengkap untuk perjalanan jarak jauh yang nyaman. Dilengkapi dengan AC, toilet, TV, WiFi, dan charging port untuk kenyamanan penumpang.'),
(2, 'Medium Bus', 'Standard', 30, 'Tersedia', '+628987654321', 'AC, Charging Port, Bagasi Luas', 'Bus standard dengan kenyamanan optimal untuk perjalanan dalam kota dan luar kota. Kapasitas bagasi yang luas untuk kebutuhan perjalanan.'),
(3, 'Mini Bus', 'VIP', 15, 'Tersedia', '+628112233445', 'AC, TV, Karaoke, Reclining Seat, Bantal Selimut, Snack', 'Bus VIP dengan fasilitas mewah seperti karaoke, reclining seat, dan snack. Cocok untuk perjalanan keluarga atau bisnis.'),
(4, 'Big Bus', 'Super Executive', 45, 'Dalam Perawatan', '+628556677889', 'AC, Toilet, TV, WiFi, Karaoke, Charging Port, Reclining Seat, Bantal Selimut, Bagasi Luas, Snack', 'Bus super executive dengan semua fasilitas premium. Saat ini dalam perawatan rutin.'),
(5, 'Medium Bus', 'Business', 25, 'Tersedia', '+628998877665', 'AC, WiFi, Charging Port, Reclining Seat', 'Bus business class untuk perjalanan bisnis yang nyaman dan produktif.');

-- Insert sample data harga_bus
INSERT INTO harga_bus (bus_id, jenis_harga, harga, satuan) VALUES 
(1, 'Paket 6 Jam', 1500000.00, 'Paket'),
(1, 'Paket 12 Jam', 2500000.00, 'Paket'),
(1, 'Paket 24 Jam', 4000000.00, 'Paket'),
(1, 'Paket 6 Jam (Weekend)', 1800000.00, 'Paket'),
(1, 'Paket 12 Jam (Weekend)', 3000000.00, 'Paket'),
(1, 'Paket 24 Jam (Weekend)', 4800000.00, 'Paket'),
(1, 'Luar Kota', 3500000.00, 'Paket'),
(2, 'Paket 6 Jam', 1200000.00, 'Paket'),
(2, 'Paket 12 Jam', 2000000.00, 'Paket'),
(2, 'Paket 24 Jam', 3200000.00, 'Paket'),
(3, 'Paket 6 Jam', 1800000.00, 'Paket'),
(3, 'Paket 12 Jam', 2800000.00, 'Paket'),
(3, 'Paket 24 Jam', 4500000.00, 'Paket');

-- Insert sample data bus_gambar
INSERT INTO bus_gambar (bus_id, gambar_url) VALUES 
(1, 'bus1_1.jpg'),
(1, 'bus1_2.jpg'),
(1, 'bus1_3.jpg'),
(2, 'bus2_1.jpg'),
(2, 'bus2_2.jpg'),
(3, 'bus3_1.jpg'),
(3, 'bus3_2.jpg'),
(4, 'bus4_1.jpg'),
(5, 'bus5_1.jpg');

-- Insert sample data datauser (dengan password hashed - password: password123)
INSERT INTO datauser (nama_lengkap, username, password, email, nomor_hp, alamat, level) VALUES 
('Administrator System', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@busmanagement.com', '081234567890', 'Jl. Administrasi No. 1', 'admin'),
('Operator Bus', 'operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator@busmanagement.com', '081234567891', 'Jl. Operator No. 2', 'operator'),
('Supir Professional', 'supir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supir1@busmanagement.com', '081234567892', 'Jl. Supir No. 3', 'supir'),
('Customer Example', 'customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer1@example.com', '081234567893', 'Jl. Customer No. 4', 'customer'),
('John Doe', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@example.com', '081234567894', 'Jl. Merdeka No. 123', 'customer');

-- Insert sample data pemesanan
INSERT INTO pemesanan (user_id, bus_id, jenis_paket, tanggal_berangkat, jam_berangkat, tanggal_kembali, lokasi_penjemputan, tujuan, jumlah_penumpang, keterangan, total_harga, status) VALUES 
(4, 1, 'Paket 12 Jam', '2024-01-15', '08:00:00', '2024-01-15', 'Jl. Sudirman No. 123, Jakarta', 'Puncak, Bogor', 25, 'Perjalanan keluarga', 2500000.00, 'Dikonfirmasi'),
(5, 2, 'Paket 6 Jam', '2024-01-20', '09:00:00', '2024-01-20', 'Jl. Thamrin No. 45, Jakarta', 'Taman Mini, Jakarta', 15, 'Study tour sekolah', 1200000.00, 'Menunggu Konfirmasi'),
(4, 3, 'Paket 24 Jam', '2024-01-25', '07:00:00', '2024-01-26', 'Hotel Grand Indonesia, Jakarta', 'Bandung', 12, 'Perjalanan bisnis perusahaan', 4500000.00, 'Dikonfirmasi');

-- Buat index untuk optimasi query
CREATE INDEX idx_bus_perusahaan ON bus(perusahaan_id);
CREATE INDEX idx_gambar_bus ON bus_gambar(bus_id);
CREATE INDEX idx_harga_bus ON harga_bus(bus_id);
CREATE INDEX idx_username ON datauser(username);
CREATE INDEX idx_email ON datauser(email);
CREATE INDEX idx_level ON datauser(level);
CREATE INDEX idx_pemesanan_user ON pemesanan(user_id);
CREATE INDEX idx_pemesanan_bus ON pemesanan(bus_id);
CREATE INDEX idx_pemesanan_status ON pemesanan(status);
CREATE INDEX idx_pemesanan_tanggal ON pemesanan(tanggal_berangkat);

CREATE VIEW view_pemesanan_lengkap AS
SELECT 
    p.*,
    u.nama_lengkap AS nama_pemesan,
    u.nomor_hp AS hp_pemesan,
    u.email AS email_pemesan,
    b.`tipe bus`,
    b.jenis AS jenis_bus,
    b.kapasitas,
    per.nama_perusahaan,
    per.whatsapp_perusahaan
FROM pemesanan p
JOIN datauser u ON p.user_id = u.id
JOIN bus b ON p.bus_id = b.id
JOIN perusahaan_bus per ON b.perusahaan_id = per.id;