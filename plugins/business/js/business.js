business = {
    mapSelector: '.business-details > .body > .map',

    init: function() {
        business.addEvents();
    },

    addEvents: function() {
        Hook.register('googleapis.maps.callbacks', business.initMap);

        $(document).on('click', '.business-upload-image-input-button', function (e) {
            console.log(e.target);
            $(e.target).closest('.business-upload-image-input-button').siblings('.business-upload-image-input').click();
        });

        $(document).on('change', '.business-upload-image-input', function (e) {
            business.changeImage(e.target);
        });
    },

    initMap: function () {
        let mapContainer = document.querySelector(business.mapSelector);
        if(mapContainer) {
            let map = new google.maps.Map(document.querySelector(business.mapSelector), {
                zoom: 8,
                center: {lat: -34.397, lng: 150.644}
            });

            let geocoder = new google.maps.Geocoder();
            let address = mapContainer.getAttribute('data-address');
            business.geocodeAddress(geocoder, map, address);

            if (document.getElementById('autocomplete')) {
                let autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'), {types: ['geocode']});
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    fillInAddress();
                });
            }
        }
    },

    geocodeAddress: function (geocoder, resultsMap, address) {
        geocoder.geocode({'address': address}, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                resultsMap.setCenter(results[0].geometry.location);
                let marker = new google.maps.Marker({
                    map: resultsMap,
                    position: results[0].geometry.location,
                    animation: google.maps.Animation.BOUNCE
                });
            }
        });
    },

    changeImage: function (input) {
        let container = $(input).closest('.business-details');
        let indicator = container.find('.image-upload-indicator');
        let image = container.find('.image');
        if (input.files.length) {
            if(input.files[0]['type'].match(/^image\//g), ['image/jpeg', 'image/png']) {
                let formData = new FormData();
                formData.append('id', $(container).data('id'));
                formData.append('image', input.files[0]);
                $.ajax({
                    url: baseUrl + 'business/image/upload?csrf_token=' + requestToken,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        indicator.fadeIn();
                    },
                    success: function (response) {
                        let data = JSON.parse(response);
                        if(data.status) {
                            $(image).attr('src', data.src);
                        }
                        indicator.fadeOut();
                    },
                    error: function (error) {
                        indicator.fadeOut();
                    }
                });
            } else {
                notify('Invalid Image', 'danger');
            }
        }
    }
};

function getBusinessFilters() {
    var filters = [];
    try {
        filters['s'] = $('#business-filter-search').val();
    } catch (e) {
    }
    try {
        filters['c'] = $('#business-filter-category').val();
    } catch (e) {
    }
    try {
        var t = $('#business-filter-type').val();
        if (t == 'f') {
            filters['f'] = 1;
        }
        if (t == 'm') {
            filters['m'] = 1;
        }
    } catch (e) {
    }
    return filters;
}

function businessGetBusinessesPage(filters) {
    var appends = '';
    if ('c' in filters) {
        appends += filters['c'] != 'all' ? '/category/' + filters['c'] : '';
        delete filters['c'];
    }
    if ('m' in filters) {
        appends += '/my-businesses';
        delete filters['m'];
    } else if ('f' in filters) {
        appends += '/featured';
        delete filters['f'];
    }
    if ('s' in filters) {
        if (filters['s'] == '') {
            delete filters['s'];
        }
    }
    if ('l' in filters) {
        if (filters['l'] == '') {
            delete filters['l'];
        }
    }
    if ('p0' in filters) {
        if (filters['p0'] == '') {
            delete filters['p0'];
        }
    }
    if ('p1' in filters) {
        if (filters['p1'] == '') {
            delete filters['p1'];
        }
    }
    var query = '';
    for (var i in filters) {
        query += '&' + i + '=' + filters[i];
    }
    query = query ? query.replace(/^&/g, '?') : query;
    var url = baseUrl + 'businesses' + appends + query;
    return loadPage(url);
}

