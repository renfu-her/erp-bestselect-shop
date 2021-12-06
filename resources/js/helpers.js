window.Addr = require('./helpers/addr');
window.Elem = require('./helpers/elem');
window.ChipElem = require('./helpers/chipElem');
window.Toast = require('./helpers/toast');
window.OrderFlow = require('./helpers/orderFlow');
window.P2p =require('./helpers/p2p');
window.Order =require('./helpers/order');


$(function(){
    window.toast = new Toast();
    $("#autoToast").toast("show");
    window.tooltipList = $('[data-bs-toggle="tooltip"]').each(function(){
        return new bootstrap.Tooltip($(this));
    });
});