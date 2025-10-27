<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset dan gaya dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        /* Container registrasi */
        .register-container {
            width: 100%;
            max-width: 500px;
        }
        
        /* Card registrasi */
        .register-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            padding: 40px 35px;
            text-align: center;
        }
        
        /* Header registrasi */
        .register-header {
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #1e40af;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .register-header p {
            color: #64748b;
            font-size: 15px;
        }
        
        /* Form registrasi */
        .register-form {
            text-align: left;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e40af;
            font-size: 14px;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            border-color: #1e40af;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        /* Tombol registrasi */
        .btn-register {
            width: 100%;
            background-color: #1e40af;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
        }
        
        .btn-register:hover {
            background-color: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.25);
        }
        
        /* Tombol kembali */
        .btn-back {
            width: 100%;
            background-color: transparent;
            color: #1e40af;
            border: 2px solid #1e40af;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background-color: #1e40af;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.25);
        }
        
        /* Footer registrasi */
        .register-footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            color: #64748b;
            font-size: 14px;
        }
        
        .register-footer a {
            color: #1e40af;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        /* Responsif */
        @media (max-width: 480px) {
            .register-card {
                padding: 30px 25px;
            }
            
            .register-header h1 {
                font-size: 24px;
            }
        }
        
        /* Pesan error */
        .error-message {
            color: #dc2626;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        /* Validasi input */
        .form-group input:invalid:not(:focus):not(:placeholder-shown) {
            border-color: #dc2626;
        }
        
        .form-group input:invalid:not(:focus):not(:placeholder-shown) + .error-message {
            display: block;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>REGISTRASI USER</h1>
                <p>Buat akun baru untuk memesan tiket bus</p>
            </div>
            <form action="register_process.php" method="post" class="register-form" id="registerForm">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                    <div class="error-message">Nama lengkap harus diisi</div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required minlength="3">
                    <div class="error-message">Username minimal 3 karakter</div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required minlength="6">
                    <div class="error-message">Password minimal 6 karakter</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Masukkan ulang password" required>
                    <div class="error-message">Konfirmasi password tidak cocok</div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan alamat email" required>
                    <div class="error-message">Format email tidak valid</div>
                </div>
                
                <div class="form-group">
                    <label for="nomor_hp">Nomor HP</label>
                    <input type="tel" id="nomor_hp" name="nomor_hp" placeholder="Masukkan nomor HP" required pattern="[0-9]{10,15}">
                    <div class="error-message">Nomor HP harus 10-15 digit angka</div>
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" placeholder="Masukkan alamat lengkap"></textarea>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    Daftar
                </button>
                
                <a href="index.html" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Login
                </a>
            </form>
            
            <div class="register-footer">
                <p>Sudah punya akun? <a href="index.html">Masuk di sini</a></p>
            </div>
        </div>
    </div>

    <script>
        // Validasi form client-side
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validasi konfirmasi password
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                document.getElementById('confirm_password').focus();
            }
        });

        // Validasi real-time untuk konfirmasi password
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const errorMessage = this.nextElementSibling;
            
            if (password !== confirmPassword && confirmPassword !== '') {
                this.style.borderColor = '#dc2626';
                errorMessage.style.display = 'block';
            } else {
                this.style.borderColor = '#e2e8f0';
                errorMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>