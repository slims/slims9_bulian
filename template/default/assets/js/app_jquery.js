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

    $('.add-to-chart-button').click(function (e) {
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

    $('.bookMarkBook').click(function(e){
        e.preventDefault()
        if ($(this).hasClass('bg-success')) return;

        let id = $(this).data('id')
        $.post('index.php?p=member&sec=bookmark', {bookmark_id: id, callback: 'json'}, (res,state,http) => {
            let classAttr = $(this).data('detail') === undefined ? 'bg-success text-white rounded-lg' : 'bg-success text-white rounded-lg px-2 py-1'
            $(this).removeClass('text-secondary').addClass(classAttr)
            $('#label-' + id).html(res.label)
            toastr.success(res.message)
        }).fail(function(state){
            toastr.error(state.responseJSON.message, '', {
                timeOut: 2000,
                onHidden: function() {
                    window.location.replace('index.php?p=member&destination=' + encodeURIComponent(window.location.href + '#card-' + id))
                }
            })
        })
    })

    $('a[data-target="#mediaSocialModal"]').click(function(){
        let id = encodeURIComponent($(this).data('id'))
        let title = encodeURIComponent($(this).data('title').replace(/<\/?[^>]+(>|$)/g, "").replace(/\"|\'/i, ''))
        $('#mediaSocialModalBody').html(`<iframe src="?p=sharelink&id=${id}&title=${title}" class="w-100" style="height: 5.5rem"></iframe>`)
    })

    let oembed = $('oembed')

    if (oembed.length > 0) {
        oembed.each(function(index,el){
            let urlSrc = $(el).attr('url').replace('watch?v=', 'embed/')
            $('figure.media').append('<iframe style="width: 100%; height: '+(window.innerHeight - 200)+'px" src="' + urlSrc + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>')  
        })
    }

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
