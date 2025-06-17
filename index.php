<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuillWizards Library</title>
    <link rel="stylesheet" href="css/qw_advert.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
                <a href="index.php"><span class="nav-icon"><i class="fa fa-book"></i></span><span>Library Home</span></a>
                <a href="https://www.quillwizards.com/"><span class="nav-icon"><i class="fa fa-house"></i></span><span>QuillWizards Home</span></a>
            </nav>
        </div>
    </header>
    <main>
        <img src="img/bookshelf_cropped.png" alt="png image from pngtree.com" id="library-home-image" style="width:40%"/>
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

    
    <script src="qw_advert_layout1.js" ></script>
    <script>
        let ad_number = Math.floor(Math.random() * (3 - 1 + 1)) + 1;
        show_qw_advert(ad_number);
    </script>
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
