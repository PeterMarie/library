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
        <a href="#add_book_form" ><button>Add Book</button></a>
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
            <section id="add_book_form">
                <h2>Add Book</h2>
                <form>
                    <fieldset>
                        <legend for="file">Upload File</legend>
                        <input type="file" name="file" id="file_upload"/>
                    </fieldset>
                    <fieldset>
                        <legend for="title">Title</legend><input type="text" name="title" id="title" />
                    </fieldset>
                    <fieldset>
                        <legend for="new_tags">Tags</legend>
                        <input type="text" name="new_tag" id="new_tag" /><button disabled>Add tag</button>
                        <span id="tags"></span>
                    </fieldset>
                    <button type="submit">Add to Library</button>
                </form>
            </section>
        </section>
    </main>
    <footer>
        <span>Created by Peter and crown james c2021</span>
    </footer>
    <script src="script.js" type="text/javascript"></script>
</body>
</html>