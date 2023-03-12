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

$("#searchbar").on("change keyup", function (e) {
  //$("#search_results").text("Worked");
});
