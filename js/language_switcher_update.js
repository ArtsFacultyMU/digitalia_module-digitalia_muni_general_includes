document.addEventListener("DOMContentLoaded", function() {
  const links = document.querySelectorAll("#block-languageswitcher a");

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
