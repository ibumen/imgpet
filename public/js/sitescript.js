/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function scrollTo(target) {
    $('html, body').stop().animate({
        scrollTop: target.offset().top
    }, 1000);

}

function addAlert(target, type, message) {

    target.html("<div class='alert alert-" + type + "'>" + message + "</div>");

}
