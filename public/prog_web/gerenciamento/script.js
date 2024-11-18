
function changePage(folder, url) {
    // Ensure the URL starts with a leading slash
    if (url.charAt(0) !== '/') {
        url = '/' + url;
    }
    
    // Navigate to the specific folder and URL
    window.location.href = window.location.origin + '/' + folder + url;
}
