function upload_album_photos(id) {
    var form = $("#photos-list .upload-photo form");
    var indicator = $("#photos-list .upload-photo .indicator");
    var input = form.find('input[type=file]');
    var imageInput = document.getElementById("album-photo-upload-input");
    if(imageInput.files.length > maxPhotosUpload) {
        alert('Max no of images allowed is ' + maxPhotosUpload);
        notifyError('Max no of images allowed is ' + maxPhotosUpload);
        indicator.fadeOut();
        input.val('');
        return false;
    }
    var container = $("#photos-list .upload-photo");
    form.ajaxSubmit({
        url: baseUrl + 'photo/album/upload?id=' + id,
        beforeSend: function() {
            indicator.show();
        },
        success: function(data) {
            var json = jQuery.parseJSON(data);
            if(json.status == 0) {
                notifyError(json.message);
            } else {
                notifySuccess(json.message);
                $(json.photos).insertAfter(container);
            }
            input.val('');
            indicator.fadeOut();
        },
        uploadProgress: function(event, position, total, percent) {

            var uI = indicator.find('span');
            uI.html(percent + '%').fadeIn();
            if(percent == 100) {
                uI.fadeOut().html("0%")
            }
        },
        error: function() {
            notifyError(form.data('error'));
            indicator.fadeOut();
            input.val('');
        }
    })
}

function upload_photos(input) {
    if(input) {
        var form = $(input).closest('form');
        input = form.find('input[type=file]');
    } else {
        var form = $("#photos-list .upload-photo form");
        input = form.find('input[type=file]');
    }
    var indicator = $("#photos-list .indicator");
    var imageInput = document.getElementById("photo-upload-input");
    if(imageInput.files.length > maxPhotosUpload) {
        alert('Max no of images allowed is ' + maxPhotosUpload);
        notifyError('Max no of images allowed is ' + maxPhotosUpload);
        indicator.fadeOut();
        input.val('');
        Hook.fire('photofilter.input.unload', null, [input.get(0)]);
        return false;
    }
    var container = $("#photos-list .upload-photo");
    var submit = function() {
        Hook.fire('photofilter.input.save', null, []);
        form.ajaxSubmit({
            url: baseUrl + 'photo/upload',
            beforeSend: function() {
                indicator.show();
            },
            success: function(data) {
                var json = jQuery.parseJSON(data);
                if(json.status == 0) {
                    notifyError(json.message);
                } else {
                    notifySuccess(json.message);
                    if(container.length) {
                        $(json.photos).insertAfter(container);
                    } else {
                        $('#photos-list').prepend($(json.photos));
                    }
                }
                Hook.fire('photofilter.input.unload', null, [input.get(0)]);
                input.val('');
                indicator.fadeOut();
            },
            uploadProgress: function(event, position, total, percent) {
                var uI = indicator.find('span');
                uI.html(percent + '%').fadeIn();
                if(percent == 100) {
                    uI.fadeOut().html("0%")
                }
            },
            error: function() {
                notifyError(form.data('error'));
                indicator.fadeOut();
                Hook.fire('photofilter.input.unload', null, [input.get(0)]);
                input.val('');
            }
        })
    };
    Hook.fire('photofilter.input.load', null, [input.get(0), {saveCallback: submit}]) ? void(0) : submit();
}

function uploadUrlImage() {
    var link = $(".urlLink");
    var linkValue = link.val();
    var loading = $(".indicatorUrl");
    var filtering = $(".indicatorUrlFilter");
    if(link !== '') {
        var submit = function () {
            var data = {link: linkValue, csrf_token: requestToken};
            data = Hook.fire('photofilter.images', data, ['link']);
            $.ajax({
                url: baseUrl + 'photo/url/upload',
                method: 'POST',
                data: data,
                beforeSend: function() {
                    filtering.hide();
                    loading.show();
                },
                success: function(data) {
                    data = jQuery.parseJSON(data);
                    loading.hide();
                    if(data.status == 1) {
                        var container = $("#photos-list .upload-photo");
                        link.val("");
                        Hook.fire('photofilter.input.unload', null, [link.get(0)]);
                        $('#photo-modal').modal('hide');
                        if(container.length) {
                            $(data.photos).insertAfter(container);
                        } else {
                            $('#photos-list').prepend($(data.photos));
                        }
                        notifySuccess(data.message);
                    } else {
                        $('#photo-modal').modal('hide');
                        notifyError(data.message);
                    }
                },
                uploadProgress: function(event, position, total, percent) {
                    var uI = loading.find('span');
                    uI.html(percent + '%').fadeIn();
                    if(percent == 100) {
                        uI.fadeOut().html("0%")
                    }
                },
            });
        };
        var init = function() {
            $(".indicatorUrlFilter").show();
        };
        var cancel = function() {
            $(".indicatorUrlFilter").hide();
            $(".indicatorUrl").hide();
        };
        Hook.fire('photofilter.input.load', null, [link.get(0), {saveCallback: submit}]) ? void(0) : submit();
        Hook.fire('photofilter.input.load', null, [link.get(0), {initCallback: init, saveCallback: submit, cancelCallback: cancel}]) ? void(0) : submit();
    } else {
        var error = "The field must not be empty";
        notifyError(error);
    }
}

function delete_photo_album(m, u) {
    confirm.url(u, m);
    return false;
}

window.paginatingAlbumPhotos = false;

