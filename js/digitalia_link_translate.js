if (document.documentElement.lang === 'en') {
  var links = document.getElementsByClassName('muni-menu-links');
  if (links.length > 0 && links[links.length-1].text == "Digitalia") {
    links[links.length-1].setAttribute("href", "https://digitalia.phil.muni.cz/en");
  }
}
