Instagram-like app using PHP where users can:

- **Login/Register**
- **Upload images & reels**
- **Like & Comment**
- **View other users' posts**
- **Data stored dynamically using phpMyAdmin**

# 📸 Insta-Clone (PHP + MySQL)

A full-stack Instagram-like clone built using **PHP**, **HTML**, **CSS**, **JavaScript**, and **phpMyAdmin (MySQL)**. This web app allows users to register, login, upload photos and reels, interact with other users' posts via likes and comments — simulating core features of Instagram.

## 🌐 Live Preview: [http://www.instaclone.infy.uk/]

---

## 🚀 Features

✅ User Registration & Login  
✅ Secure User Authentication  
✅ Upload Photos & Reels  
✅ View Reels from All Users  
✅ Like and Comment Functionality  
✅ Dynamic Storage using phpMyAdmin  
✅ Responsive & Clean UI  

---

## 🧑‍💻 Tech Stack

| Frontend | Backend | Database |
|----------|---------|----------|
| HTML5, CSS3, JavaScript | PHP | MySQL (phpMyAdmin) |

---


## ⚙️ Installation & Setup

1. Clone this repo
```bash
git clone https://github.com/rasool321/Insta-clone.git
```

2. Open in XAMPP (htdocs folder)

3. Import `insta_clone.sql` file (if included) into **phpMyAdmin**

4. Configure DB in `/db/connection.php`
```php
$host = "localhost";
$user = "root";
$password = "";
$database = "insta_clone";
```

5. Start Apache & MySQL in XAMPP  
6. Open browser: `http://localhost/Insta-clone/index.html`

---

## 🛡️ Security Notes

- Basic input validation is included.  
- For production, always use **prepared statements**, **password hashing**, and **CSRF protection**.

---

## ✨ Future Improvements

- 🔐 Password hashing (bcrypt)
- 💬 Real-time messaging with AJAX
- 📱 Mobile-friendly responsive design
- 🔔 Notifications for likes/comments

---

## 🤝 Contributing

Pull requests are welcome! If you'd like to contribute:
1. Fork the repo
2. Create your branch: `git checkout -b feature/feature-name`
3. Commit changes: `git commit -m "Added feature"`
4. Push to branch: `git push origin feature/feature-name`
5. Create a Pull Request

---

## 📩 Contact

**Rasool**  
📧 [Your Email]  
🐙 GitHub: [github.com/rasool321](https://github.com/rasool321)

---

## 🧡 Acknowledgements

Inspired by Instagram and made with 💻 & ☕ by Rasool.
```

---

Let me know if you want to include screenshots, demo video links, or add **deployment instructions on Render/Netlify/Vercel**, etc. I can help you host it too if you're ready for the next level 🚀
