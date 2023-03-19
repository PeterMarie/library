<?php

?>
<!doctype HTML>
<html>
<head>
    <title>Library Home</title>
    <link rel="stylesheet" href="css/style.css" type="text/css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <h1>Personal Library</h1>
        <a href="#add_book" ><button>Add Book</button></a>
    </header>
    <main>
        <section>
            <form>
                <input type="text" id="searchbar" name="searchbar" />
            </form>
            <div id="search_results"></div>
        </section>
        <section>
            <aside id="tags_aside">
                <span id="tag_header">Tags</span>
            </aside>
            <section id="recents">

            </section>
            <section id="results">

            </section>
            <section id="add_book">
                <h2>Add Book</h2>
                <form id="add_book_form">
                    <fieldset>
                        <legend for="file">Upload File</legend>
                        <input type="file" name="file" id="file_upload"/>
                    </fieldset>
                    <fieldset>
                        <legend for="title">Title</legend><input type="text" name="title" id="title" required />
                    </fieldset>
                    <fieldset>
                        <legend for="subtitle">Subtitle</legend><input type="text" name="subtitle" id="subtitle" />
                    </fieldset>
                    <fieldset>
                        <legend for="category">Category</legend>
                        <select name="category" id="category">
                            <option>Select Category</option>
                            <option value="1">Fiction</option>
                            <option value="2">Non-Fiction</option>
                            <option value="3">Journal Piece/Article</option>
                            <option value="4">Full Textbook</option>
                        </select>
                    </fieldset>
                    <fieldset>
                        <legend for="author">Author</legend><input type="text" class="customizible-items" name="author" id="author" data-src="authors" /><button disabled>Add Author</button>
                        <span id="authors"></span>
                    </fieldset>
                    <fieldset>
                        <legend for="new_tags">Tags</legend>
                        <input type="text" class="customizible-items" name="new_tag" id="new_tag" data-src="tags" /><button disabled>Add tag</button>
                        <span id="tags"></span>
                    </fieldset>
                    <button type="submit">Add to Library</button>
                </form>
            </section>
        </section>
    </main>
    <footer>
        <span>Created by Peter M. Ogwara &#169;2023</span>
    </footer>
    <script src="script.js" type="text/javascript"></script>
</body>
</html>