document.addEventListener("DOMContentLoaded", function() {
  let links = document.querySelectorAll("#block-islandora-muni-languageswitcher a");

  // arnenovak cinematicbrno and digilib have different block id
  if (links.length == 0) {
    links = document.querySelectorAll("#block-languageswitcher a");
  }

  links.forEach(link => {
    let url = new URL(link.href); 
    if (url.searchParams.has('languageswitch')) {
      url.searchParams.set('languageswitch', '1');
    } else {
      url.searchParams.append('languageswitch', '1');
    }
    link.href = url.toString(); 
  });
});