function businessFilterFormSubmit(form) {
    var filters = [];
    try {
        filters['s'] = $(form).find('input[name="s"]').val();
    } catch (e) {
    }
    try {
        filters['l'] = $(form).find('input[name="l"]').val();
    } catch (e) {
    }
    try {
        filters['p0'] = $(form).find('input[name="p0"]').val();
    } catch (e) {
    }
    try {
        filters['p1'] = $(form).find('input[name="p1"]').val();
    } catch (e) {
    }
    try {
        filters['c'] = $(form).find('select[name="c"]').val();
    } catch (e) {
    }
    try {
        filters['f'] = $(form).find('input[name="f"]:checked').val();
    } catch (e) {
    }
    try {
        filters['m'] = $(form).find('input[name="m"]:checked').val();
    } catch (e) {
    }
    try {
        filters['i'] = $(form).find('input[name="i"]:checked').val();
    } catch (e) {
    }
    for (var i in filters) {
        if (typeof filters[i] === 'undefined') {
            delete filters[i];
        }
    }
    return businessGetBusinessesPage(filters);
}

$('.tabbed-panels .tabs').each(function () {
    var $active, $content, $links = $(this).find('a');
    $active = $($links.filter('[href="' + location.hash + '"]')[0] || $links[0]);
    $active.addClass('active');
    $content = $($active[0].hash);
    $links.not($active).each(function () {
        $(this.hash).hide();
    });
    $(this).on('click', 'a', function (e) {
        $active.removeClass('active');
        $content.hide();
        $active = $(this);
        $content = $(this.hash);
        $active.addClass('active');
        $content.show();
        e.preventDefault();
    });
});

function businessAddPhotos(input) {
    var files = input.files;
    if (files.length) {
        var imagesHTML = '';
        for (var i = 0; i < files.length; i++) {
            imagesHTML += '<div>' + files[i]['name'] + ' (' + businessFileSize(files[i]['size']) + ')</div>';
        }
        document.getElementById('business-form-images-container').innerHTML = imagesHTML;
    }
}

function businessShowContact(button) {
    var contact = $(button).data('contact');
    $(button).parent().html('<div class="contact-info">' + contact + '</div>');
    return false;
}

