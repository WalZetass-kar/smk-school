-- =========================================================
-- SMK Negeri Contoh Indonesia — Website CMS
-- Database: smk_school
-- Engine: MySQL / MariaDB, InnoDB, utf8mb4
-- =========================================================

CREATE DATABASE IF NOT EXISTS smk_school
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smk_school;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- users (administrator accounts)
-- ---------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('superadmin','admin') NOT NULL DEFAULT 'admin',
  photo VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- school_profile (single-row settings table)
-- ---------------------------------------------------------
CREATE TABLE school_profile (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_name VARCHAR(150) NOT NULL,
  npsn VARCHAR(20) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  website VARCHAR(150) DEFAULT NULL,
  description TEXT,
  logo VARCHAR(255) DEFAULT NULL,
  favicon VARCHAR(255) DEFAULT NULL,
  theme_color_primary VARCHAR(20) DEFAULT '#0F2C59',
  theme_color_secondary VARCHAR(20) DEFAULT '#2563EB',
  theme_color_accent VARCHAR(20) DEFAULT '#F59E0B',
  theme_preset VARCHAR(50) DEFAULT 'navy_classic',
  map_url VARCHAR(255) DEFAULT NULL,
  accreditation VARCHAR(10) DEFAULT NULL,
  founded_year VARCHAR(10) DEFAULT NULL,
  running_text TEXT DEFAULT NULL,
  is_running_text_active TINYINT(1) DEFAULT 1,
  video_profile_url VARCHAR(255) DEFAULT 'https://www.youtube.com/watch?v=7vhYf5p_yhs',
  video_title VARCHAR(200) DEFAULT 'Video Profil & Fasilitas Sekolah',
  video_description TEXT DEFAULT NULL,
  video_features TEXT DEFAULT NULL,
  whatsapp_ppdb VARCHAR(50) DEFAULT '6281234567890',
  whatsapp_bkk VARCHAR(50) DEFAULT '6281234567891',
  whatsapp_tu VARCHAR(50) DEFAULT '6281234567892',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- banners (homepage hero slider)
-- ---------------------------------------------------------
CREATE TABLE banners (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  image VARCHAR(255) NOT NULL,
  button_text VARCHAR(50) DEFAULT NULL,
  button_url VARCHAR(255) DEFAULT NULL,
  sort_order INT UNSIGNED DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- principal_message (single-row)
-- ---------------------------------------------------------
CREATE TABLE principal_message (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  principal_name VARCHAR(100) NOT NULL,
  position VARCHAR(100) DEFAULT 'Kepala Sekolah',
  photo VARCHAR(255) DEFAULT NULL,
  content TEXT NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- history (single-row)
-- ---------------------------------------------------------
CREATE TABLE history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  content TEXT NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- vision_mission (single-row)
-- ---------------------------------------------------------
CREATE TABLE vision_mission (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vision TEXT NOT NULL,
  mission TEXT NOT NULL,
  goals TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- organization (structure chart entries / image)
-- ---------------------------------------------------------
CREATE TABLE organization (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  description TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- teachers (guru & tendik)
-- ---------------------------------------------------------
CREATE TABLE teachers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  nip VARCHAR(30) DEFAULT NULL,
  nuptk VARCHAR(30) DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  subject VARCHAR(100) DEFAULT NULL,
  education VARCHAR(100) DEFAULT NULL,
  position VARCHAR(100) DEFAULT NULL,
  category ENUM('guru','tendik') NOT NULL DEFAULT 'guru',
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- majors (jurusan / program keahlian)
-- ---------------------------------------------------------
CREATE TABLE majors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL UNIQUE,
  code VARCHAR(20) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  short_description VARCHAR(255) DEFAULT NULL,
  description TEXT,
  competency TEXT,
  career TEXT,
  facilities TEXT,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- news_categories
-- ---------------------------------------------------------
CREATE TABLE news_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- news (berita)
-- ---------------------------------------------------------
CREATE TABLE news (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  excerpt VARCHAR(255) DEFAULT NULL,
  content TEXT NOT NULL,
  featured_image VARCHAR(255) DEFAULT NULL,
  author_id INT UNSIGNED DEFAULT NULL,
  status ENUM('draft','publish') NOT NULL DEFAULT 'draft',
  views INT UNSIGNED NOT NULL DEFAULT 0,
  published_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE SET NULL,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_news_status_published (status, published_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- gallery_categories
-- ---------------------------------------------------------
CREATE TABLE gallery_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- galleries
-- ---------------------------------------------------------
CREATE TABLE galleries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(150) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  image VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- alumni_testimonials
-- ---------------------------------------------------------
CREATE TABLE alumni_testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  graduation_year VARCHAR(10) DEFAULT NULL,
  major VARCHAR(150) DEFAULT NULL,
  job VARCHAR(150) DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  testimonial TEXT NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- partners
-- ---------------------------------------------------------
CREATE TABLE partners (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  logo VARCHAR(255) NOT NULL,
  website VARCHAR(255) DEFAULT NULL,
  description VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- statistics
-- ---------------------------------------------------------
CREATE TABLE statistics (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  value INT UNSIGNED NOT NULL DEFAULT 0,
  icon VARCHAR(100) DEFAULT NULL,
  sort_order INT UNSIGNED DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- contacts (pesan masuk dari form kontak)
-- ---------------------------------------------------------
CREATE TABLE contacts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  subject VARCHAR(150) DEFAULT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- social_media
-- ---------------------------------------------------------
CREATE TABLE social_media (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(50) NOT NULL,
  url VARCHAR(255) NOT NULL,
  icon VARCHAR(100) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- ppdb_settings (pengaturan penerimaan siswa baru)
-- ---------------------------------------------------------
CREATE TABLE ppdb_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  academic_year VARCHAR(50) NOT NULL DEFAULT '2027/2028',
  is_open TINYINT(1) NOT NULL DEFAULT 1,
  start_date DATE DEFAULT '2027-05-01',
  end_date DATE DEFAULT '2027-07-10',
  announcement_date DATE DEFAULT '2027-07-15',
  information TEXT,
  contact_person VARCHAR(100) DEFAULT '(0761) 123456 / 0812-3456-7890',
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- ppdb_registrations (pendaftaran siswa baru)
-- ---------------------------------------------------------
CREATE TABLE ppdb_registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  registration_no VARCHAR(50) NOT NULL UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  nisn VARCHAR(20) NOT NULL,
  nik VARCHAR(25) NOT NULL,
  gender ENUM('L','P') NOT NULL,
  birth_place VARCHAR(100) NOT NULL,
  birth_date DATE NOT NULL,
  religion VARCHAR(50) NOT NULL,
  address TEXT NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  school_origin VARCHAR(150) NOT NULL,
  graduation_year VARCHAR(10) NOT NULL,
  parent_name VARCHAR(150) NOT NULL,
  parent_phone VARCHAR(30) NOT NULL,
  parent_job VARCHAR(100) DEFAULT NULL,
  major_choice_1 INT UNSIGNED DEFAULT NULL,
  major_choice_2 INT UNSIGNED DEFAULT NULL,
  average_score DECIMAL(5,2) DEFAULT NULL,
  document_photo VARCHAR(255) DEFAULT NULL,
  document_kk VARCHAR(255) DEFAULT NULL,
  document_rapor VARCHAR(255) DEFAULT NULL,
  status ENUM('pending', 'verified', 'accepted', 'rejected', 'reserve') NOT NULL DEFAULT 'pending',
  notes TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (major_choice_1) REFERENCES majors(id) ON DELETE CASCADE,
  FOREIGN KEY (major_choice_2) REFERENCES majors(id) ON DELETE SET NULL,
  INDEX idx_ppdb_status (status),
  INDEX idx_ppdb_reg_no (registration_no)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- bkk_jobs (lowongan kerja / bursa kerja khusus)
-- ---------------------------------------------------------
CREATE TABLE bkk_jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  company_name VARCHAR(150) NOT NULL,
  company_logo VARCHAR(255) DEFAULT NULL,
  job_type ENUM('fulltime', 'parttime', 'internship', 'contract') NOT NULL DEFAULT 'fulltime',
  location VARCHAR(150) NOT NULL,
  salary VARCHAR(100) DEFAULT NULL,
  description TEXT NOT NULL,
  requirements TEXT NOT NULL,
  apply_url VARCHAR(255) DEFAULT NULL,
  deadline DATE DEFAULT NULL,
  status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
  views INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_bkk_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- extracurriculars (kegiatan ekstrakurikuler)
-- ---------------------------------------------------------
CREATE TABLE extracurriculars (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  category VARCHAR(100) NOT NULL DEFAULT 'Olahraga',
  icon VARCHAR(100) DEFAULT 'bi-trophy',
  image VARCHAR(255) DEFAULT NULL,
  schedule VARCHAR(150) DEFAULT NULL,
  coach_name VARCHAR(100) DEFAULT NULL,
  description TEXT NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- achievements (prestasi siswa / kejuaraan)
-- ---------------------------------------------------------
CREATE TABLE achievements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  student_name VARCHAR(150) NOT NULL,
  competition_name VARCHAR(200) NOT NULL,
  level ENUM('sekolah', 'kota', 'provinsi', 'nasional', 'internasional') NOT NULL DEFAULT 'kota',
  rank_position VARCHAR(50) NOT NULL,
  year VARCHAR(10) NOT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_achievement_level (level),
  INDEX idx_achievement_year (year)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- downloads (pusat unduhan berkas publik)
-- ---------------------------------------------------------
CREATE TABLE downloads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'Akademik',
  file_path VARCHAR(255) NOT NULL,
  file_size VARCHAR(50) DEFAULT NULL,
  file_ext VARCHAR(10) DEFAULT 'pdf',
  description TEXT DEFAULT NULL,
  downloads_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_download_cat (category)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- DATA DEMO
-- Admin password below is "admin123" hashed with password_hash()
-- (bcrypt, cost 10) — CHANGE THIS after first login.
-- =========================================================

INSERT INTO users (name, username, email, password, role) VALUES
('Super Administrator', 'superadmin', 'superadmin@smkcontoh.sch.id',
 '$2y$10$/Jol.8YvOsihGbMpgQTM0.M5FbOQv7cDC7kPAVU5C0.ONpT4asVm6', 'superadmin'),
('Admin Konten', 'admin', 'admin@smkcontoh.sch.id',
 '$2y$10$/Jol.8YvOsihGbMpgQTM0.M5FbOQv7cDC7kPAVU5C0.ONpT4asVm6', 'admin');

INSERT INTO school_profile (school_name, npsn, address, phone, email, website, description, logo, favicon, map_url, accreditation, founded_year) VALUES
('SMK Negeri Contoh Indonesia', '12345678', 'Jl. Pendidikan Kejuruan No. 1, Pekanbaru, Riau',
 '(0761) 123456', 'info@smkcontoh.sch.id', 'https://smkcontoh.sch.id',
 'SMK Negeri Contoh Indonesia adalah sekolah menengah kejuruan yang berkomitmen mencetak lulusan kompeten, kreatif, dan siap menghadapi dunia kerja maupun dunia usaha/industri.',
 'assets/images/logo.png', 'assets/images/favicon.png',
 'https://maps.google.com/?q=SMK+Negeri+Contoh+Indonesia', 'A', '1998');

INSERT INTO banners (title, description, image, button_text, button_url, sort_order, status) VALUES
('Selamat Datang di SMK Negeri Contoh Indonesia', 'Menyiapkan Generasi Kompeten, Kreatif, dan Siap Menghadapi Dunia Kerja', 'assets/images/banner-1.jpg', 'Selengkapnya', '/profil', 1, 1),
('Penerimaan Peserta Didik Baru 2027/2028', 'Daftarkan dirimu sekarang dan raih masa depan yang cerah bersama kami', 'assets/images/banner-2.jpg', 'Daftar Sekarang', '/ppdb/daftar', 2, 1),
('Fasilitas Praktik Modern', 'Belajar langsung dengan peralatan industri terkini di setiap program keahlian', 'assets/images/banner-3.jpg', 'Lihat Jurusan', '/jurusan', 3, 1);

INSERT INTO principal_message (principal_name, position, photo, content) VALUES
('Drs. Bambang Sutrisno, M.Pd.', 'Kepala Sekolah', 'assets/images/kepala-sekolah.jpg',
 'Assalamu\'alaikum Warahmatullahi Wabarakatuh. Selamat datang di website resmi SMK Negeri Contoh Indonesia. Sekolah kami berkomitmen penuh untuk membentuk lulusan yang tidak hanya unggul secara akademik, tetapi juga memiliki keterampilan kerja nyata yang relevan dengan kebutuhan dunia usaha dan dunia industri. Melalui kurikulum berbasis kompetensi, kerja sama dengan mitra industri, serta fasilitas praktik yang terus kami kembangkan, kami berharap setiap siswa dapat tumbuh menjadi pribadi yang kompeten, kreatif, dan berkarakter. Mari bersama membangun masa depan yang lebih baik.');

INSERT INTO history (title, content) VALUES
('Sejarah SMK Negeri Contoh Indonesia',
 'SMK Negeri Contoh Indonesia didirikan pada tahun 1998 dengan tujuan menyediakan pendidikan kejuruan berkualitas bagi masyarakat. Berawal dari dua program keahlian dengan jumlah siswa terbatas, sekolah ini terus berkembang seiring waktu, baik dari sisi jumlah program keahlian, jumlah siswa, maupun fasilitas pembelajaran. Saat ini SMK Negeri Contoh Indonesia telah terakreditasi A dan menjadi salah satu rujukan pendidikan kejuruan di wilayahnya, dengan jaringan kerja sama industri yang terus bertambah setiap tahunnya.');

INSERT INTO vision_mission (vision, mission, goals) VALUES
('Menjadi lembaga pendidikan kejuruan unggulan yang menghasilkan lulusan kompeten, berkarakter, dan berdaya saing global.',
 '1. Menyelenggarakan pembelajaran berbasis kompetensi dan berorientasi industri.\n2. Membangun kerja sama yang kuat dengan dunia usaha dan dunia industri.\n3. Mengembangkan karakter disiplin, jujur, dan mandiri pada setiap siswa.\n4. Meningkatkan kompetensi pendidik dan tenaga kependidikan secara berkelanjutan.',
 'Menghasilkan lulusan yang siap kerja, siap berwirausaha, dan siap melanjutkan pendidikan ke jenjang lebih tinggi.');

INSERT INTO organization (title, image, description) VALUES
('Struktur Organisasi Sekolah', 'assets/images/struktur-organisasi.jpg', 'Bagan struktur organisasi SMK Negeri Contoh Indonesia tahun ajaran berjalan.');

INSERT INTO teachers (name, nip, nuptk, photo, subject, education, position, category, status) VALUES
('Drs. Bambang Sutrisno, M.Pd.', '196501011990031001', NULL, 'assets/images/guru-1.jpg', 'Manajemen Pendidikan', 'S2 Manajemen Pendidikan', 'Kepala Sekolah', 'guru', 'aktif'),
('Siti Rahmawati, S.Kom.', '198203152008012002', NULL, 'assets/images/guru-2.jpg', 'Rekayasa Perangkat Lunak', 'S1 Ilmu Komputer', 'Kepala Program Keahlian RPL', 'guru', 'aktif'),
('Ahmad Fauzi, S.T.', '198507202010011003', NULL, 'assets/images/guru-3.jpg', 'Teknik Komputer dan Jaringan', 'S1 Teknik Informatika', 'Guru Produktif TKJ', 'guru', 'aktif'),
('Dewi Lestari, S.Pd.', '199001102015012004', NULL, 'assets/images/guru-4.jpg', 'Akuntansi', 'S1 Pendidikan Akuntansi', 'Guru Produktif Akuntansi', 'guru', 'aktif'),
('Rudi Hartono, A.Md.', NULL, NULL, 'assets/images/tendik-1.jpg', NULL, 'D3 Administrasi', 'Kepala Tata Usaha', 'tendik', 'aktif');

INSERT INTO majors (name, slug, code, image, short_description, description, competency, career, facilities, status) VALUES
('Teknik Komputer dan Jaringan', 'teknik-komputer-dan-jaringan', 'TKJ', 'assets/images/jurusan-tkj.jpg',
 'Mempelajari instalasi, konfigurasi, dan perawatan jaringan komputer serta infrastruktur IT.',
 'Program keahlian yang membekali siswa dengan kemampuan merancang, membangun, dan memelihara sistem jaringan komputer, baik skala kecil maupun enterprise.',
 'Instalasi jaringan LAN/WAN, administrasi server, keamanan jaringan, troubleshooting perangkat keras dan lunak.',
 'Teknisi jaringan, network administrator, IT support, wirausaha di bidang IT.',
 'Lab komputer, lab jaringan, peralatan server, akses internet dedicated.', 1),
('Rekayasa Perangkat Lunak', 'rekayasa-perangkat-lunak', 'RPL', 'assets/images/jurusan-rpl.jpg',
 'Mempelajari pengembangan aplikasi web, mobile, dan desktop dari perancangan hingga implementasi.',
 'Program keahlian yang membekali siswa dengan kemampuan merancang basis data, membangun aplikasi, serta menerapkan siklus pengembangan perangkat lunak.',
 'Pemrograman web dan mobile, basis data, algoritma pemrograman, pengujian perangkat lunak.',
 'Web developer, mobile developer, quality assurance, wirausaha startup digital.',
 'Lab komputer, akses internet, perangkat pengembangan aplikasi.', 1),
('Teknik Kendaraan Ringan', 'teknik-kendaraan-ringan', 'TKR', 'assets/images/jurusan-tkr.jpg',
 'Mempelajari perawatan dan perbaikan mesin, kelistrikan, dan sistem kendaraan ringan.',
 'Program keahlian yang membekali siswa dengan kemampuan mendiagnosis dan memperbaiki kendaraan ringan sesuai standar industri otomotif.',
 'Perawatan mesin, sistem kelistrikan kendaraan, sistem kemudi dan rem, tune-up kendaraan.',
 'Teknisi otomotif, mekanik bengkel resmi, wirausaha bengkel.',
 'Bengkel praktik, unit kendaraan latih, peralatan diagnostik.', 1),
('Akuntansi dan Keuangan Lembaga', 'akuntansi-dan-keuangan-lembaga', 'AKL', 'assets/images/jurusan-akl.jpg',
 'Mempelajari pencatatan, pengelolaan, dan pelaporan keuangan sesuai standar akuntansi.',
 'Program keahlian yang membekali siswa dengan kemampuan menyusun laporan keuangan dan mengelola administrasi keuangan lembaga maupun perusahaan.',
 'Akuntansi dasar, akuntansi perusahaan jasa dan dagang, aplikasi komputer akuntansi, administrasi perpajakan.',
 'Staf akuntansi, staf keuangan, teller bank, wirausaha jasa pembukuan.',
 'Lab akuntansi, aplikasi komputer akuntansi, simulasi perbankan mini.', 1);

INSERT INTO news_categories (name, slug) VALUES
('Pengumuman', 'pengumuman'),
('Kegiatan', 'kegiatan'),
('Prestasi', 'prestasi');

INSERT INTO news (category_id, title, slug, excerpt, content, featured_image, author_id, status, views, published_at) VALUES
(1, 'Penerimaan Peserta Didik Baru Tahun Ajaran 2027/2028', 'penerimaan-peserta-didik-baru-2027-2028',
 'Pendaftaran PPDB SMK Negeri Contoh Indonesia resmi dibuka mulai bulan depan.',
 'SMK Negeri Contoh Indonesia dengan bangga mengumumkan bahwa pendaftaran Peserta Didik Baru untuk tahun ajaran 2027/2028 resmi dibuka. Calon peserta didik dapat mendaftar secara online melalui website sekolah atau datang langsung ke sekolah pada jam kerja. Sekolah menyediakan empat program keahlian unggulan yang telah terakreditasi A.',
 'assets/images/berita-1.jpg', 1, 'publish', 245, '2026-08-01 08:00:00'),
(2, 'Upacara Peringatan Hari Kemerdekaan RI', 'upacara-peringatan-hari-kemerdekaan-ri',
 'Seluruh siswa dan guru mengikuti upacara bendera dengan khidmat di lapangan sekolah.',
 'Dalam rangka memperingati Hari Kemerdekaan Republik Indonesia, SMK Negeri Contoh Indonesia menggelar upacara bendera yang diikuti oleh seluruh siswa, guru, dan tenaga kependidikan. Upacara berlangsung khidmat dengan petugas upacara dari perwakilan siswa kelas XI.',
 'assets/images/berita-2.jpg', 1, 'publish', 189, '2026-08-17 09:00:00'),
(3, 'Siswa SMK Raih Juara 1 Lomba Kompetensi Siswa Tingkat Provinsi', 'siswa-raih-juara-1-lks-tingkat-provinsi',
 'Tim siswa jurusan RPL berhasil membawa pulang piala juara 1 dalam ajang LKS tingkat provinsi.',
 'Prestasi membanggakan kembali diukir oleh siswa SMK Negeri Contoh Indonesia. Dalam ajang Lomba Kompetensi Siswa (LKS) tingkat provinsi bidang Rekayasa Perangkat Lunak, tim sekolah berhasil meraih juara 1 setelah bersaing dengan puluhan sekolah lain.',
 'assets/images/berita-3.jpg', 2, 'publish', 312, '2026-08-20 10:30:00'),
(2, 'Kegiatan Praktik Kerja Lapangan Siswa Kelas XII', 'kegiatan-praktik-kerja-lapangan-siswa-kelas-xii',
 'Sebanyak 120 siswa kelas XII diberangkatkan untuk mengikuti PKL di berbagai perusahaan mitra.',
 'SMK Negeri Contoh Indonesia melepas 120 siswa kelas XII untuk mengikuti program Praktik Kerja Lapangan (PKL) di berbagai perusahaan mitra industri. Program ini bertujuan memberikan pengalaman kerja nyata sebelum siswa menyelesaikan pendidikan.',
 'assets/images/berita-4.jpg', 2, 'publish', 156, '2026-08-25 13:00:00');

INSERT INTO gallery_categories (name, slug) VALUES
('Kegiatan Sekolah', 'kegiatan-sekolah'),
('Praktik Siswa', 'praktik-siswa'),
('Prestasi', 'prestasi'),
('Ekstrakurikuler', 'ekstrakurikuler');

INSERT INTO galleries (category_id, title, description, image) VALUES
(2, 'Praktik Jaringan Komputer', 'Siswa TKJ melakukan praktik konfigurasi jaringan.', 'assets/images/galeri-1.jpg'),
(1, 'Kegiatan Upacara Bendera', 'Upacara rutin setiap hari Senin.', 'assets/images/galeri-2.jpg'),
(3, 'Penyerahan Piala Juara LKS', 'Penyerahan piala juara LKS tingkat provinsi.', 'assets/images/galeri-3.jpg'),
(4, 'Latihan Ekstrakurikuler Pramuka', 'Kegiatan rutin ekstrakurikuler Pramuka.', 'assets/images/galeri-4.jpg');

INSERT INTO alumni_testimonials (name, graduation_year, major, job, photo, testimonial, status) VALUES
('Anisa Putri', '2022', 'Rekayasa Perangkat Lunak', 'Web Developer di PT Teknologi Nusantara', 'assets/images/alumni-1.jpg',
 'Selama belajar di SMK ini saya mendapatkan banyak pengalaman dan keterampilan yang sangat membantu ketika memasuki dunia kerja.', 1),
('Dimas Prasetyo', '2021', 'Teknik Komputer dan Jaringan', 'Network Administrator di PT Data Prima', 'assets/images/alumni-2.jpg',
 'Ilmu dan praktik yang saya dapatkan di sekolah ini menjadi bekal utama saya bekerja sebagai teknisi jaringan profesional.', 1),
('Rina Marlina', '2023', 'Akuntansi dan Keuangan Lembaga', 'Staf Keuangan di Koperasi Sejahtera', 'assets/images/alumni-3.jpg',
 'Pembelajaran praktik akuntansi yang diberikan sangat relevan dengan pekerjaan saya sekarang.', 1);

INSERT INTO partners (name, logo, website, description, status, sort_order) VALUES
('PT Teknologi Nusantara', 'assets/images/partner-1.png', 'https://teknologinusantara.example.com', 'Perusahaan teknologi informasi.', 1, 1),
('PT Data Prima', 'assets/images/partner-2.png', 'https://dataprima.example.com', 'Penyedia layanan jaringan dan infrastruktur IT.', 1, 2),
('Politeknik Negeri Riau', 'assets/images/partner-3.png', 'https://polinerau.example.com', 'Mitra kelanjutan studi bagi lulusan.', 1, 3),
('Dinas Pendidikan Provinsi Riau', 'assets/images/partner-4.png', 'https://disdik.riau.go.id', 'Instansi pemerintah bidang pendidikan.', 1, 4);

INSERT INTO statistics (title, value, icon, sort_order, status) VALUES
('Jumlah Siswa', 1240, 'bi-people-fill', 1, 1),
('Jumlah Guru', 68, 'bi-person-workspace', 2, 1),
('Jumlah Jurusan', 4, 'bi-mortarboard-fill', 3, 1),
('Alumni Bekerja', 3200, 'bi-briefcase-fill', 4, 1),
('Partner Industri', 25, 'bi-building', 5, 1);

INSERT INTO social_media (platform, url, icon, status) VALUES
('Facebook', 'https://facebook.com/smkcontohindonesia', 'bi-facebook', 1),
('Instagram', 'https://instagram.com/smkcontohindonesia', 'bi-instagram', 1),
('YouTube', 'https://youtube.com/@smkcontohindonesia', 'bi-youtube', 1),
('TikTok', 'https://tiktok.com/@smkcontohindonesia', 'bi-tiktok', 1),
('WhatsApp', 'https://wa.me/6281234567890', 'bi-whatsapp', 1);

INSERT INTO bkk_jobs (title, slug, company_name, company_logo, job_type, location, salary, description, requirements, apply_url, deadline, status)
VALUES 
('Junior Web Developer', 'junior-web-developer', 'PT Nusantara Digital Inovasi', 'assets/images/partner-1.png', 'fulltime', 'Pekanbaru', 'Rp 4.000.000 - Rp 5.500.000',
'Kami membuka kesempatan bagi lulusan SMK jurusan RPL / Rekayasa Perangkat Lunak untuk bergabung sebagai Junior Web Developer dalam mengembangkan aplikasi web enterprise.',
'1. Lulusan SMK Jurusan RPL / TKJ.\n2. Menguasai HTML, CSS, JavaScript, PHP, dan MySQL.\n3. Memahami konsep Git dan REST API menjadi nilai tambah.\n4. Disiplin, komunikatif, dan mampu bekerja dalam tim.',
'https://wa.me/6281234567890?text=Halo%2C%20saya%20ingin%20melamar%20posisi%20Junior%20Web%20Developer', '2027-10-31', 'open'),

('Teknisi Jaringan & Server Support', 'teknisi-jaringan-dan-server-support', 'PT Riau Cyber Solution', 'assets/images/partner-2.png', 'fulltime', 'Pekanbaru', 'Rp 3.800.000 - Rp 4.800.000',
'Dibutuhkan tenaga teknisi jaringan komputer muda untuk instalasi kabel fiber optic, konfigurasi router Mikrotik, dan pemeliharaan server.',
'1. Lulusan SMK Jurusan TKJ.\n2. Memahami instalasi LAN/WAN, routing Mikrotik, dan crimping kabel.\n3. Memiliki SIM C dan bersedia tugas lapangan.\n4. Siap bekerja dengan sistem shift jika dibutuhkan.',
'https://wa.me/6281234567890?text=Halo%2C%20saya%20ingin%20melamar%20posisi%20Teknisi%20Jaringan', '2027-11-15', 'open'),

('Mekanik Service Otomotif Roda Empat', 'mekanik-service-otomotif-roda-empat', 'Bengkel Resmi Maju Motor', 'assets/images/partner-3.png', 'fulltime', 'Pekanbaru', 'Rp 3.500.000 - Rp 4.500.000',
'Peluang karir bagi lulusan SMK TKR untuk menjadi mekanik servis berkala dan perbaikan sistem kelistrikan mobil.',
'1. Lulusan SMK Jurusan TKR.\n2. Menguasai tune-up mesin, servis rem, dan perawatan berkala kendaraan roda empat.\n3. Teliti, jujur, dan berorientasi pada kepuasan pelanggan.',
'info@majumotor.co.id', '2027-12-01', 'open');

INSERT INTO extracurriculars (name, slug, category, icon, image, schedule, coach_name, description, status)
VALUES
('Pramuka Ambalan', 'pramuka-ambalan', 'Kepemimpinan & Karakter', 'bi-compass', 'assets/images/galeri-1.jpg', 'Setiap Jumat, 14.30 - 17.00 WIB', 'Kak Ahmad Fauzi, S.Pd.', 'Membentuk karakter kepemimpinan, kemandirian, kedisiplinan, dan kepedulian sosial tinggi.', 1),
('IT Club & Robotik', 'it-club-dan-robotik', 'Kejuruan & Teknologi', 'bi-cpu', 'assets/images/galeri-2.jpg', 'Setiap Sabtu, 09.00 - 12.00 WIB', 'Siti Rahmawati, S.Kom.', 'Wadah pengembangan skill coding, IoT, robotika, dan persiapan lomba kompetisi kejuruan LKS.', 1),
('Futsal & Sepak Bola', 'futsal-dan-sepak-bola', 'Olahraga & Prestasi', 'bi-dribbble', 'assets/images/galeri-3.jpg', 'Setiap Selasa & Kamis, 16.00 - 18.00 WIB', 'Budi Santoso, S.Pd.', 'Melatih fisik, ketangkasan, dan sportivitas tim dalam kompetisi futsal antar sekolah.', 1),
('Palang Merah Remaja (PMR)', 'palang-merah-remaja', 'Sosial & Kemanusiaan', 'bi-heart-pulse', 'assets/images/galeri-4.jpg', 'Setiap Rabu, 15.00 - 17.00 WIB', 'Dewi Lestari, S.Pd.', 'Pelatihan pertolongan pertama pada kecelakaan (P3K), kesiapsiagaan bencana, dan aksi donor darah.', 1);

INSERT INTO achievements (title, slug, student_name, competition_name, level, rank_position, year, photo, description)
VALUES
('Juara 1 LKS SMK Bidang Web Technologies', 'juara-1-lks-smk-web-technologies', 'Rian Hidayat (Kelas XII RPL)', 'Lomba Kompetensi Siswa (LKS) SMK Tingkat Provinsi Riau', 'provinsi', 'Juara 1', '2026', 'assets/images/galeri-2.jpg', 'Berhasil meraih medali emas dalam kompetisi pengembangan aplikasi web fullstack dan berhak mewakili provinsi ke tingkat nasional.'),
('Juara 2 LKS SMK IT Network System Administration', 'juara-2-lks-smk-it-network-administration', 'Fajar Ramadhan (Kelas XII TKJ)', 'Lomba Kompetensi Siswa (LKS) SMK Tingkat Kota Pekanbaru', 'kota', 'Juara 2', '2026', 'assets/images/galeri-1.jpg', 'Meraih prestasi gemilang dalam konfigurasi server Linux, keamanan jaringan, dan simulasi jaringan enterprise.'),
('Juara 1 Turnamen Futsal Pelajar Riau Cup', 'juara-1-turnamen-futsal-pelajar-riau-cup', 'Tim Futsal SMK Negeri Contoh', 'Turnamen Futsal Antar Pelajar Se-Sumatera Bagian Tengah', 'provinsi', 'Juara 1', '2025', 'assets/images/galeri-3.jpg', 'Tim futsal sekolah sukses meraih piala bergilir setelah mengalahkan 32 tim perwakilan sekolah lainnya.');

-- ---------------------------------------------------------
-- graduation_settings & graduations (SKL Online)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS graduation_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  academic_year VARCHAR(20) NOT NULL DEFAULT '2026/2027',
  announcement_date DATETIME NOT NULL DEFAULT '2026-05-05 10:00:00',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  header_info TEXT,
  signature_city VARCHAR(100) DEFAULT 'Pekanbaru',
  signature_date DATE DEFAULT '2026-05-05',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS graduations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nisn VARCHAR(20) NOT NULL UNIQUE,
  exam_number VARCHAR(30) NOT NULL UNIQUE,
  student_name VARCHAR(150) NOT NULL,
  birth_place VARCHAR(100) DEFAULT NULL,
  birth_date DATE DEFAULT NULL,
  major_name VARCHAR(100) NOT NULL,
  average_score DECIMAL(5,2) DEFAULT '85.50',
  status ENUM('lulus', 'tidak_lulus', 'ditunda') NOT NULL DEFAULT 'lulus',
  remarks TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- agendas (Agenda Kegiatan Sekolah)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS agendas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  start_date DATE NOT NULL,
  end_date DATE DEFAULT NULL,
  time_info VARCHAR(100) DEFAULT '08.00 WIB - Selesai',
  location VARCHAR(200) DEFAULT 'Aula Utama Sekolah',
  organizer VARCHAR(100) DEFAULT 'Waka Kurikulum / Kesiswaan',
  description TEXT,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- guestbooks (Buku Tamu Digital)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS guestbooks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  institution VARCHAR(150) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  purpose ENUM('Dinas / Instansi', 'Orang Tua / Wali Siswa', 'Kemitraan Industri', 'Alumni', 'Umum') NOT NULL DEFAULT 'Umum',
  person_to_meet VARCHAR(150) DEFAULT 'Kepala Sekolah / Tata Usaha',
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- activity_logs (Audit Trail Keamanan Admin)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  user_name VARCHAR(100) DEFAULT 'System',
  user_role VARCHAR(50) DEFAULT 'admin',
  action VARCHAR(100) NOT NULL,
  module VARCHAR(100) NOT NULL,
  detail TEXT DEFAULT NULL,
  ip_address VARCHAR(45) NOT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_module (module),
  INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- rate_limits (Proteksi Brute Force & Rate Limiting)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
  rate_key VARCHAR(150) PRIMARY KEY,
  attempts INT UNSIGNED NOT NULL DEFAULT 1,
  last_attempt INT UNSIGNED NOT NULL,
  lockout_until INT UNSIGNED DEFAULT NULL
) ENGINE=InnoDB;



