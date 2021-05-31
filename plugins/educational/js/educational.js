$(document).on('click','#expandBook',function(){
    let obj = $(this);
    $("#readModal").modal("show");
    if($("#readModal #area").length){
        try {
            /*var book = ePub(obj.data('url'));
            //var rendition = book.renderTo("area", {width: 600, height: 400});
            var rendition = book.renderTo("area");
            var displayed = rendition.display();*/
            var params = URLSearchParams && new URLSearchParams(document.location.search.substring(1));
            var url = params && params.get("url") && decodeURIComponent(params.get("url"));
            var currentSectionIndex = (params && params.get("loc")) ? params.get("loc") : undefined;

            // Load the opf
            //var book = ePub(url || "https://s3.amazonaws.com/moby-dick/moby-dick.epub");
            var book = ePub(url || obj.data('url'));
            var rendition = book.renderTo("viewer", {
                width: "100%",
                height: 600,
                spread: "always"
            });

            rendition.display(currentSectionIndex);

            book.ready.then(() => {

                var next = document.getElementById("next");

                next.addEventListener("click", function(e){
                    book.package.metadata.direction === "rtl" ? rendition.prev() : rendition.next();
                    e.preventDefault();
                }, false);

                var prev = document.getElementById("prev");
                prev.addEventListener("click", function(e){
                    book.package.metadata.direction === "rtl" ? rendition.next() : rendition.prev();
                    e.preventDefault();
                }, false);

                var keyListener = function(e){

                    // Left Key
                    if ((e.keyCode || e.which) == 37) {
                        book.package.metadata.direction === "rtl" ? rendition.next() : rendition.prev();
                    }

                    // Right Key
                    if ((e.keyCode || e.which) == 39) {
                        book.package.metadata.direction === "rtl" ? rendition.prev() : rendition.next();
                    }

                };

                rendition.on("keyup", keyListener);
                document.addEventListener("keyup", keyListener, false);

            })

            var title = document.getElementById("title");

            rendition.on("rendered", function(section){
                var current = book.navigation && book.navigation.get(section.href);

                if (current) {
                    var $select = document.getElementById("toc");
                    var $selected = $select.querySelector("option[selected]");
                    if ($selected) {
                        $selected.removeAttribute("selected");
                    }

                    var $options = $select.querySelectorAll("option");
                    for (var i = 0; i < $options.length; ++i) {
                        let selected = $options[i].getAttribute("ref") === current.href;
                        if (selected) {
                            $options[i].setAttribute("selected", "");
                        }
                    }
                }

            });

            rendition.on("relocated", function(location){
                console.log(location);

                var next = book.package.metadata.direction === "rtl" ?  document.getElementById("prev") : document.getElementById("next");
                var prev = book.package.metadata.direction === "rtl" ?  document.getElementById("next") : document.getElementById("prev");

                if (location.atEnd) {
                    next.style.visibility = "hidden";
                } else {
                    next.style.visibility = "visible";
                }

                if (location.atStart) {
                    prev.style.visibility = "hidden";
                } else {
                    prev.style.visibility = "visible";
                }

            });

            rendition.on("layout", function(layout) {
                let viewer = document.getElementById("viewer");

                if (layout.spread) {
                    viewer.classList.remove('single');
                } else {
                    viewer.classList.add('single');
                }
            });

            window.addEventListener("unload", function () {
                console.log("unloading");
                this.book.destroy();
            });

            book.loaded.navigation.then(function(toc){
                var $select = document.getElementById("toc"),
                    docfrag = document.createDocumentFragment();

                toc.forEach(function(chapter) {
                    var option = document.createElement("option");
                    option.textContent = chapter.label;
                    option.setAttribute("ref", chapter.href);

                    docfrag.appendChild(option);
                });

                $select.appendChild(docfrag);

                $select.onchange = function(){
                    var index = $select.selectedIndex,
                        url = $select.options[index].getAttribute("ref");
                    rendition.display(url);
                    return false;
                };

            });

        }catch (e) {
            console.log(e);
        }
    }
   return false;
});

$(document).on('click','#donwload-book',function(){
    var id = $(this).data('id');
    $.ajax({
        url : baseUrl + "books/ajax?action=dcount&id="+id,
        succss:function(){

        }
    });
});

function closeBookModal(){
    $("#readModal").hide();
    $("#readModal").modal("hide");
    //alert('ehre');
    return false;
}

//Rating  Start

var Books = function (){
    this.ratingMeter = function(len){
        //alert(len);
        for(var j=1;j < 6;j++){
            $('.star'+j).removeClass('background-rating');
        }
        var i = 1;
        do{
            $('.star'+i).addClass('background-rating');
            i++;
        }
        while( len >= i);
        return true;
    }

    this.ratingStart = function (){
        var o =$('.books-rating-container');
        var status = o.data('status');
        if(status == 'yes'){
            var rating = o.data('myrating');
            this.ratingMeter(rating)
        }
        return true;
    }


};
$(function(){
    var bk = new Books;
    bk.ratingStart();
});
$(document).on('click','.rating-books',function(){
    var rating = $(this).data('id');
    //console.log('rating is '+rating);
    var holders = $('.books-rating-container');
    holders.attr('data-status','yes');
    holders.attr('data-myrating',rating);
    $('#myBookrating').val(rating);
    $('#RatingStatus').val('yes');
    var bk = new Books;
    bk.ratingMeter(rating);
    //insert rating into database
    var type = holders.data('type');
    var typeid = holders.data('typeid');
    $.ajax({
        url : baseUrl + "rating/books?type="+type+ "&type_id="+typeid+"&rating="+rating,
        succss:function(){

        }
    })
});

$(document).on('mouseover','.rating-books',function(){
    var len = $(this).data('id');
    var bk = new Books;
    bk.ratingMeter(len);
});


$(document).on('mouseout','.rating-books',function(){
    var o = $('#RatingStatus');
    var status = o.val();
    if(status == 'yes'){
        var rating = o.data('myrating');
        var r = $('#myBookrating').val();
        var bk = new Books;
        bk.ratingMeter(r);
        // console.debug(r);
    }else{
        for(var j=1;j < 6;j++){
            $('.star'+j).removeClass('background-rating');
        }
    }

});
//Rating End