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
    $("#search_results").text("No data");
  }
});
