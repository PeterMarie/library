function ajax(url, cFunction) {
  if (window.XMLHttpRequest) {
    // code for modern browsers
    xhttp = new XMLHttpRequest();
  } else {
    // code for old IE browsers
    xhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      cFunction(this);
    }
  };
  xhttp.open("GET", url, true);
  /* xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");*/
  xhttp.send();
}
function ajaxpost(url, cFunction, formData) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", url);
  xhr.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      cFunction(this);
    }
  };
  xhr.send(formData);
}

$("#searchbar").on("change keyup", async (e) => {
  let key = $("#searchbar").val().trim();

  if (/\S*/g.test(key)) {
    const response = await fetch("backend.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `task=search&key=${key}`,
    });

    const data = await response.text();
    $("#search_results").text(data);
  } else {
    //Do Nothing
  }
});

$(".customizible-items").on("change keyup", async (e) => {
  let key = $(this).val().trim();
  let data_src = $(this).data("src");

  if (/\S*/g.test(key)) {
    const response = await fetch("backend.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `task=retrieve&data=${data_src}&key=${key}`,
    });

    const data = await response.text();
    $(data_src).text(data);
  } else {
    //Do Nothing
  }
});

$("#add_book_form").on("submit", async (e) => {
  e.preventDefault;
  //Start form validataion
  if (
    $("#title").val() == "" ||
    document.getElementById("#file").files.length == 0 ||
    $("#category").val() <= 0 ||
    $(".authors").length <= 0 ||
    $(".tags").length <= 0
  ) {
    alert("Please fill all required fields");
  } else {
    let formdata = new FormData();
    formdata.append("file", document.getElementById("#file").files[0]);
    formdata.append("title", $("#title").val());
    formdata.append("subtitle", $("#subtitle").val());
    const authors = [];
    for (let i = 1; i <= $(".authors").length; i++) {
      let element_selector = ".authors:nth-child(" + i + ")";
      authors.push($(element_selector).data("id"));
    }
    formdata.append("authors", JSON.stringify(authors));
    const tags = [];
    for (let i = 1; i <= $(".tags").length; i++) {
      let element_selector = ".tags:nth-child(" + i + ")";
      tags.push($(element_selector).data("id"));
    }
    formdata.append("tags", JSON.stringify(tags));

    const response = await fetch("backend.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `task=add_book&data=${formdata}`,
    });

    const data = await response.text();
    $(data_src).text(data);
  }
});