function paginate_album_photos() {
    var container = $("#photos-list");
    var id = container.data('id');
    var type = container.data('type');
    var offset = container.data('offset');
    var indicator = $("#photos-list-indicator");
    var indicatorImg = indicator.find('img');
    indicatorImg.fadeIn();
    if(window.paginatingAlbumPhotos || indicator.css('display') == 'none') return false;
    window.paginatingAlbumPhotos = true;
    $.ajax({
        url: baseUrl + 'photo/album/photos/paginate?csrf_token=' + requestToken,
        data: {id: id, type: type, offset: offset},
        success: function(data) {
            var data = jQuery.parseJSON(data);
            //alert(data.photos);
            if(data.photos != '') {
                indicatorImg.fadeOut();
                container.append(data.photos);
                container.data('offset', data.offset);

            } else {
                indicator.hide();
            }
            window.paginatingAlbumPhotos = false;
        },
        error: function() {
            indicatorImg.fadeOut();
            window.paginatingAlbumPhotos = false;
        }
    });

    return false;
}

function paginate_photo_albums() {
    var container = $("#photo-album-lists");

    var id = container.data('type-id');
    var type = container.data('type');
    var offset = container.data('offset');
    var category = container.data('category')
    var link = encodeURI(container.data('link')) ? encodeURI(container.data('link')) : container.data('link');
    var indicator = $("#photos-list-indicator");
    var indicatorImg = indicator.find('img');
    indicatorImg.fadeIn();
    if(window.paginatingAlbumPhotos || indicator.css('display') == 'none') return false;
    window.paginatingAlbumPhotos = true;
    $.ajax({
        url: baseUrl + 'photo/album/paginate?csrf_token=' + requestToken,
        data: {id: id, type: type, offset: offset, category: category, link: link},
        success: function(data) {
            var data = jQuery.parseJSON(data);
            //alert(data.photos);
            if(data.albums != '') {
                indicatorImg.fadeOut();
                container.append(data.albums);
                container.data('offset', data.offset);

            } else {
                indicator.hide();
            }
            window.paginatingAlbumPhotos = false;
        },
        error: function() {
            indicatorImg.fadeOut();
            window.paginatingAlbumPhotos = false;
        }
    });

    return false;
}

function open_photo_viewer(obj) {
    var modal = $('#photoViewer');
    modal.modal('show');
    load_viewer_photo(obj);
}

function delete_photo(id, url) {
    var modal = $('#photoViewer');
    modal.modal('hide');
    var obj = $('#request-uri');
    var request_uri = obj.data('request-uri');
    url = request_uri && typeof request_uri !== 'undefined' ? url + '?link=' + request_uri : url;
    confirm.url(url);
    return false;
}

function make_photo_dp(id, url) {
    var modal = $('#photoViewer');
    modal.modal('hide');
    confirm.url(url);

    return false;
}

window.currentPageUrl = '';

function load_viewer_photo(obj) {
    var modal = $('#photoViewer');
    var image = obj.data('image');
    var id = obj.data('id');
    var photoPane = modal.find('.viewer-left');
    var contentPane = modal.find('.viewer-right');
    photoPane.html("<img src='" + image + "'/> ");
    contentPane.html('');
    if(id == undefined || id == '') {
        contentPane.hide();
        photoPane.css('width', '100%')
    } else {
        contentPane.show();
        photoPane.css('width', '70%');
        var timestamp = $.now();
        if(window.currentPageUrl) {
            //reset the window url bar

            window.history.pushState({}, timestamp + window.currentPageUrl, window.currentPageUrl);
        } else {
            window.currentPageUrl = window.location.href;
        }
        var url = baseUrl + 'photo/view/' + id;
        window.history.pushState({}, timestamp + url, url);
        $.ajax({
            url: baseUrl + 'photo/load?id=' + id + '&csrf_token=' + requestToken,
            cache: false,
            success: function(data) {
                var json = jQuery.parseJSON(data);
                if(json.status == 1) {
                    photoPane.html(json.left);
                    contentPane.html(json.right);
                    reloadInits();
                    Hook.fire('photo.viewer.loaded', null, [id, image]);
                }
            }
        })
    }


}

$(function() {
    $(window).scroll(function() {
        if(document.body.scrollHeight == document.body.scrollTop + window.innerHeight) {
            if($('#photos-list').length) {
                paginate_album_photos();
            }
            ;

            if($('#photo-album-lists').length) {
                paginate_photo_albums();
            }
        }
    });

    $(document).on('click', '.photo-viewer', function() {
        if(window.matchMedia('(max-width: 800px)').matches) {
            loadPage(baseUrl + 'photo/view/' + $(this).data('id'));
        } else {
            open_photo_viewer($(this));
        }
        return false;
    });
    $(document).on('click', '#view-photo-close', function() {
        history.go(-1);
        return false;
    });

    $(document).on('click', '#photoViewer .close', function() {
        var timestamp = $.now();
        window.history.pushState({}, timestamp + window.currentPageUrl, window.currentPageUrl);
        //window.location.href = window.currentPageUrl;
    });

    $('#photoViewer').on('hidden.bs.modal', function(e) {
        // do something...
        var timestamp = $.now();
        window.history.pushState({}, timestamp + window.currentPageUrl, window.currentPageUrl);
    });

    $(document).on('keydown', 'body', function(e) {
        if((e.keyCode || e.which) == 37) {
            // do something
            if($("#photoViewer .nav-left a").length) load_viewer_photo($(".nav-left a"))
        }
        // right arrow
        if((e.keyCode || e.which) == 39) {
            // do something
            if($("#photoViewer .nav-right a").length) load_viewer_photo($(".nav-right a"))
        }
    })

})

function remove_private_photo_user(e) {
    var id = $(e).data('id');
    var spanId = $('.private-photo-user-' + id);
    spanId.html('');
    return false;
}