		$(document).ready(function(){	
			$("#slider").easySlider({
				auto: true, 
				continuous: true
			});
			$('#mycarousel').jcarousel();
			$("#btn_search_submit").click(
				function() {
					$("#frm_search").submit();
				}
			);
        });
