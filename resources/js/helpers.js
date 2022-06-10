window.Addr = require('./helpers/addr');
window.Elem = require('./helpers/elem');
window.ChipElem = require('./helpers/chipElem');
window.Toast = require('./helpers/toast');
window.Calendar = require('./helpers/calendar');
window.Pagination = require('./helpers/pagination');

try {
    window.toast = new Toast();
    $("#autoToast").toast("show");
    window.tooltipList = $('[data-bs-toggle="tooltip"]').each(function(){
        return new bootstrap.Tooltip($(this));
    });
    window.popoverList = $('[data-bs-toggle="popover"]').each(function () {
        return new bootstrap.Popover($(this));
    });
} catch (error) {
    console.error(error);
}