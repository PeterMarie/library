<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuillWizards Library</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #7C5091, #8C60a1);
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .search-box input {
            border: none;
        }

        .search-box input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .search-box button:hover {
            background-color: #e0dff2;
            transform: scale(1.05);
        }

    </style>
</head>
<body>
    <header>
        <div class="top-header">
            <div class="title-div">
                <img src="../images/qw_silver.png" alt="QuillWizards Logo">
                <h1>QuillWizards Library</h1>
            </div>
            <nav class="nav-links">
                <a href="../" id="home-link">🏠 QuillWizards Home</a>
            </nav>
        </div>
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
