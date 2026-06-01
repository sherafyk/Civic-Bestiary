document.addEventListener('click', function (event) {
    const target = event.target;
    if (target && target.classList.contains('submitdelete')) {
        if (!window.confirm('Delete this Bestiary item?')) {
            event.preventDefault();
        }
    }
});
