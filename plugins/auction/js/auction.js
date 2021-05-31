function auction_form_list_url() {
	var form = $('#auction-list-search');
	var v = form.find('input[type=text]').val();
	var cat = $("#auction-category-list").val();

	var type = form.find('.auction-type-input').val();
	var filter = $("#auction-filter-select").val();
	var url = baseUrl + 'auctions?term=' + v + "&category=" + cat + "&type=" + type + '&filter=' + filter;

	return url;
}

function auction_submit_search(t) {
	url = auction_form_list_url();
	loadPage(url);
	return false;
}

function auction_list_change_category(t) {
	url = auction_form_list_url();
	loadPage(url);
}

function advanced_search() {
	$('.advance-search').hide();
	$('#city').hide();
	$('#state').hide();
	$('.advanced-s').click(function(e) {
		$('.advance-search').toggle();
	});
	$('.select-option').change(function() {

		var option = $('.select-option').val();
		if(option == 'City') {
			$('#city').show();
			$('#country').hide();
			$('#state').hide();
		}
		else if(option == 'State') {
			$('#state').show();
			$('#country').hide();
			$('#city').hide();
		}
		else {
			$('#country').show();
			$('#state').hide();
			$('#city').hide();
		}

	});

}

function add_cart_val() {
	$('.qty').each(function() {
		var total;
		var price;
		var qty;

		price = $('.cart-price-val').val();
		$(this).blur(function(e) {
			var total_round = $('#h-total').val();
			qty = $(this).val();
			total = qty * price;
			$(this).closest("tr").find(".cart-total").text(total);
			$(this).closest("tr").find(".cart-total").each(function(index, element) {
				total -= price;
				total_round = parseInt(total_round) + parseInt(total);

				$('.total-auction').text(total_round);
				$('.g-total').text(total_round);
				$('.g-total2').attr('value', total_round);
			});
			//

		});


	});
}

function add_feature() {
	$(".add-feature").each(function() {
		$(this).click(function(e) {
			$.ajax({
				url: "",
				success: function() {
				},
				error: function() {
				}
			});
		});

	});
}

function grand_total() {
	$('tr').each(function(index, element) {
		var total;
		total += $('tr').find('.cart-total').val();
		$('.total-auction').text(total);

	});
}

function validate_bid(price) {
	price += 1;
	var bidval = $("#bidval").val();
	var id = $("#auction-id").val();
	if(bidval < price) {
		alert("Your Bid Must be greater than or equal to " + price);
	}
	else {
		//alert("ready to bid");
		$.ajax({
			url: baseUrl + 'auction/bid?price=' + bidval + '&id=' + id + '&status=bid',
			success: function(data) {
				window.location.href = baseUrl + "auction/detail?id=" + id + "&type=bid&success=1";
			},
			error: function(data) {
			}
		});
	}
}

function update_my_bid(price) {
	price += 1;
	var bidval = $("#updateBid").val();
	var id = $("#auction-id").val();
	if(bidval < price) {
		alert("Your Bid Must be greater than or equal to " + price);
	}
	else {
		$.ajax({
			url: baseUrl + 'auction/bid?price=' + bidval + '&id=' + id + '&status=update',
			success: function(data) {
				window.location.href = baseUrl + "auction/detail?id=" + id + "&type=update&sucess=1";
			},
			error: function(data) {
			}
		});
	}
}

function approve_auction() {

}

function validate_offer(price) {
	var offer = $("#offer-price").val();
	var id = $("#auction-id").val();
	if(offer < price) {
		alert("Your offer Mustbe greater than or equal to " + price);
	}
	else {
		$.ajax({
			url: baseUrl + 'auction/bid?price=' + offer + '&id=' + id + '&status=offer',
			success: function(data) {
				window.location.href = baseUrl + "auction/detail?id=" + id + "&type=offer&success=1";
			},
			error: function(data) {
			},
		});

	}
}

function module_date() {
	if($('#show-date').val()) {
		$(".endtime").countdown($('#show-date').val(), function(event) {
			$(this).text(
				event.strftime('%D days %H:%M:%S')
			);
		});
	}
}

function timer() {
	var year = $('#auction-year').val();
	var month = $('#auction-month').val();
	if($('#auction-date').val()) {
		$("#timer").countdown($('#auction-date').val(), function(event) {
			$('#days').text(
				event.strftime('%D'));
			$('#hours').text(
				event.strftime('%H'));
			$('#mins').text(
				event.strftime('%M'));
			$('#secs').text(
				event.strftime('%S')
			);
		});
	}
}

function showMore() {
	$('.showmore').jTruncate({
		length: 200,
		moreText: "Read More",
		lessText: "Read Less",
		ellipsisText: "...",
	});
}

function showHide() {

}

function auctionInit() {
	$('.bxslider').bxSlider();
	advanced_search();
	$('[data-toggle="datepicker"]').datepicker({
		format: 'yyyy/mm/dd'
	});
	$(function() {
		$('[data-toggle="tooltip"]').tooltip()
	})
	showMore();
	add_cart_val();
	timer();
	module_date();
	grand_total();
	$('#hides').jTruncate({
		length: 10,
		moreText: "",
		lessText: "",
		ellipsisText: "...",
	});
}

$(document).ready(function(e) {
	auctionInit();
	try {
		addPageHook('auctionInit');
	} catch(e) {

	}
});

function auction_sold(c) {
	var user = $(c).data('user');
	var type = $(c).data('type');
	var auction = $(c).data('auction');
	var process = $(c).data('process');
	var bid = $(c).data('bid');
	var bidType = $(c).data('bid_type');
	if(process == 'oneTime') {
		$.ajax({
			beforeSend: function() {
				$(c).attr('disabled', 'disabled');
			},
			url: baseUrl + 'auction/approve?auction-id=' + auction + '&user_id=' + user + '&type=' + type + '&method=' + process + '&bid=' + bid + '&bid_type=' + bidType,
			success: function(data) {
				$(c).removeAttr('disabled');
				data = JSON.parse(data);
				if(data.qty > 1) {
					var input = '<input type="number" name="qty" id="qtysold" placeholder="Quantity Sold" /> <br>';
					var button = '<button data-bid="' + bid + '" data-process="qytTime" data-user="' + user + '" data-auction="' + auction + '" data-type="sold" type="button" class="btn-primary" onclick="return auction_sold(this)"> Click OK </button>';
					var htmlfield = input + button;
					document.getElementById("auction-sold-quantity").innerHTML = htmlfield;
				} else if((data.status == true) && (data.qty == 0)) {
					notifySuccess("Mark Sold Successfully");
					$('#sellModal').modal('hide');
					location.reload();
				} else if(data.status == false) {
					notifyError("Oops Something went wrong with the transaction");
				}
			}
		});
	}
	else {
		var qty = $('#qtysold').val();
		if(qty == null) {
			notifyWarning("Quantity Sold field can not be empty");
			return false;
		}
		$.ajax({
			beforeSend: function() {
				$(c).attr('disabled', 'disabled');
			},
			url: baseUrl + 'auction/approve?auction-id=' + auction + '&user_id=' + user + '&type=' + type + '&method=' + process + '&qty=' + qty + '&bid=' + bid + '&bid_type=' + bidType,
			success: function(data) {
				$(c).removeAttr('disabled');
				data = JSON.parse(data);
				if((data.status == true) && (data.qty == 0)) {
					notifySuccess("Mark Sold Successfully");
					$('#sellModal').modal('hide');
					location.reload();
				} else if(data.status == false) {
					notifyError(data.message);
				}
			}
		});
	}
	return false;
}