function businessFileSize(size) {
    var i = -1;
    var byteUnits = [' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];
    do {
        size = size / 1024;
        i++;
    } while (size > 1024);
    return Math.max(size, 0.1).toFixed(1) + byteUnits[i];
};

function updatebusinessBodyHeight() {
    try {
        $('#business > .business-body').css('height', parseInt($('#business [type=radio]:checked ~ label ~ .content').css('height').replace(/px/, '')) + 32 + 'px');
    } catch (e) {
    }
}

try {
    addPageHook('updatebusinessBodyHeight');
} catch (e) {
}

$(document).ready(function () {
    updatebusinessBodyHeight();
    $('#business > .business-body label').click(function () {
        updatebusinessBodyHeight();
    });
});

function businessFollow(button) {
    var id = $(button).data('id');
    var action = $(button).data('action');
    $.ajax({
        beforeSend: function () {
            $(button).attr('disabled', 'disabled');
        },
        url: baseUrl + 'business/follow?id=' + id + '&action=' + action,
        success: function (data) {
            $(button).removeAttr('disabled');
            data = JSON.parse(data);
            $(button).html(data.name);
            $(button).data('action', data.action);
            $('#fcount').text(data.count);
        }
    });

    return false;
}

function businessFavourite(favour) {
    var id = $(favour).data('id');
    var action = $(favour).data('action');
    $.ajax({
        beforeSend: function () {
            $(favour).attr('disabled', 'disabled');
        },
        url: baseUrl + 'business/favourite?id=' + id + '&action=' + action,
        success: function (data) {
            $(favour).removeAttr('disabled');
            data = JSON.parse(data);
            var reaction = data.action;
            if (reaction == 'unfavourite') {
                $('#faicon').removeClass('business-unfavourite');
                $('#faicon').addClass('business-favourite');
            }
            if (reaction == 'favourite') {
                $('#faicon').removeClass('business-favourite');
                $('#faicon').addClass('business-unfavourite')
            }
            $(favour).data('action', data.action);
            $('#facount').text(data.count);
        }
    });

    return false;

}


function addBusinessHours() {
    var counter = $('#addbutton').val();
    document.getElementById("business_checkbox").checked = false;
    counter = parseInt(counter);
    if (counter < 7) {
        var formValuedays = '<select class="form-control valid" name="val[visiting_hours_dayofweek_id][]"> <option value="Monday">Monday</option> <option value="Tuesday">Tuesday</option> <option value="Wednesday">Wednesday</option> <option value="Thursday">Thursday</option> <option value="Friday">Friday</option> <option value="Saturday">Saturday</option> <option value="Sunday">Sunday</option>  </select>';
        var formValuehours = '<select class="form-control valid" name="val[visiting_hours_hour_starttime][]"><option></option><option value="Closed">Closed </option><option value"00:00">12:00 AM </option><option value="00:30">12:30 AM </option><option value="01:00">01:00 AM </option><option value="01:30">01:30 AM </option><option value="02:00">02:00 AM </option> <option value="02:30">02:30 AM </option><option value="03:00">03:00 AM </option> <option value="03:30">03:30 AM </option><option value="04:00">04:00 AM </option><option value="04:30">04:30 AM </option><option value="05:00">05:00 AM </option><option value="05:30">05:30 AM </option><option value="06:00">06:00 AM </option><option value="06:30">06:30 AM </option><option value="07:00">07:00 AM </option><option value="07:30">07:30 AM </option><option value="08:00">08:00 AM </option><option value="08:30">08:30 AM </option><option value="09:00">09:00 AM </option><option value="09:30">09:30 AM </option><option value="10:00">10:00 AM </option><option value="10:30">10:30 AM </option><option value="11:00">11:00 AM </option><option value="11:30">11:30 AM </option><option value="12:00">12:00 PM </option><option value="12:30">12:30 PM </option><option value="13:00">01:00 PM </option><option value="13:30">01:30 PM </option><option value="14:00">02:00 PM </option><option value="14:30">02:30 PM </option><option value="15:00">03:00 PM </option><option value="15:30">03:30 PM </option><option value="16:00">04:00 PM </option><option value="16:30">04:30 PM </option><option value="17:00">05:00 PM </option><option value="17:30">05:30 PM </option><option value="18:00">06:00PM </option><option value="18:30">06:30 PM </option><option value="19:00">07:00 PM </option><option value="19:30">07:30 PM </option><option value="20:00">08:00 PM </option><option value="20:30">08:30 PM </option><option value="21:00">09:00 PM </option><option value="21:30">09:30 PM </option><option value="22:00">10:00 PM </option><option value="22:30">10:30 PM </option><option value="23:00">11:00 PM </option><option value="23:30">11:30 PM </option></select>';
        var formValueclose = '<select class="form-control valid" name="val[visiting_hours_hour_endtime][]"><option></option><option value="Closed">Closed </option><option value"00:00">12:00 AM </option><option value="00:30">12:30 AM </option><option value="01:00">01:00 AM </option><option value="01:30">01:30 AM </option><option value="02:00">02:00 AM </option> <option value="02:30">02:30 AM </option><option value="03:00">03:00 AM </option> <option value="03:30">03:30 AM </option><option value="04:00">04:00 AM </option><option value="04:30">04:30 AM </option><option value="05:00">05:00 AM </option><option value="05:30">05:30 AM </option><option value="06:00">06:00 AM </option><option value="06:30">06:30 AM </option><option value="07:00">07:00 AM </option><option value="07:30">07:30 AM </option><option value="08:00">08:00 AM </option><option value="08:30">08:30 AM </option><option value="09:00">09:00 AM </option><option value="09:30">09:30 AM </option><option value="10:00">10:00 AM </option><option value="10:30">10:30 AM </option><option value="11:00">11:00 AM </option><option value="11:30">11:30 AM </option><option value="12:00">12:00 PM </option><option value="12:30">12:30 PM </option><option value="13:00">01:00 PM </option><option value="13:30">01:30 PM </option><option value="14:00">02:00 PM </option><option value="14:30">02:30 PM </option><option value="15:00">03:00 PM </option><option value="15:30">03:30 PM </option><option value="16:00">04:00 PM </option><option value="16:30">04:30 PM </option><option value="17:00">05:00 PM </option><option value="17:30">05:30 PM </option><option value="18:00">06:00PM </option><option value="18:30">06:30 PM </option><option value="19:00">07:00 PM </option><option value="19:30">07:30 PM </option><option value="20:00">08:00 PM </option><option value="20:30">08:30 PM </option><option value="21:00">09:00 PM </option><option value="21:30">09:30 PM </option><option value="22:00">10:00 PM </option><option value="22:30">10:30 PM </option><option value="23:00">11:00 PM </option><option value="23:30">11:30 PM </option></select>';
        var formValue = formValuedays + formValuehours + formValueclose;
        var divId = Math.floor((Math.random() * 1000) + 1);
        var formhead = '<div id=' + divId + '>';
        var removeButton = '<i style="color:#ff0000;" class="ion-android-remove-circle" id="remove_hr" onclick="return RemoveThisBusinessHours(this)" data-id=' + divId + '> </i> </div>';
        var openingHour = formhead + formValue + removeButton;
        //document.getElementById("addmorebusiness").innerHTML = (openingHour);
        $("#addmorebusiness").append(openingHour);
        counter += 1;
        $('#addbutton').val(counter);
    } else {
        var errorcontainer = '<span class="alert alert-warning"> You cannot select more than seven days</span>';
        // $("#addmorebusiness").append(errorcontainer).fadeIn("slow");
        document.getElementById("errorcontainer").innerHTML = errorcontainer;
    }

    return false;

}


function removeBusinessHours() {
    document.getElementById("addmorebusiness").innerHTML = '';
    $('#addbutton').val('0');
    document.getElementById("errorcontainer").innerHTML = '';
}

function RemoveThisBusinessHours(fieldId) {
    var id = $(fieldId).data('id');
    $("#" + id).remove();
    document.getElementById("errorcontainer").innerHTML = '';
    var counter = $('#addbutton').val();
    counter = parseInt(counter) - 1;
    $('#addbutton').val(counter);
}

function fillInAddress() {
    var place = autocomplete.getPlace();

    for (var component in component_form) {
        document.getElementById(component).value = "";
        document.getElementById(component).disabled = false;
    }

    for (var j = 0; j < place.address_components.length; j++) {
        var att = place.address_components[j].types[0];
        if (component_form[att]) {
            var val = place.address_components[j][component_form[att]];
            document.getElementById(att).value = val;
        }
    }
}

function businessCompare() {
    var atLeasttowIsChecked = $('input[name="compare[]"]:checked');
    var count = parseInt(atLeasttowIsChecked.length);
    var checkedValue = $("input[name='compare[]']:checked").map(function () {
        return $(this).val();
    }).get();
    if ((count > 1) && (count < 5)) {
        window.location.href = baseUrl + 'business/compare?id=' + checkedValue;
    } else {
        var error = 'Selected business must be greater than one and less than 5';
        document.getElementById("error-body").innerHTML = error;
        $('#error-modal').modal('show');
    }
    return false;

}

function businessAdminMember(button) {
    var id = $(button).data('id');
    var action = $(button).data('action');
    var business = $(button).data('business')
    $.ajax({
        beforeSend: function () {
            $(button).attr('disabled', 'disabled');
        },
        url: baseUrl + 'business/admin/business?id=' + id + '&action=' + action + '&business_id=' + business,
        success: function (data) {
            $(button).removeAttr('disabled');
            data = JSON.parse(data);
            $(button).html(data.name);
            $(button).data('action', data.action);
        }
    });

    return false;
}


business.init();

$(function() {
    try {
        addPageHook('business.init');
    } catch (e) {}
});