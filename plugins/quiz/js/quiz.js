
if(typeof quiz === 'undefined') { 
    quiz = {
        init: function() { 
          
			$('.add-new-answer').click(function(e) {
                //add and remove multiple input field
                let wrapper = $(".first-q-c-c");
                e.preventDefault();
				var now = new Date(Date.now());
				//console.log(now.getSeconds());
				var sec = now.getSeconds();
                   $(wrapper).append('<div class="field added-answer-field"><div class="left"><label class="control-label"></label></div><div class="right"><div class="right-answer-b" style="border: 1px solid #e7e4e4"><input type="text" name="val[answer]['+sec+']" class="form-control" value="" placeholder="" required><input type="checkbox" name="val[correct_answer]['+sec+']" id="correct-answer" value="yes" placeholder=""><label for="correct-answer">Correct Answer</label><a href="#" class="remove-answer">Remove Answer</a> </div></div></div>');
                
                $(wrapper).on("click",".remove-answer", function(e){
                    e.preventDefault();
                    $(this).parent('div').parent('div').parent('div').remove();
                });
                
            });
        }
    }
    $(function() {
		try {
			quiz.init();
			if(quiz) {
				addPageHook('quiz.init');
			}
		} catch (e) {}
	});
}
function publish_quiz(id) {
    var o = $("#on-button-" + id);
    var status = o.attr('data-status');
    var on = o.data('on');
    var off = o.data('off');
	o.css('opacity', '0.5');
	o.attr('disabled', 'disabled');
	o.addClass('quiz-disabled');
    if (status == 1) {
        //user want to unpublish
        o.removeClass('turned').html(on).attr('data-status', 0);
        type = 'off';
    } else {
        //user want to on
        o.addClass('turned').html(off).attr('data-status', 1);
        type = 'on'
    }

    $.ajax({
        url : baseUrl + 'quiz/publish?type=' + type + '&id=' + id + '&csrf_token=' + requestToken,
		 success : function() {
            window.location = window.location;
        },
    })
    return false;
}
