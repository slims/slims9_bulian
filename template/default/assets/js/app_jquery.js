'use strict';

$(document).ready(() => {
    // 65x83
    var images = $('.fit-height');
    $.each(images, (i, v) => {
        var width = $(v).width(),
            height = (width * 83) / 65;
        // console.log(height);
        $(v).height(height)
    })

    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    $('.add-to-chart').click(function (e) {
        let biblioId = $(this).attr('data-biblio')
        $.ajax({
            method: 'POST',
            url: 'index.php?p=member',
            data: {biblio: [biblioId], callback: 'json'}
        })
            .done(function (data) {
                if (data.status) {
                    toastr.success(data.message)
                } else {
                    toastr.error(data.message)
                }
                $('#count-basket').text(data.count)
            })
            .fail(function (msg) {
                console.error('ERROR!', msg)
                toastr.error(msg.responseJSON.message, '', {
                    timeOut: 2000,
                    onHidden: function () {
                        window.location.replace('index.php?p=member')
                    }
                })
            })
    })

    $('.collapse-detail')
        .on('shown.bs.collapse', e => {
            let id = e.target.getAttribute('id')
            $(`#btn-${id} i`).removeClass('fa-angle-double-down').addClass('fa-angle-double-up')
        })
        .on('hidden.bs.collapse', e => {
            let id = e.target.getAttribute('id')
            $(`#btn-${id} i`).removeClass('fa-angle-double-up').addClass('fa-angle-double-down')
        })
});

// remove &nbsp in pagging
$('.biblioPaging .pagingList').html(function (i, h) {
    return h.replace(/&nbsp;/g, '');
});
