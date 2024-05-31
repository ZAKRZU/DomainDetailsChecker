function setThemeCookie(theme) {
    const d = new Date();
    d.setTime(d.getTime() + (30*24*60*60*1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = "theme=" + theme + ";" + expires + ";path=/";
}

function getThemeCookie() {
    let name = "theme=";
    let decodedCookies = decodeURIComponent(document.cookie);
    let cookies = decodedCookies.split(';');
    for(let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i];
        while (cookie.charAt(0) == ' ') {
            cookie = cookie.substring(1);
          }
          if (cookie.indexOf(name) == 0) {
            return cookie.substring(name.length, cookie.length);
          }
    }
}

var themeSelector = document.getElementById('theme-selector');
themeSelector.onchange = () => {
    let theme = document.getElementsByTagName('html')[0].dataset;
    let selected = themeSelector.selectedOptions[0].value;

    if (selected === "light") {
        theme.bsTheme = 'light';
        setThemeCookie('light');
    } else if (selected === 'dark') {
        theme.bsTheme = 'dark';
        setThemeCookie('dark');
    }
}

var sendButton = document.getElementById('sendButton');
sendButton.onclick = () => {
    let loading = document.getElementById('loading');
    let btnValue = document.getElementById('loading-org');
    if (loading.classList.contains('d-none')) {
        loading.classList.remove('d-none');
        btnValue.classList.add('d-none');
        sendButton.classList.add('disabled');
    }
}

var copyToClipboardButton = document.getElementById('copyButton');
if (copyToClipboardButton) {
    copyToClipboardButton.onclick = () => {
        let value = document.getElementById('clipboardValue');
        value.select();
        value.setSelectionRange(0, 9999);
        navigator.clipboard.writeText(value.value);
    }
}

function loadTheme() {
    let theme = document.getElementsByTagName('html')[0].dataset;
    let selectedTheme = getThemeCookie();
    if (!selectedTheme)
        return;
    theme.bsTheme = selectedTheme;
    if (selectedTheme === "light") {
        themeSelector.selectedIndex = 1;
    } else if (selectedTheme === "dark") {
        themeSelector.selectedIndex = 0;
    }
}

(function() {
    loadTheme();
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
})();
