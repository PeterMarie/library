<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuillWizards Library</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6f42c1, #a78bfa);
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            display: flex;
            align-items: center;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.05);
        }

        header img {
            height: 50px;
            margin-right: 15px;
        }

        header h1 {
            font-size: 26px;
            margin: 0;
        }

        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
        }

        .intro-text {
            max-width: 700px;
            font-size: 22px;
            margin-bottom: 40px;
            animation: fadeInUp 1s ease forwards;
        }

        .search-box {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
            animation: fadeInUp 1.2s ease forwards;
        }

        .search-box input {
            padding: 12px 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            width: 300px;
            max-width: 90%;
            transition: box-shadow 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .search-box button {
            padding: 12px 18px;
            font-size: 16px;
            background-color: #fff;
            color: #6f42c1;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .search-box button:hover {
            background-color: #e0dff2;
            transform: scale(1.05);
        }

        .browse-button {
            padding: 12px 20px;
            font-size: 16px;
            border: 2px solid #fff;
            background-color: transparent;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .browse-button:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: scale(1.05);
        }

        footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <header>
        <img src="../images/qw_silver.png" alt="QuillWizards Logo">
        <h1>QuillWizards Library</h1>
    </header>
    <main>
        <div class="intro-text">
            Welcome to the QuillWizards Library – a freely accessible platform where research meets opportunity. Discover, explore, and download scholarly works with ease.
        </div>
        <div class="search-box">
            <input type="text" id="searchQuery" placeholder="Search research titles, authors or keywords...">
            <button onclick="performSearch()">Search</button>
        </div>
        <button class="browse-button" onclick="browseAll()">Browse Full Library</button>
    </main>
    <footer>
        &copy; 2025 QuillWizards. All rights reserved.
    </footer>

    <script>
        function performSearch() {
            const query = document.getElementById('searchQuery').value.trim();
            if (query) {
                window.location.href = `viewer.html?search=${encodeURIComponent(query)}`;
            }
        }

        function browseAll() {
            window.location.href = 'viewer.html';
        }
    </script>
</body>
</html>
