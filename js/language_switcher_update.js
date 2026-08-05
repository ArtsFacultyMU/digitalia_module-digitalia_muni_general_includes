document.addEventListener("DOMContentLoaded", function() {
  let links = document.querySelectorAll("#block-islandora-muni-languageswitcher a");
  
  // language switche drop down
  let lang_form = document.getElementById("lang_dropdown_form_lang-dropdown-form");
  if (lang_form) {
    const url = new URL(lang_form.action, window.location.origin);
    url.searchParams.set("languageswitch", "1");
    lang_form.action = url.toString();
  }
  if (navigator.userAgent.toLowerCase().includes("firefox")) {
    let lang_select = document.getElementById("edit-lang-dropdown-select");
    lang_select.style.appearance = "button";
  }


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
