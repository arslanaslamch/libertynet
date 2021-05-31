function getstarted_show_avatar() {
    var image = document.getElementById("getstarted-image-input");
    for (i = 0; i < image.files.length; i++) {
        if (typeof FileReader != "undefined") {

            var reader = new FileReader();
            reader.onload = function (e) {
                var img = $("#getstarted-avatar");
                img.css('background-image', 'url(' + e.target.result + ')');
            }
            reader.readAsDataURL(image.files[i]);
        }
    }
}

Hook.register('ajax.form.submit.success', function (result, form, request, response) {
    if (form.id === 'get-started-form') {
        response = JSON.parse(response);
        console.log(form.id, response, typeof response);
        if (!response.status) {
            $('#get-started-form > .steps > ul > li > a')[response.step].click();
        }
    }
});

Hook.register('page.reload.init', function () {

    let form = $('#get-started-form:not(.wizard)');

    if(typeof form.steps === 'function') {
        form.steps({
			labels: {
				current: getstartedPhrases && getstartedPhrases['current-step'],
				pagination: getstartedPhrases && getstartedPhrases['pagination'],
				finish: getstartedPhrases && getstartedPhrases['finish'],
				next: getstartedPhrases && getstartedPhrases['next'],
				previous: getstartedPhrases && getstartedPhrases['previous'],
				loading: getstartedPhrases && getstartedPhrases['loading']
			},
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            stepsOrientation: "vertical",
            onStepChanging: function (event, currentIndex, newIndex) {
                // form.validate().settings.ignore = ":disabled,:hidden";
                return form.valid();
            },
            onFinishing: function (event, currentIndex) {
                // form.validate().settings.ignore = ":disabled";
                return form.valid();
            },
            onFinished: function (event, currentIndex) {
                form.submit();
            }
        });
    }

    form.show();

    let width = 920;
    let height = 920;
    $('#getstarted-image-input input[name="avatar"]').awesomeCropper({
        width: width,
        height: height,
        debug: false
    }, 10000);
});
